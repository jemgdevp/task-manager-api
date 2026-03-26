<?php

namespace App\Http\Controllers\Api\Private\Task;

use App\Events\TaskCreatedBroadcast;
use App\Events\TaskDeletedBroadcast;
use App\Events\TaskUpdatedBroadcast;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Jobs\SendEmailNotificationCreateTask;
use App\Jobs\SendEmailNotificationTaskDone;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()
            ->tasks()
            ->with('tags')
            ->latest()
            ->paginate(15);

        return response()->json($tasks);
    }

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

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->ensureTaskOwnership($request, $task);

        return response()->json([
            'data' => $task->load('tags'),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->ensureTaskOwnership($request, $task);

        $oldStatus = $task->status;
        $data = $request->validated();
        $tags = $data['tags'] ?? null;

        unset($data['tags']);

        if (!empty($data)) {
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

    private function ensureTaskOwnership(Request $request, Task $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403, 'You are not allowed to access this task.');
    }
}
