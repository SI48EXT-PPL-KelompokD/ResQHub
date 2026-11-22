<?php

namespace App\Notifications;

use App\Models\DonationReceipt;
use App\Models\GoodsDonation;
use App\Models\Shelter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DonationReceipt $receipt,
        public GoodsDonation $donation,
        public Shelter $shelter
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $itemName = $this->donation->aidNeed?->item?->name ?? 'Barang Donasi';
        $unit = $this->donation->aidNeed?->item?->unit ?? 'unit';

        return [
            'type' => 'donation_received',
            'title' => 'Donasi Barang Diterima di Posko',
            'code' => $this->donation->code,
            'donation_id' => $this->donation->id,
            'receipt_id' => $this->receipt->id,
            'shelter_id' => $this->shelter->id,
            'shelter_name' => $this->shelter->name,
            'item_name' => $itemName,
            'qty_good' => $this->receipt->qty_good,
            'qty_damaged' => $this->receipt->qty_damaged,
            'unit' => $unit,
            'received_at' => $this->receipt->received_at ? $this->receipt->received_at->format('Y-m-d H:i:s') : now()->toDateTimeString(),
            'message' => "Donasi barang Anda ({$this->donation->code}: {$itemName}) telah tiba dan diterima di {$this->shelter->name} sejumlah {$this->receipt->qty_good} {$unit} kondisi baik" . ($this->receipt->qty_damaged > 0 ? " dan {$this->receipt->qty_damaged} {$unit} rusak." : "."),
        ];
    }
}

