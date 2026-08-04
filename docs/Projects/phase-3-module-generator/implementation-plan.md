# Implementation Plan Phase 3 Module Generator

## Delivery Strategy

Urutan setiap increment: specify, plan, implement, test, review, document, dan
verify. Increment berikutnya tidak dimulai sebelum increment sebelumnya lulus.

## Increments

| Increment | Scope | Depends On | Acceptance | Verification | Status |
|---|---|---|---|---|---|
| INC-001 | Finalisasi contract dan profile | Phase 2 | Profile, input, dan output disepakati | Review spec/ADR | Siap dimulai |
| INC-002 | Input DTO dan validation | INC-001 | Input valid/invalid memiliki diagnostic stabil | Unit test | Planned |
| INC-003 | Stub/profile engine | INC-002 | Placeholder dan profile menghasilkan plan deterministik | Contract test | Planned |
| INC-004 | Conflict detection dan dry-run | INC-003 | Tidak ada side effect sebelum validasi lulus | Feature test | Planned |
| INC-005 | Staging dan atomic promotion | INC-004 | Staging ULID, rename atomic, cleanup | Integration test | Planned |
| INC-006 | Command `module:make` | INC-005 | Human/JSON output dan exit code stabil | Command test | Planned |
| INC-007 | Hardening dan quality gate | INC-006 | Security, dependency, docs, dan full gate lulus | Full verification | Planned |

## Technical Tasks

- [ ] Finalisasi specification, profile, dan ADR dengan detail keputusan.
- [ ] Buat input/plan/result DTO serta validator.
- [ ] Buat profile registry dan stub resolver yang deterministic.
- [ ] Buat conflict detector memakai `ModuleRegistry`.
- [ ] Buat dry-run plan tanpa side effect.
- [ ] Buat staging writer, atomic promotion, rollback, dan cleanup.
- [ ] Buat command `module:make` dengan output human-readable/JSON.
- [ ] Tambahkan test positif, negatif, conflict, dry-run, dan failure cleanup.
- [ ] Jalankan quality gate dan perbarui dokumentasi evidence.
