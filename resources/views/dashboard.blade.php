@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
        <p class="text-gray-600">Selamat datang, {{ Auth::user()->name }}</p>
    </div>

    <livewire:dashboard />
@endsection