@extends('layouts.donor')

@section('title', 'Janji Donasi Barang')
@section('back', route('donor.goods-donations.index'))

@section('content')
    <form method="POST" action="{{ route('donor.goods-donations.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="aid_need_id" value="{{ $need->id }}">

        @include('donor.goods-donations._form', [
            'submit' => 'Konfirmasi Janji Donasi'
        ])

        <p class="text-center text-xs text-gray-500">
            Setelah dikonfirmasi Anda mendapat kode donasi (contoh DN-2610-0042) untuk ditunjukkan saat menyerahkan barang.
        </p>
    </form>
@endsection