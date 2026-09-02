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
| Frontend | UI module berada di `resources/js/pages/<Namespace>/<Module>/*`; `System/*` tetap source existing untuk area Sistem. |
| Generator | `module:make <Namespace> <Module>` tersedia untuk baseline baru; `--domain` tetap alias kompatibilitas. |
| Vocabulary produk | Nama tampil dan dokumentasi produk memakai Bahasa Indonesia; identifier teknis existing tidak di-rename tanpa work item migrasi. |

## Mapping Current ke Target

| Current source | Target produk | Keputusan rencana |
| --- | --- | --- |
| `System/AccessControl` | Sistem / Kontrol Akses | Pertahankan source; nama tampil memakai Bahasa Indonesia. |
| `System/UserManagement` | Sistem / Pengguna | Pertahankan source; jangan merge fisik dulu karena route, permission, UI, tests, dan contract sudah banyak. |
| `System/SystemSetting` | Sistem / Pengaturan Sistem | Pertahankan source; setting bukan dumping ground master data. |
| `System/AuditLog` | Sistem / Audit Trail | Pertahankan source; nama produk Audit Trail, implementation tetap `AuditLog` sampai ada migrasi eksplisit. |

## Priority Scale

- P0: Foundation blocker. Harus selesai sebelum module domain banyak dibuat.
- P1: Core foundation bisnis. Menjadi dependency hampir semua module.
- P2: People core. Mulai membentuk data operasional utama pesantren.
- P3: Operational core. Workflow harian asrama, akademik, tahfidz, dan presensi.
- P4: Pengasuhan dan rekam santri. Perizinan, kedisiplinan, prestasi,
  kesehatan, pembinaan, dan alumni.
- P5: Finance, document, communication, reporting, donation, asset. Bergantung
  pada data core.
- P6: Expansion. Ditunda dulu; tidak masuk release awal.

## Phase 0: Starterkit Alignment (P0)

### 0.1 Audit compatibility foundation

Tujuan: memastikan 4 module existing punya dokumentasi module, dependency, route,
permission, migration, UI, dan test map.

Status: selesai melalui work item
[`starterkit-alignment`](../starterkit-alignment/README.md). Source `System/*`
tetap menjadi implementation namespace untuk area Sistem; rename fisik ke
istilah teknis lain tidak dilakukan di Phase 0. `Console/*` adalah rencana lama
yang tidak menjadi target langsung setelah revisi vocabulary ini.

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

### 0.4 Tetapkan strategy vocabulary `System` sebagai Sistem

Tujuan: mencegah rename besar yang mematahkan foundation dan menyelaraskan nama
tampil "Sistem" dengan source existing `System/*`.

Status: keputusan sementara sudah tercatat: `System/*` tetap implementation
namespace. Keputusan rename fisik final ditunda sampai ada work item migrasi
khusus.

Acceptance:

- ADR atau work item menyatakan apakah `System/*` tetap menjadi namespace teknis
  final, atau dipindah ke istilah teknis lain.
- Jika dipindah, ada daftar alias/bridge dan regression suite.
- Nama tampil final adalah "Sistem"; source existing tetap `System/*` sampai
  migrasi teknis disetujui.

Checkpoint P0:

- `php artisan optimize:clear --no-ansi`
- `php artisan module:validate --no-ansi`
- `php artisan starter:verify --no-ansi`
- Focused tests untuk module generator dan 4 module foundation.

Gate menuju `Organization/Organization`: Task 1 dan Task 2 roadmap selesai.
Task 3 frontend path decision sudah selesai; UI baru setelah keputusan ini
dibuat di `resources/js/pages/<Namespace>/<Module>/`.

## Phase 1: Sistem, Organisasi, dan Tahun Ajaran (P1)

### 1.1 System/AccessControl consolidation

Target capability: user account, auth integration, roles, permissions, policy,
2FA integration, profile access.

Urutan:

1. Dokumentasikan source existing sebagai capability AccessControl.
2. Tentukan batas UserManagement: tetap sub-module implementation atau digabung secara bertahap.
3. Stabilkan permission seed dan role default untuk pengguna pesantren.
4. Tambahkan audit event untuk mutation sensitif yang belum tercakup.

Dependencies: Phase 0.

### 1.2 System/SystemSetting product baseline

Target capability: setting aplikasi yang dapat diubah runtime.

Urutan:

1. Definisikan setting baseline SakaSantri: branding, timezone, date format, pagination, session, mail, numbering prefix.
2. Pastikan SystemSetting bukan dumping ground master data.
3. Tambahkan registration contract untuk module-owned setting bila belum cukup.

Dependencies: AccessControl, AuditTrail.

### 1.3 System/AuditLog / AuditTrail vocabulary

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

## Phase 2: People Core / Data Orang (P2)

Catatan istilah: nama tampil memakai Bahasa Indonesia. Source existing
`HumanResource/HumanResource` tetap stabil; module pesantrian baru memakai
candidate namespace teknis `Pesantrian` bila disetujui saat mulai module.

