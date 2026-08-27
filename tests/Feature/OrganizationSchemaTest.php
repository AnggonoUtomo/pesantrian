<?php

use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use Illuminate\Support\Facades\Schema;

it('memiliki schema organization units dengan ULID dan field minimum', function (): void {
    expect(Schema::hasTable('organization_units'))->toBeTrue()
        ->and(Schema::hasColumns('organization_units', [
            'id',
            'parent_id',
            'code',
            'name',
            'type',
            'status',
            'location_name',
            'created_at',
            'updated_at',
        ]))->toBeTrue()
        ->and(Schema::getColumnType('organization_units', 'id'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('organization_units', 'parent_id'))->toBeIn(['string', 'varchar']);
});

it('membuat organization unit record dengan ULID otomatis', function (): void {
    $unit = OrganizationUnitRecord::query()->create([
        'code' => 'YA',
        'name' => 'Yayasan Saka',
        'type' => 'foundation',
        'status' => 'active',
        'location_name' => 'Kantor Yayasan',
    ]);

    expect((string) $unit->getKey())->toHaveLength(26)
        ->and($unit->code)->toBe('YA')
        ->and($unit->status)->toBe('active');

    $this->assertDatabaseHas('organization_units', [
        'id' => $unit->getKey(),
        'code' => 'YA',
        'name' => 'Yayasan Saka',
    ]);
});
