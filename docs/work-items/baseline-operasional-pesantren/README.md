# Work Item: Baseline Operasional Pesantren

## Status

Active - revisi dokumen acuan untuk membuat baseline SakaSantri lebih
pesantren-first dan lebih mudah dipahami operator/pemula.

## Owner dan Lokasi

- Owner: lintas module.
- Target kode: tidak ada pada work item ini.
- Target dokumentasi:
  - `docs/SakaSantri_Architecture_Baseline_v0.1-r1-ID.md`
  - `docs/README.md`
  - `docs/ARCHITECTURE.md`
  - `docs/FOLDER-STRUCTURE.md`
  - `docs/MODULES.md`
  - `docs/work-items/module-roadmap/`

## Keputusan Awal

- Nama tampil, menu, dan dokumentasi produk memakai Bahasa Indonesia yang
  familiar untuk operator pesantren.
- Identifier teknis memakai PascalCase ASCII dan tidak di-rename tanpa work item
  migrasi.
- Source existing seperti `System`, `Academic`, dan `HumanResource` tetap stabil
  sampai ada compatibility audit untuk rename.
- Area kebutuhan pesantren yang diprioritaskan:
  - PPDB / Penerimaan Santri Baru;
  - Data Induk Santri dan Wali;
  - Kelas / Rombel / Kurikulum;
  - Tahfidz / Hafalan;
  - Presensi Santri;
  - Perizinan Santri;
  - Pelanggaran / Kedisiplinan;
  - Prestasi;
  - Kesehatan / Klinik;
  - Konseling / Pembinaan;
  - Alumni;
  - Tagihan / Pembayaran / Tunggakan;
  - Donasi / Wakaf;
  - Inventaris / Aset.
- Koperasi dan Perpustakaan ditunda sampai baseline aplikasi berjalan dan
  kebutuhan module inti lain lebih jelas.

## Tidak Dikerjakan

- Tidak rename namespace/folder/module source.
- Tidak mengubah route, permission key, Inertia component path, migration, atau
  test runtime.
- Tidak membuat module baru.
- Tidak membuat ADR terpisah sebelum keputusan rename teknis disetujui.

## Risiko Terbuka

- Baseline file masih bernama `v0.1-r1`, sementara isi mulai bergerak ke revisi
  produk berikutnya. Perlu keputusan apakah akan membuat file `v0.1-r2`.
- Istilah teknis untuk module baru perlu dipilih sebelum generator dipakai:
  apakah memakai Indonesia penuh seperti `Pesantrian/Santri`, atau menjaga
  English identifier untuk sebagian area existing.
- Rename `HumanResource` menjadi istilah teknis Indonesia seperti
  `Kepegawaian` berisiko mematahkan route, permission, UI, autoload, dan test;
  harus menjadi work item migrasi tersendiri bila disetujui.

## Verifikasi

- `git diff --check`
- Review manual tabel module dan phase roadmap.
