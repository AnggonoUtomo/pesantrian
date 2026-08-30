# PenerimaanSantri

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace teknis: `Pesantrian`
- Module teknis: `PenerimaanSantri`
- Nama tampil: `PPDB / Penerimaan Santri Baru`
- Candidate source: `app/Modules/Pesantrian/PenerimaanSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/PenerimaanSantri/`
- Status: `Active - UI read page`

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

Contract accepted admission untuk calon consumer `Pesantrian/Santri`
didokumentasikan di
[`contracts/accepted-admission.md`](contracts/accepted-admission.md). Runtime
contract belum dibuat karena module `Pesantrian/Santri` belum tersedia sebagai
consumer nyata.

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
  - `student_admissions`.

Nama table memakai istilah teknis Bahasa Inggris agar selaras dengan pola
source code, sedangkan nama tampil tetap PPDB / Penerimaan Santri Baru.

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
Metadata audit runtime dibuat ringkas: field yang berubah, target status, dan
hasil inti pendaftaran. Nomor telepon wali, notes bebas, dan detail checklist
dokumen tidak disimpan pada audit metadata baseline awal.

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
- Increment 3 data foundation selesai:
  - table `student_admissions`
  - model `StudentAdmissionRecord`
  - schema minimum calon santri, wali snapshot, status, administrasi biaya, dan
    checklist dokumen
- Increment 4 backend read/list dan create/update minimum selesai:
  - API list pendaftaran dengan filter, sort, dan pagination
  - API create/update pendaftaran internal/admin
  - `registration_no` auto-generate `SNTR-xxxx`
  - validasi administrasi biaya sederhana dan checklist dokumen
  - backend permission untuk view/manage
- Increment 5 lifecycle status pendaftaran selesai:
  - API verify `submitted -> verified`
  - API accept/reject `verified -> accepted/rejected`
  - API cancel `draft/submitted/verified -> cancelled`
  - terminal state `accepted/rejected/cancelled` dikunci dari transisi dan
    update biasa
  - `decided_at` dan `decided_by` dicatat saat lifecycle decision
- Increment 6 audit mutation selesai:
  - create/update pendaftaran mencatat audit event aman
  - verify/accept/reject/cancel mencatat audit event aman
  - metadata audit tidak menyimpan nomor telepon wali, notes bebas, atau detail
    checklist dokumen
- Increment 7 candidate conversion contract selesai sebagai dokumentasi:
  - accepted admission reader dan DTO candidate terdokumentasi
  - ownership, failure semantics, dan rule eligibility awal terdokumentasi
  - runtime implementation ditunda sampai `Pesantrian/Santri` tersedia sebagai
    consumer nyata
- Increment 8 UI/Inertia read page selesai:
  - web route Inertia `pesantrian.admissions.index`
  - page canonical
    `resources/js/pages/Pesantrian/PenerimaanSantri/pages/Index.tsx`
  - komponen business-specific di
    `resources/js/pages/Pesantrian/PenerimaanSantri/components/`
  - filter search/status/unit/status biaya, summary, empty state, pagination,
    dan UX unauthorized
  - menu sidebar namespace `Pesantrian` untuk PPDB
