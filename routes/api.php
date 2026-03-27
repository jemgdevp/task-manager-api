<?php

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Private API Routes
use App\Http\Controllers\Api\Private\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Private\Auth\RegisteredUserController;
use App\Http\Controllers\Api\Private\Task\TaskController;
use App\Models\User;

// Public API Routes
use App\Http\Controllers\Api\Public\StatusController;

/**
 * Status Route
 */
Route::get('/status', [StatusController::class, 'index'])->name('status');

/**
 * API Routes Authentication & Registration
 */
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login',);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated.',
        ], 401);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Email is already verified.',
        ]);
    }

    $user->sendEmailVerificationNotification();

    return response()->json([
        'message' => 'Verification link sent.',
    ]);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (Request $request, string $id, string $hash) {
    $user = User::findOrFail($id);

    if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    $wasAlreadyVerified = $user->hasVerifiedEmail();

    if (!$wasAlreadyVerified && $user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    $status = $wasAlreadyVerified ? 'already-verified' : 'verified';
    $frontendVerifyEmailUrl = (string) config('app.frontend_verify_email_url', '');

    if ($frontendVerifyEmailUrl !== '') {
        $separator = str_contains($frontendVerifyEmailUrl, '?') ? '&' : '?';

        return redirect()->away($frontendVerifyEmailUrl.$separator.'status='.$status);
    }

    return response()->json([
        'message' => $status === 'verified'
            ? 'Email verified successfully.'
            : 'Email was already verified.',
        'status' => $status,
    ]);
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

/**
 * Broadcasting Auth Route (Private / Presence Channels)
 */
Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum');

/**
 * Protected API Routes (Require Authentication)
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::patch('/{task}', [TaskController::class, 'update'])->name('partial-update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });
});
