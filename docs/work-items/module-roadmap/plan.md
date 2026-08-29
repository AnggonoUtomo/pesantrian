# Implementation Plan: Module Roadmap SakaSantri

## Overview

Roadmap ini memetakan starterkit yang sudah berjalan ke target module
SakaSantri. Tujuannya bukan membuat semua module sekaligus, tetapi memastikan
urutan pembangunan tidak melanggar dependency, tidak mematahkan route/UI/test
yang sudah ada, dan tetap mengikuti DDD-lite Modular Monolith + Hexagonal
Architecture.

## Evidence Ringkas

| Area | Kondisi terbukti |
| --- | --- |
| Runtime module | `System/AccessControl`, `System/AuditLog`, `System/SystemSetting`, `System/UserManagement` enabled dan bootable. |
| Validation | `php artisan module:validate --no-ansi` lulus. |
| Foundation | `php artisan starter:verify --no-ansi` lulus. |
| Identifier | Migration aplikasi sudah dominan memakai ULID; media table package masih memakai integer id karena mengikuti Spatie Media Library. |
| Frontend | UI module berada di `resources/js/pages/<Namespace>/<Module>/*`; `System/*` masih implementation bridge menuju vocabulary `Console`. |
| Generator | `module:make <Namespace> <Module>` tersedia untuk baseline baru; `--domain` tetap alias kompatibilitas. |

## Mapping Current ke Target

| Current source | Target produk | Keputusan rencana |
| --- | --- | --- |
| `System/AccessControl` | `Console/AccessControl` | Baseline final memakai `Console`; pertahankan source dulu sebagai bridge sampai audit consumer selesai. |
| `System/UserManagement` | bagian dari `Console/AccessControl` | Jangan merge fisik dulu. Buat compatibility plan karena route, permission, UI, tests, dan contract sudah banyak. |
| `System/SystemSetting` | `Console/SystemSetting` | Baseline final memakai `Console`; pertahankan source dulu sebagai bridge sampai generator dan docs module siap. |
| `System/AuditLog` | `Console/AuditTrail` | Baseline final memakai `Console/AuditTrail`; `AuditLog` hanya implementation bridge sampai rename disetujui. |

## Priority Scale

- P0: Foundation blocker. Harus selesai sebelum module domain banyak dibuat.
- P1: Core foundation bisnis. Menjadi dependency hampir semua module.
- P2: People core. Mulai membentuk data operasional utama pesantren.
- P3: Operational core. Workflow harian akademik/asrama.
- P4: Finance, document, communication, reporting. Bergantung pada data core.
- P5: Expansion. Ditunda dulu; tidak masuk release awal.

## Phase 0: Starterkit Alignment (P0)

### 0.1 Audit compatibility foundation

Tujuan: memastikan 4 module existing punya dokumentasi module, dependency, route,
permission, migration, UI, dan test map.

Status: selesai melalui work item
[`starterkit-alignment`](../starterkit-alignment/README.md). Source `System/*`
tetap menjadi implementation bridge; rename fisik ke `Console/*` tidak dilakukan
di Phase 0.

Acceptance:

- Mapping dokumentasi setara tersedia di
  [`starterkit-alignment`](../starterkit-alignment/README.md).
- Consumer route name, permission key, Inertia page, migration, dan public
  boundary tercatat sebelum rename.
- `module:validate`, `starter:verify`, dan route listing tetap lulus.

### 0.2 Selaraskan generator dengan baseline

Tujuan: generator menghasilkan module baru sesuai baseline SakaSantri.

Status: selesai melalui commit `3e2460b` dan didokumentasikan di
[`starterkit-alignment/module-generation.md`](../starterkit-alignment/module-generation.md).

Acceptance:

- Command tetap backward compatible dengan `--domain`, dan mendukung argumen
  `Namespace Module`.
- Default skeleton tidak membuat folder kosong tanpa concern nyata.
- `module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`, `Routes/*`, dan README dibuat konsisten.
- Dry-run untuk `Organization Organization` dan `Academic AcademicPeriod` valid tanpa menulis file.

### 0.3 Selaraskan frontend page path

Tujuan: menentukan pola final untuk UI module sebelum menambah UI baru.

Status: selesai sebagai keputusan dokumentasi. Detail mapping dan compatibility
plan tersedia di [`frontend-module-path.md`](frontend-module-path.md).

Acceptance:

- Source lama `resources/js/pages/System/*` dan UI Organization existing tetap
  sebagai bridge sampai ada work item migrasi frontend per module.
