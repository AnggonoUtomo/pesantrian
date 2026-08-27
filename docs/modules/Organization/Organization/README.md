# Organization

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace: `Organization`
- Module: `Organization`
- Source: `app/Modules/Organization/Organization/`
- Frontend: `resources/js/pages/Organization/Organization/` sebagai path
  Inertia aktif sementara sampai keputusan `resources/js/modules/*` dibuat
  melalui work item terpisah.
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
  - import/export organisasi,
  - pemindahan frontend ke `resources/js/modules/*`.

## Public Boundary

Public boundary dibuat hanya ketika ada consumer nyata. Saat ini module belum
mengekspor contract lintas module untuk consumer lain.

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
- Frontend permission hanya UX untuk menampilkan/menyembunyikan kontrol.
- Audit perubahan struktur dicatat melalui bridge AuditLog/AuditTrail existing
  untuk create, update, archive, dan restore unit organisasi.

## Operasi

- Migration module berada di
  `app/Modules/Organization/Organization/Database/Migrations/`.
- Seeder/factory hanya dibuat ketika ada data/test nyata.
- `ServiceProvider.php` menjadi composition root dan tidak berisi business
  logic.
- Route web aktif untuk halaman Inertia dan mutation form.
- Route API aktif untuk list/create/update minimum.

## Verifikasi Utama

```bash
php artisan module:make Organization Organization --dry-run --json --no-ansi
php artisan test --filter=Organization
php artisan test --filter=NavigationSidebarTest
npm run build
php artisan module:validate --no-ansi
php artisan starter:verify --no-ansi
git diff --check
```

Focused tests berada di `tests/Feature/OrganizationUnitApiTest.php`,
`tests/Feature/OrganizationUnitPresentationTest.php`, dan test terkait sidebar
serta Ziggy route.
