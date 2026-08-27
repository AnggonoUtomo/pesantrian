# Dokumentasi Laboratory Compliance Platform

Dokumentasi ini adalah acuan aktif yang ringkas untuk membangun Laboratory
Compliance & Evidence Platform di atas fondasi Starter13. Arsip sebelumnya
tersedia di `../Old_docs/` bila konteks historis benar-benar diperlukan.

## Mulai di sini

1. Baca [Product Baseline](PRODUCT-BASELINE.md) untuk visi, scope Release 1,
   domain, invariant, dan urutan pengembangan.
2. Baca [Project](PROJECT.md) untuk ringkasan tujuan produk dan fondasi.
3. Baca [Arsitektur](ARCHITECTURE.md) untuk batas struktur dan dependensi.
4. Baca [Struktur Folder](FOLDER-STRUCTURE.md) sebelum membuat atau mengubah
   module, generator, port, adapter, atau struktur.
5. Baca [Daftar Modul](MODULES.md) untuk ownership, dependency, dan status.
6. Baca [Workflow](WORKFLOW.md) sebelum memulai pekerjaan.
7. Pilih pemeriksaan dari [Quality](QUALITY.md) sesuai risiko perubahan.
8. Baca [Keputusan](DECISIONS.md) saat menyentuh keputusan yang telah ditetapkan.
9. Baca [API](API.md) saat mengubah endpoint publik.

Template untuk PRD, module, work item, plan, task, dan ADR tersedia di
[`templates/`](templates/README.md). Aturan folder pekerjaan berada di
[`modules/`](modules/README.md) dan [`work-items/`](work-items/README.md).

## Stack saat ini

- Laravel 13, PHP 8.4+, MySQL, dan Redis.
- Laravel React starter kit, Inertia, React, TypeScript, Vite, Tailwind, dan
  shadcn/ui.
- Ziggy untuk route frontend dan Spatie Permission untuk authorization.
- CI menggunakan GitHub Actions; lingkungan lokal utama memakai Laragon.

## Prinsip dokumentasi

Dokumentasikan hanya hal yang membantu keputusan atau operasi: contract publik,
perubahan perilaku, langkah pemulihan, dan keputusan yang sulit dibalik.
Jangan menduplikasi kode atau membuat log kerja panjang untuk perubahan kecil.

Semua dokumen ditulis dalam Bahasa Indonesia dan memakai tautan relatif.
