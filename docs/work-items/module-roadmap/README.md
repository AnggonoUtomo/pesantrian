# Work Item: Module Roadmap SakaSantri

## Status

Active - Phase 0 foundation siap untuk work item Organization.

## Owner dan Lokasi

- Owner: lintas module.
- Target kode: `app/Modules/`, `packages/StarterKit/`, dan `resources/js/`.
- Target dokumentasi: `docs/modules/` dan `docs/work-items/module-roadmap/`.

## Kondisi Awal

Starterkit saat ini sudah memiliki foundation aktif:

| Source sekarang | Target capability baseline | Catatan |
| --- | --- | --- |
| `System/AccessControl` | `Console/AccessControl` | Role, permission, authorization adapter, role catalog, dashboard system. Baseline final memakai `Console`. |
| `System/UserManagement` | `Console/AccessControl` | User account lifecycle, invite, status, avatar, impersonation. Baseline memasukkan user account ke AccessControl. |
| `System/SystemSetting` | `Console/SystemSetting` | Runtime setting, category update, idempotency repository, runtime policy. Baseline final memakai `Console`. |
| `System/AuditLog` | `Console/AuditTrail` | Audit entry, authentication activity, system activity listener. Baseline final memakai `Console/AuditTrail`. |

Command dan runtime yang sudah terbukti:

- `php artisan module:list --no-ansi` menemukan 4 module enabled dan bootable.
- `php artisan module:validate --no-ansi` lulus.
- `php artisan starter:verify --no-ansi` lulus.

## Update Phase 0

Work item [`starterkit-alignment`](../starterkit-alignment/README.md) sudah
menutup gate awal sebelum module domain pertama:

- Foundation `System/*` diaudit sebagai implementation bridge menuju baseline
  `Console/*`.
- Generator module sudah menerima pola baseline `module:make <Namespace>
  <Module>`.
- `--domain` tetap tersedia sebagai alias kompatibilitas untuk source lama.
- Skeleton generator tidak membuat folder kosong sebagai placeholder.
- Panduan pembuatan module baru tersedia di
  [`../starterkit-alignment/module-generation.md`](../starterkit-alignment/module-generation.md).

## Scope

- Membuat mapping prioritas module SakaSantri berdasarkan baseline dan kode starterkit.
- Menentukan urutan incremental agar tiap phase meninggalkan aplikasi tetap sehat.
- Mencatat gap foundation yang harus ditutup sebelum module domain pesantren dibuat banyak.

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

- Baseline frontend memakai `resources/js/modules/*`, sedangkan source sekarang memakai `resources/js/pages/System/*`.
- Baseline menyebut `Console/AuditTrail`, source sekarang memakai `System/AuditLog`.
- Baseline memasukkan user lifecycle ke `AccessControl`, source sekarang memisah `UserManagement`.
- Namespace teknis area kesantrian memakai `StudentLife`, dengan label baca
  Bahasa Indonesia "Kesantrian".

Risiko frontend dan rename `System -> Console` tidak menghambat work item
`Organization/Organization` untuk dokumentasi, skeleton, dan backend slice
minimum. Risiko tersebut menjadi gate sebelum memindahkan UI existing atau
menambah UI module baru.
