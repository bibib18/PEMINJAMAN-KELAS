<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@kampus.test'], [
            'name' => 'Admin Kampus',
            'password' => 'password',
            'role' => 'admin',
            'nim_nip' => 'ADM-001',
        ]);

        User::updateOrCreate(['email' => 'mahasiswa@kampus.test'], [
            'name' => 'Mahasiswa',
            'password' => 'password',
            'role' => 'user',
            'nim_nip' => '2024010001',
            'phone' => '081200000000',
        ]);

        $rooms = [
            ['A-101', 'Ruang Kuliah A-101', 'Gedung A', 1, 40, 'Proyektor, AC, Papan tulis'],
            ['A-102', 'Ruang Kuliah A-102', 'Gedung A', 1, 40, 'Proyektor, AC, Papan tulis'],
            ['A-201', 'Ruang Kuliah A-201', 'Gedung A', 2, 60, 'Proyektor, AC, Sound system'],
            ['B-101', 'Lab Komputer 1', 'Gedung B', 1, 30, 'PC, AC, Proyektor, Wi-Fi'],
            ['B-102', 'Lab Komputer 2', 'Gedung B', 1, 30, 'PC, AC, Proyektor, Wi-Fi'],
            ['B-201', 'Lab Jaringan', 'Gedung B', 2, 24, 'Switch, Router, AC, Proyektor'],
            ['C-101', 'Ruang Seminar', 'Gedung C', 1, 100, 'Proyektor, AC, Sound system, Podium'],
            ['C-201', 'Ruang Diskusi', 'Gedung C', 2, 15, 'AC, Papan tulis, TV'],
        ];

        foreach ($rooms as [$kode, $nama, $gedung, $lantai, $kapasitas, $fasilitas]) {
            Room::updateOrCreate(['kode' => $kode], compact('nama', 'gedung', 'lantai', 'kapasitas', 'fasilitas') + ['status' => 'tersedia']);
        }
    }
}
