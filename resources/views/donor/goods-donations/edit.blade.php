@extends('layouts.donor')

@section('title', 'Ubah Janji Donasi')

@section('panel')
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-bold text-slate-900">Ubah janji: {{ $donation->code }}</h3>
        <a href="{{ route('donor.goods-donations.index') }}"
           class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition"
           title="Tutup panel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    </div>

    <form method="POST" action="{{ route('donor.goods-donations.update', $donation) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('donor.goods-donations._form', ['submit' => 'Simpan Perubahan'])
    </form>
@endsection
