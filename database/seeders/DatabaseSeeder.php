<?php

namespace Database\Seeders;

use App\Modules\Academic\AcademicPeriod\Database\Seeders\AcademicPeriodDemoSeeder;
use App\Modules\HumanResource\HumanResource\Database\Seeders\HumanResourceDemoSeeder;
use App\Modules\Organization\Organization\Database\Seeders\OrganizationDemoSeeder;
use App\Modules\Pesantrian\PenerimaanSantri\Database\Seeders\PenerimaanSantriDemoSeeder;
use App\Modules\Pesantrian\Santri\Database\Seeders\SantriDemoSeeder;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Database\Seeders\AuditLogSeeder;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\UserManagement\Database\Seeders\UserManagementSeeder;
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
            UserManagementSeeder::class,
            AuditLogSeeder::class,
            SystemSettingSeeder::class,
            OrganizationDemoSeeder::class,
            AcademicPeriodDemoSeeder::class,
            HumanResourceDemoSeeder::class,
            PenerimaanSantriDemoSeeder::class,
            SantriDemoSeeder::class,
        ]);
    }
}
