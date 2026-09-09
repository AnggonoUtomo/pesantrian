# Implementation Plan: Pesantrian/PrestasiSantri

## Overview

PrestasiSantri dibangun sebagai module Pesantrian untuk mencatat capaian positif
santri. Implementasi mengikuti pola module pesantrian terbaru: dokumentasi dulu,
skeleton dan permission, contract readiness, data foundation, API read, API
mutation, lifecycle, seeder demo, UI/Inertia, QA browser, dan user manual.

## Architecture Decisions

- Namespace teknis memakai `Pesantrian`, module teknis `PrestasiSantri`, nama
  tampil `Prestasi`.
- Source backend berada di `app/Modules/Pesantrian/PrestasiSantri/`.
- Source frontend berada di `resources/js/pages/Pesantrian/PrestasiSantri/`.
- Page Inertia harus tipis; komponen UI masuk ke folder `components`.
- Catatan prestasi memakai snapshot santri dan pembina agar histori stabil.
- Lampiran bukti/sertifikat ditunda sampai module `Support/Document` tersedia.
- Mutation memakai Application action dan audit event; controller tidak
  melakukan persistence langsung.
- Public contract keluar module ditunda sampai consumer nyata ada.

## Dependency Order

```text
Documentation baseline
    -> Module skeleton + permission
        -> Contract readiness
            -> Data foundation
                -> Read/list API
                    -> Mutation API kategori/catatan
                        -> Lifecycle submit/verify/void
                            -> Demo seeder
                                -> UI list/detail
                                    -> UI mutation
                                        -> QA browser + user manual
```

## Increment Plan

### Increment 1: Documentation Baseline

- Buat folder dokumentasi module.
- Buat README, specification, plan, dan tasks.
- Update indeks module.

Checkpoint:

- Dokumen cukup untuk memulai skeleton module.

### Increment 2: Module Skeleton dan Permission

- Jalankan dry-run generator.
- Buat skeleton module.
- Buat permission identity.
- Wire permission ke seeder AccessControl.
- Tambahkan permission identity test.

Checkpoint:

- `php artisan module:validate --no-ansi` berhasil.
- Permission baseline tersedia.

### Increment 3: Contract Readiness

- Audit `ActiveStudentReader`.
- Audit `ActiveEmployeeReader`.
- Audit `ActiveAcademicPeriodReader`.
- Tambahkan readiness test agar PrestasiSantri tidak membaca model
  Infrastructure module dependency.

Checkpoint:

- Selector dan validasi dependency bisa lewat contract existing.

### Increment 4: Data Foundation

- Buat migration category, achievement, dan revision.
- Buat record model Infrastructure.
- Buat factory minimum.
- Tambahkan data foundation tests.

Checkpoint:

- Table memakai ULID.
- Index MySQL eksplisit.
- Snapshot santri/pembina tersedia.

### Increment 5: Backend Read/List

- Buat DTO/read model.
- Buat query list/filter/search.
- Buat query detail.
- Buat repository read.
- Buat controller/resource API read.

Checkpoint:

- List mendukung pagination, search, kategori, tingkat, status, periode, dan
  rentang tanggal.
- Detail menampilkan revision history.

### Increment 6: Kategori dan Catatan Draft

- Buat request validation kategori.
- Buat action create/update/archive kategori.
- Buat request validation catatan draft.
- Buat action create/update catatan draft.
- Tambahkan audit.

Checkpoint:

- Kategori aktif bisa dipakai.
- Draft bisa dibuat dan diubah.

### Increment 7: Submit dan Verifikasi

- Buat action submit.
- Buat action verify dengan hasil `verified` atau `needs_revision`.
- Buat revision history.
- Tambahkan audit lifecycle.

Checkpoint:

- Submitted tidak bisa diedit langsung.
- Verified menjadi riwayat resmi.

### Increment 8: Void / Pembatalan Aman

- Buat action void dengan alasan wajib.
- Tambahkan rule agar void tidak menghapus data.
- Tambahkan audit dan revision history.

Checkpoint:

- Catatan dapat dibatalkan aman.
- Reason tidak memuat payload sensitif.

### Increment 9: Demo Seeder

- Buat `PrestasiSantriDemoSeeder`.
- Update `DatabaseSeeder`.
- Tambahkan test idempotent seeder.
- Dokumentasikan data demo.

Checkpoint:

- Seeder mendukung demo lifecycle lengkap.

### Increment 10: UI/Inertia List dan Detail

- Buat page index/detail.
- Buat komponen summary/filter/table/card/detail/pagination.
- Tambahkan sidebar menu namespace Pesantrian.
- Tambahkan presentation dan Ziggy tests.

Checkpoint:

- Page tipis.
- Console bebas route/Ziggy error.

### Increment 11: UI Mutation

- Buat dialog kategori.
- Buat dialog catatan prestasi.
- Buat dialog submit, verify, needs revision, dan void.
- Jalankan typecheck, lint, dan build.

Checkpoint:

- Mutation UI memakai route web.
- Backend tetap authority permission dan validasi.

### Increment 12: QA Browser dan User Manual

- Buat browser spec dan fixture.
- QA desktop/mobile.
- Update user manual lifecycle.
- Update README/tasks hasil final.

Checkpoint:

- Flow list/detail/mutation utama siap diuji manual user.

## Risks and Mitigations

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Boundary Prestasi tumpang tindih dengan Tahfidz | Data hafalan tercatat ganda | Prestasi hanya mencatat penghargaan/lomba/capaian resmi; setoran tetap milik Tahfidz. |
| Lampiran bukti dibutuhkan cepat | Operator belum bisa upload sertifikat | Simpan metadata bukti manual dulu, integrasi Document ditunda eksplisit. |
| Terlalu banyak tingkat/jenis prestasi | UI membingungkan pemula | Gunakan enum awal sederhana dan kategori yang bisa dikelola. |
| Verified diedit langsung | Riwayat resmi tidak reliable | Pakai lifecycle revision/void, bukan update bebas. |
| Dependency lintas module bocor ke Infrastructure | Arsitektur rusak | Readiness test dan contract existing wajib dipakai. |

## Verification Strategy

- Documentation: review manual dokumen module dan roadmap.
- Backend foundation: `php artisan module:validate --no-ansi`.
- Focused tests: `php artisan test --filter=PrestasiSantri --no-ansi`.
- Route checks: `php artisan route:list --name=pesantrian.prestasi-santri --no-ansi`.
- Frontend: `npm run types:check`, `npm run lint:check`, `npm run build`.
- Browser QA hemat quote:
  `E2E_START_SERVER=true npx playwright test tests/Browser/prestasi-santri.spec.ts`.

## Open Questions

- Prefix nomor prestasi final akan dikunci di `PRS-` atau memakai setting
  module?
- Role apa saja yang boleh verify di deployment nyata?
- Apakah butuh tampilan prestasi per santri di halaman detail Santri pada fase
  awal, atau cukup di list Prestasi dulu?
