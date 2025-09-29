<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RulesController extends Controller
{
    public function accept(Request $request)
    {
        $request->session()->put('rules_accepted', true);

        return response()->json(['status' => 'success']);
    }
}
