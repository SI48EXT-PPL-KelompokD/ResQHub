<?php

namespace App\Notifications;

use App\Models\VolunteerTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskUpdated extends Notification
{
    use Queueable;

    public function __construct(public VolunteerTask $task) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $cancelled = $this->task->status === 'dibatalkan';

        return [
            'type' => $cancelled ? 'task_cancelled' : 'task_updated',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => $cancelled
                ? 'Tugas "'.$this->task->title.'" dibatalkan oleh koordinator.'
                : 'Detail tugas "'.$this->task->title.'" berubah. Periksa deskripsi, lokasi, kuota, dan jadwal terbaru.',
            'start_at' => $this->task->start_at?->toIso8601String(),
            'end_at' => $this->task->end_at?->toIso8601String(),
        ];
    }
}
