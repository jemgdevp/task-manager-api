<?php

namespace App\Jobs;

// Laravel Basic
use App\Models\Task;

// Mail
use App\Mail\TaskCreated;

// Laravel
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationCreateTask implements ShouldQueue
{
    // Es importante que el job implemente ShouldQueue para que se ejecute de forma asíncrona
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Task $task)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Obtener la tarea con el usuario asociado para asegurarnos de tener la información necesaria para enviar el correo
        $task = $this->task->fresh('user');

        if (!$task || !$task->user) {
            return;
        }

        Mail::to($task->user->email)->send(new TaskCreated($task));
    }
}
