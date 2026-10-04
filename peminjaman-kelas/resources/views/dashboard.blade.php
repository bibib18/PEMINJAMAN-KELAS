@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $berikutnya = $isAdmin ? null : $mendatang->first();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary"><x-icon name="plus" :size="16" /> Ajukan peminjaman</a>
    </div>

    {{-- Ringkasan satu baris; tiap angka membuka daftar yang relevan. --}}
    <div class="statline mt-6">
        @if ($isAdmin)
            <a href="{{ route('approvals.index') }}" class="stat"><p class="stat-value {{ $stats['pending'] ? 'text-accent' : '' }}">{{ $stats['pending'] }}</p><p class="stat-label">Menunggu persetujuan</p></a>
            <a href="{{ route('schedule.index') }}" class="stat"><p class="stat-value">{{ $stats['hari_ini'] }}</p><p class="stat-label">Dipakai hari ini</p></a>
            <a href="{{ route('bookings.index') }}" class="stat"><p class="stat-value">{{ $stats['bulan_ini'] }}</p><p class="stat-label">Pengajuan bulan ini</p></a>
            <a href="{{ route('rooms.index') }}" class="stat"><p class="stat-value">{{ $stats['ruang'] }}</p><p class="stat-label">Ruang terdaftar</p></a>
        @else
            <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="stat"><p class="stat-value {{ $stats['pending'] ? 'text-accent' : '' }}">{{ $stats['pending'] }}</p><p class="stat-label">Menunggu persetujuan</p></a>
            <a href="{{ route('bookings.index', ['status' => 'disetujui']) }}" class="stat"><p class="stat-value">{{ $stats['disetujui'] }}</p><p class="stat-label">Disetujui, belum berlangsung</p></a>
            <a href="{{ route('bookings.index', ['status' => 'ditolak']) }}" class="stat"><p class="stat-value">{{ $stats['ditolak'] }}</p><p class="stat-label">Ditolak</p></a>
            <a href="{{ route('bookings.index') }}" class="stat"><p class="stat-value">{{ $stats['total'] }}</p><p class="stat-label">Total pengajuan</p></a>
        @endif
    </div>

    @if ($isAdmin)
        <section class="mt-10">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="section-title">Perlu tindakan</h2>
                @if ($antrean->isNotEmpty())
                    <a href="{{ route('approvals.index') }}" class="link text-sm">Buka halaman persetujuan</a>
                @endif
            </div>

            @if ($antrean->isEmpty())
                <p class="text-sm text-muted">Tidak ada pengajuan yang menunggu. Semua sudah diproses.</p>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead><tr><th>Tanggal &amp; jam</th><th>Ruang</th><th>Peminjam</th><th>Keperluan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                        <tbody>
                            @foreach ($antrean as $b)
                                <tr>
                                    <td data-label="Waktu" class="whitespace-nowrap"><div class="font-semibold">{{ $b->tanggal->translatedFormat('d M Y') }}</div><div class="mono text-muted">{{ $b->jam }}</div></td>
                                    <td data-label="Ruang"><span class="mono font-semibold">{{ $b->room->kode }}</span></td>
                                    <td data-label="Peminjam">{{ $b->user->name }}</td>
                                    <td data-label="Keperluan" class="max-w-xs"><span class="line-clamp-2">{{ $b->keperluan }}</span></td>
                                    <td class="text-right"><a href="{{ route('approvals.index') }}" class="btn btn-secondary btn-sm">Tinjau</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="mt-10">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="section-title">Jadwal terdekat</h2>
                <a href="{{ route('schedule.index') }}" class="link text-sm">Lihat jadwal ruang</a>
            </div>

            @if ($mendatang->isEmpty())
                <p class="text-sm text-muted">Belum ada peminjaman yang disetujui.</p>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead><tr><th>Tanggal &amp; jam</th><th>Ruang</th><th>Peminjam</th><th>Keperluan</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                        <tbody>
                            @foreach ($mendatang as $b)
                                <tr>
                                    <td data-label="Waktu" class="whitespace-nowrap"><div class="font-semibold">{{ $b->tanggal->isToday() ? 'Hari ini' : $b->tanggal->translatedFormat('d M Y') }}</div><div class="mono text-muted">{{ $b->jam }}</div></td>
                                    <td data-label="Ruang"><span class="mono font-semibold">{{ $b->room->kode }}</span> <span class="text-muted">· {{ $b->room->nama }}</span></td>
                                    <td data-label="Peminjam">{{ $b->user->name }}</td>
                                    <td data-label="Keperluan" class="max-w-xs"><span class="line-clamp-2">{{ $b->keperluan }}</span></td>
                                    <td class="text-right"><a href="{{ route('bookings.show', $b) }}" class="link text-sm">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @else
        <section class="mt-10">
            <h2 class="section-title mb-3">Peminjaman berikutnya</h2>
            @if ($berikutnya)
                <a href="{{ route('bookings.show', $berikutnya) }}" class="block border-l-4 border-primary py-1 pl-4 no-underline">
                    <p class="text-lg font-bold">
                        {{ $berikutnya->tanggal->isToday() ? 'Hari ini' : $berikutnya->tanggal->translatedFormat('l, d F Y') }}
                        <span class="mono ml-1 font-semibold">{{ $berikutnya->jam }}</span>
                    </p>
                    <p class="text-sm"><span class="mono font-semibold">{{ $berikutnya->room->kode }}</span> · {{ $berikutnya->room->nama }}, {{ $berikutnya->room->gedung }}</p>
                    <p class="text-sm text-muted line-clamp-1">{{ $berikutnya->keperluan }}</p>
                </a>
            @else
                <p class="text-sm text-muted">Belum ada peminjaman yang disetujui. <a href="{{ route('schedule.index') }}" class="link">Cek ruang yang kosong</a> lalu ajukan.</p>
            @endif
        </section>

        <section class="mt-10">
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="section-title">Pengajuan terbaru</h2>
                <a href="{{ route('bookings.index') }}" class="link text-sm">Semua riwayat</a>
            </div>

            @if ($terbaru->isEmpty())
                <p class="text-sm text-muted">Kamu belum pernah mengajukan peminjaman. <a href="{{ route('bookings.create') }}" class="link">Ajukan yang pertama</a>.</p>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead><tr><th>Diajukan</th><th>Ruang</th><th>Tanggal &amp; jam</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr></thead>
                        <tbody>
                            @foreach ($terbaru as $b)
                                <tr>
                                    <td data-label="Diajukan" class="whitespace-nowrap text-muted">{{ $b->created_at->diffForHumans() }}</td>
                                    <td data-label="Ruang"><span class="mono font-semibold">{{ $b->room->kode }}</span></td>
                                    <td data-label="Waktu" class="whitespace-nowrap">{{ $b->tanggal->translatedFormat('d M Y') }} <span class="mono text-muted">{{ $b->jam }}</span></td>
                                    <td data-label="Status"><x-status-badge :status="$b->status_tampil" /></td>
                                    <td class="text-right"><a href="{{ route('bookings.show', $b) }}" class="link text-sm">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endsection
