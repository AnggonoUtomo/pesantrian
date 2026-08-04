# Discovery Phase 3 Module Generator

- [x] Phase 2 diverifikasi sebagai dependency: package, manifest, permission,
  registry, dan command sudah tersedia.
- [x] `app/Modules` dipindai read-only; tidak ada module runtime valid yang
  boleh ditimpa generator.
- [x] `ModuleRegistry` menjadi source validasi identity dan duplicate.
- [x] Baseline generator, stub engine, dan console dibaca.
- [x] Forbidden dependency Wayfinder dan Laravel Boost dicatat sebagai aturan.
- [x] Profile generator pertama disepakati sebagai `default-v1`.
- [x] Phase 3 awal hanya membuat module baru; mode extension ditunda.
- [x] Strategi atomic promotion Windows disepakati: staging ULID, validasi,
  rename atomic, dan cleanup saat gagal.

## Initial Inventory

| Area | Kondisi awal | Dampak |
|---|---|---|
| Package | `packages/StarterKit` tersedia | Generator diletakkan di package |
| Registry | `ModuleRegistry` read-only tersedia | Dipakai untuk conflict validation |
| Console | `module:discover`, `validate`, `list` tersedia | `module:make` menjadi command baru |
| Module runtime | `app/Modules` kosong | Tidak ada existing module yang ditimpa |
| Test | Contract dan command test tersedia | Generator menambah unit/feature/integration test |
