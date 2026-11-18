@php
    $roleOptions = [
        'warga' => ['Warga', 'Mengirim laporan kejadian dan memantau statusnya.'],
        'relawan' => ['Relawan', 'Menerima tawaran tugas sesuai keahlian dan jangkauan.'],
        'donatur' => ['Donatur', 'Menjanjikan barang untuk kebutuhan posko.'],
    ];
    $staffOptions = [
        'nakes' => ['Tenaga Kesehatan', 'Mencatat pemeriksaan dan triase pengungsi. Wajib punya nomor STR.'],
        'koordinator' => ['Koordinator Posko', 'Mengelola posko, pengungsi, kebutuhan, dan stok.'],
    ];
    $allRoles = $roleOptions + $staffOptions;
@endphp

<div>
    <p class="text-sm font-semibold text-slate-600">Langkah {{ $step }} dari 2</p>

    @if ($step === 1)
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Daftar sebagai apa?</h1>
        <p class="mt-3 text-[15px] text-slate-700">Pilih peran yang paling sesuai dengan yang akan Anda lakukan di ResQHub.</p>

        <form wire:submit="continueToDetails" class="mt-7">
            <fieldset>
                <legend class="sr-only">Peran</legend>
                <div class="divide-y divide-line border-y border-line bg-white">
                    @foreach ($roleOptions as $value => [$label, $hint])
                        <label wire:key="role-{{ $value }}" class="flex cursor-pointer items-start gap-3 px-4 py-4 hover:bg-paper {{ $role === $value ? 'bg-brand-50' : '' }}">
                            <input type="radio" name="role" value="{{ $value }}" wire:click="selectRole('{{ $value }}')" @checked($role === $value) class="mt-1 h-4 w-4 border-slate-400 text-brand-600 focus:ring-brand-600">
                            <span>
                                <span class="block font-bold">{{ $label }}</span>
                                <span class="mt-0.5 block text-sm text-slate-700">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="mt-7">
                <legend class="text-sm font-bold">Petugas</legend>
                <p class="mt-1 text-sm text-slate-700">Akun petugas baru bisa dipakai setelah diaktifkan oleh Admin Pusdalops.</p>
                <div class="mt-3 divide-y divide-line border-y border-line bg-white">
                    @foreach ($staffOptions as $value => [$label, $hint])
                        <label wire:key="role-{{ $value }}" class="flex cursor-pointer items-start gap-3 px-4 py-4 hover:bg-paper {{ $role === $value ? 'bg-brand-50' : '' }}">
                            <input type="radio" name="role" value="{{ $value }}" wire:click="selectRole('{{ $value }}')" @checked($role === $value) class="mt-1 h-4 w-4 border-slate-400 text-brand-600 focus:ring-brand-600">
                            <span>
                                <span class="block font-bold">{{ $label }}</span>
                                <span class="mt-0.5 block text-sm text-slate-700">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            @error('role')<p role="alert" class="rq-error">{{ $message }}</p>@enderror

            <button type="submit" class="rq-btn mt-7 w-full" wire:loading.attr="disabled" wire:target="continueToDetails">Lanjutkan</button>
        </form>
    @else
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight">Lengkapi data Anda</h1>
        <p class="mt-3 text-[15px] text-slate-700">
            Mendaftar sebagai <strong>{{ $allRoles[$role][0] ?? '' }}</strong>.
            <button type="button" wire:click="backToRole" class="rq-link cursor-pointer">Ganti peran</button>
        </p>

        @if ($this->needsApproval())
            <p class="mt-5 border-l-4 border-amber-600 bg-amber-50 px-4 py-3 text-sm text-amber-950">Akun ini menunggu aktivasi Admin Pusdalops sebelum dapat digunakan.</p>
        @endif

        <form wire:submit="register" class="mt-7 space-y-5" novalidate>
            <div>
                <label for="name" class="rq-label">Nama lengkap</label>
                <input id="name" type="text" wire:model="name" autocomplete="name" class="rq-input">
                @error('name')<span class="rq-error">{{ $message }}</span>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="email" class="rq-label">Email</label>
                    <input id="email" type="email" wire:model="email" autocomplete="email" class="rq-input">
                    @error('email')<span class="rq-error">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="phone" class="rq-label">Nomor HP</label>
                    <input id="phone" type="tel" wire:model="phone" autocomplete="tel" inputmode="tel" placeholder="081234567890" class="rq-input">
                    @error('phone')<span class="rq-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div>
                <label for="regency" class="rq-label">Wilayah</label>
                <select id="regency" wire:model="regency" class="rq-input">
                    @foreach (\App\Livewire\Auth\Register::REGENCIES as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('regency')<span class="rq-error">{{ $message }}</span>@enderror
            </div>

            @if ($role === 'relawan')
                <fieldset>
                    <legend class="rq-label">Keahlian</legend>
                    <div class="grid gap-x-4 gap-y-2.5 sm:grid-cols-2">
                        @foreach ($availableSkills as $skill)
                            <label wire:key="skill-{{ $skill->id ?? $skill['id'] }}" class="flex cursor-pointer items-center gap-2.5 text-[15px]">
                                <input type="checkbox" wire:model="skills" value="{{ $skill->id ?? $skill['id'] }}" class="h-4 w-4 rounded border-slate-400 text-brand-600 focus:ring-brand-600">
                                {{ $skill->name ?? $skill['name'] }}
                            </label>
                        @endforeach
                    </div>
                    @error('skills')<span class="rq-error">{{ $message }}</span>@enderror
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="radius_km" class="rq-label">Jangkauan tugas</label>
                        <select id="radius_km" wire:model="radius_km" class="rq-input">
                            @foreach ([5, 10, 20, 50] as $radius)
                                <option value="{{ $radius }}">Hingga {{ $radius }} km</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="availability" class="rq-label">Kesiapan</label>
                        <select id="availability" wire:model="availability" class="rq-input">
                            <option value="siap">Siap ditugaskan</option>
                            <option value="tidak_siap">Belum siap</option>
                        </select>
                    </div>
                </div>
            @endif

            @if ($role === 'nakes')
                <div>
                    <label for="str_number" class="rq-label">Nomor STR</label>
                    <input id="str_number" type="text" wire:model="str_number" class="rq-input">
                    @error('str_number')<span class="rq-error">{{ $message }}</span>@enderror
                </div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="rq-label">Kata sandi</label>
                    <input id="password" type="password" wire:model="password" autocomplete="new-password" class="rq-input">
                    <span class="mt-1.5 block text-[13px] text-slate-600">Minimal 8 karakter.</span>
                    @error('password')<span class="rq-error">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="rq-label">Ulangi kata sandi</label>
                    <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" class="rq-input">
                </div>
            </div>

            <button type="submit" class="rq-btn w-full" wire:loading.attr="disabled" wire:target="register">
                <span wire:loading.remove wire:target="register">{{ $this->needsApproval() ? 'Kirim pendaftaran' : 'Buat akun' }}</span>
                <span wire:loading wire:target="register">Memproses...</span>
            </button>
        </form>
    @endif

    <p class="mt-7 border-t border-line pt-5 text-[15px] text-slate-700">
        Sudah punya akun? <a href="{{ route('login') }}" class="rq-link">Masuk</a>
    </p>
</div>
