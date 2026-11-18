<?php

namespace App\Notifications;

use App\Models\DisasterReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DisasterReportVerifiedNotification extends Notification
{
    use Queueable;

    public function __construct(public DisasterReport $report) {}

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
        $accepted = $this->report->status === DisasterReport::STATUS_ACCEPTED;

        return [
            'type' => 'disaster_report_verified',
            'disaster_report_id' => $this->report->id,
            'status' => $this->report->status,
            'title' => $accepted ? 'Laporan Anda diterima' : 'Laporan Anda ditolak',
            'message' => $accepted
                ? 'Laporan "'.$this->report->title.'" telah diverifikasi dan dikaitkan ke kejadian bencana resmi.'
                : 'Laporan "'.$this->report->title.'" ditolak. Alasan: '.$this->report->reject_reason,
        ];
    }
}
