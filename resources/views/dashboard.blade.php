
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $berikutnya = $isAdmin ? null : $mendatang->first();
    @endphp

    <div class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">SISTEM INFORMASI AKADEMIK</p>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">
                Ringkasan aktivitas peminjaman ruang kelas.
            </p>
        </div>

        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <x-icon name="plus" :size="16" />
            Ajukan peminjaman
        </a>
    </div>

    <div class="dashboard-date">
        <x-icon name="calendar" :size="16" />
        {{ now()->translatedFormat('l, d F Y') }}
    </div>

    {{-- Ringkasan statistik --}}
    <div class="statline dashboard-stats">
        @if ($isAdmin)
            <a href="{{ route('approvals.index') }}" class="stat">
                <span class="stat-label">Menunggu persetujuan</span>
                <span class="stat-value {{ $stats['pending'] ? 'text-accent' : '' }}">
                    {{ $stats['pending'] }}
                </span>
            </a>

            <a href="{{ route('schedule.index') }}" class="stat">
                <span class="stat-label">Dipakai hari ini</span>
                <span class="stat-value">{{ $stats['hari_ini'] }}</span>
            </a>

            <a href="{{ route('bookings.index') }}" class="stat">
                <span class="stat-label">Pengajuan bulan ini</span>
                <span class="stat-value">{{ $stats['bulan_ini'] }}</span>
            </a>

            <a href="{{ route('rooms.index') }}" class="stat">
                <span class="stat-label">Ruang terdaftar</span>
                <span class="stat-value">{{ $stats['ruang'] }}</span>
            </a>
        @else
            <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="stat">
                <span class="stat-label">Menunggu persetujuan</span>
                <span class="stat-value {{ $stats['pending'] ? 'text-accent' : '' }}">
                    {{ $stats['pending'] }}
                </span>
            </a>

            <a href="{{ route('bookings.index', ['status' => 'disetujui']) }}" class="stat">
                <span class="stat-label">Disetujui, belum berlangsung</span>
                <span class="stat-value">{{ $stats['disetujui'] }}</span>
            </a>

            <a href="{{ route('bookings.index', ['status' => 'ditolak']) }}" class="stat">
                <span class="stat-label">Ditolak</span>
                <span class="stat-value">{{ $stats['ditolak'] }}</span>
            </a>

            <a href="{{ route('bookings.index') }}" class="stat">
                <span class="stat-label">Total pengajuan</span>
                <span class="stat-value">{{ $stats['total'] }}</span>
            </a>
        @endif
    </div>

    @if ($isAdmin)
        {{-- Antrean persetujuan --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <h2 class="section-title">Perlu tindakan</h2>

                @if ($antrean->isNotEmpty())
                    <a href="{{ route('approvals.index') }}" class="link text-sm">
                        Lihat semua
                    </a>
                @endif
            </div>

            @if ($antrean->isEmpty())
                <div class="empty-state">
                    <p class="empty-state-title">Tidak ada pengajuan tertunda</p>
                    <p class="text-sm text-muted">
                        Pengajuan baru akan muncul di bagian ini.
                    </p>
                </div>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead>
                            <tr>
                                <th>Tanggal &amp; jam</th>
                                <th>Ruang</th>
                                <th>Peminjam</th>
                                <th>Keperluan</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($antrean as $b)
                                <tr>
                                    <td data-label="Waktu" class="whitespace-nowrap">
                                        <div class="font-semibold">
                                            {{ $b->tanggal->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="mono text-muted">{{ $b->jam }}</div>
                                    </td>
                                    <td data-label="Ruang">
                                        <span class="mono font-semibold">{{ $b->room->kode }}</span>
                                    </td>
                                    <td data-label="Peminjam">{{ $b->user->name }}</td>
                                    <td data-label="Keperluan" class="max-w-xs">
                                        <span class="line-clamp-2">{{ $b->keperluan }}</span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('approvals.index') }}" class="btn btn-secondary btn-sm">
                                            Tinjau
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Jadwal terdekat --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <h2 class="section-title">Jadwal terdekat</h2>
                <a href="{{ route('schedule.index') }}" class="link text-sm">
                    Lihat jadwal ruang
                </a>
            </div>

            @if ($mendatang->isEmpty())
                <div class="empty-state">
                    <p class="text-sm text-muted">
                        Belum ada peminjaman yang disetujui.
                    </p>
                </div>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead>
                            <tr>
                                <th>Tanggal &amp; jam</th>
                                <th>Ruang</th>
                                <th>Peminjam</th>
                                <th>Keperluan</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mendatang as $b)
                                <tr>
                                    <td data-label="Waktu" class="whitespace-nowrap">
                                        <div class="font-semibold">
                                            {{ $b->tanggal->isToday() ? 'Hari ini' : $b->tanggal->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="mono text-muted">{{ $b->jam }}</div>
                                    </td>
                                    <td data-label="Ruang">
                                        <span class="mono font-semibold">{{ $b->room->kode }}</span>
                                        <span class="text-muted">· {{ $b->room->nama }}</span>
                                    </td>
                                    <td data-label="Peminjam">{{ $b->user->name }}</td>
                                    <td data-label="Keperluan" class="max-w-xs">
                                        <span class="line-clamp-2">{{ $b->keperluan }}</span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('bookings.show', $b) }}" class="link text-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @else
        {{-- Peminjaman berikutnya untuk mahasiswa --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <h2 class="section-title">Peminjaman berikutnya</h2>
            </div>

            @if ($berikutnya)
                <a href="{{ route('bookings.show', $berikutnya) }}" class="next-booking">
                    <div>
                        <p class="next-booking-date">
                            {{ $berikutnya->tanggal->isToday() ? 'Hari ini' : $berikutnya->tanggal->translatedFormat('l, d F Y') }}
                        </p>
                        <p class="next-booking-room">
                            <span class="mono font-semibold">{{ $berikutnya->room->kode }}</span>
                            · {{ $berikutnya->room->nama }},
                            {{ $berikutnya->room->gedung }}
                        </p>
                        <p class="text-sm text-muted">
                            {{ $berikutnya->jam }} · {{ $berikutnya->keperluan }}
                        </p>
                    </div>
                    <x-icon name="chevron-right" :size="18" />
                </a>
            @else
                <div class="empty-state">
                    <p class="empty-state-title">Belum ada jadwal peminjaman</p>
                    <p class="text-sm text-muted">
                        Periksa ketersediaan ruang sebelum mengajukan peminjaman.
                    </p>
                    <a href="{{ route('schedule.index') }}" class="link text-sm">
                        Lihat jadwal ruang →
                    </a>
                </div>
            @endif
        </section>

        {{-- Riwayat pengajuan --}}
        <section class="dashboard-section">
            <div class="section-heading">
                <h2 class="section-title">Pengajuan terbaru</h2>
                <a href="{{ route('bookings.index') }}" class="link text-sm">
                    Semua riwayat
                </a>
            </div>

            @if ($terbaru->isEmpty())
                <div class="empty-state">
                    <p class="empty-state-title">Belum ada pengajuan</p>
                    <p class="text-sm text-muted">
                        Pengajuan peminjaman kelas kamu akan ditampilkan di sini.
                    </p>
                    <a href="{{ route('bookings.create') }}" class="link text-sm">
                        Buat pengajuan →
                    </a>
                </div>
            @else
                <div class="panel table-wrap">
                    <table class="table table-stack">
                        <thead>
                            <tr>
                                <th>Diajukan</th>
                                <th>Ruang</th>
                                <th>Tanggal &amp; jam</th>
                                <th>Status</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($terbaru as $b)
                                <tr>
                                    <td data-label="Diajukan" class="whitespace-nowrap text-muted">
                                        {{ $b->created_at->diffForHumans() }}
                                    </td>
                                    <td data-label="Ruang">
                                        <span class="mono font-semibold">{{ $b->room->kode }}</span>
                                    </td>
                                    <td data-label="Waktu" class="whitespace-nowrap">
                                        {{ $b->tanggal->translatedFormat('d M Y') }}
                                        <span class="mono text-muted">{{ $b->jam }}</span>
                                    </td>
                                    <td data-label="Status">
                                        <x-status-badge :status="$b->status_tampil" />
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('bookings.show', $b) }}" class="link text-sm">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endsection