- UI module baru memakai `resources/js/pages/<Namespace>/<Module>/` sebagai
  canonical path.
- Ziggy route name, URL, permission key, dan Inertia component path tidak
  berubah tanpa compatibility plan.

### 0.4 Tetapkan strategy compatibility `System` ke `Console`

Tujuan: mencegah rename besar yang mematahkan foundation.

Status: keputusan sementara sudah tercatat: `System/*` tetap implementation
bridge. Keputusan rename fisik final ditunda sampai ada work item migrasi
khusus.

Acceptance:

- ADR atau work item menyatakan apakah `System/*` akan tetap menjadi implementation namespace, atau dipindah ke `Console/*`.
- Jika dipindah, ada daftar alias/bridge dan regression suite.
- Baseline final tetap `Console`; `System/*` hanya boleh dipertahankan sebagai
  compatibility bridge sementara bila rename langsung berisiko.

Checkpoint P0:

- `php artisan optimize:clear --no-ansi`
- `php artisan module:validate --no-ansi`
- `php artisan starter:verify --no-ansi`
- Focused tests untuk module generator dan 4 module foundation.

Gate menuju `Organization/Organization`: Task 1 dan Task 2 roadmap selesai.
Task 3 frontend path decision sudah selesai; UI baru setelah keputusan ini
dibuat di `resources/js/pages/<Namespace>/<Module>/`.

## Phase 1: Console, Organization, dan Academic Period (P1)

### 1.1 Console/AccessControl consolidation

Target capability: user account, auth integration, roles, permissions, policy,
2FA integration, profile access.

Urutan:

1. Dokumentasikan source existing sebagai capability AccessControl.
2. Tentukan batas UserManagement: tetap sub-module implementation atau digabung secara bertahap.
3. Stabilkan permission seed dan role default untuk pengguna pesantren.
4. Tambahkan audit event untuk mutation sensitif yang belum tercakup.

Dependencies: Phase 0.

### 1.2 Console/SystemSetting product baseline

Target capability: setting aplikasi yang dapat diubah runtime.

Urutan:

1. Definisikan setting baseline SakaSantri: branding, timezone, date format, pagination, session, mail, numbering prefix.
2. Pastikan SystemSetting bukan dumping ground master data.
3. Tambahkan registration contract untuk module-owned setting bila belum cukup.

Dependencies: AccessControl, AuditTrail.

### 1.3 Console/AuditTrail

Target capability: audit trail untuk aktivitas auth, access control, setting,
dan mutation module domain.

Urutan:

1. Tetapkan vocabulary product `AuditTrail` sambil menjaga implementation `AuditLog`.
2. Pastikan redaction metadata sensitif.
3. Definisikan contract `AuditRecorder` sebagai public boundary untuk module baru.

Dependencies: AccessControl, UserManagement bridge.

### 1.4 Organization/Organization

Target capability: yayasan, pesantren, unit, lokasi, hierarchy, affiliation.

Vertical slice minimum:

1. Module skeleton dan metadata.
2. Entity/table inti organization units dengan ULID.
3. CRUD read/list + create/update minimum untuk unit.
4. Permission `organization.view/manage`.
5. Audit event untuk perubahan struktur.

Dependencies: AccessControl, AuditTrail.

### 1.5 Academic/AcademicPeriod

Target capability: academic year, semester, active period, opening/closing.

Vertical slice minimum:

1. Module skeleton dan metadata.
2. Table academic years/terms dengan ULID.
3. Active period query contract untuk Academic, Finance, Reporting.
4. Permission dan audit.

Dependencies: Organization, AccessControl, AuditTrail.

Checkpoint P1:

- User admin dapat mengelola access/setting/audit.
- Admin dapat membuat unit organisasi dan periode akademik aktif.
- Module baru lulus validation dan focused tests.

## Phase 2: People Core / Pesantrian Awal (P2)

Catatan istilah: area Pesantrian memakai namespace teknis `StudentLife` agar
lebih mudah dicerna, dengan label Bahasa Indonesia "Pesantrian".

### 2.1 HumanResource/HumanResource

Target capability: employee, teacher, ustadz, staff, position, employment
status, assignment dasar.

Dependencies: Organization, AccessControl, AuditTrail.

Vertical slice:

- Master employee minimum.
- Assignment ke unit.
- Public contract untuk teacher/staff lookup.
- Permission `human_resource.*`.

### 2.2 StudentLife/Guardian

Target capability: guardian identity, contact, relation ke santri, future access
relation.

