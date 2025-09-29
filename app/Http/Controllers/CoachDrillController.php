<?php

namespace App\Http\Controllers;

use App\Models\Drill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachDrillController extends Controller
{
    public function index()
    {
        $drills = Auth::user()->drills()->orderBy('created_at', 'desc')->get();
        return view('coach.drills.index', compact('drills'));
    }

    public function create()
    {
        return view('coach.drills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_type' => 'required|string|max:255',
            'criteria' => 'nullable|json',
        ]);

        Auth::user()->drills()->create($request->all());

        return redirect()->route('coach.drills.index')->with('success', 'Drill created successfully!');
    }

    public function edit(Drill $drill)
    {
        // Ensure the coach owns the drill
        if ($drill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('coach.drills.edit', compact('drill'));
    }

    public function update(Request $request, Drill $drill)
    {
        // Ensure the coach owns the drill
        if ($drill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_type' => 'required|string|max:255',
            'criteria' => 'nullable|json',
        ]);

        $drill->update($request->all());

        return redirect()->route('coach.drills.index')->with('success', 'Drill updated successfully!');
    }

    public function destroy(Drill $drill)
    {
        // Ensure the coach owns the drill
        if ($drill->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $drill->delete();

        return redirect()->route('coach.drills.index')->with('success', 'Drill deleted successfully!');
    }
}
