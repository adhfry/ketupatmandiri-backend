<?php
namespace Database\Seeders;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::create([
            'name'     => 'Administrator Ketupat',
            'email'    => 'admin@ketupat.com',
            'password' => Hash::make('password123'), // Password default
            'role'     => 'admin',
        ]);

        // 2. Buat Akun User (Masyarakat Biasa)
        $user = User::create([
            'name'     => 'Warga Wenda',
            'email'    => 'warga@ketupat.com',
            'password' => Hash::make('password123'), // Password default
            'role'     => 'user',
        ]);

        // 3. Buat beberapa contoh Laporan untuk User tersebut
        Laporan::create([
            'user_id'   => $user->id,
            'judul'     => 'Jalan Berlubang Parah di Perempatan',
            'deskripsi' => 'Terdapat lubang sedalam 15cm yang sangat membahayakan pengendara motor, terutama saat hujan karena tertutup genangan air.',
            'foto'      => null,
            'latitude'  => -6.2088, // Contoh koordinat (Jakarta)
            'longitude' => 106.8456,
            'status'    => 'menunggu',
        ]);

        Laporan::create([
            'user_id'   => $user->id,
            'judul'     => 'Lampu Penerangan Jalan Mati',
            'deskripsi' => 'Lampu jalan di sepanjang gang utama mati sejak 3 hari lalu. Kondisi sangat gelap dan rawan kejahatan di malam hari.',
            'foto'      => null,
            'latitude'  => -6.2115,
            'longitude' => 106.8451,
            'status'    => 'diproses',
        ]);

        Laporan::create([
            'user_id'   => $user->id,
            'judul'     => 'Saluran Air Mampet',
            'deskripsi' => 'Got di depan balai warga mampet karena tumpukan sampah plastik, menyebabkan banjir saat hujan deras.',
            'foto'      => null,
            'latitude'  => -6.2135,
            'longitude' => 106.8471,
            'status'    => 'selesai',
        ]);
    }
}
