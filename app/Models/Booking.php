<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'jumlah_peserta',
        'keperluan',
        'status',
        'catatan_admin',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Peminjaman disetujui lain yang waktunya beririsan di ruang & tanggal yang sama.
     * $mulai / $selesai harus berformat H:i:s.
     */
    public function scopeBentrok(Builder $q, int $roomId, string $tanggal, string $mulai, string $selesai, ?int $exceptId = null): Builder
    {
        return $q->where('room_id', $roomId)
            ->whereDate('tanggal', $tanggal)
            ->where('status', 'disetujui')
            ->where('jam_mulai', '<', $selesai)
            ->where('jam_selesai', '>', $mulai)
            ->when($exceptId, fn ($qq) => $qq->where('id', '!=', $exceptId));
    }

    /** Disetujui dan waktunya belum lewat. */
    public function scopeAkan(Builder $q): Builder
    {
        $now = now();

        return $q->where('status', 'disetujui')->where(function ($w) use ($now) {
            $w->whereDate('tanggal', '>', $now->toDateString())
                ->orWhere(fn ($x) => $x->whereDate('tanggal', $now->toDateString())
                    ->where('jam_selesai', '>', $now->format('H:i:s')));
        });
    }

    /** Disetujui dan waktunya sudah lewat (ditampilkan sebagai "Selesai"). */
    public function scopeSudahLewat(Builder $q): Builder
    {
        $now = now();

        return $q->where('status', 'disetujui')->where(function ($w) use ($now) {
            $w->whereDate('tanggal', '<', $now->toDateString())
                ->orWhere(fn ($x) => $x->whereDate('tanggal', $now->toDateString())
                    ->where('jam_selesai', '<=', $now->format('H:i:s')));
        });
    }

    public function getJamAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5).' – '.substr($this->jam_selesai, 0, 5);
    }

    public function sudahSelesai(): bool
    {
        return $this->tanggal->copy()->setTimeFromTimeString($this->jam_selesai)->isPast();
    }

    /** Status untuk ditampilkan: "disetujui" yang sudah lewat menjadi "selesai". */
    public function getStatusTampilAttribute(): string
    {
        return $this->status === 'disetujui' && $this->sudahSelesai() ? 'selesai' : $this->status;
    }

    public function bisaDibatalkan(): bool
    {
        return in_array($this->status, ['pending', 'disetujui'], true) && ! $this->sudahSelesai();
    }
}