Dependencies: Organization, AccessControl, AuditTrail.

Vertical slice:

- Guardian master minimum.
- Contact dan family relation.
- Query contract untuk Student/Finance.

### 2.3 StudentLife/Student

Target capability: student master, lifecycle, registration, transfer,
graduation.

Dependencies: Organization, Guardian contract, AccessControl, AuditTrail.

Vertical slice:

- Student master dengan `student_no` sebagai business identifier dan ULID `id`.
- Link guardian.
- Assign organization unit.
- Lifecycle status minimum: active, inactive, transferred, graduated.
- Event `StudentRegistered`.

Checkpoint P2:

- Data orang utama tersedia: employee, guardian, student.
- Student dapat direlasikan ke unit dan wali.
- Contract lookup siap untuk Dormitory, Academic, Finance.

## Phase 3: Residential dan Academic Core (P3)

### 3.1 StudentLife/Dormitory

Target capability: dormitory, room, occupancy, placement, musyrif relation.

Dependencies: Organization, Student contract, HumanResource contract,
AuditTrail.

Vertical slice:

- Dormitory dan room master.
- Placement santri ke kamar.
- Occupancy rule minimum.
- Event `StudentPlacedInDormitory`.

### 3.2 Academic/Academic

Target capability: class, subject, teaching assignment, attendance, academic
workflow dasar.

Dependencies: Organization, AcademicPeriod, Student contract, HumanResource
contract, AuditTrail.

Vertical slice:

- Class/rombel dan subject master minimum.
- Student class assignment.
- Teacher assignment.
- Attendance recording minimum.

Checkpoint P3:

- Santri punya konteks asrama dan akademik.
- Reporting dasar dapat mulai membaca occupancy dan class roster.

## Phase 4: Finance, Document, Communication, Reporting (P4)

### 4.1 Platform/Document

Target capability: document metadata, attachment reference, document
requirement, adapter Spatie Media Library.

Dependencies: AccessControl, AuditTrail.

Catatan: walau baseline menaruh Document setelah Finance, module ini boleh
diprioritaskan lebih awal bila Student registration membutuhkan dokumen.

### 4.2 Finance/StudentFinance

Target capability: fee definition, invoice, payment, outstanding balance.

Dependencies: Organization, AcademicPeriod, Student contract, Guardian query,
Document bila payment proof diperlukan, AuditTrail.

Vertical slice:

- Fee definition.
- Invoice issue.
- Payment record.
- Outstanding invoice query.

### 4.3 Communication/Announcement

Target capability: announcement publishing, audience, attachment, lifecycle.

Dependencies: Organization, audience contracts, Document, Notification,
AuditTrail.

### 4.4 Platform/Notification

Target capability: database/email notification, future WhatsApp/SMS adapter.

Dependencies: events dari module lain, SystemSetting runtime settings.

### 4.5 Platform/Reporting

Target capability: dashboard, export, read model/projection, management view.

Dependencies: read/query contract atau projection dari module P1-P4.

Checkpoint P4:

- Alur operasional utama terhubung: student data, asrama/akademik, tagihan,
  dokumen, komunikasi, dan dashboard awal.

## Phase 5: Expansion (P5) - Ditunda

Tidak dibuat pada release awal. Masuk backlog nanti setelah release awal stabil:

- Payroll.
- Procurement.
- Inventory.
- Asset.
- POS/koperasi.
- Laundry.
- Klinik.
- Perpustakaan.
- Donasi/wakaf.
- Payment gateway/VA/QRIS.
- Public API penuh.
- BI kompleks.
- AI assistant.

## Dependency Graph Ringkas

```text
Phase 0 Starterkit alignment
  -> AccessControl / AuditTrail / SystemSetting
      -> Organization
          -> AcademicPeriod
          -> HumanResource
          -> Guardian
              -> Student
                  -> Dormitory
                  -> Academic
                  -> StudentFinance
                      -> Reporting
          -> Document
              -> Announcement
              -> StudentFinance payment proof
      -> Notification
          -> Announcement
          -> Finance reminders
```

## Open Questions

- Kapan source `System/*` dipindah fisik ke `Console/*` setelah compatibility
  audit selesai?
- Apakah `UserManagement` tetap module terpisah sebagai implementation detail,
  atau digabung bertahap ke `AccessControl`?
- Apakah `Document` perlu naik ke Phase 2 karena registrasi santri hampir pasti
  membutuhkan dokumen?
- Payment gateway tetap expansion dan ditunda dulu.
