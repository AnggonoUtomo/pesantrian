# Tasks: Baseline Operasional Pesantren

## Sebelum Mulai

- [x] `AGENTS.md` dibaca.
- [x] `docs/README.md`, `docs/ARCHITECTURE.md`, `docs/FOLDER-STRUCTURE.md`,
  dan `docs/MODULES.md` dibaca.
- [x] Preferensi user tentang kebutuhan pesantren dicatat.
- [x] Scope dibatasi ke dokumentasi; tidak ada coding/runtime change.

## Increment 1: Vocabulary dan Peta Kebutuhan

- [x] Tambahkan aturan nama tampil Bahasa Indonesia.
  - Acceptance: dokumen membedakan nama tampil, nama domain bisnis, dan
    identifier teknis.
  - Verification: `git diff --check`.
- [x] Tambahkan peta kebutuhan pesantren.
  - Acceptance: mencakup PPDB, Data Induk Santri/Wali, Akademik, Tahfidz,
    Presensi, Perizinan, Kedisiplinan, Prestasi, Kesehatan, Pembinaan, Alumni,
    Keuangan Santri, Donasi/Wakaf, Inventaris/Aset.
  - Verification: review manual baseline.
- [x] Tandai Koperasi dan Perpustakaan sebagai ditunda.
  - Acceptance: tidak masuk prioritas baseline running.
  - Verification: review manual baseline dan module index.

## Increment 2: Module Index dan Roadmap

- [x] Update `docs/MODULES.md`.
  - Acceptance: module memiliki nama tampil, namespace/module teknis, tanggung
    jawab, dependency, dan status.
  - Verification: `git diff --check`.
- [x] Update `docs/work-items/module-roadmap/README.md`.
  - Acceptance: kondisi, scope, prinsip prioritas, dan risiko mencerminkan
    baseline operasional pesantren.
  - Verification: review manual.
- [x] Update `docs/work-items/module-roadmap/plan.md`.
  - Acceptance: phase dan dependency graph mengikuti peta kebutuhan baru.
  - Verification: review manual.
- [x] Update `docs/work-items/module-roadmap/tasks.md`.
  - Acceptance: task berikutnya tersusun incremental dan tidak langsung coding.
  - Verification: review manual.

## Increment 3: Keputusan Rename Teknis

- [ ] Putuskan apakah source existing tetap memakai identifier sekarang atau
  dimigrasikan ke istilah Indonesia teknis.
- [ ] Bila migrasi disetujui, buat work item compatibility terpisah.

Jangan membuat module baru dari checklist ini tanpa instruksi eksplisit user.
