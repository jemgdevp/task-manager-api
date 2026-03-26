<?php

namespace Tests\Feature;

use App\Jobs\SendEmailNotificationCreateTask;
use App\Jobs\SendEmailNotificationTaskDone;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_task_and_dispatch_email_job(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Implement API endpoint',
            'description' => 'Create task endpoint and tests',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'Implement API endpoint');
        $response->assertJsonPath('data.status', 'pending');

        Queue::assertPushed(SendEmailNotificationCreateTask::class);
    }

    public function test_done_email_job_is_dispatched_only_on_pending_to_done_transition(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->patchJson("/api/tasks/{$task->id}", [
            'status' => 'done',
        ])->assertOk();

        Queue::assertPushed(SendEmailNotificationTaskDone::class, 1);

        $this->patchJson("/api/tasks/{$task->id}", [
            'description' => 'No status change',
        ])->assertOk();

        Queue::assertPushed(SendEmailNotificationTaskDone::class, 1);
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);

        $this->getJson("/api/tasks/{$task->id}")->assertForbidden();
        $this->patchJson("/api/tasks/{$task->id}", ['title' => 'Hacked'])->assertForbidden();
        $this->deleteJson("/api/tasks/{$task->id}")->assertForbidden();
    }

    public function test_user_can_sync_tags_when_creating_and_updating_task(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $tagA = Tag::factory()->create();
        $tagB = Tag::factory()->create();

        Sanctum::actingAs($user);

        $createResponse = $this->postJson('/api/tasks', [
            'title' => 'Task with tags',
            'tags' => [$tagA->id],
        ]);

        $createResponse->assertCreated();
        $taskId = $createResponse->json('data.id');

        $task = Task::findOrFail($taskId);
        $this->assertCount(1, $task->tags);

        $this->patchJson("/api/tasks/{$taskId}", [
            'tags' => [$tagB->id],
        ])->assertOk();

        $task->refresh();
        $this->assertEquals([$tagB->id], $task->tags()->pluck('tags.id')->all());
    }
}
