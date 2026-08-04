# Discovery Phase 3 Module Generator

- [x] Phase 2 diverifikasi sebagai dependency: package, manifest, permission,
      registry, dan command sudah tersedia.
- [x] `app/Modules` dipindai read-only; tidak ada module runtime valid yang
      boleh ditimpa generator.
- [x] `ModuleRegistry` menjadi source validasi identity dan duplicate.
- [x] Baseline generator, stub engine, dan console dibaca.
- [x] Baseline folder structure, generator spec, stub spec, dan test plan dibaca
      sebelum memulai Phase 3.
- [x] Forbidden dependency Wayfinder dan Laravel Boost dicatat sebagai aturan.
- [x] Profile generator pertama disepakati sebagai `default-v1`.
- [x] Phase 3 awal hanya membuat module baru; mode extension ditunda.
- [x] Strategi atomic promotion Windows disepakati: staging ULID, validasi,
      rename atomic, dan cleanup saat gagal.

## Initial Inventory

| Area           | Kondisi awal                                   | Dampak                                           |
| -------------- | ---------------------------------------------- | ------------------------------------------------ |
| Package        | `packages/StarterKit` tersedia                 | Generator diletakkan di package                  |
| Registry       | `ModuleRegistry` read-only tersedia            | Dipakai untuk conflict validation                |
| Console        | `module:discover`, `validate`, `list` tersedia | `module:make` menjadi command baru               |
| Module runtime | `app/Modules` kosong                           | Tidak ada existing module yang ditimpa           |
| Test           | Contract dan command test tersedia             | Generator menambah unit/feature/integration test |

## Preflight Evidence

- Authoritative source: `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, dan
  baseline `03.04`, `03.05`, `03.08`, `03.10`, `06.05`, `06.06`, `06.07`.
- Existing code: `packages/StarterKit` berisi manifest, permission, registry,
  dan command read-only yang akan dipakai generator.
- Golden structure: canonical module structure dari `03.04-FOLDER-STRUCTURE.md`
  dan `03.08-STUB-SPEC.md`.
- Acceptance: output `default-v1` harus cocok dengan structure test, validasi
  registry, conflict, dry-run, staging, cleanup, dan output JSON.
- Rollback trace: perubahan Phase 3 ditelusuri melalui task, execution log, dan
  commit terpisah dari Phase 2.
