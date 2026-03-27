<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Task Manager API',
    version: '0.0.1',
    description: 'Documentación OpenAPI para Task Manager API.',
)]
#[OA\Server(
    url: '/',
    description: 'Servidor principal',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'https',
    scheme: 'bearer',
    bearerFormat: 'Token',
)]
#[OA\PathItem(path: '/api/status')]
final class OpenApiSpec
{
    #[OA\Get(
        path: '/api/status',
        operationId: 'getApiStatus',
        tags: ['Health'],
        summary: 'Obtiene el estado de la API',
        responses: [
            new OA\Response(response: 200, description: 'API operativa'),
        ],
    )]
    #[OA\Post(
        path: '/api/tasks',
        operationId: 'getTasks',
        tags: ['Tasks'],
        summary: 'Obtiene la lista de tareas',
        responses: [
            new OA\Response(response: 200, description: 'Lista de tareas'),
        ],
    )]
    public function status(): void
    {
    }
}
