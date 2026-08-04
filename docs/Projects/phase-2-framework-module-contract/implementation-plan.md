# Rencana Implementasi Phase 2 Framework dan Module Contract

## Strategi

Implementasi dilakukan incremental. Setiap increment memiliki acceptance
criteria, focused test, verification command, execution evidence, dan review
checklist sebelum increment berikutnya dimulai.

## Increment

| Increment | Scope | Bergantung Pada | Verifikasi | Status |
|---|---|---|---|---|
| INC-001 | Finalisasi contract dan ADR | Phase 1 | Review spec/ADR | Selesai |
| INC-002 | Composer package dan service provider | INC-001 | Composer autoload, Laravel boot | Selesai |
| INC-003 | Manifest/value object/schema validator | INC-002 | Unit test manifest valid/invalid | Selesai |
| INC-004 | Permission identity validator | INC-003 | Unit test schema/duplicate key | Selesai |
| INC-005 | Registry, discovery, dan isolation | INC-003, INC-004 | Integration test valid/invalid | Selesai |
| INC-006 | Command discover/validate/list | INC-005 | Command human-readable/JSON | Selesai |
| INC-007 | Hardening, test, dan documentation evidence | INC-006 | Full quality gate | Planned |

## Detail Pelaksanaan

### INC-001 — Contract dan ADR

- Kondisi awal: `packages/StarterKit` dan module registry belum ada.
- File: specification, implementation plan, task, roadmap, ADR, dan planning.
- Perubahan: menetapkan manifest tunggal, runtime config terpisah, registry
  read-only, dan invalid module isolation.
- Alasan: generator Phase 3 membutuhkan contract yang tidak ambigu.
- Evidence: ADR disetujui sebelum coding.

### INC-002 — Package dan provider

- Target file: `packages/StarterKit/composer.json`, source package, dan provider.
- Perubahan: package didaftarkan melalui Composer path repository dan provider
  dapat dimuat Laravel.
- Alasan: reusable framework tidak boleh menjadi kode ad hoc aplikasi.
- Evidence: autoload dan application boot lulus.

### INC-003 — Manifest contract

- Target: DTO/value object manifest dan schema validator.
- Perubahan: validasi field wajib, PascalCase, namespace, semver, status, path,
  provider, dependencies, permission source, dan config source.
- Alasan: manifest adalah identity deklaratif module.
- Evidence: test manifest valid, missing field, invalid status, dan invalid path.

### INC-004 — Permission contract

- Target: validator `permissions.php` dan permission metadata value object.
- Perubahan: validasi key unik, description, owner module, dan sensitive flag.
- Alasan: permission identity harus dimiliki module owner.
- Evidence: test metadata valid, field missing, duplicate key, dan forbidden data.

### INC-005 — Registry dan discovery

- Target: registry service, filesystem scanner, discovery result, dan diagnostic.
- Perubahan: menemukan module berdasarkan manifest, memvalidasi `permissions.php`,
  mengisolasi invalid module, serta mendeteksi duplicate module identity dan
  duplicate permission key.
- Alasan: module valid tetap diproses saat module lain rusak.
- Evidence: integration test valid/invalid/duplicate module dan duplicate
  permission key lulus.

### INC-006 — Console command

- Target: `module:discover`, `module:validate`, dan `module:list`.
- Perubahan: output human-readable, `--json`, diagnostic stabil, dan exit code.
- Alasan: developer dan CI membutuhkan entry point yang dapat diulang.
- Evidence: command test biasa, JSON, failure path, dan exit code lulus.

### INC-007 — Hardening dan evidence

- Target: package, test, docs, dan forbidden dependency scan.
- Perubahan: hardening error handling, redaction diagnostic, execution log, dan
  quality gate.
- Alasan: contract framework menjadi dependency Phase 3.
- Evidence: full test, static analysis, lint, dan build lulus.

## Technical Tasks

- [ ] Finalisasi contract dan ADR.
  - File: `specification.md`, `ADR-0001`, `implementation-plan.md`.
  - Hasil yang diharapkan: boundary tidak memiliki Open Decision blocking.
- [ ] Buat package `packages/StarterKit`.
  - File: Composer metadata, source package, provider, dan test boot.
  - Hasil yang diharapkan: package dapat di-autoload Laravel.
- [ ] Buat manifest dan permission validator.
  - File: contract/value object, schema validator, dan unit test.
  - Hasil yang diharapkan: valid/invalid/duplicate memiliki diagnostic stabil.
- [ ] Buat registry dan command read-only.
  - File: registry, discovery service, command, dan integration test.
  - Hasil yang diharapkan: module valid tetap diproses saat module lain invalid.
- [ ] Tutup quality gate dan documentation evidence.
  - File: test, log, task, dan roadmap.
  - Hasil yang diharapkan: Phase 2 siap menjadi input Phase 3.
