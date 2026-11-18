@component('layouts.auth', ['title' => 'Masuk'])
    <h1 class="text-3xl font-extrabold tracking-tight">Masuk ke akun Anda</h1>

    @if (str_contains((string) session('url.intended'), '/laporan'))
        <p class="mt-3 text-[15px] leading-relaxed text-slate-700">Masuk dengan akun warga untuk mengirim laporan kejadian. Belum punya akun? <a href="{{ route('register', ['peran' => 'warga']) }}" class="rq-link">Daftar sebagai warga</a>.</p>
    @elseif (str_contains((string) session('url.intended'), '/donor/'))
        <p class="mt-3 text-[15px] leading-relaxed text-slate-700">Masuk dengan akun donatur untuk melanjutkan donasi. Belum punya akun? <a href="{{ route('register', ['peran' => 'donatur']) }}" class="rq-link">Daftar sebagai donatur</a>.</p>
    @else
        <p class="mt-3 text-[15px] text-slate-700">Gunakan email atau nomor HP yang terdaftar.</p>
    @endif

    @if (session('status'))
        <p role="status" class="mt-6 border-l-4 border-green-700 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert" class="mt-6 border-l-4 border-red-700 bg-red-50 px-4 py-3 text-sm text-red-900">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
        @csrf

        <div>
            <label for="login" class="rq-label">Email atau nomor HP</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" class="rq-input">
        </div>

        <div x-data="{ show: false }">
            <div class="mb-1.5 flex items-baseline justify-between gap-4">
                <label for="password" class="text-sm font-semibold text-ink">Kata sandi</label>
                <a href="{{ route('password.request') }}" class="rq-link text-sm">Lupa kata sandi?</a>
            </div>
            <div class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="current-password" class="rq-input pr-20">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 cursor-pointer px-3 text-sm font-semibold text-slate-600 hover:text-ink" x-text="show ? 'Sembunyikan' : 'Lihat'">Lihat</button>
            </div>
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-[15px] text-slate-700">
            <input name="remember" type="checkbox" class="h-4 w-4 rounded border-slate-400 text-brand-600 focus:ring-brand-600">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="rq-btn w-full">Masuk</button>
    </form>

    <p class="mt-7 border-t border-line pt-5 text-[15px] text-slate-700">
        Belum punya akun? <a href="{{ route('register') }}" class="rq-link">Daftar sekarang</a>
    </p>

    @if (app()->environment('local'))
        <details class="mt-5 text-sm text-slate-700">
            <summary class="cursor-pointer font-semibold">Akun demo untuk pengujian</summary>
            <p class="mt-2 text-[13px] text-slate-600">Klik salah satu untuk mengisi form. Kata sandi semua akun: password.</p>
            <div class="mt-2 grid grid-cols-2 gap-1.5">
                @foreach ([
                    'Admin' => 'admin@resqhub.id',
                    'Koordinator' => 'koordinator.budi@resqhub.id',
                    'Nakes' => 'nakes.hendra@resqhub.id',
                    'Relawan' => 'relawan.rian@resqhub.id',
                    'Donatur' => 'donatur.anisa@resqhub.id',
                    'Warga' => 'warga.ahmad@resqhub.id',
                ] as $label => $email)
                    <button type="button" onclick="document.getElementById('login').value='{{ $email }}';document.getElementById('password').value='password'" class="h-9 cursor-pointer rounded-md border border-line bg-white px-3 text-left text-[13px] font-medium hover:border-ink">{{ $label }}</button>
                @endforeach
            </div>
        </details>
    @endif
@endcomponent
