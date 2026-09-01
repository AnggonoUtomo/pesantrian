# Santri

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `Santri`
- Nama tampil: `Data Induk Santri dan Wali`
- Candidate source: `app/Modules/Pesantrian/Santri/`
- Candidate frontend: `resources/js/pages/Pesantrian/Santri/`
- Status: Active - UI create/update/convert

## Tujuan

Module `Pesantrian/Santri` menjadi sumber data induk santri aktif di aplikasi.
Module ini menerima santri dari PPDB yang sudah diterima, dapat juga mencatat
santri internal secara administratif, lalu menyediakan data rujukan untuk modul
akademik, tahfidz, presensi, perizinan, kedisiplinan, kesehatan, pembinaan,
alumni, dan keuangan.

Nama tampil sengaja memakai Bahasa Indonesia agar mudah dikenali operator:
`Data Induk Santri dan Wali`. Nama teknis tetap `Pesantrian/Santri` supaya
source code konsisten dengan struktur DDD-lite modular monolith.

## Boundary

### Dimiliki Santri

- Nomor induk santri sebagai business identifier.
- Identitas santri minimum.
- Status lifecycle santri.
- Unit organisasi utama santri.
- Link asal dari pendaftaran PPDB bila santri berasal dari
  `Pesantrian/PenerimaanSantri`.
- Snapshot wali minimum untuk kebutuhan kontak awal.
- Audit mutasi data induk dan perubahan status.
- Public read contract ringkas untuk modul consumer.

### Tidak Dimiliki Santri

- Lifecycle calon santri, verifikasi PPDB, dan keputusan diterima/ditolak.
- Tagihan, pembayaran, tunggakan, dan alokasi keuangan.
- Penempatan kelas/rombel/kurikulum.
- Presensi, izin, pelanggaran, prestasi, tahfidz, kesehatan, dan pembinaan.
- Master wali reusable lintas santri bila relasi sudah kompleks.
- Lampiran dokumen dan file upload.

## Keputusan Baseline

Untuk baseline awal, `Pesantrian/Santri` berjalan lebih dulu dengan snapshot
wali minimum. Module `Pesantrian/WaliSantri` tetap planned dan dipromosikan
setelah kebutuhan master wali reusable terbukti, misalnya satu wali mengelola
banyak santri, menjadi billing contact, emergency contact, atau akses portal
wali.

Konversi dari PPDB wajib memakai public boundary
`Pesantrian/PenerimaanSantri`, bukan membaca table atau model Infrastructure
PPDB secara langsung.

## Dependency

- `Organization/Organization`: rujukan unit organisasi utama santri.
- `Pesantrian/PenerimaanSantri`: sumber pendaftaran accepted untuk konversi.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit perubahan data dan lifecycle.
- `System/SystemSetting`: format nomor induk santri bila sudah tersedia sebagai
  setting runtime.
- `Pesantrian/WaliSantri`: planned, belum menjadi dependency wajib pada slice
  awal.

## Identifier

- Primary key teknis memakai ULID.
- Business identifier memakai `student_no` dengan nama tampil `NIS`.
- Format baseline: `NIS` + nomor urut auto-generate dari setting, misalnya
  `NIS-0001`.
- Import atau override nomor induk existing dapat ditambahkan setelah baseline
  create/update stabil.

## Lifecycle Status

Status minimum:

- `active`: santri aktif.
- `inactive`: sementara tidak aktif.
- `transferred`: pindah/keluar.
- `graduated`: lulus.

Archive/restore adalah state operasional terpisah dari status lifecycle. Data
yang sudah dipakai modul lain tidak dihapus permanen pada baseline.

## Public Boundary Candidate

Public boundary dibuat hanya saat consumer nyata tersedia. Candidate awal:

- read model santri ringkas untuk selector dan relasi modul lain;
- lookup by `id` atau `student_no`;
- query santri aktif berdasarkan unit organisasi;
- konversi dari accepted admission PPDB menjadi data induk santri.

Consumer dilarang bergantung pada model Eloquent Infrastructure Santri.

## UI Baseline

UI baca data Santri tersedia di `resources/js/pages/Pesantrian/Santri/`.
Halaman `pages/Index.tsx` dan `pages/Show.tsx` dijaga tetap tipis; komponen
filter, table, summary, pagination, empty state, akses ditolak, badge status,
panel detail, form create/update, dan dialog konversi PPDB berada di folder
`components/`.

Menu sidebar berada di namespace Pesantrian dengan nama tampil `Data Induk
Santri` dan mengarah ke route `pesantrian.students.index`.

Mutation UI memakai route web Inertia agar mendapatkan redirect dan flash toast:

- `pesantrian.students.store`
- `pesantrian.students.update`
- `pesantrian.students.from-admission`

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../PenerimaanSantri/contracts/accepted-admission.md`](../PenerimaanSantri/contracts/accepted-admission.md)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian Santri --dry-run --json --no-ansi
php artisan module:make Pesantrian Santri --force --yes --no-ansi
php artisan module:validate
php artisan test --filter=Santri
npm run types:check
npm run lint:check
npm run build
```
