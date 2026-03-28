<?php

namespace App\Http\Controllers\Api\Private\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

#[Group(name: 'Auth', description: 'Authentication and access token endpoints', weight: 20)]
class RegisteredUserController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    #[Endpoint(operationId: 'register', title: 'Registra un nuevo usuario')]
    #[Response(
        status: 201,
        description: 'Usuario registrado exitosamente',
        type: 'array{message: string, email_verification_required: bool, user: array{id: int, name: string, email: string, email_verified: bool}, access_token: string, token_type: string}',
    )]
    #[Response(status: 422, description: 'Error de validación', type: 'array{message: string, errors: array<string, array<int, string>>}')]
    public function store(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'email_verification_required' => !$user->hasVerifiedEmail(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
