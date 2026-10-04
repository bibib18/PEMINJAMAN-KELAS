<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'gedung',
        'lantai',
        'kapasitas',
        'fasilitas',
        'status',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getFasilitasListAttribute(): array
    {
        return collect(explode(',', (string) $this->fasilitas))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values()
            ->all();
    }

    public function isTersedia(): bool
    {
        return $this->status === 'tersedia';
    }
}
