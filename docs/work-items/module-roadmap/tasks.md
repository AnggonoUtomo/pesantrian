# Tasks: Module Roadmap SakaSantri

## Sebelum Mulai

- [x] `AGENTS.md` dibaca.
- [x] `docs/README.md`, `ARCHITECTURE.md`, `FOLDER-STRUCTURE.md`, dan
  `MODULES.md` dibaca.
- [x] Kode starterkit dan module existing diinventarisasi secara read-only.
- [x] Verifikasi runtime foundation dijalankan.

## Task 1: Foundation Compatibility Audit

**Description:** Audit consumer untuk 4 module existing sebelum rename/move
apapun.

**Acceptance criteria:**

- [ ] Daftar route name, permission key, Inertia component path, test, seeder,
  migration, dan public contract untuk `System/*` tersedia.
- [ ] Risiko rename `System -> Console` dan `AuditLog -> AuditTrail` tercatat.
- [ ] Baseline final `Console` disetujui, dengan `System/*` hanya sebagai bridge
  sementara bila dibutuhkan.
- [ ] Keputusan bridge/rename disetujui user.

**Verification:**

- [ ] `php artisan route:list --path=system --no-ansi`.
- [ ] `php artisan module:validate --no-ansi`.
- [ ] Focused tests module foundation.

**Dependencies:** None.

**Estimated scope:** M.

## Task 2: Generator Baseline Alignment

**Description:** Selaraskan `module:make` dengan baseline SakaSantri sebelum
module domain baru dibuat.

**Acceptance criteria:**

- [ ] Generator tidak membuat folder optional tanpa concern nyata secara default.
- [ ] Command/dokumentasi mendukung istilah `Namespace` sambil menjaga
  backward compatibility `--domain`.
- [ ] Dry-run untuk `Organization Organization` dan `Academic AcademicPeriod`
  valid.
- [ ] Test generator diperbarui.

**Verification:**

- [ ] `php artisan module:make Organization --domain=Organization --dry-run --json`.
- [ ] `php artisan module:make AcademicPeriod --domain=Academic --dry-run --json`.
- [ ] `php artisan test --filter=ModuleMakeCommandTest`.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Task 1.

**Estimated scope:** M.

## Task 3: Frontend Module Path Decision

**Description:** Putuskan dan dokumentasikan cara menyelaraskan
`resources/js/pages/System/*` ke baseline `resources/js/modules/*`.

**Acceptance criteria:**

- [ ] Mapping current page path ke target module path tersedia.
- [ ] Keputusan bridge atau move bertahap tertulis.
- [ ] Ziggy/Inertia naming tidak berubah tanpa compatibility plan.

**Verification:**

- [ ] `npm run types:check`.
- [ ] `npm run build`.
- [ ] Focused browser smoke bila route/UI dipindah.

**Dependencies:** Task 1.

**Estimated scope:** S untuk keputusan, M bila mulai move path.

## Task 4: Organization Foundation

**Description:** Bangun module pertama domain pesantren sebagai source of truth
yayasan, unit, lokasi, dan hierarchy.

**Acceptance criteria:**

- [ ] Module `Organization/Organization` valid.
- [ ] Table inti memakai ULID.
- [ ] Read/list dan create/update unit minimum tersedia.
- [ ] Permission dan audit tersedia.

**Verification:**

- [ ] Focused feature/unit tests Organization.
- [ ] `php artisan module:validate --no-ansi`.
- [ ] `php artisan starter:verify --no-ansi`.

**Dependencies:** Tasks 1-2.

**Estimated scope:** M per vertical slice.

## Task 5: Academic Period Foundation

**Description:** Bangun module periode akademik yang menjadi context untuk
Academic, Finance, dan Reporting.

**Acceptance criteria:**

- [ ] Module `Academic/AcademicPeriod` valid.
- [ ] Academic year/term dan active period tersedia.
- [ ] Query contract active period siap dipakai consumer.
- [ ] Permission dan audit tersedia.

**Verification:**

- [ ] Focused tests AcademicPeriod.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Task 4.

**Estimated scope:** M.

## Task 6: People Core / Kesantrian Slice

**Description:** Bangun urutan people core: HumanResource, Guardian, lalu
Student. Area kesantrian memakai namespace teknis `StudentLife`.

**Acceptance criteria:**

- [ ] HumanResource menyediakan employee/staff/teacher lookup.
- [ ] Guardian menyediakan identity/contact dan relation contract.
- [ ] Student menyediakan master, guardian link, unit assignment, dan lifecycle
  awal.
- [ ] Event `StudentRegistered` tersedia.

**Verification:**

- [ ] Focused tests per module.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Tasks 4-5.

**Estimated scope:** M per module slice.

## Task 7: Residential and Academic Core

**Description:** Bangun Dormitory dan Academic setelah Student dan HR contract
siap.

**Acceptance criteria:**

- [ ] Dormitory room placement dan occupancy minimum berjalan.
- [ ] Academic class/subject/assignment/attendance minimum berjalan.
- [ ] Tidak ada direct mutation lintas module.

**Verification:**

- [ ] Focused tests Dormitory dan Academic.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Task 6.

**Estimated scope:** M per vertical slice.

## Task 8: Finance, Document, Communication, Notification, Reporting

**Description:** Bangun capability P4 setelah data core tersedia.

**Acceptance criteria:**

- [ ] Document adapter siap untuk attachment dan document reference.
- [ ] StudentFinance dapat issue invoice dan record payment minimum.
- [ ] Announcement dapat publish ke audience dasar.
- [ ] Notification menangani database/email event dasar.
- [ ] Reporting membaca projection/query dari module core.

**Verification:**

- [ ] Focused tests per module.
- [ ] `php artisan module:validate --no-ansi`.
- [ ] Build frontend bila UI ditambahkan.

**Dependencies:** Tasks 4-7.

**Estimated scope:** M per vertical slice.

## Checkpoint

- [ ] Setelah Phase 0, user review dan setujui keputusan compatibility.
- [ ] Setelah Phase 1, user review Organization dan AcademicPeriod sebagai
  foundation bisnis.
- [ ] Setelah Phase 2, user review data orang utama sebelum Dormitory/Academic.
- [ ] Setelah Phase 4, review release awal dan putuskan expansion.
- [ ] Expansion/P5 tetap ditunda sampai ada keputusan baru.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
