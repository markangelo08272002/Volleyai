<?php

namespace App\Http\Controllers;

use App\Models\VolleyballSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VarsityDashboardController extends Controller
{
    public function index()
    {
        // Fetch the latest 10 volleyball sessions for the authenticated user using the Eloquent model
        $sessions = VolleyballSession::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $performanceData = [];
        foreach ($sessions as $session) {
            $session->score = $session->grade ?? 0;

            // Populate performanceData for the chart
            $performanceData[] = [
                'date' => $session->created_at->format('M d'),
                'score' => $session->score,
            ];
        }

        // Reverse the performanceData so the chart displays chronologically
        $performanceData = array_reverse($performanceData);

        // Pass the sessions and performance data to the Blade view
        return view('varsity.dashboard', compact('sessions', 'performanceData'));
    }
}
