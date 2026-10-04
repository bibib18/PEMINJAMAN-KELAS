@csrf

@if ($errors->any())
    <div class="alert alert-error mb-6" role="alert"><x-icon name="alert" class="mt-0.5" /><span>Periksa kembali isian yang ditandai.</span></div>
@endif

<fieldset class="fieldset">
    <legend class="legend">Identitas ruang</legend>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="field">
            <label class="label" for="kode">Kode ruang</label>
            <input id="kode" name="kode" value="{{ old('kode', $room->kode) }}" required class="input mono" placeholder="A-101" @error('kode') aria-invalid="true" @enderror>
            @error('kode') <p class="error-text">{{ $message }}</p> @enderror
        </div>
        <div class="field sm:col-span-2">
            <label class="label" for="nama">Nama ruang</label>
            <input id="nama" name="nama" value="{{ old('nama', $room->nama) }}" required class="input" @error('nama') aria-invalid="true" @enderror>
            @error('nama') <p class="error-text">{{ $message }}</p> @enderror
        </div>
        <div class="field sm:col-span-2">
            <label class="label" for="gedung">Gedung</label>
            <input id="gedung" name="gedung" value="{{ old('gedung', $room->gedung) }}" required class="input" @error('gedung') aria-invalid="true" @enderror>
            @error('gedung') <p class="error-text">{{ $message }}</p> @enderror
        </div>
        <div class="field">
            <label class="label" for="lantai">Lantai</label>
            <input id="lantai" name="lantai" type="number" min="1" value="{{ old('lantai', $room->lantai) }}" required class="input" @error('lantai') aria-invalid="true" @enderror>
            @error('lantai') <p class="error-text">{{ $message }}</p> @enderror
        </div>
    </div>
</fieldset>

<fieldset class="fieldset mt-8">
    <legend class="legend">Kapasitas &amp; fasilitas</legend>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="field">
            <label class="label" for="kapasitas">Kapasitas (orang)</label>
            <input id="kapasitas" name="kapasitas" type="number" min="1" value="{{ old('kapasitas', $room->kapasitas) }}" required class="input" @error('kapasitas') aria-invalid="true" @enderror>
            @error('kapasitas') <p class="error-text">{{ $message }}</p> @enderror
        </div>
        <div class="field sm:col-span-2">
            <label class="label" for="fasilitas">Fasilitas</label>
            <input id="fasilitas" name="fasilitas" value="{{ old('fasilitas', $room->fasilitas) }}" class="input" placeholder="Proyektor, AC, Papan tulis" @error('fasilitas') aria-invalid="true" @enderror>
            @error('fasilitas') <p class="error-text">{{ $message }}</p> @else <p class="hint">Pisahkan dengan koma.</p> @enderror
        </div>
        <div class="field">
            <label class="label" for="status">Status</label>
            <select id="status" name="status" class="input">
                <option value="tersedia" @selected(old('status', $room->status) === 'tersedia')>Tersedia</option>
                <option value="perawatan" @selected(old('status', $room->status) === 'perawatan')>Perawatan</option>
            </select>
            @error('status') <p class="error-text">{{ $message }}</p> @enderror
        </div>
    </div>
</fieldset>

<div class="mt-8 flex gap-3 border-t border-line pt-5">
    <button type="submit" class="btn btn-primary">Simpan ruang</button>
    <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Batal</a>
</div>
