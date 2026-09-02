<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class BusinessDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_database_seeder_membuat_demo_lifecycle_module_bisnis_secara_idempotent(): void
    {
        $this->seed();
        $this->seed();

        self::assertSame(7, DB::table('organization_units')->where('code', 'like', 'DEMO-%')->count());
        self::assertSame(1, DB::table('organization_units')->where('code', 'DEMO-YAYASAN')->where('status', 'active')->count());
        self::assertSame(1, DB::table('organization_units')->where('code', 'DEMO-ARSIP')->where('status', 'inactive')->count());

        self::assertSame(3, DB::table('academic_years')->where('code', 'like', '202%')->count());
        self::assertSame(1, DB::table('academic_years')->where('code', '2026-2027')->where('status', 'active')->count());
        self::assertSame(1, DB::table('academic_terms')->where('code', '2026-2027-GANJIL')->where('is_active', true)->count());
        self::assertSame(1, DB::table('academic_terms')->where('code', '2025-2026-GENAP')->where('status', 'closed')->count());

        self::assertSame(6, DB::table('employees')->where('employee_no', 'like', 'PEG-DEMO-%')->count());
        self::assertSame(4, DB::table('employees')->where('employee_no', 'like', 'PEG-DEMO-%')->where('status', 'active')->count());
        self::assertSame(2, DB::table('employees')->where('employee_no', 'like', 'PEG-DEMO-%')->where('status', 'inactive')->count());
        self::assertSame(1, DB::table('employees')->where('employee_no', 'PEG-DEMO-003')->where('employment_type', 'teacher')->count());
        self::assertSame(1, DB::table('employees')->where('employee_no', 'PEG-DEMO-004')->where('employment_type', 'ustadz')->count());
        self::assertSame(6, DB::table('employee_unit_assignments')->where('role', 'like', 'demo_%')->count());

        self::assertSame(6, DB::table('student_admissions')->where('registration_no', 'like', 'PPDB-DEMO-%')->count());
        self::assertSame(1, DB::table('student_admissions')->where('registration_no', 'PPDB-DEMO-ACCEPTED')->where('status', 'accepted')->count());
        self::assertSame(1, DB::table('student_admissions')->where('registration_no', 'PPDB-DEMO-REJECTED')->where('status', 'rejected')->count());
        self::assertSame(1, DB::table('student_admissions')->where('registration_no', 'PPDB-DEMO-CANCELLED')->where('status', 'cancelled')->count());

        self::assertSame(6, DB::table('students')->where('student_no', 'like', 'NIS-DEMO-%')->count());
        self::assertSame(1, DB::table('students')->where('student_no', 'NIS-DEMO-AKTIF')->where('status', 'active')->whereNull('archived_at')->count());
        self::assertSame(1, DB::table('students')->where('student_no', 'NIS-DEMO-PINDAH')->where('status', 'transferred')->count());
        self::assertSame(1, DB::table('students')->where('student_no', 'NIS-DEMO-LULUS')->where('status', 'graduated')->count());
        self::assertSame(1, DB::table('students')->where('student_no', 'NIS-DEMO-ARSIP')->whereNotNull('archived_at')->count());
        self::assertSame(6, DB::table('student_guardians')->where('guardian_name', 'like', 'Wali Demo %')->count());

        self::assertSame(3, DB::table('academic_curricula')->where('code', 'like', 'KUR-DEMO-%')->count());
        self::assertSame(4, DB::table('class_levels')->where('code', 'like', 'DEMO-%')->count());
        self::assertSame(5, DB::table('class_groups')->where('code', 'like', 'DEMO-%')->count());
        self::assertSame(1, DB::table('class_groups')->where('code', 'DEMO-MTS-VII-A')->where('status', 'active')->whereNull('archived_at')->count());
        self::assertSame(1, DB::table('class_groups')->where('code', 'DEMO-ARSIP')->where('status', 'archived')->whereNotNull('archived_at')->count());
        self::assertSame(4, DB::table('class_group_students')->where('student_no', 'like', 'NIS-DEMO-%')->count());
        self::assertSame(2, DB::table('class_group_students')->where('status', 'active')->whereNotNull('active_period_student_key')->count());
        self::assertSame(1, DB::table('class_group_students')->where('status', 'transferred')->whereNull('active_period_student_key')->count());
        self::assertSame(1, DB::table('class_group_students')->where('status', 'removed')->whereNull('active_period_student_key')->count());
        self::assertSame(
            3,
            DB::table('class_group_homerooms')
                ->join('class_groups', 'class_groups.id', '=', 'class_group_homerooms.class_group_id')
                ->where('class_groups.code', 'like', 'DEMO-%')
                ->count(),
        );
        self::assertSame(2, DB::table('class_group_homerooms')->where('status', 'active')->whereNotNull('active_class_group_key')->count());
        self::assertSame(1, DB::table('class_group_homerooms')->where('status', 'ended')->whereNull('active_class_group_key')->count());
    }

    public function test_demo_seeder_tidak_membuat_data_bisnis_di_production(): void
    {
        Config::set('app.env', 'production');

        $this->seed();

        self::assertSame(0, DB::table('organization_units')->where('code', 'like', 'DEMO-%')->count());
        self::assertSame(0, DB::table('academic_years')->count());
        self::assertSame(0, DB::table('employees')->count());
        self::assertSame(0, DB::table('student_admissions')->count());
        self::assertSame(0, DB::table('students')->count());
        self::assertSame(0, DB::table('class_groups')->count());
        self::assertSame(0, DB::table('class_group_students')->count());
        self::assertSame(0, DB::table('class_group_homerooms')->count());
        self::assertSame(0, User::where('email', 'like', 'user-management-dummy-%@example.test')->count());
    }
}
