<?php

namespace App\Livewire\Layout;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function open(string $id)
    {
        /** @var DatabaseNotification $notification */
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $target = $this->targetFor($notification);

        return $target ? $this->redirect($target, navigate: true) : null;
    }

    private function targetFor(DatabaseNotification $notification): ?string
    {
        $data = $notification->data;
        $user = Auth::user();

        return match (true) {
            isset($data['disaster_report_id']) && $user->isWarga() => route('admin.reports.show', $data['disaster_report_id']),
            isset($data['task_id']) && $user->canAccessModule('modul_g') => route('admin.tasks'),
            default => null,
        };
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.layout.notification-bell', [
            'notifications' => $user->notifications()->latest()->take(8)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
