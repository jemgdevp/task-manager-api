<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Private API Routes
use App\Http\Controllers\Api\Private\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Private\Auth\RegisteredUserController;
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


