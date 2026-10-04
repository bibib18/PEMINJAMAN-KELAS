@extends('layouts.app')

@section('title', 'Jadwal Ruang')

@section('content')
    @php
        $me = auth()->user();
        // Sumbu waktu 07:00–21:00 (840 menit) dipetakan ke 0–100%.
        $toPct = function (string $t): float {
            [$h, $m] = array_map('intval', explode(':', substr($t, 0, 5)));
            return max(0, min(100, (($h * 60 + $m) - 420) / 840 * 100));
        };
        $hours = range(7, 21);
        $roomsById = $rooms->keyBy('id');
        $semua = $byRoom->flatten(1)->sortBy('jam_mulai')->values();
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Jadwal ruang</h1>
            <p class="page-sub">{{ $tanggal->translatedFormat('l, d F Y') }} · hanya peminjaman yang sudah disetujui.</p>
        </div>

        <form method="GET" action="{{ route('schedule.index') }}" class="flex items-center gap-2">
            <a href="{{ route('schedule.index', ['tanggal' => $tanggal->copy()->subDay()->toDateString()]) }}" class="btn btn-secondary !px-2.5" aria-label="Hari sebelumnya"><x-icon name="chevron-left" :size="16" /></a>
            <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()" class="input !w-auto" aria-label="Pilih tanggal">
            <a href="{{ route('schedule.index', ['tanggal' => $tanggal->copy()->addDay()->toDateString()]) }}" class="btn btn-secondary !px-2.5" aria-label="Hari berikutnya"><x-icon name="chevron-right" :size="16" /></a>
            @unless ($tanggal->isToday())
                <a href="{{ route('schedule.index') }}" class="btn btn-secondary">Hari ini</a>
            @endunless
        </form>
    </div>

    <div class="panel mt-6 overflow-x-auto">
        <div class="min-w-[44rem]">
            <div class="timeline-head border-b border-line bg-[#faf9f6] text-xs text-muted">
                <div class="sticky left-0 z-10 bg-[#faf9f6] px-4 py-2 font-semibold uppercase tracking-wider">Ruang</div>
                <div class="relative h-8">
                    @foreach ($hours as $h)
                        @php $left = ($h - 7) / 14 * 100; @endphp
                        <span class="mono absolute top-2 {{ $loop->first ? '' : ($loop->last ? '-translate-x-full pr-1' : '-translate-x-1/2') }}"
                              style="left: {{ $left }}%">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</span>
                    @endforeach
                </div>
            </div>

            @foreach ($rooms as $room)
                <div class="timeline-row border-b border-line last:border-b-0">
                    <div class="sticky left-0 z-10 bg-surface px-4 py-2.5">
                        <div class="mono text-sm font-semibold">{{ $room->kode }}</div>
                        <div class="truncate text-xs text-muted">{{ $room->nama }}</div>
                    </div>
                    <div class="timeline-track">
                        @if (! $room->isTersedia())
                            <div class="timeline-bar is-maintenance" style="left:0; width:100%">Perawatan</div>
                        @else
                            @foreach ($byRoom->get($room->id, collect()) as $b)
                                @php $l = $toPct($b->jam_mulai); $w = $toPct($b->jam_selesai) - $l; @endphp
                                <div class="timeline-bar" style="left: {{ $l }}%; width: {{ $w }}%" title="{{ $b->jam }} · {{ $b->keperluan }}">{{ $b->jam }}</div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <section class="mt-10">
        <h2 class="section-title mb-3">Rincian hari ini</h2>
        <div class="panel table-wrap">
            <table class="table table-stack">
                <thead><tr><th>Jam</th><th>Ruang</th><th>Keperluan</th><th>Peminjam</th></tr></thead>
                <tbody>
                    @forelse ($semua as $b)
                        @php $r = $roomsById[$b->room_id] ?? null; @endphp
                        <tr>
                            <td data-label="Jam" class="mono whitespace-nowrap font-semibold">{{ $b->jam }}</td>
                            <td data-label="Ruang"><span class="mono font-semibold">{{ $r?->kode }}</span> <span class="text-muted">· {{ $r?->nama }}</span></td>
                            <td data-label="Keperluan" class="max-w-xs"><span class="line-clamp-2">{{ $b->keperluan }}</span></td>
                            <td data-label="Peminjam">{{ ($me->isAdmin() || $b->user_id === $me->id) ? $b->user->name : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-muted">Tidak ada peminjaman yang disetujui pada tanggal ini. Semua ruang kosong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tanggal->greaterThanOrEqualTo(today()))
            <p class="mt-4 text-sm text-muted">Melihat slot kosong? <a href="{{ route('bookings.create', ['tanggal' => $tanggal->toDateString()]) }}" class="link">Ajukan peminjaman untuk {{ $tanggal->translatedFormat('d F') }}</a>.</p>
        @endif
    </section>
@endsection
