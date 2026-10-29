<div class="space-y-6">

    <!-- Toast Notification -->
    @if ($notification)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.dismissNotification(); }, 4000)"
            class="fixed top-20 right-6 z-50 max-w-sm w-full p-4 rounded-xl shadow-lg border transition-all transform ease-out duration-300 {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}"
        >
            <div class="flex items-start justify-between space-x-3">
                <div class="flex items-center space-x-2">
                    @if ($notification['type'] === 'error')
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                    <p class="text-xs sm:text-sm font-semibold">{{ $notification['message'] }}</p>
                </div>
                <button wire:click="dismissNotification" class="text-slate-400 hover:text-slate-600 p-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Manajemen Pengguna
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Kelola akun pengguna, hak akses peran, dan verifikasi aktivasi akun koordinator & nakes.
            </p>
        </div>

        <button 
            wire:click="openCreateModal"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span>Tambah Pengguna</span>
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Search -->
            <div class="relative lg:col-span-2">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama, email, no HP, atau domisili..." 
                    class="w-full pl-10 pr-4 py-2 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-800 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                />
            </div>

            <!-- Role Filter -->
            <div>
                <select 
                    wire:model.live="roleFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Peran</option>
                    <option value="admin">Admin Pusdalops</option>
                    <option value="koordinator">Koordinator Posko</option>
                    <option value="nakes">Tenaga Kesehatan</option>
                    <option value="relawan">Relawan</option>
                    <option value="donatur">Donatur</option>
                    <option value="warga">Warga Terdampak</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select 
                    wire:model.live="statusFilter"
                    class="w-full py-2 px-3 text-xs sm:text-sm bg-slate-50 hover:bg-white focus:bg-white text-slate-700 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
                >
                    <option value="">Semua Status Akun</option>
                    <option value="1">Aktif</option>
                    <option value="0">Menunggu / Non-aktif</option>
                </select>
            </div>

        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] font-semibold uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-5">Nama & Profil</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Peran</th>
                        <th class="py-3.5 px-4">Domisili</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Name & Initial -->
                            <td class="py-3.5 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-900 block leading-tight">
                                            {{ $user->name }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 block mt-0.5">
                                            Terdaftar {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-700 block">{{ $user->email }}</span>
                                <span class="text-[11px] text-slate-500 font-mono block mt-0.5">
                                    {{ $user->phone ?? 'Belum ada No HP' }}
                                </span>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-4">
                                @php
                                    $roleBadgeStyles = match ($user->role) {
                                        'admin' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                        'koordinator' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                                        'nakes' => 'bg-teal-50 text-teal-700 border-teal-200/80',
                                        'relawan' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                                        'donatur' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                        'warga' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadgeStyles }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>

                            <!-- Regency -->
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $user->regency ?? '-' }}
                            </td>

                            <!-- Active Status Toggle (FR-00A Approval) -->
                            <td class="py-3.5 px-4 text-center">
                                <button 
                                    wire:click="toggleStatus({{ $user->id }})"
                                    title="Klik untuk ubah status akun"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold cursor-pointer transition {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' }}"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    <span>{{ $user->is_active ? 'Aktif' : 'Non-aktif' }}</span>
                                </button>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end space-x-1">
                                    <button 
                                        wire:click="openEditModal({{ $user->id }})" 
                                        title="Ubah data pengguna"
                                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    @if ($user->id !== auth()->id())
                                        <button 
                                            wire:click="confirmDelete({{ $user->id }})" 
                                            title="Hapus pengguna"
                                            class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-700">Tidak ada data pengguna ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter peran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $users->links() }}
        </div>

    </div>

    <!-- Modal Form Tambah Pengguna -->
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeCreateModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <form wire:submit.prevent="createUser">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 mb-4" id="modal-title">
                                Tambah Pengguna Baru
                            </h3>

                            <div class="space-y-4">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                                    <input type="text" wire:model="name" required class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="Contoh: Muhammad Fadli" />
                                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                                    <input type="email" wire:model="email" required class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="nama@email.com" />
                                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
                                    <input type="text" wire:model="phone" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="081234567890" />
                                    @error('phone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Role & Status Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Peran (Role)</label>
                                        <select wire:model="role" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                                            <option value="warga">Warga Terdampak</option>
                                            <option value="relawan">Relawan</option>
                                            <option value="donatur">Donatur</option>
                                            <option value="nakes">Tenaga Kesehatan</option>
                                            <option value="koordinator">Koordinator Posko</option>
                                            <option value="admin">Admin Pusdalops</option>
                                        </select>
                                        @error('role') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Akun</label>
                                        <select wire:model="is_active" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                                            <option value="1">Aktif Langsung</option>
                                            <option value="0">Non-aktif (Menunggu)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Regency -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Domisili (Kabupaten/Kota)</label>
                                    <input type="text" wire:model="regency" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="Contoh: Kabupaten Bandung" />
                                </div>

                                <!-- Password -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Awal</label>
                                    <input type="password" wire:model="password" required class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="Minimal 6 karakter" />
                                    @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                            <button type="button" wire:click="closeCreateModal" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                                Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Form Edit Pengguna -->
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="closeEditModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <form wire:submit.prevent="updateUser">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">
                                Ubah Data Pengguna
                            </h3>

                            <div class="space-y-4">
                                <!-- Nama -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                                    <input type="text" wire:model="name" required class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
                                    @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                                    <input type="email" wire:model="email" required class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
                                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
                                    <input type="text" wire:model="phone" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
                                </div>

                                <!-- Role & Status Row -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Peran (Role)</label>
                                        <select wire:model="role" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                                            <option value="warga">Warga Terdampak</option>
                                            <option value="relawan">Relawan</option>
                                            <option value="donatur">Donatur</option>
                                            <option value="nakes">Tenaga Kesehatan</option>
                                            <option value="koordinator">Koordinator Posko</option>
                                            <option value="admin">Admin Pusdalops</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Akun</label>
                                        <select wire:model="is_active" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                                            <option value="1">Aktif</option>
                                            <option value="0">Non-aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Regency -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Domisili (Kabupaten/Kota)</label>
                                    <input type="text" wire:model="regency" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" />
                                </div>

                                <!-- Password (Optional) -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Ganti Kata Sandi (Opsional)</label>
                                    <input type="password" wire:model="password" class="w-full py-2 px-3 text-xs sm:text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition" placeholder="Kosongkan jika tidak diubah" />
                                    @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-3 rounded-b-2xl">
                            <button type="button" wire:click="closeEditModal" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                                Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Konfirmasi Hapus -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Hapus Pengguna</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-100 text-xs sm:text-sm font-semibold rounded-xl border border-slate-200 transition">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteUser" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition">
                            Hapus Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
