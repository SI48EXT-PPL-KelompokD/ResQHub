<?php

namespace App\Notifications;

use App\Models\VolunteerTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskOfferReceived extends Notification
{
    use Queueable;

    public function __construct(public VolunteerTask $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_offer',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => 'Anda menerima penawaran tugas "'.$this->task->title.'".',
            'start_at' => $this->task->start_at?->toIso8601String(),
            'end_at' => $this->task->end_at?->toIso8601String(),
        ];
    }
}
