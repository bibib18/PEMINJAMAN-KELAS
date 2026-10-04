@extends('layouts.app')

@section('title', 'Persetujuan')

@section('content')
    <h1 class="page-title">Persetujuan</h1>
    <p class="page-sub">Pengajuan yang menunggu keputusan, dari tanggal terdekat. Setiap keputusan meminta konfirmasi dan tidak bisa diubah.</p>

    @if ($errors->has('catatan_admin'))
        <div class="alert alert-error mt-6" role="alert"><x-icon name="alert" class="mt-0.5" /><span>{{ $errors->first('catatan_admin') }}</span></div>
    @endif

    <div class="panel mt-6 divide-y divide-line">
        @forelse ($bookings as $b)
            @php
                $ringkas = $b->room->kode.' pada '.$b->tanggal->translatedFormat('d F Y').' pukul '.$b->jam.' oleh '.$b->user->name;
            @endphp
            <article class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <h2 class="font-bold">{{ $b->tanggal->translatedFormat('l, d F Y') }} <span class="mono ml-1 font-semibold">{{ $b->jam }}</span></h2>
                            @if ($b->bentrok) <x-status-badge status="bentrok" /> @endif
                        </div>
                        <p class="mt-1 text-sm">
                            <span class="mono font-semibold">{{ $b->room->kode }}</span> · {{ $b->room->nama }}
                            <span class="text-muted">({{ $b->room->gedung }})</span>
                        </p>
                        <p class="text-sm text-muted">
                            {{ $b->user->name }} <span class="mono">({{ $b->user->nim_nip }})</span> ·
                            <span class="mono">{{ $b->jumlah_peserta }}/{{ $b->room->kapasitas }}</span> peserta ·
                            diajukan {{ $b->created_at->diffForHumans() }}
                        </p>
                    </div>
                    <a href="{{ route('bookings.show', $b) }}" class="link text-sm">Detail</a>
                </div>

                <p class="mt-3 max-w-2xl border-l-2 border-line-strong pl-3 text-sm">{{ $b->keperluan }}</p>

                @if ($b->bentrok)
                    <p class="mt-3 text-sm font-medium text-[#a12a1d]">Jadwal ini bentrok dengan peminjaman yang sudah disetujui, jadi tidak bisa disetujui. Tolak, atau minta peminjam memilih jam lain.</p>
                @endif

                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    <form method="POST" action="{{ route('approvals.approve', $b) }}" class="flex gap-2"
                          data-confirm="Setujui peminjaman {{ $ringkas }}? Jadwal ruang akan terkunci untuk waktu tersebut."
                          data-confirm-label="Setujui">
                        @csrf @method('PATCH')
                        <input name="catatan_admin" class="input" maxlength="500" placeholder="Catatan untuk peminjam (opsional)" aria-label="Catatan persetujuan">
                        <button type="submit" class="btn btn-primary shrink-0" @disabled($b->bentrok)><x-icon name="check" :size="16" /> Setujui</button>
                    </form>

                    <form method="POST" action="{{ route('approvals.reject', $b) }}" class="flex gap-2"
                          data-confirm="Tolak peminjaman {{ $ringkas }}? Alasan yang kamu tulis akan dilihat peminjam."
                          data-confirm-label="Tolak pengajuan" data-confirm-tone="danger">
                        @csrf @method('PATCH')
                        <input name="catatan_admin" class="input" maxlength="500" required placeholder="Alasan penolakan (wajib)" aria-label="Alasan penolakan">
                        <button type="submit" class="btn btn-danger-outline shrink-0"><x-icon name="x" :size="16" /> Tolak</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="px-5 py-14 text-center">
                <p class="font-semibold">Tidak ada pengajuan yang menunggu.</p>
                <p class="mt-1 text-sm text-muted">Pengajuan baru akan muncul di sini dan di penanda menu Persetujuan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endsection
