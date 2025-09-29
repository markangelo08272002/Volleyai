<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Volleyball\VolleyballSessionController;
use App\Http\Controllers\RulesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/volleyball-sessions/{session}/progress', [VolleyballSessionController::class, 'getProgress']);

Route::middleware('auth:sanctum')->post('/rules/accept', [RulesController::class, 'accept']);
