@extends('layouts.app')

@section('title', 'Detail Peminjaman')

@section('content')
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <a href="{{ route('bookings.index') }}" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" :size="14" /> Peminjaman</a>

    <div class="mt-3 flex flex-wrap items-center gap-3">
        <h1 class="page-title">Peminjaman <span class="mono">{{ $booking->room->kode }}</span></h1>
        <x-status-badge :status="$booking->status_tampil" />
    </div>
    <p class="page-sub">Diajukan {{ $booking->created_at->translatedFormat('d F Y, H:i') }}</p>

    @if ($booking->status === 'pending')
        <div class="alert alert-info mt-6 max-w-3xl"><x-icon name="clock" class="mt-0.5" /><span>Menunggu keputusan admin. Kamu masih bisa membatalkan pengajuan ini selama belum berlangsung.</span></div>
    @elseif ($booking->status === 'ditolak')
        <div class="alert alert-error mt-6 max-w-3xl"><x-icon name="x" class="mt-0.5" /><span>Pengajuan ditolak. Lihat alasannya di bawah, lalu ajukan ulang dengan ruang atau jam lain.</span></div>
    @endif

    <div class="panel panel-pad mt-6 max-w-3xl">
        <dl class="grid gap-x-8 gap-y-5 sm:grid-cols-2">
            <div><dt class="eyebrow">Ruang</dt><dd class="mt-1 font-semibold">{{ $booking->room->nama }}</dd><dd class="text-sm text-muted">{{ $booking->room->gedung }}, lantai {{ $booking->room->lantai }}</dd></div>
            <div><dt class="eyebrow">Tanggal</dt><dd class="mt-1 font-semibold">{{ $booking->tanggal->translatedFormat('l, d F Y') }}</dd><dd class="mono text-sm text-muted">{{ $booking->jam }}</dd></div>
            <div><dt class="eyebrow">Peminjam</dt><dd class="mt-1 font-semibold">{{ $booking->user->name }}</dd><dd class="mono text-sm text-muted">{{ $booking->user->nim_nip }}</dd></div>
            <div><dt class="eyebrow">Peserta</dt><dd class="mt-1 font-semibold"><span class="mono">{{ $booking->jumlah_peserta }}</span> dari <span class="mono">{{ $booking->room->kapasitas }}</span> kursi</dd></div>
            <div class="sm:col-span-2"><dt class="eyebrow">Keperluan</dt><dd class="mt-1">{{ $booking->keperluan }}</dd></div>
        </dl>

        @if ($booking->catatan_admin || $booking->approver)
            <div class="mt-6 border-t border-line pt-5">
                <div class="eyebrow">Keputusan admin</div>
                @if ($booking->catatan_admin)<p class="mt-1">{{ $booking->catatan_admin }}</p>@endif
                @if ($booking->approver)
                    <p class="mt-1 text-sm text-muted">{{ $booking->approver->name }}, {{ $booking->approved_at?->translatedFormat('d F Y, H:i') }}</p>
                @endif
            </div>
        @endif

        @if ($booking->bisaDibatalkan() || ($isAdmin && $booking->status === 'pending'))
            <div class="mt-6 flex flex-wrap gap-3 border-t border-line pt-5">
                @if ($isAdmin && $booking->status === 'pending')
                    <a href="{{ route('approvals.index') }}" class="btn btn-primary">Proses di halaman persetujuan</a>
                @endif
                @if ($booking->bisaDibatalkan())
                    <form method="POST" action="{{ route('bookings.cancel', $booking) }}"
                          data-confirm="Batalkan peminjaman {{ $booking->room->kode }} pada {{ $booking->tanggal->translatedFormat('d F Y') }} pukul {{ $booking->jam }}? Ruang akan tersedia lagi untuk orang lain."
                          data-confirm-label="Ya, batalkan" data-confirm-tone="danger">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-danger-outline">Batalkan peminjaman</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@endsection
