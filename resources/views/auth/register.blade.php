@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <h1 class="page-title">Buat akun</h1>
    <p class="page-sub">Untuk mahasiswa dan dosen. Akun admin dibuat oleh pengelola sistem.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        @if ($errors->any())
            <div class="alert alert-error" role="alert"><x-icon name="alert" class="mt-0.5" /><span>Periksa kembali isian yang ditandai.</span></div>
        @endif

        <div class="field">
            <label class="label" for="name">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="input" @error('name') aria-invalid="true" @enderror>
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="field">
                <label class="label" for="nim_nip">NIM / NIP</label>
                <input id="nim_nip" name="nim_nip" value="{{ old('nim_nip') }}" required class="input mono" @error('nim_nip') aria-invalid="true" @enderror>
                @error('nim_nip') <p class="error-text">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label class="label" for="phone">No. HP <span class="font-normal text-muted">(opsional)</span></label>
                <input id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel" autocomplete="tel" class="input" @error('phone') aria-invalid="true" @enderror>
                @error('phone') <p class="error-text">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="input" @error('email') aria-invalid="true" @enderror>
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="field">
                <label class="label" for="password">Kata sandi</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input" @error('password') aria-invalid="true" @enderror>
                @error('password') <p class="error-text">{{ $message }}</p> @else <p class="hint">Minimal 8 karakter.</p> @enderror
            </div>
            <div class="field">
                <label class="label" for="password_confirmation">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">Buat akun</button>
    </form>

    <p class="mt-6 border-t border-line pt-5 text-sm text-muted">
        Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a>
    </p>
@endsection
