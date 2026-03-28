<?php

namespace App\Http\Controllers\Api\Private\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

#[Group(name: 'Auth', description: 'Authentication and access token endpoints', weight: 20)]
class AuthenticatedSessionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    #[Endpoint(operationId: 'login', title: 'Inicia sesión y obtiene un token de acceso')]
    #[Response(
        status: 200,
        description: 'Inicio de sesión exitoso',
        type: 'array{message: string, user: array{id: int, name: string, email: string, email_verified: bool}, access_token: string, token_type: string}',
    )]
    #[Response(status: 401, description: 'Credenciales inválidas', type: 'array{message: string}')]
    #[Response(status: 422, description: 'Error de validación', type: 'array{message: string, errors: array<string, array<int, string>>}')]
    
    public function store(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', (string) ($validated['email'] ?? ''))->first();

        if (! $user || ! Hash::check((string) ($validated['password'] ?? ''), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    #[Endpoint(operationId: 'logout', title: 'Cierra la sesión del usuario actual')]
    #[Response(status: 200, description: 'Sesión cerrada exitosamente', type: 'array{message: string}')]
    public function destroy(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        if ($request->hasSession()) {
            auth()->guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
