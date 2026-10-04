@extends('layouts.app')

@section('title', 'Ubah Ruang')

@section('content')
    <a href="{{ route('rooms.index') }}" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" :size="14" /> Ruang kelas</a>
    <h1 class="page-title mt-3">Ubah ruang <span class="mono">{{ $room->kode }}</span></h1>

    <form method="POST" action="{{ route('rooms.update', $room) }}" class="panel panel-pad mt-6 max-w-3xl">
        @method('PUT')
        @include('rooms._form')
    </form>
@endsection
