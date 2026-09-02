<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Database\Seeders;

use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use Illuminate\Database\Seeder;

final class OrganizationDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $foundation = $this->upsertUnit('DEMO-YAYASAN', 'Yayasan Saka Santri', 'foundation', null, 'active', 'Kantor Yayasan');
        $pesantren = $this->upsertUnit('DEMO-PESANTREN', 'Pesantren Saka Santri', 'pesantren', $foundation->id, 'active', 'Komplek Utama');

        $this->upsertUnit('DEMO-MTS', 'MTs Saka Santri', 'education_unit', $pesantren->id, 'active', 'Gedung MTs');
        $this->upsertUnit('DEMO-MA', 'MA Saka Santri', 'education_unit', $pesantren->id, 'active', 'Gedung MA');
        $this->upsertUnit('DEMO-ASRAMA-PUTRA', 'Asrama Putra', 'dormitory', $pesantren->id, 'active', 'Blok Putra');
        $this->upsertUnit('DEMO-ASRAMA-PUTRI', 'Asrama Putri', 'dormitory', $pesantren->id, 'active', 'Blok Putri');
        $this->upsertUnit('DEMO-ARSIP', 'Unit Demo Nonaktif', 'education_unit', $pesantren->id, 'inactive', 'Gedung Lama');
    }

    private function upsertUnit(
        string $code,
        string $name,
        string $type,
        ?string $parentId,
        string $status,
        ?string $locationName,
    ): OrganizationUnitRecord {
        $unit = OrganizationUnitRecord::firstOrNew(['code' => $code]);
        $unit->fill([
            'name' => $name,
            'type' => $type,
            'parent_id' => $parentId,
            'status' => $status,
            'location_name' => $locationName,
        ]);
        $unit->save();

        return $unit;
    }
}
