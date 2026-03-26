<?php

namespace App\Jobs;

use App\Models\Task;

// Mail
use App\Mail\TaskCreated;

// Laravel
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationCreateTask implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Task $task) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $task = $this->task->fresh('user');

        if (!$task || !$task->user) {
            return;
        }

        Mail::to($task->user->email)->send(new TaskCreated($task));
    }
}