### 2.1 HumanResource/HumanResource

Target capability: employee, teacher, ustadz, staff, position, employment
status, assignment dasar.

Dependencies: Organization, AccessControl, AuditTrail.

Vertical slice:

- Master employee minimum.
- Assignment ke unit.
- Public contract untuk teacher/staff lookup.
- Permission `human_resource.*`.

### 2.2 Pesantrian/PenerimaanSantri

Target capability: PPDB, calon santri, status pendaftaran, verifikasi, dan
konversi menjadi santri.

Dependencies: Organization, WaliSantri bila data wali diminta saat pendaftaran,
Document bila lampiran dibutuhkan.

Vertical slice:

- Candidate registration minimum.
- Status pendaftaran.
- Validasi data awal.
- Event `PenerimaanSantriAccepted` atau vocabulary final yang disetujui.

### 2.3 Pesantrian/Santri

Target capability: data induk santri, lifecycle, link asal PPDB, unit utama,
dan snapshot wali minimum.

Dependencies: Organization, PenerimaanSantri, AccessControl, AuditTrail,
SystemSetting bila format nomor induk dibuat configurable.

Vertical slice:

- Student master dengan `student_no` sebagai business identifier dan ULID `id`.
- Snapshot wali minimum untuk kontak awal.
- Assign organization unit.
- Lifecycle status minimum: active, inactive, transferred, graduated.
- Konversi dari PPDB accepted melalui public contract PenerimaanSantri.
- Public read contract ringkas untuk Academic, Tahfidz, Finance, dan module
  santri lain saat consumer nyata tersedia.

Catatan baseline:

- `Pesantrian/Santri` berjalan dulu dengan snapshot wali minimum agar data induk
  santri bisa menjadi poros modul berikutnya.
- `Pesantrian/WaliSantri` dipromosikan setelah kebutuhan master wali reusable
  jelas.
- Nama tampil: Data Induk Santri dan Wali.

### 2.4 Pesantrian/WaliSantri

Target capability: guardian identity, contact, relation ke santri, future access
relation.

Dependencies: Santri contract, Organization, AccessControl, AuditTrail.

Vertical slice:

- Guardian master minimum.
- Contact dan family relation.
- Query contract untuk Student/Finance.
- Promosi snapshot wali Santri menjadi master wali bila dibutuhkan.

Checkpoint P2:

- Data orang utama tersedia: employee, guardian, student.
- Student dapat direlasikan ke unit dan wali.
- Contract lookup siap untuk Dormitory, Academic, Finance.
- Nama tampil memakai SDM Pesantren, Penerimaan Santri Baru, Wali Santri, dan
  Data Induk Santri.

## Phase 3: Asrama, Akademik, Tahfidz, dan Presensi (P3)

### 3.1 Academic/KelasRombel

Target capability: kelas, rombel, kurikulum minimum, penempatan santri, dan
wali kelas.

Dependencies: Organization, AcademicPeriod, Santri contract, HumanResource
contract, AuditTrail.

Vertical slice:

- Master kelas dan rombel minimum per tahun/term akademik.
- Penempatan santri aktif ke rombel.
- Assignment wali kelas dari SDM/guru.
- Kurikulum minimum sebagai label/struktur awal tanpa subject detail kompleks.

### 3.2 Pesantrian/Asrama

Target capability: dormitory, room, occupancy, placement, musyrif relation.

Dependencies: Organization, Student contract, HumanResource contract,
AuditTrail.

Vertical slice:

- Dormitory dan room master.
- Placement santri ke kamar.
- Occupancy rule minimum.
- Event `StudentPlacedInDormitory` bila consumer nyata tersedia.

### 3.3 Academic/Academic

Target capability: class, subject, teaching assignment, attendance, academic
workflow dasar.

Dependencies: Organization, AcademicPeriod, Student contract, HumanResource
contract, AuditTrail.

Vertical slice:

- Subject master dan teaching assignment lanjutan.
- Attendance akademik formal berbasis rombel/jadwal.
- Teacher assignment.

### 3.4 Pesantrian/Tahfidz

Target capability: target hafalan, setoran, murojaah, capaian, dan pembimbing
tahfidz.

Dependencies: Santri contract, HumanResource contract, AcademicPeriod bila
dibutuhkan untuk periode target.

### 3.5 Pesantrian/PresensiSantri

Target capability: presensi kegiatan santri yang tidak sepenuhnya akademik
formal, misalnya kegiatan pesantren/asrama.

Dependencies: Santri contract, Academic atau Asrama bila sumber jadwal sudah
tersedia.

Checkpoint P3:

- Santri punya konteks asrama dan akademik.
- Reporting dasar dapat mulai membaca occupancy dan class roster.

## Phase 4: Pengasuhan dan Rekam Santri (P4)

