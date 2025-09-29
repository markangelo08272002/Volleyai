<?php

namespace App\Jobs;

use App\Models\VolleyballSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class ProcessVolleyballSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The volleyball session instance.
     *
     * @var \App\Models\VolleyballSession
     */
    public $session;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(VolleyballSession $session)
    {
        $this->session = $session;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->session->update(['progress' => 10]);

            // Step 1: Pose Estimation
            $absoluteVideoPath = Storage::disk('public')->path($this->session->video_path);
            $keypointsDir = storage_path('app/public/keypoints');
            if (!file_exists($keypointsDir)) mkdir($keypointsDir, 0777, true);

            $matplotlibCacheDir = storage_path('app/matplotlib_cache');
            if (!file_exists($matplotlibCacheDir)) mkdir($matplotlibCacheDir, 0777, true);

            $poseScript = base_path('python_scripts/pose_estimation.py');
            $poseProcess = Process::timeout(300)->env([
                'MPLCONFIGDIR' => $matplotlibCacheDir,
                'PYTHONHASHSEED' => '0'
            ])->run([base_path('venv/Scripts/python.exe'), $poseScript, $absoluteVideoPath, $keypointsDir]);

            if (!$poseProcess->successful()) {
                throw new \Exception('Pose estimation failed: ' . $poseProcess->errorOutput());
            }

            preg_match('/Keypoints saved to (.*?\.json)/', $poseProcess->output(), $matches);
            $keypointsJsonPath = $matches[1] ?? null;
            if (!$keypointsJsonPath) {
                throw new \Exception('Could not find keypoints JSON path in Python script output.');
            }

            $this->session->update([
                'keypoints_json_path' => str_replace(storage_path('app/public'), '', $keypointsJsonPath),
                'status' => 'keypoints_extracted',
                'progress' => 30,
            ]);

            // Step 1.5: Generate Stickman Animation
            $animationDir = storage_path('app/public/animations');
            if (!file_exists($animationDir)) mkdir($animationDir, 0777, true);
            $animationPath = $animationDir . '/' . $this->session->id . '_stickman.gif';

            $animationScript = base_path('python_scripts/generate_stickman_from_keypoints.py');
            $animationProcess = Process::timeout(300)->run([base_path('venv/Scripts/python.exe'), $animationScript, $keypointsJsonPath, $animationPath]);

            if (!$animationProcess->successful()) {
                // Log the error but don't fail the job, as the animation is not critical
                \Illuminate\Support\Facades\Log::error('Stickman animation failed: ' . $animationProcess->errorOutput());
            } else {
                $this->session->update([
                    'stickman_animation_path' => str_replace(storage_path('app/public'), '', $animationPath),
                ]);
            }

            // Step 2: Action Analysis
            $analysisDir = storage_path('app/public/analysis');
            if (!file_exists($analysisDir)) mkdir($analysisDir, 0777, true);

            $analysisScript = base_path('python_scripts/action_analysis.py');
            $actionType = $this->session->action_type ?? 'unknown'; // Get the action type, default to unknown
            $analysisProcess = Process::run([base_path('venv/Scripts/python.exe'), $analysisScript, $keypointsJsonPath, $analysisDir, $actionType]);

            if (!$analysisProcess->successful()) {
                throw new \Exception('Action analysis failed: ' . $analysisProcess->errorOutput());
            }

            $analysisOutput = $analysisProcess->output();
            preg_match('/LLM Prompt: ([\s\S]*)/s', $analysisOutput, $promptMatches);
            $llmPrompt = trim($promptMatches[1] ?? null);

            if (!$llmPrompt) {
                throw new \Exception('Could not extract LLM prompt from analysis script output.');
            }

            preg_match('/Analysis saved to (.*?\.json)/', $analysisOutput, $analysisMatches);
            $analysisJsonPath = $analysisMatches[1] ?? null;
            if ($analysisJsonPath && file_exists($analysisJsonPath)) {
                $analysisData = json_decode(file_get_contents($analysisJsonPath), true);
                $this->session->update([
                    'metrics' => $analysisData['metrics'] ?? null,
                    'status' => 'analysis_completed',
                    'progress' => 60,
                ]);
            }

            // Step 3: Get LLM Feedback
            $this->session->update(['progress' => 90]);
            $llmScript = base_path('python_scripts/llm_feedback.py');
            $llmProcess = Process::timeout(300)->run([base_path('venv/Scripts/python.exe'), $llmScript, escapeshellarg($llmPrompt)]);

            \Illuminate\Support\Facades\Log::info('LLM Script Output: ' . $llmProcess->output());
            \Illuminate\Support\Facades\Log::info('LLM Script Error Output: ' . $llmProcess->errorOutput());

            if (!$llmProcess->successful()) {
                throw new \Exception('LLM feedback generation failed: ' . $llmProcess->errorOutput());
            }

            $aiFeedback = trim($llmProcess->output());

            if (!$aiFeedback) {
                throw new \Exception('Could not extract feedback from LLM script output.');
            }

            // Extract the grade from the feedback
            $grade = null;
            if (preg_match('/Grade:\s*(\d+)/', $aiFeedback, $matches)) {
                $grade = (int)$matches[1];
            }

            $this->session->update([
                'ai_feedback' => $aiFeedback,
                'grade' => $grade,
                'status' => 'completed',
                'progress' => 100,
            ]);

        } catch (\Exception $e) {
            $this->session->update(['status' => 'failed', 'ai_feedback' => $e->getMessage(), 'progress' => $this->session->progress]);
        }
    }
}