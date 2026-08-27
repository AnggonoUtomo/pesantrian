# Organization

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace: `Organization`
- Module: `Organization`
- Source: `app/Modules/Organization/Organization/`
- Frontend: ditunda sampai keputusan frontend module path selesai
- Status: `Active`

## Tujuan

Module `Organization/Organization` menjadi source of truth untuk struktur
organisasi SakaSantri: yayasan, pesantren, unit pendidikan, unit operasional,
lokasi, dan relasi hierarchy dasar.

Module ini adalah foundation bisnis pertama sebelum AcademicPeriod,
HumanResource, StudentLife, Finance, Communication, dan Reporting memakai data
unit.

## Boundary

- Memiliki:
  - identitas unit organisasi,
  - tipe unit,
  - hierarchy parent-child unit,
  - status aktif/nonaktif unit,
  - metadata lokasi dasar.
- Tidak memiliki:
  - periode akademik,
  - pegawai/guru/staff,
  - santri/wali,
  - asrama/kamar,
  - kelas/rombel akademik,
  - tagihan atau pembayaran,
  - UI penuh sebelum keputusan frontend path.

## Public Boundary

Public boundary dibuat hanya ketika ada consumer nyata. Pada slice pertama,
module belum mengekspor contract lintas module.

Candidate public boundary untuk increment berikutnya:

- query active/list organization unit untuk AcademicPeriod, HumanResource, dan
  StudentLife;
- DTO ringkas unit organisasi;
- event perubahan struktur organisasi jika AuditTrail atau Reporting menjadi
  consumer nyata.

## Data dan Identifier

- Table awal: `organization_units`.
- Primary identifier: ULID.
- Business identifier: `code` untuk kode unit organisasi.
- Slug opsional dapat ditambahkan bila route/UI membutuhkan identifier baca.

## Permission dan Audit

- Permission awal:
  - `organization.view`
  - `organization.manage`
- Backend menjadi authority authorization.
- Audit perubahan struktur dicatat untuk create/update unit melalui bridge
  AuditLog/AuditTrail existing ketika slice audit diimplementasikan.

## Operasi

- Migration module berada di
  `app/Modules/Organization/Organization/Database/Migrations/`.
- Seeder/factory hanya dibuat ketika ada data/test nyata.
- `ServiceProvider.php` menjadi composition root dan tidak berisi business
  logic.
- Route file boleh kosong sampai ada endpoint nyata.

## Verifikasi Utama

```bash
php artisan module:make Organization Organization --dry-run --json --no-ansi
php artisan module:validate --no-ansi
php artisan starter:verify --no-ansi
git diff --check
```

Focused tests ditambahkan saat behavior backend pertama dibuat.

