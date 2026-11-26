<?php

namespace App\Notifications;

use App\Models\EarlyWarning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EarlyWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const EVENT_ISSUED = 'diterbitkan';

    public const EVENT_ESCALATED = 'tingkat_naik';

    public const EVENT_REVOKED = 'dicabut';

    public function __construct(public EarlyWarning $warning, public string $event) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $level = ucfirst($this->warning->level);

        return [
            'type' => 'early_warning',
            'event' => $this->event,
            'early_warning_id' => $this->warning->id,
            'level' => $this->warning->level,
            'title' => match ($this->event) {
                self::EVENT_ESCALATED => 'Peringatan naik ke tingkat '.$level,
                self::EVENT_REVOKED => 'Peringatan dicabut',
                default => 'Peringatan '.$level.': '.$this->warning->title,
            },
            'message' => $this->event === self::EVENT_REVOKED
                ? 'Peringatan "'.$this->warning->title.'" telah dicabut. Alasan: '.$this->warning->revoke_reason
                : $this->warning->instruction,
        ];
    }
}
