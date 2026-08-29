# Specification: HumanResource/HumanResource

## Status

Increment 4 selesai: module memiliki backend API minimum untuk read/list,
create, update, validasi payload, dan authorization backend.

## Tujuan dan Scope

Bangun module `HumanResource/HumanResource` sebagai source of truth SDM
SakaSantri.

Scope slice awal:

- module skeleton dan metadata;
- table employee minimum dengan ULID;
- business identifier `employee_no`;
- assignment pegawai ke unit organisasi;
- status lifecycle aktif/nonaktif;
- tipe kerja awal untuk teacher, ustadz, musyrif, staff administrasi, staff
  finance, dan kepala unit;
- permission `human_resource.view` dan `human_resource.manage`;
- read/list employee minimum;
- create/update employee minimum;
- activate/deactivate employee;
- audit mutation employee;
- candidate employee lookup contract sebagai pegangan consumer berikutnya.

## Arsitektur

- Hexagon: `app/Modules/HumanResource/HumanResource`.
- Inbound adapter:
  - HTTP API atau web route backend minimum;
  - UI/Inertia ditunda sampai backend foundation valid.
- Use case awal:
  - `ListEmployees`;
  - `CreateEmployee`;
  - `UpdateEmployee`;
  - `ActivateEmployee`;
  - `DeactivateEmployee`;
  - `AssignEmployeeToUnit`.
- Candidate public contract:
  - `EmployeeLookupReader`;
  - `EmployeeLookupData`;
  - `TeacherLookupReader` bila `Academic/Academic` menjadi consumer nyata.
- Outbound port:
  - repository employee bila query/mutation membutuhkan boundary eksplisit.
- Outbound adapter:
  - Eloquent model/repository di Infrastructure.
- Composition root:
  - `app/Modules/HumanResource/HumanResource/ServiceProvider.php`.

Domain dibuat ketika rule murni mulai bernilai, misalnya validasi lifecycle
employee, assignment aktif per unit, atau rule teacher/ustadz yang dipakai
consumer akademik. Jika rule masih CRUD sederhana, implementasi awal boleh
berada di Application dengan test yang ketat.

## Di Luar Scope

- Payroll, gaji, tunjangan, slip gaji, pajak, dan reimbursement.
- User account, authentication, role, permission, 2FA, impersonation, dan
  lifecycle login.
- Jadwal mengajar detail, subject assignment, rombel, attendance akademik, dan
  assessment.
- Placement asrama, room occupancy, dan jadwal musyrif detail.
- Document upload dan employee document lifecycle.
- Import/export employee.
- Approval workflow HR kompleks.
- Multi-yayasan/multi-tenant SaaS.

## Contract

### Input

Input employee minimum:

- `employee_no`: string wajib, unik, contoh `EMP-2026-0012`;
- `name`: string wajib;
- `preferred_name`: string opsional;
- `employment_type`: teacher/ustadz/musyrif/finance_staff/administration_staff/unit_head/staff;
- `position`: string opsional atau reference ringan sesuai kebutuhan data;
- `status`: active/inactive;
- `joined_on`: date opsional;
- `left_on`: date opsional, wajib kosong saat employee active;
- `primary_unit_id`: ULID opsional ke Organization bila penempatan utama
  dibutuhkan pada slice awal;
- `notes`: string opsional dan tidak boleh memuat data sensitif.

Input assignment unit minimum:

- `employee_id`: ULID wajib;
- `organization_unit_id`: ULID wajib;
- `role`: string wajib, misalnya `teacher`, `musyrif`, atau `unit_head`;
- `starts_on`: date opsional;
- `ends_on`: date opsional, harus setelah `starts_on` bila terisi;
- `is_primary`: boolean.

### Output

Output read/list minimum:

- `id`;
- `employee_no`;
- `name`;
- `employment_type`;
- `position`;
- `status`;
- `primary_unit`;
- `joined_on`;
- `left_on`.

Candidate lookup output untuk consumer:

- `id`;
- `employee_no`;
- `display_name`;
- `employment_type`;
- `primary_unit_id`;
- `is_active`.

### Failure

