# PenerimaanSantri

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace teknis: `Pesantrian`
- Module teknis: `PenerimaanSantri`
- Nama tampil: `PPDB / Penerimaan Santri Baru`
- Candidate source: `app/Modules/Pesantrian/PenerimaanSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/PenerimaanSantri/`
- Status: `Active - skeleton module`

## Tujuan

Module `Pesantrian/PenerimaanSantri` menjadi pintu awal calon santri sebelum
menjadi data induk santri aktif. Module ini mengelola formulir pendaftaran,
status seleksi/verifikasi, data awal calon santri, data wali minimum saat
pendaftaran, dan proses penerimaan menjadi santri.

PPDB dipisahkan dari `Pesantrian/Santri` karena lifecycle calon santri berbeda
dari lifecycle santri aktif.

## Boundary

- Memiliki:
  - data calon santri;
  - nomor pendaftaran sebagai business identifier;
  - data pendaftaran awal;
  - data wali minimum yang diperlukan untuk pendaftaran;
  - status pendaftaran;
  - status administrasi biaya pendaftaran sederhana bila periode PPDB
    mewajibkan biaya;
  - checklist dokumen pendaftaran minimum;
  - proses verifikasi/seleksi minimum;
  - keputusan diterima/ditolak/batal;
  - jejak konversi calon santri menjadi santri aktif.
- Tidak memiliki:
  - data induk santri aktif final;
  - lifecycle akademik santri;
  - invoice resmi, kuitansi, payment gateway, VA, QRIS, dan rekonsiliasi
    pembayaran;
  - upload dan arsip file dokumen penuh;
  - akun login wali/santri;
  - alokasi kelas, rombel, asrama, atau tahfidz final;
  - workflow seleksi kompleks multi-tahap.

## Public Boundary

Public boundary dibuat hanya ketika ada consumer nyata.

Candidate consumer awal:

- `Pesantrian/Santri` membutuhkan data calon santri yang sudah diterima untuk
  dibuat menjadi data induk santri;
- `Pesantrian/WaliSantri` membutuhkan data wali dari pendaftaran bila wali akan
  dipromosikan menjadi master wali;
- `Finance/StudentFinance` dapat mengambil referensi pendaftaran bila biaya
  PPDB perlu dibuat sebagai invoice resmi pada increment berikutnya;
- `Support/Document` dapat mengambil referensi checklist dokumen bila upload
  dan arsip file digital sudah masuk scope berikutnya.

Candidate public boundary:

- query pendaftaran diterima yang siap dikonversi;
- DTO ringkas calon santri;
- event `CalonSantriAccepted` atau vocabulary final yang disetujui saat coding.

Consumer lintas module tidak boleh membaca model Infrastructure
`CalonSantriRecord` secara langsung.

## Data dan Identifier

- Primary identifier: ULID.
- Business identifier:
  - `registration_no`, misalnya `SNTR-0001`.
- Keputusan awal:
  - `registration_no` dibuat otomatis oleh sistem saat create;
  - baseline awal memakai konfigurasi nomor di `System/SystemSetting`, contoh
    prefix `SNTR` dengan sequence auto-generate untuk bagian `-xxxx`;
  - sebelum numbering runtime tersedia, generator nomor boleh memakai strategi
    sederhana yang tetap unik dan teruji.
- Candidate table:
  - `student_admissions` atau `calon_santri_registrations`.

Nama table final diputuskan saat increment data foundation agar selaras dengan
konvensi source dan bahasa teknis yang dipilih.

## Permission dan Audit

- Permission awal:
  - `penerimaan_santri.view`
  - `penerimaan_santri.manage`
  - `penerimaan_santri.decide`
- Backend menjadi authority authorization.
- Frontend permission hanya untuk UX.
- Audit mutation minimum:
  - pendaftaran dibuat;
  - data pendaftaran diperbarui;
  - pendaftaran diverifikasi;
  - pendaftaran diterima;
  - pendaftaran ditolak;
  - pendaftaran dibatalkan.

Metadata audit tidak boleh menyimpan data sensitif yang tidak diperlukan.

## Operasi

- Module dibuat hanya setelah dokumen ini disetujui.
- Generator memakai `php artisan module:make Pesantrian PenerimaanSantri`.
- Slice awal fokus pada data pendaftaran, status, administrasi biaya sederhana,
  dan checklist dokumen, bukan invoice resmi, payment gateway, upload file,
  atau akun portal wali.
- PPDB awal adalah flow internal/admin. Public registration form tanpa login
  ditunda sampai flow internal, validasi data, dan security boundary stabil.
- Data wali pada PPDB disimpan sebagai snapshot pendaftaran dulu. Promosi ke
  master `Pesantrian/WaliSantri` dilakukan melalui contract/use case setelah
  module WaliSantri tersedia.
- Biaya pendaftaran bersifat opsional per periode PPDB. Pada baseline awal,
  PPDB hanya menyimpan status administrasi dan nominal biaya pendaftaran
  sederhana. Invoice resmi, pembayaran, kuitansi, tunggakan, payment gateway,
  dan rekonsiliasi tetap menjadi ownership `Finance/StudentFinance`.
- Dokumen pendaftaran pada baseline awal berupa checklist verifikasi. Upload
  dan arsip file digital ditunda sampai boundary `Support/Document` atau
  document storage siap.
- Konversi menjadi Santri boleh didokumentasikan dulu sebagai candidate
  contract sampai module `Pesantrian/Santri` dibuat.

## Verifikasi Utama

```bash
php artisan module:make Pesantrian PenerimaanSantri --dry-run --json --no-ansi
php artisan module:make Pesantrian PenerimaanSantri --force --yes --no-ansi
php artisan test --filter=PenerimaanSantri
php artisan module:validate --no-ansi
php artisan starter:verify --no-ansi
npm run types:check
npm run lint:check
npm run build
git diff --check
```

## Status Implementasi

- Increment 1 dokumentasi module selesai.
- Increment 2 skeleton module selesai:
  - `app/Modules/Pesantrian/PenerimaanSantri/module.json`
  - `app/Modules/Pesantrian/PenerimaanSantri/module.php`
  - `app/Modules/Pesantrian/PenerimaanSantri/permissions.php`
  - `app/Modules/Pesantrian/PenerimaanSantri/ServiceProvider.php`
  - `app/Modules/Pesantrian/PenerimaanSantri/Routes/*`
- Data foundation, route behavior, UI, audit mutation, dan conversion contract
  belum dibuat.
