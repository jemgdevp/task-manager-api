<?php

namespace App\Http\Controllers\Api\Private\Auth;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

#[Group(name: 'Auth', description: 'Authentication and access token endpoints', weight: 20)]
class BroadcastAuthController extends Controller
{
    #[Endpoint(operationId: 'authenticateBroadcastChannel', title: 'Autoriza el acceso a canales privados/presence')]
    #[Response(status: 200, description: 'Autorización de canal exitosa')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 403, description: 'No autorizado para el canal', type: 'array{message: string}')]
    public function __invoke(Request $request): HttpResponse
    {
        return Broadcast::auth($request);
    }
}
