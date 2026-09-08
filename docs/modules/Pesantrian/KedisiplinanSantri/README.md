# Pelanggaran / Kedisiplinan

Source module akan mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md)
dan [`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `KedisiplinanSantri`
- Nama tampil: `Pelanggaran / Kedisiplinan`
- Candidate source: `app/Modules/Pesantrian/KedisiplinanSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/KedisiplinanSantri/`
- Status: Active - QA browser dan user manual selesai

## Tujuan

Module `Pesantrian/KedisiplinanSantri` mengelola catatan pelanggaran santri,
kategori pelanggaran, poin/tingkat pelanggaran bila dipakai, tindakan
pembinaan, dan riwayat penyelesaian. Bahasa layar memakai istilah sederhana
untuk operator: **Pelanggaran / Kedisiplinan**.

Baseline awal dibuat untuk admin/internal dulu. Operator atau pembina mencatat
kejadian, memilih kategori/tingkat, mencatat tindak lanjut, lalu menutup kasus
ketika sudah selesai. Module ini tidak boleh menjadi catatan bebas tanpa
struktur karena datanya akan dipakai untuk pembinaan, komunikasi wali, dan
laporan.

## Boundary

### Dimiliki KedisiplinanSantri

- Catatan pelanggaran santri.
- Kategori pelanggaran.
- Tingkat/poin pelanggaran baseline.
- Kronologi kejadian dan lokasi kejadian berbasis teks/snapshot.
- Tindakan pembinaan atau sanksi edukatif.
- Penanggung jawab/pembina yang menangani.
- Status penyelesaian kasus.
- Riwayat perubahan status dan audit.

### Tidak Dimiliki KedisiplinanSantri

- Data induk santri dan wali master.
- Presensi harian santri.
- Lifecycle izin santri.
- Konseling mendalam atau catatan psikologis sensitif.
- Rekam medis klinik.
- Tagihan, denda, atau pembayaran.
- Notifikasi otomatis ke wali.
- Lampiran dokumen/bukti kompleks.

## Dependency

- `Pesantrian/Santri`: sumber santri aktif untuk selector dan validasi.
- `HumanResource/HumanResource`: sumber pembina/petugas aktif bila diperlukan.
- `Pesantrian/PresensiSantri`: planned/read-only; nanti bisa menjadi sumber
  rekap alfa/terlambat, tetapi tidak otomatis membuat pelanggaran.
- `Pesantrian/PerizinanSantri`: planned/read-only; nanti bisa menjadi sumber
  informasi terlambat kembali, tetapi tidak otomatis membuat pelanggaran.
- `Pesantrian/WaliSantri`: planned; baseline cukup menyimpan snapshot wali bila
  diperlukan untuk konteks komunikasi.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit lifecycle kedisiplinan.
- `Support/Document`: planned; lampiran bukti ditunda sampai module dokumen
  dibuat.

Dependency lintas module wajib melalui public contract/Application DTO yang
tersedia. KedisiplinanSantri tidak boleh membaca model Eloquent Infrastructure
module lain secara langsung.

## Contract Readiness

Contract awal yang diperkirakan dibutuhkan:

- `ActiveStudentReader` dari `Pesantrian/Santri` untuk selector dan validasi
  santri aktif.
- `ActiveEmployeeReader` dari `HumanResource/HumanResource` untuk selector
  pembina/petugas.

Pada source module, contract tersebut dibungkus oleh query Application
KedisiplinanSantri:

- `ListDisciplineStudentCandidates`;
- `ListDisciplineOfficerCandidates`.

Candidate contract yang mungkin dibuat setelah consumer nyata masuk:

- reader ringkasan pelanggaran aktif per santri untuk dashboard operator;
- reader riwayat kedisiplinan santri untuk wali/santri atau laporan;
- event ringan ketika kasus kedisiplinan dibuat, diselesaikan, atau dibatalkan.

Baseline tidak membuat contract keluar sebagai placeholder.

Integrasi PresensiSantri dan PerizinanSantri masih dibatasi sebagai kandidat
read-only. Data alfa/terlambat atau terlambat kembali tidak otomatis menjadi
kasus pelanggaran tanpa review manusia.

## Lifecycle Baseline

```text
Catatan draft
    -> Submit catatan
    -> Review/validasi pembina
    -> Tindak lanjut pembinaan
    -> Selesai atau Void
    -> Riwayat
```

Status catatan minimum:

- `draft`: catatan disiapkan, belum resmi.
- `submitted`: catatan diajukan untuk ditangani.
- `in_review`: sedang diverifikasi atau diklarifikasi.
- `action_assigned`: tindakan pembinaan/sanksi sudah ditentukan.
- `resolved`: kasus selesai.
- `void`: catatan dibatalkan karena salah input atau tidak valid.

## Severity dan Poin Baseline

| Severity | Nama tampil | Contoh penggunaan |
| --- | --- | --- |
| `minor` | Ringan | Terlambat kembali, tidak rapi, pelanggaran tata tertib ringan. |
| `moderate` | Sedang | Mengulang pelanggaran, meninggalkan kegiatan tanpa izin. |
| `major` | Berat | Pelanggaran serius yang butuh penanganan pengasuh/pimpinan. |

Poin pelanggaran bersifat opsional pada baseline, tetapi struktur data tetap
disiapkan agar pesantren yang memakai sistem poin tidak perlu mengganti
arsitektur.

## UI Baseline

UI akan berada di `resources/js/pages/Pesantrian/KedisiplinanSantri/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Pesantrian/KedisiplinanSantri/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- KedisiplinanSantriDashboard.tsx
    |-- KedisiplinanSantriFilters.tsx
    |-- KedisiplinanSantriTable.tsx
    |-- KedisiplinanSantriDetailPanel.tsx
    |-- KedisiplinanSantriActionBar.tsx
    |-- KedisiplinanSantriFormFields.tsx
    |-- KedisiplinanSantriMutationDialog.tsx
    |-- KedisiplinanSantriLifecycleDialogs.tsx
    |-- KedisiplinanSantriStatusBadge.tsx
    |-- KedisiplinanSantriSummaryCards.tsx
    `-- KedisiplinanSantriPagination.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil
`Pelanggaran / Kedisiplinan`.

## Permission Candidate

- `kedisiplinan_santri.view`
- `kedisiplinan_santri.manage`
- `kedisiplinan_santri.review`
- `kedisiplinan_santri.resolve`
- `kedisiplinan_santri.archive`

Permission dapat disesuaikan saat implementasi bila terlalu granular, tetapi
backend tetap menjadi authority otorisasi.

## Data Demo

Seeder demo wajib idempotent dan tidak berjalan di `production`.

Data demo minimum:

- catatan draft;
- catatan submitted;
- catatan sedang review;
- catatan dengan tindakan pembinaan;
- catatan resolved;
- catatan void;
- variasi severity ringan/sedang/berat.

Seeder memakai santri demo aktif dan petugas demo yang sudah tersedia.

Kode demo utama:

- `DIS-DEMO-DRAFT`
- `DIS-DEMO-SUBMITTED`
- `DIS-DEMO-INREVIEW`
- `DIS-DEMO-ACTION`
- `DIS-DEMO-RESOLVED`
- `DIS-DEMO-VOID`

## QA Browser

QA browser dilakukan pada halaman daftar dan detail
`/pesantrian/student-discipline-cases` untuk desktop dan mobile/responsive.

Hasil Increment 13:

- daftar bisa dibuka;
- pencarian/filter utama terlihat;
- tombol `Buat kasus` terlihat untuk user berizin;
- link detail tersedia;
- detail menampilkan lifecycle, histori revisi, dan aksi kedisiplinan;
- console browser bersih dari error/warning;
- pemeriksaan accessibility axe menghasilkan 0 violation pada desktop dan
  mobile setelah landmark layout dashboard/sidebar dirapikan.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)
- [`../PresensiSantri/`](../PresensiSantri/)
- [`../PerizinanSantri/`](../PerizinanSantri/)
- [`../../../modules/HumanResource/HumanResource/`](../../../modules/HumanResource/HumanResource/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian KedisiplinanSantri --dry-run --json --no-ansi
php artisan module:make Pesantrian KedisiplinanSantri --force --yes --no-ansi
php artisan module:validate --no-ansi
php artisan test --filter=KedisiplinanSantri
npm run types:check
npm run lint:check
npm run build
npx playwright test tests/Browser/kedisiplinan-santri.spec.ts
```
