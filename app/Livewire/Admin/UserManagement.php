<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

#[Layout('layouts.admin')]
#[Title('Manajemen Pengguna - ResQHub')]
class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';

    // Modal state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    // Form inputs
    public $userId;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $role = 'warga';
    public $regency = '';
    public $is_active = true;

    // Flash message
    public $notification = null;

    public function mount(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Admin Pusdalops BPBD yang memiliki wewenang mengelola pengguna.');
        }
    }

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createUser()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'koordinator', 'nakes', 'relawan', 'donatur', 'warga'])],
            'regency' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'regency' => $this->regency,
            'is_active' => $this->is_active,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->notify('Pengguna baru berhasil ditambahkan.');
    }

    public function openEditModal($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->regency = $user->regency;
        $this->is_active = (bool) $user->is_active;
        $this->password = '';

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function updateUser()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'koordinator', 'nakes', 'relawan', 'donatur', 'warga'])],
            'regency' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $user = User::findOrFail($this->userId);
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'regency' => $this->regency,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $user->update($data);

        $this->showEditModal = false;
        $this->resetForm();
        $this->notify('Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->notify("Akun {$user->name} berhasil {$statusText}.");
    }

    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->userId === auth()->id()) {
            $this->notify('Anda tidak dapat menghapus akun Anda sendiri.', 'error');
            $this->showDeleteModal = false;
            return;
        }

        $user = User::findOrFail($this->userId);
        $name = $user->name;
        $user->delete();

        $this->showDeleteModal = false;
        $this->userId = null;
        $this->notify("Pengguna {$name} berhasil dihapus.");
    }

    protected function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->role = 'warga';
        $this->regency = '';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    protected function notify($message, $type = 'success')
    {
        $this->notification = [
            'message' => $message,
            'type' => $type,
        ];
    }

    public function dismissNotification()
    {
        $this->notification = null;
    }

    public function render()
    {
        $query = User::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('regency', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === '1');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
