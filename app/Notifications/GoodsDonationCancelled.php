<?php

namespace App\Notifications;

use App\Models\GoodsDonation;
use Illuminate\Notifications\Notification;

class GoodsDonationCancelled extends Notification
{
    public function __construct(
        public GoodsDonation $donation,
        public string $itemName,
        public string $donorName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'goods_donation_cancelled',
            'goods_donation_id' => $this->donation->id,
            'code' => $this->donation->code,
            'message' => "{$this->donorName} membatalkan janji {$this->donation->code} "
                ."({$this->itemName}, {$this->donation->quantity}). "
                ."Alasan: {$this->donation->cancel_reason}",
        ];
    }
}
