<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CoachActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VarsityDashboardController;
use App\Http\Controllers\VarsityDrillController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', fn () => view('welcome'));

Route::get('/test-drills', function () {
    return redirect()->route('coach.drills.index');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Common Dashboard Redirect
    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => view('admin.dashboard'),
            'coach' => redirect()->route('coach.dashboard'),
            // Varsity users are redirected to controller-based dashboard!
            'varsity' => redirect()->route('varsity.dashboard'),
            default => abort(403, 'Unauthorized'),
        };
    })->name('dashboard');

    // Admin-only
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        // ... other admin routes
    });

    // Coach-only
    Route::middleware('role:coach')->group(function () {
        Route::get('/coach/dashboard', [App\Http\Controllers\CoachController::class, 'dashboard'])->name('coach.dashboard');
        Route::get('/coach/activities', [App\Http\Controllers\CoachActivityController::class, 'index'])->name('coach.activities');
        Route::get('/coach/players', [App\Http\Controllers\CoachController::class, 'managePlayers'])->name('coach.manage.players');
        Route::post('/coach/players/assign', [App\Http\Controllers\CoachController::class, 'assignPlayers'])->name('coach.assign.players');
        Route::get('/coach/drills', [App\Http\Controllers\CoachDrillController::class, 'index'])->name('coach.drills.index');
        Route::get('/coach/drills/create', [App\Http\Controllers\CoachDrillController::class, 'create'])->name('coach.drills.create');
        Route::post('/coach/drills', [App\Http\Controllers\CoachDrillController::class, 'store'])->name('coach.drills.store');
        Route::get('/coach/drills/{drill}/edit', [App\Http\Controllers\CoachDrillController::class, 'edit'])->name('coach.drills.edit');
        Route::put('/coach/drills/{drill}', [App\Http\Controllers\CoachDrillController::class, 'update'])->name('coach.drills.update');
        Route::delete('/coach/drills/{drill}', [App\Http\Controllers\CoachDrillController::class, 'destroy'])->name('coach.drills.destroy');
        Route::post('/coach/sessions/{session}/feedback', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'storeManualFeedback'])->name('coach.sessions.feedback.store');
        Route::get('/coach/players/{player}/sessions', [App\Http\Controllers\CoachController::class, 'showPlayerSessions'])->name('coach.players.sessions');
    });

    // Varsity-only
    Route::middleware('role:varsity')->group(function () {
        Route::get('/varsity/drills', [VarsityDrillController::class, 'index'])->name('varsity.drills');
        Route::get('/varsity/dashboard', [VarsityDashboardController::class, 'index'])->name('varsity.dashboard');

        // Volleyball Feedback System Routes
        Route::get('/volleyball/upload', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'showUploadForm'])->name('volleyball.upload.form');
        Route::post('/volleyball/upload', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'uploadVideo'])->name('volleyball.upload');
        Route::get('/session/{session}', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'show'])->name('volleyball.session.show');
        Route::get('/session/{session}/progress', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'getProgress'])->name('volleyball.session.progress');

        Route::get('/volleyball/drill/start', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'showDrillForm'])->name('volleyball.drill.start.form');
        Route::post('/volleyball/upload-drill', [App\Http\Controllers\Volleyball\VolleyballSessionController::class, 'uploadDrillVideo'])->name('volleyball.drill.upload');
        Route::post('/keep-alive', fn() => response()->json(['status' => 'ok']))->name('keep-alive');
        // ... other varsity routes
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Google OAuth Routes
Route::get('auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.login');

Route::get('auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->stateless()->user();
    $user = User::where('email', $googleUser->getEmail())->first();

    if (! $user) {
        $user = User::create([
            'name' => $googleUser->getName() ?? $googleUser->getNickname(),
            'email' => $googleUser->getEmail(),
            'role' => null,
            'password' => Hash::make(Str::random(24)),
        ]);
        Auth::login($user);

        return redirect()->route('oauth.role.select.view', ['user_id' => $user->id]);
    }

    if ($user->role === null) {
        Auth::login($user);

        return redirect()->route('oauth.role.select.view', ['user_id' => $user->id]);
    }

    Auth::login($user);

    return redirect('/dashboard');
});

Route::get('/select-role/{user_id}', function ($user_id) {
    return view('auth.select-role', ['user_id' => $user_id]);
})->middleware('auth')->name('oauth.role.select.view');

Route::post('/select-role', function (Request $request) {
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'role' => 'required|in:varsity,coach',
    ]);
    $user = User::findOrFail($request->user_id);
    $user->role = $request->role;
    $user->save();
    Auth::login($user); // Re-login

    return redirect('/dashboard');
})->middleware('auth')->name('oauth.role.select');

require __DIR__.'/auth.php';
