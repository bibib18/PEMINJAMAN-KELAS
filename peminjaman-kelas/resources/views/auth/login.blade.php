@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <h1 class="page-title">Masuk</h1>
    <p class="page-sub">Gunakan email dan kata sandi akun kampusmu.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4" novalidate>
        @csrf

        @if ($errors->any())
            <div class="alert alert-error" role="alert"><x-icon name="alert" class="mt-0.5" /><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="input" @error('email') aria-invalid="true" @enderror placeholder="nama@kampus.ac.id">
        </div>

        <div class="field">
            <label class="label" for="password">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-line-strong accent-primary"> Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn btn-primary w-full">Masuk</button>
    </form>

    <p class="mt-6 border-t border-line pt-5 text-sm text-muted">
        Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar sebagai mahasiswa/dosen</a>
    </p>
@endsection