- Validasi gagal: `422`.
- Actor tidak punya permission: `403`.
- Record tidak ditemukan: `404`.
- Duplicate `employee_no`: `422`.
- Unit organisasi tidak valid atau tidak aktif: `422`.
- Deactivate employee ditolak bila masih punya assignment aktif yang wajib
  ditutup lebih dulu, bila rule ini disetujui pada increment lifecycle.

## Data

Table kandidat:

- `employees`
  - `id` ULID primary;
  - `employee_no` string unique;
  - `name` string;
  - `preferred_name` nullable string;
  - `employment_type` string;
  - `position` nullable string atau FK bila reference table diperlukan;
  - `status` string;
  - `joined_on` nullable date;
  - `left_on` nullable date;
  - `primary_unit_id` nullable ULID;
  - `notes` nullable text;
  - `created_at`, `updated_at`.
- `employee_unit_assignments`
  - `id` ULID primary;
  - `employee_id` ULID foreign key;
  - `organization_unit_id` ULID;
  - `role` string;
  - `starts_on` nullable date;
  - `ends_on` nullable date;
  - `is_primary` boolean;
  - `created_at`, `updated_at`.

Traceability awal cukup dari audit mutation dan timestamp. Riwayat employment
detail dapat diperluas pada increment berikutnya bila kebutuhan operasional
menuntut.

## Authorization dan Audit

- `human_resource.view`: membaca daftar/detail employee.
- `human_resource.manage`: create/update/activate/deactivate/assign employee.
- Backend permission wajib dicek di controller atau middleware route.
- Audit mutation minimum:
  - `human_resource.employee.created`;
  - `human_resource.employee.updated`;
  - `human_resource.employee.activated`;
  - `human_resource.employee.deactivated`;
  - `human_resource.employee.assigned_to_unit`.

Metadata audit tidak boleh memuat password, token, credential, atau payload
sensitif. Relasi ke user account tidak dibuat otomatis pada slice awal.

## UI

UI penuh ditunda sampai backend foundation valid. Bila UI ditambahkan pada
increment berikutnya:

- Page canonical:
  `resources/js/pages/HumanResource/HumanResource/pages/Index.tsx`.
- Komponen business-specific ditempatkan di
  `resources/js/pages/HumanResource/HumanResource/components/`.
- `Index.tsx` wajib minimal sebagai komposer page/layout.
- Routing memakai Ziggy named routes.
- State wajib mencakup loading, empty, validation error, success toast, dan
  authorization UX.

## Dependency

- `Organization/Organization`: dependency produk untuk assignment employee ke
  unit. Slice awal tidak mengambil model/repository Organization secara
  langsung; validasi unit harus lewat boundary yang disetujui atau data
  reference minimum yang aman.
- `System/AccessControl` bridge: permission backend.
- `System/AuditLog` bridge: audit recorder existing sampai vocabulary final
  `Console/AuditTrail` dimigrasikan.

Tidak ada dependency ke StudentLife, Academic, Finance, Dormitory, Document,
Communication, atau Reporting pada slice awal.

## Acceptance Criteria

- [x] Module `HumanResource/HumanResource` dapat dibuat oleh generator dan
  valid.
- [x] Migration employee minimum memakai ULID.
- [x] Permission `human_resource.view` dan `human_resource.manage` tersedia.
- [x] Actor dengan permission dapat list/create/update employee minimum.
- [x] Actor tanpa permission ditolak oleh backend.
- [x] Duplicate `employee_no` ditolak.
- [x] Assignment ke unit organisasi memiliki foundation schema dan relasi
  persistence minimum.
- [ ] Activate/deactivate employee menjaga lifecycle dasar.
- [ ] Mutation employee mencatat audit/event aman.
- [ ] Candidate lookup contract terdokumentasi tanpa mengekspos model
  Infrastructure.
- [x] `php artisan module:validate --no-ansi` lulus.
- [x] Focused tests data foundation HumanResource lulus.
- [x] Focused feature tests backend HumanResource lulus.

## Risiko Terbuka

- Relasi `User Account` ke `Employee` belum diputuskan untuk slice awal; baseline
  memisahkan account login dari employee identity.
- Bentuk final `position` masih bisa berupa string sederhana atau reference
  table tergantung kebutuhan data nyata.
- Rule deactivate employee dengan assignment aktif perlu diputuskan saat
  increment lifecycle.
- Public employee lookup belum dibuat di source sampai consumer pertama
  disetujui.
