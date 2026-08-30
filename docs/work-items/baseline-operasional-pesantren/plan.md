# Plan: Baseline Operasional Pesantren

## Tujuan

Menyelaraskan dokumen acuan SakaSantri agar lebih mudah dipahami oleh pemula dan
operator pesantren, tanpa mengorbankan stabilitas teknis source code yang sudah
berjalan.

## Prinsip

1. Bahasa produk dan menu memakai Bahasa Indonesia.
2. Identifier teknis tetap stabil sampai ada keputusan migrasi.
3. Module baru harus lahir dari kebutuhan operasional nyata, bukan dari menu UI.
4. Setiap module punya ownership data, lifecycle, authorization, audit, dan
   verifikasi.
5. Koperasi dan Perpustakaan ditunda sampai baseline aplikasi berjalan.

## Increment 1: Vocabulary dan Peta Kebutuhan

- Tambahkan pemisahan nama tampil, nama domain bisnis, dan identifier teknis.
- Tambahkan peta kebutuhan pesantren yang disetujui user.
- Tandai capability yang ditunda.

Acceptance:

- Baseline menjelaskan kenapa Bahasa Indonesia dipakai untuk nama tampil.
- Peta kebutuhan produk mencakup daftar prioritas yang disetujui.
- Koperasi dan Perpustakaan eksplisit ditunda.

Verification:

- `git diff --check`
- Review manual baseline.

## Increment 2: Module Index dan Roadmap

- Update `docs/MODULES.md` agar punya nama tampil, namespace/module teknis,
  tanggung jawab, dependency, dan status.
- Update `docs/work-items/module-roadmap/` agar phase berikutnya mengikuti peta
  kebutuhan pesantren.

Acceptance:

- Module existing tetap tercatat sebagai source active.
- Module target baru tersusun dengan dependency yang masuk akal.
- Phase roadmap memberi urutan kerja incremental.

Verification:

- `git diff --check`
- Review manual `docs/MODULES.md` dan module-roadmap.

## Increment 3: Keputusan Rename Teknis

Belum dikerjakan. Increment ini hanya dimulai bila user menyetujui migrasi
identifier teknis.

Candidate keputusan:

- tetap memakai `HumanResource` untuk source, nama tampil `SDM Pesantren`; atau
- migrasi source ke istilah teknis Indonesia seperti `Kepegawaian`.

Acceptance:

- Ada daftar consumer route, permission, Inertia page, test, seeder, migration,
  dan contract sebelum rename.
- Ada compatibility bridge bila rename source dilakukan.

Verification:

- focused route/Ziggy tests;
- focused module tests;
- `php artisan module:validate --no-ansi`;
- `npm run types:check`;
- `npm run build`.

## Batas Berhenti

Berhenti setelah dokumen acuan dan roadmap tersusun. Jangan lanjut membuat
module baru sampai user memilih work item berikutnya.
