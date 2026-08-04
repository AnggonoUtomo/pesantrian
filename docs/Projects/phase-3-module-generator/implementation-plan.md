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
