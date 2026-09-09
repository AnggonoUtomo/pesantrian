# Perizinan Santri

Source module akan mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md)
dan [`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `PerizinanSantri`
- Nama tampil: `Perizinan Santri`
- Candidate source: `app/Modules/Pesantrian/PerizinanSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/PerizinanSantri/`
- Status: Active - documentation baseline ready

## Tujuan

Module `Pesantrian/PerizinanSantri` mengelola izin santri secara operasional:
izin keluar area pesantren, izin pulang, izin sakit ringan, approval internal,
status kembali, dan riwayat izin. Bahasa layar memakai istilah yang familiar
untuk operator: **Perizinan Santri**.

Baseline awal dibuat untuk admin/internal dulu. Operator membuat permohonan
izin, petugas berwenang menyetujui atau menolak, lalu mencatat santri sudah
kembali. Public form wali/santri, tanda tangan digital, dan integrasi
notifikasi otomatis ditunda sampai flow internal stabil.

UI internal sekarang mendukung list/detail serta mutation utama: buat draft,
edit draft, submit, approve, reject, check-out, return/check-in, dan void.
Semua aksi tetap divalidasi di backend.

QA browser desktop/mobile untuk lifecycle utama sudah tersedia di
`tests/Browser/perizinan-santri.spec.ts`. Panduan manual pengguna pemula
tersedia di `docs/USER-MANUAL-LIFECYCLE.md`.

## Boundary

### Dimiliki PerizinanSantri

- Permohonan izin santri.
- Jenis izin baseline: keluar, pulang, sakit, dan kegiatan khusus.
- Rentang waktu izin dan batas kembali.
- Tujuan/alasan izin.
- Kontak wali snapshot bila diperlukan.
- Approval sederhana: draft/submitted/approved/rejected.
- Check-out dan check-in/kembali.
- Pembatalan/void izin dengan alasan.
- Riwayat perubahan status dan audit.

### Tidak Dimiliki PerizinanSantri

- Data induk santri dan wali master.
- Presensi harian santri.
- Rekam medis klinik.
- Pelanggaran/kedisiplinan akibat terlambat kembali.
- Tagihan atau denda.
- Jadwal kegiatan detail.
- Surat/dokumen lampiran kompleks.
- Portal wali/santri dan public form.

## Dependency

- `Pesantrian/Santri`: sumber santri aktif untuk selector dan validasi.
- `HumanResource/HumanResource`: sumber petugas/approver aktif bila diperlukan.
- `Pesantrian/PresensiSantri`: consumer read-only awal; bisa membaca izin yang
  sudah layak dipakai pada tanggal presensi melalui public contract
  PerizinanSantri.
- `Pesantrian/Asrama`: konteks lokasi/kamar santri bila izin terkait asrama.
- `Pesantrian/WaliSantri`: planned; baseline memakai snapshot wali dari data
  santri/pendaftaran sampai master wali dibuat.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit lifecycle izin.
- `Support/Document`: planned; lampiran surat izin/sakit ditunda sampai module
  dokumen dibuat.

Dependency lintas module wajib melalui public contract/Application DTO yang
tersedia. PerizinanSantri tidak boleh membaca model Eloquent Infrastructure
module lain secara langsung.

## Contract Readiness

Contract awal yang dibutuhkan:

- `ActiveStudentReader` dari `Pesantrian/Santri` untuk selector dan validasi
  santri aktif.
- `ActiveEmployeeReader` dari `HumanResource/HumanResource` untuk selector
  approver atau petugas yang mencatat izin.
- `ApprovedStudentPermitReader` dari PerizinanSantri untuk PresensiSantri,
  membaca izin `approved`, `checked_out`, dan `returned` pada tanggal presensi.
  Hasilnya dikembalikan sebagai `StudentPermitAttendanceStatusData`.

Baseline tidak membuat contract keluar sebagai placeholder. Contract keluar
baru dibuat ketika consumer nyata sudah masuk increment integrasi.

## Lifecycle Baseline

```text
Draft izin
    -> Submit permohonan
    -> Review petugas
    -> Approved atau Rejected
    -> Check-out bila santri keluar
    -> Check-in/kembali
    -> Closed
```

Status izin minimum:

- `draft`: permohonan disiapkan, belum diajukan.
- `submitted`: permohonan diajukan dan menunggu keputusan.
- `approved`: izin disetujui.
- `rejected`: izin ditolak dengan alasan.
- `checked_out`: santri sudah keluar/izin berjalan.
- `returned`: santri sudah kembali.
- `void`: permohonan dibatalkan karena salah input atau tidak jadi dipakai.

## Public Boundary

Public boundary keluar dari PerizinanSantri yang sudah tersedia:

- `ApprovedStudentPermitReader`: daftar izin approved per tanggal untuk
  PresensiSantri.
- `LateStudentPermitReader`: daftar izin yang terlambat kembali atau overdue
  untuk KedisiplinanSantri. Reader ini read-only dan tidak membuat kasus
  pelanggaran.

Candidate tambahan yang mungkin dibutuhkan nanti:

- status izin aktif santri untuk dashboard operator;
- rekap izin untuk Reporting;
- ringkasan izin untuk portal wali/santri.

## UI Baseline

UI akan berada di `resources/js/pages/Pesantrian/PerizinanSantri/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Pesantrian/PerizinanSantri/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- PerizinanSantriDashboard.tsx
    |-- PerizinanSantriFilters.tsx
    |-- PerizinanSantriTable.tsx
    |-- PerizinanSantriDetailPanel.tsx
    |-- PerizinanSantriActionBar.tsx
    |-- PerizinanSantriFormFields.tsx
    |-- PerizinanSantriMutationDialog.tsx
    |-- PerizinanSantriLifecycleDialogs.tsx
    |-- PerizinanSantriStatusBadge.tsx
    |-- PerizinanSantriSummaryCards.tsx
    `-- PerizinanSantriPagination.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil
`Perizinan Santri`.

## Permission Candidate

- `perizinan_santri.view`
- `perizinan_santri.manage`
- `perizinan_santri.approve`
- `perizinan_santri.checkout`
- `perizinan_santri.return`
- `perizinan_santri.archive`

Permission dapat disesuaikan saat implementasi bila terlalu granular, tetapi
backend tetap menjadi authority otorisasi.

## Data Demo

Seeder demo wajib idempotent dan tidak berjalan di `production`.

Data demo tersedia melalui `PerizinanSantriDemoSeeder` dan dipanggil dari
`DatabaseSeeder` setelah data demo santri tersedia.

Data demo mencakup:

- `IZN-DEMO-DRAFT`: draft izin pulang singkat.
- `IZN-DEMO-SUBMITTED`: izin kegiatan yang menunggu review.
- `IZN-DEMO-APPROVED`: izin yang sudah disetujui.
- `IZN-DEMO-REJECTED`: izin yang ditolak dengan alasan.
- `IZN-DEMO-CHECKEDOUT`: izin berjalan/santri sudah keluar.
- `IZN-DEMO-RETURNED`: izin selesai dan santri kembali terlambat.
- `IZN-DEMO-VOID`: izin dibatalkan karena salah input.

Seeder memakai santri demo `NIS-DEMO-AKTIF` dan `NIS-DEMO-PPDB`, snapshot wali
utama dari module Santri, serta actor user demo dari AccessControl.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)
- [`../PresensiSantri/`](../PresensiSantri/)
- [`../Asrama/`](../Asrama/)
- [`../../../modules/HumanResource/HumanResource/`](../../../modules/HumanResource/HumanResource/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian PerizinanSantri --dry-run --json --no-ansi
php artisan module:make Pesantrian PerizinanSantri --force --yes --no-ansi
php artisan module:validate
php artisan test --filter=PerizinanSantri
npm run types:check
npm run lint:check
npm run build
npx playwright test tests/Browser/perizinan-santri.spec.ts
```
