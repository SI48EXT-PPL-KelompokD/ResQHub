<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - ResQHub</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <header class="sticky top-0 z-10 border-b border-gray-200 bg-white">
        <div class="mx-auto flex h-14 max-w-xl items-center gap-3 px-4">
            <a href="@yield('back', route('admin.dashboard'))" aria-label="Tutup"
               class="-ml-2 flex h-11 w-11 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </a>
            <h1 class="text-lg font-semibold">@yield('title')</h1>
        </div>
    </header>

    <main class="mx-auto max-w-xl px-4 py-4">
        @if (session('success'))
            <div role="status" class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        @foreach (['status', 'aid_need_id', 'cancel_reason'] as $key)
            @error($key)
                <div role="alert" class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $message }}</div>
            @enderror
        @endforeach

        @yield('content')
    </main>
</body>
</html>