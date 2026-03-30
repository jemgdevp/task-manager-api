<?php

namespace App\Http\Controllers\Api\Private\Task;

// Events Reverb
use App\Events\TaskCreatedBroadcast;
use App\Events\TaskDeletedBroadcast;
use App\Events\TaskUpdatedBroadcast;

// Laravel Basic
use App\Http\Controllers\Controller;

// Laravel Form Request
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

// Jobs
use App\Jobs\SendEmailNotificationCreateTask;
use App\Jobs\SendEmailNotificationTaskDone;

// Models
use App\Models\Task;

// Scramble API
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;

// Laravel Request and JsonResponse
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group(name: 'Tasks', description: 'Task management endpoints', weight: 30)]
class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[Endpoint(operationId: 'listTasks', title: 'Obtiene la lista de tareas del usuario autenticado')]
    #[QueryParameter('page', description: 'Número de página para la paginación', type: 'int', required: false, example: 1)]
    #[Response(status: 200, description: 'Lista paginada de tareas')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()
            ->tasks()
            ->with('tags')
            ->latest()
            ->paginate(15);

        return response()->json($tasks);
    }

    #[Endpoint(operationId: 'createTask', title: 'Crea una nueva tarea')]
    #[Response(status: 201, description: 'Tarea creada exitosamente', type: 'array{message: string, data: App\\Models\\Task}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 422, description: 'Error de validación', type: 'array{message: string, errors: array<string, array<int, string>>}')]
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? [];

        unset($data['tags']);

        $task = Task::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => $data['status'] ?? 'pending',
        ]);

        if (!empty($tags)) {
            $task->tags()->sync($tags);
        }

        SendEmailNotificationCreateTask::dispatch($task);
        event(new TaskCreatedBroadcast($task->fresh()->load('tags')));

        return response()->json([
            'message' => 'Task created successfully',
            'data' => $task->load('tags'),
        ], 201);
    }

    #[Endpoint(operationId: 'showTask', title: 'Obtiene el detalle de una tarea')]
    #[PathParameter('task', description: 'ID de la tarea', type: 'int', example: 1)]
    #[Response(status: 200, description: 'Detalle de la tarea', type: 'array{data: App\\Models\\Task}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 403, description: 'Sin permisos para acceder a la tarea', type: 'array{message: string}')]
    #[Response(status: 404, description: 'Tarea no encontrada', type: 'array{message: string}')]
    public function show(Request $request, Task $task): JsonResponse
    {
        $this->ensureTaskOwnership($request, $task);

        return response()->json([
            'data' => $task->load('tags'),
        ]);
    }

    #[Endpoint(operationId: 'updateTask', title: 'Actualiza completamente una tarea')]
    #[PathParameter('task', description: 'ID de la tarea', type: 'int', example: 1)]
    #[Response(status: 200, description: 'Tarea actualizada exitosamente', type: 'array{message: string, data: App\\Models\\Task}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 403, description: 'Sin permisos para actualizar la tarea', type: 'array{message: string}')]
    #[Response(status: 404, description: 'Tarea no encontrada', type: 'array{message: string}')]
    #[Response(status: 422, description: 'Error de validación', type: 'array{message: string, errors: array<string, array<int, string>>}')]
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        return $this->performUpdate($request, $task);
    }

    #[Endpoint(operationId: 'partialUpdateTask', title: 'Actualiza parcialmente una tarea', method: 'PATCH')]
    #[PathParameter('task', description: 'ID de la tarea', type: 'int', example: 1)]
    #[Response(status: 200, description: 'Tarea actualizada exitosamente', type: 'array{message: string, data: App\\Models\\Task}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 403, description: 'Sin permisos para actualizar la tarea', type: 'array{message: string}')]
    #[Response(status: 404, description: 'Tarea no encontrada', type: 'array{message: string}')]
    #[Response(status: 422, description: 'Error de validación', type: 'array{message: string, errors: array<string, array<int, string>>}')]
    public function partialUpdate(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        return $this->performUpdate($request, $task);
    }

    #[Endpoint(operationId: 'deleteTask', title: 'Elimina una tarea')]
    #[PathParameter('task', description: 'ID de la tarea', type: 'int', example: 1)]
    #[Response(status: 200, description: 'Tarea eliminada exitosamente', type: 'array{message: string}')]
    #[Response(status: 401, description: 'No autenticado', type: 'array{message: string}')]
    #[Response(status: 403, description: 'Sin permisos para eliminar la tarea', type: 'array{message: string}')]
    #[Response(status: 404, description: 'Tarea no encontrada', type: 'array{message: string}')]
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->ensureTaskOwnership($request, $task);

        $taskId = $task->id;
        $userId = $task->user_id;

        $task->delete();
        event(new TaskDeletedBroadcast($taskId, $userId));

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    private function performUpdate(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->ensureTaskOwnership($request, $task);

        $oldStatus = $task->status;
        $data = $request->validated();
        $tags = $data['tags'] ?? null;

        unset($data['tags']);

        if (! empty($data)) {
            $task->update($data);
        }

        if (is_array($tags)) {
            $task->tags()->sync($tags);
        }

        if ($oldStatus !== 'done' && $task->fresh()->status === 'done') {
            SendEmailNotificationTaskDone::dispatch($task->fresh());
        }

        event(new TaskUpdatedBroadcast($task->fresh()->load('tags')));

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->fresh()->load('tags'),
        ]);
    }

    private function ensureTaskOwnership(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403, 'You are not allowed to access this task.');
    }
}
