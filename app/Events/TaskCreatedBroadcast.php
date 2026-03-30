<?php

namespace App\Events;

// Models
use App\Models\Task;

// Laravel Basic
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCreatedBroadcast implements ShouldBroadcastNow
{
    // Laravel Basic
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Task $task)
    {
        // I dont need this , on this case
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            // new PrivateChannel('channel-name'),
            new PrivateChannel('App.Models.User.' . $this->task->user_id),
            new PrivateChannel('user.' . $this->task->user_id . '.tasks'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'task' => $this->task->load('tags')->toArray(),
        ];
    }
}
