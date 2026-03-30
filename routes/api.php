<?php

// Laravel Routing
use Illuminate\Support\Facades\Route;

// Private API Routes
use App\Http\Controllers\Api\Private\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\Private\Auth\BroadcastAuthController;
use App\Http\Controllers\Api\Private\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Private\Auth\RegisteredUserController;
use App\Http\Controllers\Api\Private\Task\TaskController;

// Public API Routes
use App\Http\Controllers\Api\Public\StatusController;

/**
 * Status Route
 */
Route::controller(StatusController::class)->group(function () {
    Route::get("/status", "index")->name("status");
});

// Redirects for API Documentation (Assuming you have a documentation generator set up)
Route::redirect("/documentation", "/docs/api")->name("documentation.ui");
Route::redirect("/documentation.json", "/docs/api.json")->name(
    "documentation.json",
);

/**
 * API Routes Authentication & Registration
 */
Route::post("/login", [AuthenticatedSessionController::class, "store"])->name(
    "login",
);
Route::post("/logout", [
    AuthenticatedSessionController::class,
    "destroy",
])->name("logout");
Route::post("/register", [RegisteredUserController::class, "store"])->name(
    "register",
);
Route::post("/email/verification-notification", [
    EmailVerificationController::class,
    "sendNotification",
])
    ->middleware(["auth:sanctum", "throttle:6,1"])
    ->name("verification.send");

Route::get("/email/verify/{id}/{hash}", [
    EmailVerificationController::class,
    "verify",
])
    ->middleware(["signed", "throttle:6,1"])
    ->name("verification.verify");

/**
 * Broadcasting Auth Route (Private / Presence Channels)
 */
Route::post("/broadcasting/auth", BroadcastAuthController::class)->middleware(
    "auth:sanctum",
);

/**
 * Protected API Routes (Require Authentication)(Sanctum Middleware)
 */
Route::middleware("auth:sanctum")->group(function () {
    Route::prefix("tasks") ->name("tasks.") ->group(function () {

            Route::get("/", [TaskController::class, "index"])->name("index");
            Route::post("/", [TaskController::class, "store"])->name("store");
            Route::get("/{task}", [TaskController::class, "show"])->name(
                "show",
            );
            Route::put("/{task}", [TaskController::class, "update"])->name(
                "update",
            );
            Route::patch("/{task}", [
                TaskController::class,
                "partialUpdate",
            ])->name("partial-update");
            Route::delete("/{task}", [TaskController::class, "destroy"])->name(
                "destroy",
            );
        });
});
