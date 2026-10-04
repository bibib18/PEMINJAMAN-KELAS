@props(['status'])
@php
    $label = [
        'pending' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'tersedia' => 'Tersedia',
        'perawatan' => 'Perawatan',
        'bentrok' => 'Bentrok jadwal',
    ][$status] ?? ucfirst($status);
@endphp
<span {{ $attributes->class(['badge', 'badge-'.$status]) }}>{{ $label }}</span>
