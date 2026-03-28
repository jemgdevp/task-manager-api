<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

#[Group(name: 'Health', description: 'Service health and availability endpoints', weight: 10)]
class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[Endpoint(operationId: 'getApiStatus', title: 'Obtiene el estado de la API')]
    #[Response(
        status: 200,
        description: 'API operativa',
        type: 'array{status: string, message: string, database: string, timestamp: string, version: string}',
    )]
    public function index(): JsonResponse
    {
        $appVersion = env('APP_VERSION', 'unknown');

        try {
            DB::connection()->getPdo();
            $dbStatus = 'Database connection successful';
        } catch (\Exception $e) {
            $dbStatus = 'Database connection failed: ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Backend is running',
            'database' => $dbStatus,
            'timestamp' => now()->toDateTimeString(),
            'version' => $appVersion,
        ]);
    }
}
