@extends('layouts.donor')

@section('title', 'Ubah Janji Donasi')
@section('back', route('donor.goods-donations.index'))

@section('content')
    <form method="POST" action="{{ route('donor.goods-donations.update', $donation) }}" class="space-y-4">
        @csrf
        @method('PUT')
        @include('donor.goods-donations._form', ['submit' => 'Simpan Perubahan'])
    </form>
@endsection

