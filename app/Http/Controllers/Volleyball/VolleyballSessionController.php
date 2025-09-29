<?php

namespace App\Http\Controllers\Volleyball;

use App\Http\Controllers\Controller;
use App\Models\VolleyballSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use App\Jobs\ProcessVolleyballSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VolleyballSessionController extends Controller
{
    public function showUploadForm()
    {
        return view('varsity.volleyball.upload');
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime|max:50000', // Max 50MB
        ]);

        $videoPath = $request->file('video')->store('volleyball_videos', 'public');

        $session = auth()->user()->volleyballSessions()->create([
            'video_path' => $videoPath,
            'status' => 'processing',
        ]);

        // Dispatch the job to the queue
        ProcessVolleyballSession::dispatch($session);

        return redirect()->route('varsity.dashboard')->with('success', 'Video uploaded successfully! It is now being processed in the background.');
    }

    public function showDashboard()
    {
        $sessions = auth()->user()->volleyballSessions()->latest()->get();

        return view('varsity.dashboard', compact('sessions'));
    }

    public function show(VolleyballSession $session)
    {
        // Ensure the user is authorized to view this session
        $this->authorize('view', $session);

        if (auth()->user()->role === 'coach') {
            return view('coach.sessions.show', compact('session'));
        } else {
            return view('varsity.volleyball.show', compact('session'));
        }
    }

    public function getProgress(VolleyballSession $session)
    {
        $this->authorize('view', $session);

        return response()->json([
            'progress' => $session->progress,
            'status' => $session->status,
            'stickman_animation_path' => $session->stickman_animation_path ? Storage::url($session->stickman_animation_path) : null,
        ]);
    }

    public function showDrillForm()
    {
        return view('varsity.drills.start');
    }

    public function uploadDrillVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|max:50000', // Max 50MB
            'action_type' => 'required|string|in:volleyball_spike,block,serve,setter,dive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $videoFile = $request->file('video');

        // Manually check MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $videoFile->getPathname());
        finfo_close($finfo);

        \Illuminate\Support\Facades\Log::info('Detected MIME type by finfo: ' . $mimeType);
        \Illuminate\Support\Facades\Log::info('Is MIME type in allowed list: ' . (in_array($mimeType, ['video/webm', 'video/mp4']) ? 'true' : 'false'));

        if (!in_array($mimeType, ['video/webm', 'video/mp4', 'video/x-matroska'])) {
            return response()->json(['errors' => ['video' => ['The video field must be a file of type: video/webm, video/mp4.']]], 422);
        }

        $videoPath = $videoFile->store('volleyball_drills', 'public');

        $session = auth()->user()->volleyballSessions()->create([
            'video_path' => $videoPath,
            'status' => 'processing',
            'action_type' => $request->action_type, // Store the selected action type
        ]);

        // Dispatch the job to the queue
        ProcessVolleyballSession::dispatch($session);

        return response()->json([
            'message' => 'Drill video uploaded successfully!',
            'redirect_url' => route('varsity.dashboard')
        ]);
    }

    public function storeManualFeedback(Request $request, VolleyballSession $session)
    {
        $this->authorize('update', $session); // Ensure coach is authorized to update session

        $request->validate([
            'manual_feedback' => 'required|string',
        ]);

        $session->update([
            'manual_feedback' => $request->manual_feedback,
        ]);

        return back()->with('success', 'Manual feedback saved successfully!');
    }
}