<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ManagementLokasi;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lokasiStadion = ManagementLokasi::create(['nama_lokasi' => 'Stadion Utama', 'is_active' => true]);
        $lokasiGaleri = ManagementLokasi::create(['nama_lokasi' => 'Galeri Seni Kota', 'is_active' => true]);
        $lokasiTaman = ManagementLokasi::create(['nama_lokasi' => 'Taman Kota', 'is_active' => true]);

        $events = [
            [
                'user_id' => 1,
                'judul' => 'Konser Musik Rock',
                'deskripsi' => 'Nikmati malam penuh energi dengan band rock terkenal.',
                'tanggal_waktu' => '2024-08-15 19:00:00',
                'lokasi_id' => $lokasiStadion->id,
                'kategori_id' => 1,
                'gambar' => 'konser_rock.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Pameran Seni Kontemporer',
                'deskripsi' => 'Jelajahi karya seni modern dari seniman lokal dan internasional.',
                'tanggal_waktu' => '2024-09-10 10:00:00',
                'lokasi_id' => $lokasiGaleri->id,
                'kategori_id' => 2,
                'gambar' => 'pameran_seni.jpg',
            ],
            [
                'user_id' => 1,
                'judul' => 'Festival Makanan Internasional',
                'deskripsi' => 'Cicipi berbagai hidangan lezat dari seluruh dunia.',
                'tanggal_waktu' => '2024-10-05 12:00:00',
                'lokasi_id' => $lokasiTaman->id,
                'kategori_id' => 3,
                'gambar' => 'festival_makanan.jpg',
            ],
        ];
        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
