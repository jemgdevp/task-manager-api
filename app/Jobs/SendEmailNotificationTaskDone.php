<?php

namespace App\Jobs;

use App\Mail\TaskDone;
use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationTaskDone implements ShouldQueue
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

        Mail::to($task->user->email)->send(new TaskDone($task));
    }
}
