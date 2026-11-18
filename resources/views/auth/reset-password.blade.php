@component('layouts.auth', ['title' => 'Kata Sandi Baru'])
    <h1 class="text-3xl font-extrabold tracking-tight">Buat kata sandi baru</h1>
    <p class="mt-3 text-[15px] text-slate-700">Gunakan minimal 8 karakter.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="rq-label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="rq-input">
            @error('email')<span class="rq-error">{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="password" class="rq-label">Kata sandi baru</label>
            <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="rq-input">
            @error('password')<span class="rq-error">{{ $message }}</span>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="rq-label">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="rq-input">
        </div>

        <button type="submit" class="rq-btn w-full">Simpan kata sandi</button>
    </form>
@endcomponent
