@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')

@section('content')
    @php $me = auth()->user(); @endphp

    <a href="{{ route('bookings.index') }}" class="link inline-flex items-center gap-1 text-sm"><x-icon name="arrow-left" :size="14" /> Peminjaman</a>
    <h1 class="page-title mt-3">Ajukan peminjaman</h1>
    <p class="page-sub">Jam operasional 07:00–21:00. Pengajuan baru berstatus menunggu sampai admin memutuskan.</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
        <form method="POST" action="{{ route('bookings.store') }}" class="panel panel-pad">
            @csrf

            @if ($errors->any())
                <div class="alert alert-error mb-6" role="alert">
                    <x-icon name="alert" class="mt-0.5" />
                    <span>Pengajuan belum bisa dikirim. Periksa {{ $errors->count() }} isian yang ditandai.</span>
                </div>
            @endif

            <fieldset class="fieldset">
                <legend class="legend">1 · Peminjam</legend>
                <div class="grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                    <div><span class="text-muted">Nama</span><div class="font-semibold">{{ $me->name }}</div></div>
                    <div><span class="text-muted">NIM / NIP</span><div class="mono font-semibold">{{ $me->nim_nip }}</div></div>
                </div>
            </fieldset>

            <fieldset class="fieldset mt-8">
                <legend class="legend">2 · Ruang dan waktu</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="field sm:col-span-2">
                        <label class="label" for="room_id">Ruang kelas</label>
                        <select id="room_id" name="room_id" required class="input" @error('room_id') aria-invalid="true" @enderror>
                            <option value="">Pilih ruang</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" data-capacity="{{ $room->kapasitas }}" @selected((int) old('room_id', $selectedRoom) === $room->id)>
                                    {{ $room->kode }} · {{ $room->nama }} ({{ $room->gedung }})
                                </option>
                            @endforeach
                        </select>
                        @error('room_id') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="tanggal">Tanggal</label>
                        <input id="tanggal" name="tanggal" type="date" min="{{ today()->toDateString() }}" value="{{ old('tanggal', $tanggal) }}" required class="input" @error('tanggal') aria-invalid="true" @enderror>
                        @error('tanggal') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="jumlah_peserta">Jumlah peserta</label>
                        <input id="jumlah_peserta" name="jumlah_peserta" type="number" min="1" value="{{ old('jumlah_peserta') }}" required class="input" @error('jumlah_peserta') aria-invalid="true" @enderror>
                        @error('jumlah_peserta') <p class="error-text">{{ $message }}</p> @else <p class="hint" id="capacity-hint">Pilih ruang untuk melihat kapasitasnya.</p> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="jam_mulai">Jam mulai</label>
                        <input id="jam_mulai" name="jam_mulai" type="time" min="07:00" max="21:00" value="{{ old('jam_mulai') }}" required class="input mono" @error('jam_mulai') aria-invalid="true" @enderror>
                        @error('jam_mulai') <p class="error-text">{{ $message }}</p> @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="jam_selesai">Jam selesai</label>
                        <input id="jam_selesai" name="jam_selesai" type="time" min="07:00" max="21:00" value="{{ old('jam_selesai') }}" required class="input mono" @error('jam_selesai') aria-invalid="true" @enderror>
                        @error('jam_selesai') <p class="error-text">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p id="conflict-warning" class="alert alert-error mt-4" role="alert" hidden></p>
                {{-- Di layar kecil panel jadwal berada di bawah form, jadi ringkasannya diulang di sini. --}}
                <p id="slots-inline" class="hint mt-3 lg:hidden" aria-live="polite"></p>
            </fieldset>

            <fieldset class="fieldset mt-8">
                <legend class="legend">3 · Tujuan penggunaan</legend>
                <div class="field">
                    <label class="label" for="keperluan">Keperluan</label>
                    <textarea id="keperluan" name="keperluan" rows="3" required maxlength="500" class="input"
                              placeholder="Contoh: Kuliah pengganti Basis Data kelas TRK 4B" @error('keperluan') aria-invalid="true" @enderror>{{ old('keperluan') }}</textarea>
                    @error('keperluan') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </fieldset>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-line pt-5">
                <button type="submit" class="btn btn-primary">Kirim pengajuan</button>
                <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>

        <aside class="panel panel-pad h-fit lg:sticky lg:top-6" aria-live="polite">
            <h2 class="section-title">Jadwal ruang</h2>
            <p class="page-sub" id="availability-date"></p>
            <div id="availability" class="mt-4 text-sm"></div>
        </aside>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var room = document.getElementById('room_id');
    var date = document.getElementById('tanggal');
    var start = document.getElementById('jam_mulai');
    var end = document.getElementById('jam_selesai');
    var peserta = document.getElementById('jumlah_peserta');
    var box = document.getElementById('availability');
    var dateLabel = document.getElementById('availability-date');
    var warn = document.getElementById('conflict-warning');
    var capHint = document.getElementById('capacity-hint');
    var inline = document.getElementById('slots-inline');
    var url = @json(route('bookings.availability'));
    var items = [];
    var seq = 0;

    function toMin(t) { var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10); }
    function line(text, cls) { var p = document.createElement('p'); p.textContent = text; if (cls) p.className = cls; return p; }

    function renderConflict() {
        var hit = null;
        if (start.value && end.value && toMin(end.value) > toMin(start.value)) {
            items.forEach(function (i) {
                if (i.status === 'disetujui' && toMin(start.value) < toMin(i.selesai) && toMin(end.value) > toMin(i.mulai)) hit = i;
            });
        }
        if (hit) {
            warn.textContent = 'Bentrok dengan peminjaman yang sudah disetujui pukul ' + hit.mulai + '–' + hit.selesai + '. Pilih jam atau ruang lain.';
            warn.hidden = false;
        } else {
            warn.hidden = true;
        }
    }

    function render() {
        box.replaceChildren();
        if (!items.length) {
            box.appendChild(line('Belum ada peminjaman pada tanggal ini. Ruang kosong sepanjang hari.', 'text-muted'));
            inline.textContent = 'Belum ada peminjaman pada tanggal ini. Ruang kosong sepanjang hari.';
            return;
        }
        inline.textContent = 'Jadwal terisi: ' + items.map(function (i) {
            return i.mulai + '–' + i.selesai + (i.status === 'disetujui' ? '' : ' (menunggu)');
        }).join(', ') + '.';
        var ul = document.createElement('ul');
        ul.className = 'divide-y divide-line';
        items.forEach(function (i) {
            var li = document.createElement('li');
            li.className = 'flex items-center justify-between gap-3 py-2';
            var t = document.createElement('span'); t.className = 'mono font-semibold'; t.textContent = i.mulai + '–' + i.selesai;
            var b = document.createElement('span');
            b.className = 'badge ' + (i.status === 'disetujui' ? 'badge-disetujui' : 'badge-pending');
            b.textContent = i.status === 'disetujui' ? 'Terpakai' : 'Menunggu';
            li.append(t, b); ul.appendChild(li);
        });
        box.appendChild(ul);
        box.appendChild(line('"Menunggu" belum memblokir jadwal, tetapi bisa disetujui lebih dulu.', 'hint mt-3'));
    }

    function load() {
        items = [];
        warn.hidden = true;
        inline.textContent = '';
        if (!room.value || !date.value) {
            dateLabel.textContent = '';
            box.replaceChildren(line('Pilih ruang dan tanggal untuk melihat jadwal yang sudah terisi.', 'text-muted'));
            return;
        }
        var my = ++seq;
        dateLabel.textContent = room.options[room.selectedIndex].text.split(' · ')[0] + ' · ' + date.value;
        box.replaceChildren(line('Memuat jadwal…', 'text-muted'));

        fetch(url + '?room_id=' + encodeURIComponent(room.value) + '&tanggal=' + encodeURIComponent(date.value), {
            headers: { 'Accept': 'application/json' }, credentials: 'same-origin'
        })
        .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(function (data) { if (my !== seq) return; items = data; render(); renderConflict(); })
        .catch(function () {
            if (my !== seq) return;
            box.replaceChildren(line('Jadwal gagal dimuat. Bentrok tetap diperiksa saat pengajuan dikirim.', 'text-muted'));
        });
    }

    function updateCapacity() {
        var opt = room.options[room.selectedIndex];
        var cap = opt && opt.dataset.capacity;
        if (peserta) peserta.max = cap || '';
        if (capHint) capHint.textContent = cap ? 'Kapasitas ruang ini ' + cap + ' orang.' : 'Pilih ruang untuk melihat kapasitasnya.';
    }

    room.addEventListener('change', function () { updateCapacity(); load(); });
    date.addEventListener('change', load);
    start.addEventListener('input', renderConflict);
    end.addEventListener('input', renderConflict);
    updateCapacity();
    load();
})();
</script>
@endpush
