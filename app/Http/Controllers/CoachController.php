<?php

namespace App\Http\Controllers;

use App\Models\VolleyballSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachController extends Controller
{
    public function dashboard()
    {
        $coachId = Auth::id();
        $managedPlayerIds = User::where('coach_id', $coachId)->pluck('id');
        $sessions = VolleyballSession::whereIn('user_id', $managedPlayerIds)
                                ->with('user')
                                ->orderBy('created_at', 'desc')
                                ->get();
        return view('coach.dashboard', compact('sessions'));
    }

    public function managePlayers()
    {
        $coachId = Auth::id();
        $varsityPlayers = User::where('role', 'varsity')->get();
        $managedPlayers = User::where('coach_id', $coachId)->pluck('id')->toArray();
        return view('coach.manage-players', compact('varsityPlayers', 'managedPlayers'));
    }

    public function assignPlayers(Request $request)
    {
        $request->validate([
            'player_ids' => 'nullable|array',
            'player_ids.*' => 'exists:users,id',
        ]);

        $coachId = Auth::id();

        // Unassign all players currently managed by this coach
        User::where('coach_id', $coachId)->update(['coach_id' => null]);

        // Assign selected players to this coach
        if ($request->has('player_ids')) {
            User::whereIn('id', $request->player_ids)->update(['coach_id' => $coachId]);
        }

        return redirect()->route('coach.manage.players')->with('success', 'Players assigned successfully!');
    }

    public function showPlayerSessions(User $player)
    {
        // Ensure the coach manages this player
        if ($player->coach_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $sessions = $player->volleyballSessions()->orderBy('created_at', 'desc')->get();

        return view('coach.players.sessions', compact('player', 'sessions'));
    }
}
