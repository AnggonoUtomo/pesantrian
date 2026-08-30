# Work Item: Module Roadmap SakaSantri

## Status

Active - Phase 1 foundation Organization dan AcademicPeriod selesai; Phase 2
People Core berjalan melalui HumanResource. Roadmap diselaraskan dengan baseline
operasional pesantren berbahasa Indonesia.

## Owner dan Lokasi

- Owner: lintas module.
- Target kode: `app/Modules/`, `packages/StarterKit/`, dan `resources/js/`.
- Target dokumentasi: `docs/modules/` dan `docs/work-items/module-roadmap/`.

## Kondisi Awal

Starterkit saat ini sudah memiliki foundation aktif:

| Source sekarang | Target capability baseline | Catatan |
| --- | --- | --- |
| `System/AccessControl` | Sistem / Kontrol Akses | Role, permission, authorization adapter, role catalog, dashboard system. Source existing dipertahankan. |
| `System/UserManagement` | Sistem / Pengguna | User account lifecycle, invite, status, avatar, impersonation. Tetap module source terpisah sampai ada keputusan consolidation. |
| `System/SystemSetting` | Sistem / Pengaturan Sistem | Runtime setting, category update, idempotency repository, runtime policy. |
| `System/AuditLog` | Sistem / Audit Trail | Audit entry, authentication activity, system activity listener. Nama produk Audit Trail, source existing `AuditLog`. |

Catatan revisi baseline: nama tampil dan dokumentasi produk memakai Bahasa
Indonesia. Source existing `System/*`, `Academic/*`, dan `HumanResource/*`
tetap menjadi identifier teknis stabil sampai ada work item migrasi khusus.

Command dan runtime yang sudah terbukti:

- `php artisan module:list --no-ansi` menemukan 4 module enabled dan bootable.
- `php artisan module:validate --no-ansi` lulus.
- `php artisan starter:verify --no-ansi` lulus.

## Update Phase 0

Work item [`starterkit-alignment`](../starterkit-alignment/README.md) sudah
menutup gate awal sebelum module domain pertama:

- Foundation `System/*` diaudit sebagai implementation namespace untuk area
  Sistem.
- Generator module sudah menerima pola baseline `module:make <Namespace>
  <Module>`.
- `--domain` tetap tersedia sebagai alias kompatibilitas untuk source lama.
- Skeleton generator tidak membuat folder kosong sebagai placeholder.
- Panduan pembuatan module baru tersedia di
  [`../starterkit-alignment/module-generation.md`](../starterkit-alignment/module-generation.md).

## Update Organization Foundation

Work item [`Organization/Organization`](../../modules/Organization/Organization/)
sudah menutup foundation module domain pertama:

- module `Organization/Organization` valid;
- table `organization_units` memakai ULID;
- read/list, create/update, archive, dan restore unit tersedia;
- hierarchy parent-child dasar tersedia;
- UI Inertia sementara tersedia di `resources/js/pages/Organization/Organization/`;
- sidebar sudah mengelompokkan menu berdasarkan namespace;
- permission `organization.view` dan `organization.manage` menjadi authority
  backend;
- audit mutation struktur organisasi tercatat melalui bridge AuditLog/AuditTrail
  existing.

Public contract lintas module belum dibuat karena belum ada consumer nyata.
Keputusan frontend canonical tersedia di
[`frontend-module-path.md`](frontend-module-path.md): UI module memakai
`resources/js/pages/<Namespace>/<Module>/`; `System/*` tetap source existing
sampai ada work item consolidation.

## Update AcademicPeriod Foundation

Work item [`Academic/AcademicPeriod`](../../modules/Academic/AcademicPeriod/)
sudah menutup foundation periode akademik:

- module `Academic/AcademicPeriod` valid;
- table `academic_years` dan `academic_terms` memakai ULID;
- read/list, create/update, activate, dan close term tersedia;
- candidate active-period contract terdokumentasi dan belum diimplementasikan
  sampai consumer nyata disetujui;
- UI Inertia tersedia di `resources/js/pages/Academic/AcademicPeriod/`;
- menu `Periode Akademik` muncul pada namespace Academic;
- permission `academic_period.view` dan `academic_period.manage` menjadi
  authority backend;
- audit mutation periode akademik tercatat melalui bridge AuditLog/AuditTrail
  existing;
- QA UI desktop/mobile sudah memverifikasi flow mutation, lifecycle, filter,
  aksesibilitas axe, console error, dan failed network response.

## Scope

- Membuat mapping prioritas module SakaSantri berdasarkan baseline dan kode starterkit.
- Menentukan urutan incremental agar tiap phase meninggalkan aplikasi tetap sehat.
- Mencatat gap foundation yang harus ditutup sebelum module domain pesantren dibuat banyak.
- Menambahkan peta kebutuhan operasional pesantren: PPDB, Data Induk
  Santri/Wali, Akademik, Tahfidz, Presensi, Perizinan, Kedisiplinan, Prestasi,
  Kesehatan, Pembinaan, Alumni, Keuangan Santri, Donasi/Wakaf, dan
  Inventaris/Aset.

## Tidak Dikerjakan

- Tidak membuat module baru.
- Tidak memindahkan namespace/folder module existing.
- Tidak memindahkan route, UI, migration, atau source runtime existing.
- Tidak menghapus compatibility path `System/*`.

## Prinsip Prioritas

1. Stabilkan foundation yang sudah ada sebelum membangun domain pesantren.
2. Jangan rename/move module existing sebelum consumer audit route, permission, Inertia page, test, seeder, dan contract selesai.
3. Module baru dibuat dari dependency paling dasar: organisasi dan periode akademik sebelum santri, akademik, asrama, dan keuangan.
4. Tiap module dimulai dari vertical slice minimum: metadata, migration inti, permission, read page, satu mutation utama, audit, test, lalu dokumentasi.
5. Public contract dibuat hanya ketika ada consumer nyata.

## Risiko Terbuka

- Source UI existing masih berada di `resources/js/pages/System/*` dan
  `resources/js/pages/Organization/Organization/*`; ini diterima sebagai bridge
  sementara, bukan canonical untuk UI baru.
- Baseline operasional memakai nama tampil "Audit Trail", source sekarang tetap
  `System/AuditLog`.
- Baseline memasukkan user lifecycle ke `AccessControl`, source sekarang memisah `UserManagement`.
- Namespace teknis lama area Pesantrian memakai `StudentLife`; baseline
  operasional baru memilih nama tampil "Pesantrian" dan candidate namespace
  teknis `Pesantrian` untuk module baru.
- Koperasi dan Perpustakaan ditunda sampai baseline aplikasi berjalan.

Keputusan mempertahankan source `System/*` tidak menghambat work item
`Organization/Organization`, `Academic/AcademicPeriod`, atau module pesantrian
berikutnya. Pemindahan UI/source existing tetap harus memakai work item migrasi
tersendiri agar Ziggy route name, URL, permission key, dan Inertia component
path tidak patah diam-diam.