### 4.1 Pesantrian/PerizinanSantri

Target capability: izin keluar, izin pulang, izin sakit, approval ringan, status
kembali, dan riwayat izin.

Dependencies: Santri contract, WaliSantri contract bila notifikasi/approval wali
dibutuhkan, HumanResource contract bila butuh pembina/approver.

### 4.2 Pesantrian/KedisiplinanSantri

Target capability: catatan pelanggaran, kategori, poin/tingkat bila dipakai,
tindakan pembinaan, dan riwayat penyelesaian.

Dependencies: Santri contract, WaliSantri contract, HumanResource contract.

### 4.3 Pesantrian/PrestasiSantri

Target capability: catatan prestasi, kategori, tingkat, tanggal/periode, dan
lampiran bukti bila diperlukan.

Dependencies: Santri contract, Document bila lampiran diperlukan.

### 4.4 Pesantrian/KesehatanSantri

Target capability: kunjungan klinik, keluhan, tindakan awal, rujukan, dan
catatan kesehatan operasional.

Dependencies: Santri contract, HumanResource contract bila ada petugas.
Authorization lebih ketat karena data sensitif.

### 4.5 Pesantrian/PembinaanSantri

Target capability: catatan konseling/pembinaan, rencana tindak lanjut, status
pendampingan, dan aktor pembina/konselor.

Dependencies: Santri contract, HumanResource contract. Authorization lebih ketat
karena data sensitif.

### 4.6 Pesantrian/Alumni

Target capability: profil alumni, tahun lulus, kontak, dan relasi historis ke
data santri.

Dependencies: Santri contract.

## Phase 5: Keuangan, Dokumen, Komunikasi, Laporan, Donasi, dan Aset (P5)

### 5.1 Support/Document

Target capability: document metadata, attachment reference, document
requirement, adapter Spatie Media Library.

Dependencies: AccessControl, AuditTrail.

Catatan: walau baseline menaruh Document setelah Finance, module ini boleh
diprioritaskan lebih awal bila Student registration membutuhkan dokumen.

### 5.2 Finance/StudentFinance

Target capability: fee definition, invoice, payment, outstanding balance.

Dependencies: Organization, AcademicPeriod, Student contract, Guardian query,
Document bila payment proof diperlukan, AuditTrail.

Vertical slice:

- Fee definition.
- Invoice issue.
- Payment record.
- Outstanding invoice query.

### 5.3 Communication/Announcement

Target capability: announcement publishing, audience, attachment, lifecycle.

Dependencies: Organization, audience contracts, Document, Notification,
AuditTrail.

### 5.4 Support/Notification

Target capability: database/email notification, future WhatsApp/SMS adapter.

Dependencies: events dari module lain, SystemSetting runtime settings.

### 5.5 Support/Reporting

Target capability: dashboard, export, read model/projection, management view.

Dependencies: read/query contract atau projection dari module P1-P4.

### 5.6 Finance/DonationWaqf

Target capability: donatur, akad/jenis donasi atau wakaf, penerimaan, alokasi
tujuan, bukti penerimaan, dan laporan akuntabilitas.

Dependencies: Organization, Document bila bukti/lampiran diperlukan.

### 5.7 Support/Asset

Target capability: inventaris/aset, kode inventaris, unit/lokasi, kondisi,
mutasi, dan pemeliharaan minimum.

Dependencies: Organization.

Checkpoint P5:

- Alur operasional utama terhubung: student data, asrama/akademik, tagihan,
  dokumen, komunikasi, dashboard awal, donasi/wakaf, dan inventaris/aset.

## Phase 6: Expansion - Ditunda Setelah Baseline Running

Tidak dibuat pada release awal. Masuk backlog nanti setelah release awal stabil:

- Payroll.
- Procurement.
- POS/koperasi.
- Laundry.
- Perpustakaan.
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
          -> WaliSantri
          -> PenerimaanSantri
              -> Santri
                  -> KelasRombel
                  -> Asrama
                  -> Tahfidz
                  -> PresensiSantri
                  -> PerizinanSantri
                  -> KedisiplinanSantri
                  -> PrestasiSantri
                  -> KesehatanSantri
                  -> PembinaanSantri
                  -> Alumni
                  -> StudentFinance
                      -> DonationWaqf
                      -> Reporting
          -> Document
              -> Announcement
              -> StudentFinance payment proof
          -> Asset
      -> Notification
          -> Announcement
          -> Finance reminders
```

## Open Questions

- Apakah source `System/*` tetap menjadi namespace teknis final untuk Sistem,
  menggantikan rencana lama `Console/*`?
- Apakah `UserManagement` tetap module terpisah sebagai implementation detail,
  atau digabung bertahap ke `AccessControl`?
- Apakah `Support/Document` perlu naik ke Phase 2 karena registrasi santri hampir pasti
  membutuhkan dokumen?
- Payment gateway tetap expansion dan ditunda dulu.
