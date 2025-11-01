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
            ->orderBy('created_at', 'asc')
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

        // Pass the sessions and performance data to the Blade view
        return view('varsity.dashboard', compact('performanceData', 'sessions'));
    }

    public function sessions()
    {
        // Fetch all volleyball sessions for the authenticated user using the Eloquent model
        $sessions = VolleyballSession::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $performanceData = [];

        // Pass the sessions to the Blade view
        return view('varsity.sessions', compact('sessions', 'performanceData'));
    }
}
