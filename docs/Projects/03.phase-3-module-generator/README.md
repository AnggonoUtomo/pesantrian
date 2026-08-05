# Phase 3 Module Generator

## Konteks

Phase 1 menyiapkan starter kit dan runtime. Phase 2 menyiapkan package
`StarterKit`, manifest, permission identity, registry, dan command discovery.
Phase 3 membangun generator `module:make` berdasarkan contract tersebut.

## Tujuan

Generator harus dapat membuat struktur module secara deterministik, aman, dan
dapat diuji tanpa mengubah file sebelum semua validasi lulus.

## Dokumen Folder

- `specification.md`: requirement dan contract generator.
- `implementation-plan.md`: urutan increment implementasi.
- `tasks.md`: checklist kerja terperinci.
- `roadmap.md`: milestone Phase 3.
- `decisions/ADR-0001-MODULE-GENERATOR-BOUNDARY.md`: keputusan boundary.
- `decisions/ADR-0002-EXTENSION-OVERWRITE.md`: keputusan extension dan overwrite.
- `planning/discovery.md`: inventory dan dependency awal.
- `planning/execution-log.md`: catatan tindakan dan evidence.

## Status

`TASK-009 selesai`.

Generator `module:make` sudah memiliki input typed, profile deterministic,
preview, conflict detection, dry-run, staging ULID, atomic promotion, cleanup,
output JSON, guard `--force`, mode extension additive, dan overwrite dengan
backup/restore.

## Verifikasi implementasi 2026-08-04

- Focused generator test: 20 test, 65 assertion, lulus.
- Full backend test: 85 test, 256 assertion, lulus.
- Pint, TypeScript, ESLint, Prettier, dan frontend build: lulus.
- `module:validate --json` dengan fixture hasil generate: 1 module valid.
- Scan source generator terhadap Wayfinder dan Laravel Boost: bersih.

## Hardening Registry dan Generator

Evaluasi lanjutan menemukan registry masih memakai pencarian dua level,
`module:validate` belum menerima target module, dan sebagian diagnostic masih
menampilkan path atau pesan exception mentah. Perbaikan ini dicatat sebagai
`TASK-008` dan dikerjakan sebelum generator dipakai untuk module berikutnya.

Focused registry/generator test lulus 23 test dan 62 assertion. Full CI lulus
157 test dan 596 assertion; PHPStan juga lulus tanpa error.

## Extension dan Overwrite

`TASK-009` mengaktifkan extension untuk module existing. Tanpa `--overwrite`,
file yang sudah ada dilewati. Overwrite hanya aktif dengan kombinasi
`--extension --overwrite --force --yes`; file existing dibackup dan dipulihkan
jika promotion gagal.

Focused extension/overwrite test lulus 22 test dan 72 assertion. Full CI lulus
165 test dan 625 assertion.
