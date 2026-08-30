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
- [x] Risiko rename `System` dan `AuditLog` tercatat.
- [x] Nama tampil final memakai Bahasa Indonesia: Sistem dan Audit Trail.
- [x] Keputusan sementara: `System/*` tetap implementation namespace; rename
  fisik butuh work item migrasi terpisah.

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

## Task 6: People Core / Data Orang

**Description:** Bangun urutan people core: HumanResource, PPDB, WaliSantri,
lalu Santri. Nama tampil memakai Bahasa Indonesia; source existing
HumanResource tetap stabil.

**Acceptance criteria:**

- [x] HumanResource menyediakan employee/staff/teacher foundation dan read page.
- [x] Dokumentasi awal `Pesantrian/PenerimaanSantri` tersedia untuk review.
- [x] Skeleton module `Pesantrian/PenerimaanSantri` dibuat dan registry valid.
- [x] Data foundation `Pesantrian/PenerimaanSantri` tersedia dengan schema
  calon santri, wali snapshot, status, administrasi biaya, dan checklist
  dokumen.
- [x] API read/list dan create/update minimum `Pesantrian/PenerimaanSantri`
  tersedia untuk flow internal/admin.
- [ ] PenerimaanSantri menyediakan calon santri, status pendaftaran, dan
  konversi awal.
- [ ] WaliSantri menyediakan identity/contact dan relation contract.
- [ ] Santri menyediakan master, wali link, unit assignment, dan lifecycle
  awal.
- [ ] Event `SantriRegistered` atau vocabulary final yang disetujui tersedia.

**Verification:**

- [ ] Focused tests per module.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Tasks 4-5.

**Estimated scope:** M per module slice.

## Task 7: Asrama, Akademik, Tahfidz, dan Presensi

**Description:** Bangun Asrama, Academic, Tahfidz, dan PresensiSantri setelah
Santri dan HR contract siap.

**Acceptance criteria:**

- [ ] Asrama room placement dan occupancy minimum berjalan.
- [ ] Academic class/subject/assignment/attendance akademik minimum berjalan.
- [ ] Tahfidz/hafalan minimum berjalan.
- [ ] Presensi kegiatan santri minimum berjalan.
- [ ] Tidak ada direct mutation lintas module.

**Verification:**

- [ ] Focused tests Asrama, Academic, Tahfidz, dan PresensiSantri.
- [ ] `php artisan module:validate --no-ansi`.

**Dependencies:** Task 6.

**Estimated scope:** M per vertical slice.

## Task 8: Pengasuhan dan Rekam Santri

**Description:** Bangun Perizinan, Kedisiplinan, Prestasi, Kesehatan,
Pembinaan, dan Alumni sebagai module operasional santri setelah data Santri
stabil.

**Acceptance criteria:**

- [ ] PerizinanSantri memiliki lifecycle izin dan status kembali.
- [ ] KedisiplinanSantri memiliki catatan pelanggaran dan tindak lanjut.
- [ ] PrestasiSantri memiliki catatan prestasi.
- [ ] KesehatanSantri dan PembinaanSantri memakai authorization lebih ketat.
- [ ] Alumni berasal dari transisi lifecycle Santri.

**Verification:**

- [ ] Focused tests per module.
- [ ] `php artisan module:validate --no-ansi`.
- [ ] Build frontend bila UI ditambahkan.

**Dependencies:** Task 6.

**Estimated scope:** M per vertical slice.

## Task 9: Finance, Document, Communication, Notification, Reporting, Donation, Asset

**Description:** Bangun capability keuangan, dokumen, komunikasi, laporan,
donasi/wakaf, dan inventaris/aset setelah data core tersedia.

**Acceptance criteria:**

- [ ] Document adapter siap untuk attachment dan document reference.
- [ ] StudentFinance dapat issue invoice dan record payment minimum.
- [ ] Announcement dapat publish ke audience dasar.
- [ ] Notification menangani database/email event dasar.
- [ ] Reporting membaca projection/query dari module core.
- [ ] DonationWaqf mencatat donasi/wakaf terpisah dari tagihan santri.
- [ ] Asset mencatat inventaris/aset dan lokasi/unit.

**Verification:**

- [ ] Focused tests per module.
- [ ] `php artisan module:validate --no-ansi`.
- [ ] Build frontend bila UI ditambahkan.

**Dependencies:** Tasks 4-8.

**Estimated scope:** M per vertical slice.

## Checkpoint

- [x] Setelah Phase 0, user review dan setujui keputusan compatibility untuk
  mulai `Organization/Organization` tanpa rename fisik `System/*`.
- [ ] Setelah Phase 1, user review Organization dan AcademicPeriod sebagai
  foundation bisnis.
- [ ] Setelah Phase 2, user review data orang utama sebelum Dormitory/Academic.
- [ ] Setelah Phase 3, user review asrama, akademik, tahfidz, dan presensi.
- [ ] Setelah Phase 4, user review pengasuhan dan rekam santri.
- [ ] Setelah Phase 5, review release awal dan putuskan expansion.
- [ ] Koperasi dan Perpustakaan tetap ditunda sampai baseline aplikasi running.
- [ ] Expansion tetap ditunda sampai ada keputusan baru.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
