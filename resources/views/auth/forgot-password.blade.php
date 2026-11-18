@component('layouts.auth', ['title' => 'Lupa Kata Sandi'])
    <h1 class="text-3xl font-extrabold tracking-tight">Atur ulang kata sandi</h1>
    <p class="mt-3 text-[15px] leading-relaxed text-slate-700">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>

    @if (session('status'))
        <p role="status" class="mt-6 border-l-4 border-green-700 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
        @csrf
        <div>
            <label for="email" class="rq-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="rq-input">
            @error('email')<span class="rq-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="rq-btn w-full">Kirim tautan</button>
    </form>

    <p class="mt-7 border-t border-line pt-5 text-[15px] text-slate-700">
        Sudah ingat? <a href="{{ route('login') }}" class="rq-link">Kembali masuk</a>
    </p>
@endcomponent
