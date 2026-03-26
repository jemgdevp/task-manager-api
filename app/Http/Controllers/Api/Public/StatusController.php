<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            'version' => $appVersion
        ]);
    }
}
