<?php

use Illuminate\Support\Facades\Route;

// Redirect Web Page
Route::get('/', function () {
    $frontendUrl = env('VITE_FRONTEND_URL');
    if (!$frontendUrl) {
        abort(500, 'Frontend URL is not configured.');
    }
    return redirect()->away($frontendUrl);
});
