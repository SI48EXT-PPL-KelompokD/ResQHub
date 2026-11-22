<?php

namespace App\Services;

use App\Models\DonationReceipt;
use App\Models\GoodsDonation;
use App\Models\Shelter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\DonationReceivedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DonationReceiptService
{
    /**
     * Record a new donation receipt.
     */
    public function recordReceipt(
        GoodsDonation $donation,
        int $shelterId,
        int $qtyGood,
        int $qtyDamaged,
        User $receivedBy,
        ?Carbon $receivedAt = null
    ): DonationReceipt {
        if (!$donation->isDijanjikan()) {
            throw new InvalidArgumentException("Hanya donasi berstatus 'Dijanjikan' yang dapat dicatat penerimaannya.");
        }

        if ($qtyGood < 0 || $qtyDamaged < 0) {
            throw new InvalidArgumentException("Jumlah barang baik dan rusak tidak boleh bernilai negatif.");
        }

        if (($qtyGood + $qtyDamaged) <= 0) {
            throw new InvalidArgumentException("Total barang yang diterima harus lebih dari 0.");
        }

        $shelter = Shelter::findOrFail($shelterId);

        return DB::transaction(function () use ($donation, $shelter, $qtyGood, $qtyDamaged, $receivedBy, $receivedAt) {
            $receipt = DonationReceipt::create([
                'goods_donation_id' => $donation->id,
                'shelter_id' => $shelter->id,
                'qty_good' => $qtyGood,
                'qty_damaged' => $qtyDamaged,
                'received_by' => $receivedBy->id,
                'received_at' => $receivedAt ?? now(),
                'cancel_reason' => null,
            ]);

            // Update status donasi menjadi 'diterima'
            $donation->update(['status' => 'diterima']);

            // Tambahkan stok posko & buat mutasi masuk jika ada barang baik
            $itemId = $donation->aidNeed?->item_id;
            if ($itemId) {
                $stock = Stock::firstOrCreate(
                    [
                        'shelter_id' => $shelter->id,
                        'item_id' => $itemId,
                    ],
                    [
                        'quantity' => 0,
                        'min_threshold' => 10,
                    ]
                );

                if ($qtyGood > 0) {
                    $stock->increment('quantity', $qtyGood);

                    StockMovement::create([
                        'stock_id' => $stock->id,
                        'type' => 'masuk',
                        'quantity' => $qtyGood,
                        'reason' => "Penerimaan donasi barang {$donation->code} (kondisi baik: {$qtyGood}, rusak: {$qtyDamaged})",
                        'donation_receipt_id' => $receipt->id,
                        'distribution_id' => null,
                        'created_by' => $receivedBy->id,
                    ]);
                }
            }

            // Tambah jumlah terpenuhi pada kebutuhan terkait
            $aidNeed = $donation->aidNeed;
            if ($aidNeed && $qtyGood > 0) {
                $aidNeed->increment('qty_fulfilled', $qtyGood);
                if ($aidNeed->qty_fulfilled >= $aidNeed->qty_needed) {
                    $aidNeed->update(['status' => 'terpenuhi']);
                }
            }

            // Kirim notifikasi ke donatur
            if ($donation->donor) {
                $donation->donor->notify(new DonationReceivedNotification($receipt, $donation, $shelter));
            }

            return $receipt;
        });
    }

    /**
     * Update an existing donation receipt.
     */
    public function updateReceipt(
        DonationReceipt $receipt,
        int $newQtyGood,
        int $newQtyDamaged,
        User $user,
        ?string $reason = null
    ): DonationReceipt {
        if ($receipt->isCancelled()) {
            throw new InvalidArgumentException("Penerimaan yang sudah dibatalkan tidak dapat diubah.");
        }

        if ($receipt->goodsDonation && $receipt->goodsDonation->isDisalurkan()) {
            throw new InvalidArgumentException("Penerimaan tidak dapat diubah karena barang donasi ini sudah disalurkan.");
        }

        if ($newQtyGood < 0 || $newQtyDamaged < 0) {
            throw new InvalidArgumentException("Jumlah barang baik dan rusak tidak boleh bernilai negatif.");
        }

        if (($newQtyGood + $newQtyDamaged) <= 0) {
            throw new InvalidArgumentException("Total barang yang diterima harus lebih dari 0.");
        }

        $diffGood = $newQtyGood - $receipt->qty_good;
        $itemId = $receipt->goodsDonation?->aidNeed?->item_id;

        $stock = null;
        if ($itemId) {
            $stock = Stock::where('shelter_id', $receipt->shelter_id)
                ->where('item_id', $itemId)
                ->first();

            // Pengecekan sisa stok jika kuantitas barang baik dikurangi
            if ($diffGood < 0 && (!$stock || ($stock->quantity + $diffGood) < 0)) {
                $currentStock = $stock ? $stock->quantity : 0;
                throw new InvalidArgumentException("Jumlah barang baik tidak dapat dikurangi melebihi sisa stok yang ada di posko (stok saat ini: {$currentStock}). Sebagian barang kemungkinan telah disalurkan.");
            }
        }

        return DB::transaction(function () use ($receipt, $stock, $diffGood, $newQtyGood, $newQtyDamaged, $user, $reason) {
            // Update stok dan catat mutasi penyesuaian jika ada perubahan kuantitas baik
            if ($diffGood !== 0 && $stock) {
                $stock->increment('quantity', $diffGood);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'penyesuaian',
                    'quantity' => $diffGood,
                    'reason' => "Koreksi penerimaan donasi {$receipt->goodsDonation->code}: baik {$receipt->qty_good}->{$newQtyGood}, rusak {$receipt->qty_damaged}->{$newQtyDamaged}" . ($reason ? " ({$reason})" : ""),
                    'donation_receipt_id' => $receipt->id,
                    'distribution_id' => null,
                    'created_by' => $user->id,
                ]);
            }

            // Sesuaikan pemenuhan kebutuhan
            $aidNeed = $receipt->goodsDonation?->aidNeed;
            if ($aidNeed && $diffGood !== 0) {
                $aidNeed->increment('qty_fulfilled', $diffGood);
                if ($aidNeed->qty_fulfilled >= $aidNeed->qty_needed) {
                    $aidNeed->update(['status' => 'terpenuhi']);
                } elseif ($aidNeed->status === 'terpenuhi' && $aidNeed->qty_fulfilled < $aidNeed->qty_needed) {
                    $aidNeed->update(['status' => 'terbuka']);
                }
            }

            $receipt->update([
                'qty_good' => $newQtyGood,
                'qty_damaged' => $newQtyDamaged,
            ]);

            return $receipt;
        });
    }

    /**
     * Cancel / delete a donation receipt.
     */
    public function cancelReceipt(
        DonationReceipt $receipt,
        string $cancelReason,
        User $user
    ): DonationReceipt {
        if ($receipt->isCancelled()) {
            throw new InvalidArgumentException("Penerimaan donasi ini sudah dibatalkan sebelumnya.");
        }

        if (trim($cancelReason) === '') {
            throw new InvalidArgumentException("Alasan pembatalan wajib diisi.");
        }

        if ($receipt->goodsDonation && $receipt->goodsDonation->isDisalurkan()) {
            throw new InvalidArgumentException("Penerimaan tidak dapat dibatalkan karena barang donasi ini sudah disalurkan.");
        }

        $itemId = $receipt->goodsDonation?->aidNeed?->item_id;
        $stock = null;
        if ($itemId) {
            $stock = Stock::where('shelter_id', $receipt->shelter_id)
                ->where('item_id', $itemId)
                ->first();

            if ($stock && $stock->quantity < $receipt->qty_good) {
                throw new InvalidArgumentException("Penerimaan tidak dapat dibatalkan karena sisa stok di posko ({$stock->quantity}) kurang dari jumlah barang yang diterima ({$receipt->qty_good}). Barang kemungkinan telah disalurkan.");
            }
        }

        return DB::transaction(function () use ($receipt, $stock, $cancelReason, $user) {
            // Kurangi stok posko kembali & catat mutasi penyesuaian/pembatalan
            if ($stock && $receipt->qty_good > 0) {
                $stock->decrement('quantity', $receipt->qty_good);

                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'penyesuaian',
                    'quantity' => -$receipt->qty_good,
                    'reason' => "Pembatalan penerimaan donasi {$receipt->goodsDonation->code}: {$cancelReason}",
                    'donation_receipt_id' => $receipt->id,
                    'distribution_id' => null,
                    'created_by' => $user->id,
                ]);
            }

            // Kurangi pemenuhan kebutuhan kembali
            $aidNeed = $receipt->goodsDonation?->aidNeed;
            if ($aidNeed && $receipt->qty_good > 0) {
                $aidNeed->decrement('qty_fulfilled', $receipt->qty_good);
                if ($aidNeed->status === 'terpenuhi' && $aidNeed->qty_fulfilled < $aidNeed->qty_needed) {
                    $aidNeed->update(['status' => 'terbuka']);
                }
            }

            // Kembalikan status donasi menjadi 'dijanjikan'
            if ($receipt->goodsDonation) {
                $receipt->goodsDonation->update(['status' => 'dijanjikan']);
            }

            // Set cancel_reason dan soft delete penerimaan
            $receipt->update(['cancel_reason' => $cancelReason]);
            $receipt->delete();

            return $receipt;
        });
    }

    /**
     * Get shelters accessible to the given user.
     */
    public function getSheltersForUser(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Shelter::orderBy('name')->get();
        }

        return Shelter::where('coordinator_id', $user->id)->orderBy('name')->get();
    }
}
