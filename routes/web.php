<?php

use Illuminate\Support\Facades\Route;

// Redirect Web Page
Route::get('/', function () {
    $frontendUrl = config('app.frontend_url');
    if (!$frontendUrl) {
        abort(500, 'Frontend URL is not configured.');
    }
    return redirect()->away($frontendUrl);
});
