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
        $completed = $this->task->status === 'selesai';

        return [
            'type' => match (true) {
                $cancelled => 'task_cancelled',
                $completed => 'task_completed',
                default => 'task_updated',
            },
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => match (true) {
                $cancelled => 'Tugas "'.$this->task->title.'" dibatalkan oleh koordinator.'
                    .($this->task->cancel_reason ? ' Alasan: '.$this->task->cancel_reason : ''),
                $completed => 'Tugas "'.$this->task->title.'" sudah ditandai selesai. Terima kasih atas kontribusi Anda.',
                default => 'Detail tugas "'.$this->task->title.'" berubah. Periksa deskripsi, lokasi, kuota, dan jadwal terbaru.',
            },
            'reason' => $cancelled ? $this->task->cancel_reason : null,
            'start_at' => $this->task->start_at?->toIso8601String(),
            'end_at' => $this->task->end_at?->toIso8601String(),
        ];
    }
}
