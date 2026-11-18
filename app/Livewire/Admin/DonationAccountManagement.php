<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\DisasterEvent;
use App\Models\DonationAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Rekening Donasi Resmi - ResQHub')]
class DonationAccountManagement extends Component
{
    use AuthorizesRequests, WithPagination;

    public const BANKS = [
        'Bank Rakyat Indonesia (BRI)',
        'Bank Mandiri',
        'Bank Central Asia (BCA)',
        'Bank Negara Indonesia (BNI)',
    ];

    protected $paginationTheme = 'tailwind';

    // Filter
    public $eventFilter = '';

    // Panel form (accountId null = tambah, berisi id = ubah)
    public $showForm = false;

    public $accountId = null;

    public $disaster_event_id = '';

    public $bank_name = '';

    public $account_number = '';

    public $account_holder = '';

    public $notes = '';

    public $is_active = '1'; // string "1" / "0" agar cocok dengan option select

    // Modal hapus
    public $showDeleteModal = false;

    public $deleteId = null;

    // Modal nonaktifkan
    public $showStatusModal = false;

    public $statusId = null;

    // Toast
    public $notification = null;

    public function mount(): void
    {
        $this->authorize('viewAny', DonationAccount::class);
    }

    public function updatingEventFilter(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'disaster_event_id' => ['required', 'exists:disaster_events,id'],
            'bank_name' => ['required', Rule::in(self::BANKS)],
            'account_number' => [
                'required', 'string', 'max:50', 'regex:/^[0-9\-]+$/',
                Rule::unique('donation_accounts', 'account_number')
                    ->where('disaster_event_id', $this->disaster_event_id)
                    ->ignore($this->accountId),
            ],
            'account_holder' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'disaster_event_id' => 'kejadian',
            'bank_name' => 'nama bank',
            'account_number' => 'nomor rekening',
            'account_holder' => 'nama pemilik rekening',
            'notes' => 'keterangan',
            'is_active' => 'status',
        ];
    }

    // ---------------------------------------------------------------
    // Form tambah / ubah
    // ---------------------------------------------------------------

    public function openCreateForm(): void
    {
        $this->authorize('create', DonationAccount::class);

        $this->resetForm();
        $this->disaster_event_id = $this->eventFilter;
        $this->showForm = true;
    }

    public function openEditForm($id): void
    {
        $account = DonationAccount::findOrFail($id);
        $this->authorize('update', $account);

        $this->accountId = $account->id;
        $this->disaster_event_id = $account->disaster_event_id;
        $this->bank_name = $account->bank_name;
        $this->account_number = $account->account_number;
        $this->account_holder = $account->account_holder;
        $this->notes = $account->notes ?? '';
        $this->is_active = $account->is_active ? '1' : '0';
        $this->resetErrorBag();

        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'disaster_event_id' => $this->disaster_event_id,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_holder' => $this->account_holder,
            'notes' => $this->notes !== '' ? $this->notes : null,
            'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
        ];

        $account = $this->accountId ? DonationAccount::findOrFail($this->accountId) : null;

        if ($account) {
            $this->authorize('update', $account);

            // Donasi menyimpan disaster_event_id sendiri, jadi kejadian rekening
            // tidak boleh dipindah setelah rekening menerima donasi.
            if ($account->hasDonations() && (int) $data['disaster_event_id'] !== (int) $account->disaster_event_id) {
                $this->addError('disaster_event_id', 'Kejadian tidak dapat diubah karena rekening sudah menerima donasi.');

                return;
            }
        } else {
            $this->authorize('create', DonationAccount::class);
        }

        $message = DB::transaction(function () use ($account, $data) {
            if ($account) {
                $account->fill($data);

                $changes = [];
                foreach ($account->getDirty() as $field => $new) {
                    $changes[$field] = ['old' => $account->getOriginal($field), 'new' => $new];
                }

                $account->save();

                if ($changes) {
                    ActivityLog::record(auth()->id(), $account, 'updated_donation_account', $changes);
                }

                return 'Rekening donasi berhasil diperbarui.';
            }

            $created = DonationAccount::create($data);
            ActivityLog::record(auth()->id(), $created, 'created_donation_account', $data);

            return 'Rekening donasi berhasil ditambahkan.';
        });

        $this->closeForm();
        $this->notify($message);
    }

    // ---------------------------------------------------------------
    // Aktif / nonaktif
    // ---------------------------------------------------------------

    public function requestToggle($id): void
    {
        $account = DonationAccount::findOrFail($id);
        $this->authorize('update', $account);

        // Menonaktifkan butuh konfirmasi, mengaktifkan langsung.
        if ($account->is_active) {
            $this->statusId = $account->id;
            $this->showStatusModal = true;

            return;
        }

        $this->applyToggle($account);
    }

    public function confirmDeactivate(): void
    {
        $account = DonationAccount::findOrFail($this->statusId);
        $this->authorize('update', $account);

        $this->showStatusModal = false;
        $this->statusId = null;

        if ($account->is_active) {
            $this->applyToggle($account);
        }
    }

    public function cancelStatus(): void
    {
        $this->showStatusModal = false;
        $this->statusId = null;
    }

    protected function applyToggle(DonationAccount $account): void
    {
        $old = (bool) $account->is_active;

        DB::transaction(function () use ($account, $old) {
            $account->update(['is_active' => ! $old]);

            ActivityLog::record(auth()->id(), $account, 'changed_donation_account_status', [
                'is_active' => ['old' => $old, 'new' => ! $old],
            ]);
        });

        // Jaga agar form yang sedang terbuka tidak menampilkan status lama.
        if ((int) $this->accountId === $account->id) {
            $this->is_active = $account->is_active ? '1' : '0';
        }

        $text = $old ? 'dinonaktifkan' : 'diaktifkan';
        $this->notify("Rekening {$account->bank_name} berhasil {$text}.");
    }

    // ---------------------------------------------------------------
    // Hapus
    // ---------------------------------------------------------------

    public function confirmDelete($id): void
    {
        $account = DonationAccount::findOrFail($id);
        $this->authorize('delete', $account);

        $this->deleteId = $account->id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function deleteAccount(): void
    {
        $accountId = (int) $this->deleteId;

        $deleted = DB::transaction(function () use ($accountId) {
            $account = DonationAccount::lockForUpdate()->findOrFail($accountId);
            $this->authorize('delete', $account);

            // Aturan bisnis: rekening yang sudah menerima donasi hanya boleh dinonaktifkan.
            if ($account->hasDonations()) {
                return false;
            }

            // Catat sebelum dihapus agar subject_id dan datanya masih tersimpan di log.
            ActivityLog::record(auth()->id(), $account, 'deleted_donation_account', [
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'disaster_event_id' => $account->disaster_event_id,
            ]);

            $account->delete();

            return true;
        });

        $this->showDeleteModal = false;
        $this->deleteId = null;

        if (! $deleted) {
            $this->notify('Rekening sudah menerima donasi dan hanya dapat dinonaktifkan.', 'error');

            return;
        }

        if ((int) $this->accountId === $accountId) {
            $this->closeForm();
        }

        $this->notify('Rekening donasi berhasil dihapus.');
    }

    // ---------------------------------------------------------------
    // Util
    // ---------------------------------------------------------------

    protected function resetForm(): void
    {
        $this->accountId = null;
        $this->disaster_event_id = '';
        $this->bank_name = '';
        $this->account_number = '';
        $this->account_holder = '';
        $this->notes = '';
        $this->is_active = '1';
        $this->resetErrorBag();
    }

    protected function notify($message, $type = 'success'): void
    {
        $this->notification = ['message' => $message, 'type' => $type];
    }

    public function dismissNotification(): void
    {
        $this->notification = null;
    }

    public function render()
    {
        $query = DonationAccount::with('disasterEvent')->withCount('fundDonations');

        if ($this->eventFilter !== '') {
            $query->where('disaster_event_id', $this->eventFilter);
        }

        return view('livewire.admin.donation-account-management', [
            'accounts' => $query->orderBy('disaster_event_id')->orderBy('id')->paginate(10),
            'events' => DisasterEvent::orderBy('name')->get(['id', 'name']),
            'banks' => self::BANKS,
            'deleteTarget' => $this->deleteId ? DonationAccount::find($this->deleteId) : null,
            'statusAccount' => $this->statusId ? DonationAccount::find($this->statusId) : null,
        ]);
    }
}
