<?php

namespace App\Http\Controllers\Api\Private\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

#[Group(name: 'Auth', description: 'Authentication and access token endpoints', weight: 20)]
class EmailVerificationController extends Controller
{
    #[Endpoint(operationId: 'sendEmailVerificationNotification', title: 'Envía el correo de verificación')]
    #[Response(status: 200, description: 'Notificación procesada', type: 'array{message: string}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 429, description: 'Demasiadas solicitudes')]
    public function sendNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
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
    }

    #[Endpoint(operationId: 'verifyEmailAddress', title: 'Verifica el correo electrónico del usuario')]
    #[PathParameter('id', description: 'ID del usuario a verificar', type: 'int', example: 1)]
    #[PathParameter('hash', description: 'Hash de verificación', type: 'string', example: 'ebf5f2c0f6f5f5a...')]
    #[Response(status: 200, description: 'Correo verificado o ya verificado', type: 'array{message: string, status: string}')]
    #[Response(status: 302, description: 'Redirección a frontend de verificación')]
    #[Response(status: 403, description: 'Enlace de verificación inválido', type: 'array{message: string}')]
    #[Response(status: 404, description: 'Usuario no encontrado', type: 'array{message: string}')]
    #[Response(status: 429, description: 'Demasiadas solicitudes')]
    public function verify(Request $request, string $id, string $hash): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        $wasAlreadyVerified = $user->hasVerifiedEmail();

        if (! $wasAlreadyVerified && $user->markEmailAsVerified()) {
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
    }
}
