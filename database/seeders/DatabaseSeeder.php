<?php

namespace Database\Seeders;

use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Semua seeder module dipanggil dari satu entry point global.
        // Urutan mengikuti dependency: authorization lebih dahulu, lalu module
        // yang memakai capability tersebut.
        $this->call([
            AccessControlSeeder::class,
        ]);
    }
}
