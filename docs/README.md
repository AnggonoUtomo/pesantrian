# Dokumentasi Starter13

Dokumentasi ini adalah acuan aktif yang ringkas untuk pengembangan Starter13.
Arsip baseline sebelumnya tersedia di `../Old_docs/` bila konteks historis
benar-benar diperlukan.

## Mulai di sini

1. Baca [Arsitektur](ARCHITECTURE.md) untuk batas struktur dan dependensi.
2. Baca [Workflow](WORKFLOW.md) sebelum mengubah kode atau konfigurasi.
3. Baca [Keputusan](DECISIONS.md) saat pekerjaan menyentuh keputusan yang telah
   ditetapkan.
4. Baca [API](API.md) saat mengubah endpoint publik.

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
