<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VolleyballSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    // 🏐 Dashboard: view all players managed by this coach
    public function dashboard()
    {
        $coach = Auth::user();

        // Get all player IDs managed by this coach
        $playerIds = $coach->players()->pluck('users.id');

        // Fetch volleyball sessions of those players
        $sessions = VolleyballSession::whereIn('user_id', $playerIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coach.dashboard', compact('sessions'));
    }

    // 👥 Manage varsity players
    public function managePlayers()
    {
        $coach = Auth::user();
        $varsityPlayers = User::where('role', 'varsity')->get();
        $assignedPlayerIds = $coach->players()->pluck('users.id')->toArray();

        return view('coach.manage-players', compact('varsityPlayers', 'assignedPlayerIds'));
    }

    // ✅ Assign players to coach
    public function assignPlayers(Request $request)
    {
        $request->validate([
            'player_ids' => 'nullable|array',
            'player_ids.*' => 'exists:users,id',
        ]);

        $coach = Auth::user();
        $coach->players()->sync($request->player_ids ?? []);

        return redirect()->route('coach.manage.players')->with('success', 'Players assigned successfully!');
    }

    // 📋 Show one player's sessions
    public function showPlayerSessions($id)
    {
        $coach = Auth::user();
        $player = User::findOrFail($id);

        if (!$coach->players()->where('users.id', $player->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $sessions = VolleyballSession::where('user_id', $player->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('coach.players.sessions', compact('player', 'sessions'));
    }
}
