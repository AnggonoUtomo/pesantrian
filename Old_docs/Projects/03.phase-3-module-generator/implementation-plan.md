# Implementation Plan Phase 3 Module Generator

## Delivery Strategy

Urutan setiap increment: specify, plan, implement, test, review, document, dan
verify. Increment berikutnya tidak dimulai sebelum increment sebelumnya lulus.

## Increments

| Increment | Scope | Depends On | Acceptance | Verification | Status |
|---|---|---|---|---|---|
| INC-001 | Finalisasi contract dan profile | Phase 2 | Profile, input, dan output disepakati | Review spec/ADR | Selesai |
| INC-002 | Input DTO dan validation | INC-001 | Input valid/invalid memiliki diagnostic stabil | Unit test | Selesai |
| INC-003 | Stub/profile engine | INC-002 | Placeholder dan profile menghasilkan plan deterministik | Contract test | Selesai |
| INC-004 | Conflict detection dan dry-run | INC-003 | Tidak ada side effect sebelum validasi lulus | Feature test | Selesai |
| INC-005 | Staging dan atomic promotion | INC-004 | Staging ULID, rename atomic, cleanup | Integration test | Selesai |
| INC-006 | Command `module:make` | INC-005 | Human/JSON output dan exit code stabil | Command test | Selesai |
| INC-007 | Hardening dan quality gate | INC-006 | Security, dependency, docs, dan full gate lulus | Full verification | Selesai |
| INC-008 | Hardening registry dan generator | INC-007 | Discovery recursive, target validate, dan diagnostic aman | Focused test dan full CI | Selesai |
| INC-009 | Extension dan overwrite aman | INC-008 | Extension, backup/restore, guard, dan cleanup lulus | Focused test dan full CI | Selesai |

## Technical Tasks

- [x] Finalisasi specification, profile, dan ADR dengan detail keputusan.
- [x] Buat input DTO serta validator; plan dan result DTO dibuat pada increment berikutnya.
- [x] Buat profile `default-v1` dan plan resolver deterministic. Registry
  multi-profile menjadi enhancement setelah baseline generator.
- [x] Buat conflict detector memakai `ModuleRegistry`.
- [x] Buat preview/dry-run plan tanpa side effect.
- [x] Buat staging writer, atomic promotion, dan cleanup. Rollback overwrite
  belum diperlukan karena target existing selalu ditolak.
- [x] Buat command `module:make` dengan output human-readable/JSON.
- [x] Tambahkan test positif, negatif, conflict, dry-run, mutasi tanpa `--force`, dan failure cleanup.
- [x] Jalankan quality gate, scan forbidden dependency, validasi hasil generate, dan perbarui dokumentasi evidence.

## INC-008 - Hardening registry dan generator

- Kondisi awal: `ModuleRegistry` memakai glob dua level, sehingga nested module
  dapat terlewat. `module:validate` hanya mendukung validasi global, padahal
  workflow generator membutuhkan validasi target. Beberapa diagnostic memakai
  path absolut atau pesan exception mentah.
- File target: `packages/StarterKit/src/Modules/ModuleRegistry.php`, command
  `module:validate`, command discovery/list/inspect yang memakai diagnostic,
  test registry/command, dan dokumentasi console.
- File diubah: `packages/StarterKit/src/Modules/ModuleRegistry.php`, command
  module, conflict detector, test registry/command, dan dokumentasi console.
- Perubahan: scanner manifest dibuat recursive, validasi
  target ditambahkan, diagnostic dikembalikan dengan path relatif dan pesan
  aman, conflict detector tidak menerima detail internal registry, dan output
  command memiliki `message` serta `diagnostics` yang konsisten.
- Alasan: registry adalah source of truth untuk identity module dan harus aman
  dipakai generator pada struktur folder yang berkembang.
- Acceptance: nested module ditemukan; target valid/invalid memiliki code dan
  exit code stabil; path absolut, secret, dan exception internal tidak keluar;
  generator tetap tidak menulis file sebelum preview lulus.
- Verification: focused registry/generator test, command `module:discover`,
  `module:validate`, `module:inspect`, dry-run `module:make`, dan `composer ci:check`.
- Evidence: focused test lulus 23 test/62 assertion; PHPStan lulus; command
  target valid menghasilkan `MODULE_VALID`; target tidak ditemukan menghasilkan
  `MODULE_TARGET_NOT_FOUND`; nested discovery berhasil; full CI lulus dengan
  157 test dan 596 assertion.

## INC-009 - Extension dan overwrite aman

- Kondisi awal: generator menolak semua target existing dan `--force` belum
  membedakan extension atau overwrite.
- File target: input request, preview/conflict detector, promotion service,
  command `module:make`, test generator, ADR, specification, dan execution log.
- Perubahan: menambah mode `--extension`, `--overwrite`, backup file existing,
  restore saat gagal, dan guard kombinasi option.
- Alasan: module existing dapat dilengkapi tanpa membuka overwrite diam-diam.
- Acceptance: extension hanya membuat file baru; overwrite mengganti file dalam
  plan dengan backup; option tidak valid gagal tanpa side effect; failure
  mengembalikan file existing dan membersihkan staging.
- Verification: focused generator test, dry-run JSON, mutation test, failure
  cleanup test, dan `composer ci:check`.
- Rollback: revert increment ini mengembalikan generator ke mode module baru.
- Rollback: perubahan dapat dibatalkan pada increment ini tanpa mengubah schema
  database atau file module existing.
