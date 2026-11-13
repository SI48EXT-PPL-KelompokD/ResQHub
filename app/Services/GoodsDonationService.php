<?php

namespace App\Services;

use App\Enums\GoodsDonationStatus;
use App\Models\ActivityLog;
use App\Models\AidNeed;
use App\Models\GoodsDonation;
use App\Models\User;
use App\Notifications\GoodsDonationCancelled;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class GoodsDonationService
{
    public function create(User $donor, array $data): GoodsDonation
    {
        return retry(3, fn () => DB::transaction(function () use ($donor, $data) {
            $need = $this->lockOpenNeed((int) $data['aid_need_id']);
            $this->assertWithinRemaining($need, (int) $data['quantity']);

            $donation = GoodsDonation::create([
                'code' => $this->generateCode(),
                'donor_id' => $donor->id,
                'aid_need_id' => $need->id,
                'quantity' => (int) $data['quantity'],
                'delivery_method' => $data['delivery_method'],
                'est_arrival' => $data['est_arrival'],
                'status' => GoodsDonationStatus::Dijanjikan,
            ]);

            $need->update(['qty_pledged' => $need->qty_pledged + $donation->quantity]);

            ActivityLog::record($donor->id, $donation, 'created_goods_donation', [
                'code' => $donation->code,
                'aid_need_id' => $need->id,
                'quantity' => $donation->quantity,
            ]);

            return $donation;
        }), 0, fn ($e) => $e instanceof UniqueConstraintViolationException);
    }

    public function update(GoodsDonation $donation, User $actor, array $data): GoodsDonation
    {
        return DB::transaction(function () use ($donation, $actor, $data) {
            $fresh = GoodsDonation::query()->lockForUpdate()->findOrFail($donation->id);
            $this->assertPledged($fresh);

            $need = $this->lockOpenNeed($fresh->aid_need_id);
            $newQty = (int) $data['quantity'];
            $this->assertWithinRemaining($need, $newQty, $fresh->quantity);

            $old = [
                'quantity' => $fresh->quantity,
                'delivery_method' => $fresh->delivery_method->value,
                'est_arrival' => $fresh->est_arrival->toDateString(),
            ];
            $new = [
                'quantity' => $newQty,
                'delivery_method' => $data['delivery_method'],
                'est_arrival' => Carbon::parse($data['est_arrival'])->toDateString(),
            ];

            $changes = [];
            foreach ($new as $field => $value) {
                if ($value !== $old[$field]) {
                    $changes[$field] = ['old' => $old[$field], 'new' => $value];
                }
            }

            if ($changes) {
                $fresh->update($new);
                $need->update(['qty_pledged' => $need->qty_pledged + ($newQty - $old['quantity'])]);
                ActivityLog::record($actor->id, $fresh, 'updated_goods_donation', $changes);
            }

            return $fresh;
        });
    }

    public function cancel(GoodsDonation $donation, ?User $actor, string $reason, bool $auto = false): GoodsDonation
    {
        return DB::transaction(function () use ($donation, $actor, $reason, $auto) {
            $fresh = GoodsDonation::query()->lockForUpdate()->findOrFail($donation->id);
            $this->assertPledged($fresh);

            $need = AidNeed::withTrashed()->lockForUpdate()->find($fresh->aid_need_id);

            $fresh->update([
                'status' => GoodsDonationStatus::Dibatalkan,
                'cancel_reason' => $reason,
            ]);

            if ($need) {
                $need->update(['qty_pledged' => max(0, $need->qty_pledged - $fresh->quantity)]);
            }

            ActivityLog::record(
                $actor?->id,
                $fresh,
                $auto ? 'auto_cancelled_goods_donation' : 'cancelled_goods_donation',
                ['quantity' => $fresh->quantity, 'reason' => $reason],
            );

            if (! $auto && $need) {
                $this->notifyCoordinators($fresh, $need, $actor);
            }

            return $fresh;
        });
    }

    private function lockOpenNeed(int $id): AidNeed
    {
        $need = AidNeed::query()->lockForUpdate()->find($id);

        if (! $need || $need->status !== 'terbuka') {
            throw ValidationException::withMessages([
                'aid_need_id' => 'Kebutuhan ini sudah tidak dibuka untuk donasi.',
            ]);
        }

        return $need;
    }

    private function assertWithinRemaining(AidNeed $need, int $quantity, int $ownQuantity = 0): void
    {
        $remaining = max(0, $need->qty_needed - $need->qty_pledged + $ownQuantity);

        if ($quantity < 1 || $quantity > $remaining) {
            throw ValidationException::withMessages([
                'quantity' => $remaining > 0
                    ? "Jumlah maksimal {$remaining} sesuai sisa kebutuhan."
                    : 'Kebutuhan ini sudah terpenuhi seluruhnya.',
            ]);
        }
    }

    private function assertPledged(GoodsDonation $donation): void
    {
        if (! $donation->isPledged()) {
            throw ValidationException::withMessages([
                'status' => 'Janji donasi sudah tidak berstatus Dijanjikan.',
            ]);
        }
    }

    private function generateCode(): string
    {
        $prefix = 'DN-'.now()->format('ym').'-';

        $last = GoodsDonation::withTrashed()
            ->where('code', 'like', $prefix.'%')
            ->orderByDesc('code')
            ->lockForUpdate()
            ->value('code');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function notifyCoordinators(GoodsDonation $donation, AidNeed $need, ?User $donor): void
    {
        $itemName = DB::table('items')->where('id', $need->item_id)->value('name') ?? 'barang';
        $coordinatorId = DB::table('shelters')->where('id', $need->shelter_id)->value('coordinator_id');

        $recipients = User::query()->whereKey($coordinatorId)->where('is_active', true)->get();

        Notification::send($recipients, new GoodsDonationCancelled(
            $donation,
            $itemName,
            $donor?->name ?? 'Donatur',
        ));
    }
}
