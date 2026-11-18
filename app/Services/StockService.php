<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Shelter;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Dapatkan daftar posko yang dapat diakses oleh user.
     */
    public function getSheltersForUser(User $user): Collection
    {
        if ($user->isAdmin()) {
            return Shelter::orderBy('name')->get();
        }

        return Shelter::where('coordinator_id', $user->id)->orderBy('name')->get();
    }

    /**
     * Tambah stok awal atau barang masuk di luar donasi.
     * Tercatat sebagai mutasi masuk.
     */
    public function recordIncomingStock(array $data, User $user): Stock
    {
        return DB::transaction(function () use ($data, $user) {
            $shelterId = (int) $data['shelter_id'];
            $itemId = (int) $data['item_id'];
            $quantity = (int) $data['quantity'];
            $minThreshold = isset($data['min_threshold']) ? (int) $data['min_threshold'] : 10;
            $source = trim($data['source'] ?? 'Stok Awal');
            $notes = isset($data['notes']) ? trim($data['notes']) : '';

            // Cari apakah stok sudah ada (termasuk yang terhapus/soft-deleted)
            $stock = Stock::withTrashed()
                ->where('shelter_id', $shelterId)
                ->where('item_id', $itemId)
                ->first();

            if ($stock) {
                if ($stock->trashed()) {
                    $stock->restore();
                    $stock->quantity = $quantity;
                } else {
                    $stock->quantity += $quantity;
                }
                $stock->min_threshold = $minThreshold;
                $stock->save();
            } else {
                $stock = Stock::create([
                    'shelter_id' => $shelterId,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'min_threshold' => $minThreshold,
                ]);
            }

            // Alasan mutasi masuk
            $reason = "Barang masuk ({$source})";
            if (! empty($notes)) {
                $reason .= ": {$notes}";
            }

            // Catat mutasi masuk
            StockMovement::create([
                'stock_id' => $stock->id,
                'type' => 'masuk',
                'quantity' => $quantity,
                'reason' => $reason,
                'donation_receipt_id' => null,
                'distribution_id' => null,
                'created_by' => $user->id,
            ]);

            return $stock;
        });
    }

    /**
     * Ubah/koreksi stok dengan alasan wajib.
     * Selisih tercatat sebagai mutasi penyesuaian.
     */
    public function adjustStock(Stock $stock, int $newQuantity, string $reason, ?int $newThreshold, User $user): Stock
    {
        $reason = trim($reason);
        if (empty($reason)) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penyesuaian stok wajib diisi.',
            ]);
        }

        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'newQuantity' => 'Jumlah stok tidak boleh bernilai negatif.',
            ]);
        }

        return DB::transaction(function () use ($stock, $newQuantity, $reason, $newThreshold, $user) {
            $currentQuantity = (int) $stock->quantity;
            $diff = $newQuantity - $currentQuantity;

            $stock->quantity = $newQuantity;
            if ($newThreshold !== null && $newThreshold >= 0) {
                $stock->min_threshold = $newThreshold;
            }
            $stock->save();

            // Bila ada selisih, catat sebagai mutasi penyesuaian
            if ($diff !== 0) {
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'type' => 'penyesuaian',
                    'quantity' => $diff,
                    'reason' => 'Penyesuaian stok: ' . $reason,
                    'donation_receipt_id' => null,
                    'distribution_id' => null,
                    'created_by' => $user->id,
                ]);
            }

            return $stock;
        });
    }

    /**
     * Hapus stok logistik.
     * Baris stok hanya dapat dihapus bila jumlahnya 0 dan tidak memiliki kebutuhan terbuka.
     * Riwayat mutasi tetap tersimpan (via soft delete).
     */
    public function deleteStock(Stock $stock): bool
    {
        if ($stock->quantity > 0) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak dapat dihapus karena masih memiliki jumlah barang tersisa (' . $stock->quantity . '). Lakukan penyesuaian stok menjadi 0 terlebih dahulu jika barang rusak atau habis.',
            ]);
        }

        if ($stock->hasOpenNeeds()) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak dapat dihapus karena posko ini masih memiliki pengajuan kebutuhan terbuka untuk barang ini.',
            ]);
        }

        return (bool) $stock->delete();
    }
}
