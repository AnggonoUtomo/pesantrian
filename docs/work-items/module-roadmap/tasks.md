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

- [x] Daftar route name, permission key, Inertia component path, test, seeder,
  migration, dan public contract untuk `System/*` tersedia.
- [x] Risiko rename `System -> Console` dan `AuditLog -> AuditTrail` tercatat.
- [x] Baseline final `Console` disetujui, dengan `System/*` hanya sebagai bridge
  sementara bila dibutuhkan.
- [x] Keputusan sementara: `System/*` tetap implementation bridge; rename fisik
  butuh work item migrasi terpisah.

**Verification:**

- [x] `php artisan route:list --path=system --no-ansi`.
- [x] `php artisan module:validate --no-ansi`.
- [x] `php artisan starter:verify --no-ansi`.

**Evidence:** selesai melalui
[`../starterkit-alignment/`](../starterkit-alignment/).

**Dependencies:** None.

**Estimated scope:** M.

## Task 2: Generator Baseline Alignment

**Description:** Selaraskan `module:make` dengan baseline SakaSantri sebelum
module domain baru dibuat.

**Acceptance criteria:**

- [x] Generator tidak membuat folder optional tanpa concern nyata secara default.
- [x] Command/dokumentasi mendukung istilah `Namespace` sambil menjaga
  backward compatibility `--domain`.
- [x] Dry-run untuk `Organization Organization` dan `Academic AcademicPeriod`
  valid.
- [x] Test generator diperbarui.

**Verification:**

- [x] `php artisan module:make Organization Organization --dry-run --json --no-ansi`.
- [x] `php artisan module:make Academic AcademicPeriod --dry-run --json --no-ansi`.
- [x] `php artisan test --filter=ModuleMakeCommandTest`.
- [x] `php artisan module:validate --no-ansi`.

**Evidence:** selesai melalui
[`../starterkit-alignment/module-generation.md`](../starterkit-alignment/module-generation.md).

**Dependencies:** Task 1.

**Estimated scope:** M.

## Task 3: Frontend Module Path Decision

**Description:** Putuskan dan dokumentasikan cara menyelaraskan
`resources/js/pages/System/*` ke namespace final saat consolidation disetujui.

**Acceptance criteria:**

- [x] Mapping current page path ke target module path tersedia.
- [x] Keputusan bridge atau move bertahap tertulis.
- [x] Ziggy/Inertia naming tidak berubah tanpa compatibility plan.

**Verification:**

- [x] `npm run types:check`.
- [x] `npm run build`.
- [x] Focused browser smoke tidak diperlukan karena belum ada route/UI yang
  dipindah pada Task 3 ini.

**Evidence:** keputusan tersedia di
[`frontend-module-path.md`](frontend-module-path.md).

**Dependencies:** Task 1.

**Estimated scope:** S untuk keputusan, M bila mulai move path.

**Gate:** selesai sebagai keputusan. UI module baru memakai
`resources/js/pages/<Namespace>/<Module>/*`; UI existing tetap bridge sampai ada work item migrasi
frontend per module.

## Task 4: Organization Foundation

**Description:** Bangun module pertama domain pesantren sebagai source of truth
yayasan, unit, lokasi, dan hierarchy.

**Acceptance criteria:**

- [x] Module `Organization/Organization` valid.
- [x] Table inti memakai ULID.
- [x] Read/list dan create/update unit minimum tersedia.
- [x] Permission dan audit tersedia.

**Verification:**

- [x] Focused feature/unit tests Organization.
- [x] `php artisan module:validate --no-ansi`.
- [x] `php artisan starter:verify --no-ansi`.

**Evidence:** selesai melalui
[`../../modules/Organization/Organization/`](../../modules/Organization/Organization/).
Slice aktif sudah mencakup API/web read-list, create/update, hierarchy parent
dasar, archive/restore non-destruktif, pagination UI, sidebar namespace, dan
audit mutation.

**Dependencies:** Tasks 1-2.

**Estimated scope:** M per vertical slice.

**Status:** selesai untuk foundation Organization. Public contract lintas module
belum dibuat karena belum ada consumer nyata.

## Task 5: Academic Period Foundation

**Description:** Bangun module periode akademik yang menjadi context untuk
Academic, Finance, dan Reporting.

**Acceptance criteria:**

- [x] Module `Academic/AcademicPeriod` valid.
- [x] Academic year/term dan active period tersedia.
- [x] Candidate query contract active period siap menjadi pegangan consumer.
- [x] Permission dan audit tersedia.

**Verification:**

- [x] Focused tests AcademicPeriod.
- [x] `php artisan module:validate --no-ansi`.

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

- [x] Setelah Phase 0, user review dan setujui keputusan compatibility untuk
  mulai `Organization/Organization` tanpa rename fisik `System -> Console`.
- [ ] Setelah Phase 1, user review Organization dan AcademicPeriod sebagai
  foundation bisnis.
- [ ] Setelah Phase 2, user review data orang utama sebelum Dormitory/Academic.
- [ ] Setelah Phase 4, review release awal dan putuskan expansion.
- [ ] Expansion/P5 tetap ditunda sampai ada keputusan baru.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
