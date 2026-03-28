<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Sanctum\PersonalAccessToken;

// Define broadcast channels for private and presence channels
$resolveAuthorizedUserId = static function ($authenticatedUser): ?int {
    $bearerToken = request()->bearerToken();

    if ($bearerToken) {
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if ($accessToken && $accessToken->tokenable_type === User::class) {
            return (int) $accessToken->tokenable_id;
        }
    }

    if (isset($authenticatedUser->id)) {
        return (int) $authenticatedUser->id;
    }

    return null;
};

Broadcast::channel('App.Models.User.{id}', function ($user, $id) use ($resolveAuthorizedUserId) {
    return $resolveAuthorizedUserId($user) === (int) $id;
});

Broadcast::channel('user.{id}.tasks', function ($user, $id) use ($resolveAuthorizedUserId) {
    return $resolveAuthorizedUserId($user) === (int) $id;
});
