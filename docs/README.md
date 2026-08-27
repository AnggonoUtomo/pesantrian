# Dokumentasi SakaSantri

Dokumentasi ini adalah acuan aktif untuk pengembangan **SakaSantri**, sistem
terpadu pengelolaan manajemen dan operasional pesantren. Ringkasan di folder
ini mengikuti baseline lengkap:

- [SakaSantri Architecture Baseline v0.1-r1](SakaSantri_Architecture_Baseline_v0.1-r1-ID.md)

## Mulai di sini

1. Baca [Project](PROJECT.md) untuk tujuan, scope, dan constraint produk.
2. Baca [Arsitektur](ARCHITECTURE.md) untuk batas module dan arah dependency.
3. Baca [Struktur Folder](FOLDER-STRUCTURE.md) sebelum mengubah struktur.
4. Baca [Daftar Module](MODULES.md) untuk ownership dan dependency awal.
5. Baca [Workflow](WORKFLOW.md) sebelum memulai pekerjaan.
6. Pilih pemeriksaan dari [Quality](QUALITY.md) sesuai risiko.
7. Baca [Keputusan](DECISIONS.md) saat menyentuh keputusan aktif.
8. Baca [API](API.md) saat mengubah endpoint publik.

Template tersedia di [`templates/`](templates/README.md). Konvensi dokumentasi
module dan work item berada di [`modules/`](modules/README.md) dan
[`work-items/`](work-items/README.md).

## Stack Baseline

- Backend: PHP, Laravel 13, Laravel Fortify.
- Frontend: React, TypeScript, Inertia, Tailwind CSS, shadcn/ui, Framer Motion,
  Ziggy, Vite.
- Database/cache/queue: MySQL, Laravel cache, Laravel queue, Laravel events,
  Laravel notifications.
- Package utama: `spatie/laravel-permission`, `spatie/laravel-medialibrary`,
  `tightenco/ziggy`.
- Identifier aplikasi: ULID sebagai primary identifier pada table aplikasi.
- Model produk: non-SaaS, single yayasan, multi-unit.
- Bahasa dokumentasi: Bahasa Indonesia.
- Bahasa identifier teknis: Bahasa Inggris.

## Prinsip

SakaSantri dibangun sebagai DDD-lite Modular Monolith dengan Hexagonal
Architecture. Module berada di `app/Modules/<Namespace>/<Module>/`; namespace
adalah area/kategori bisnis, sedangkan module adalah bounded capability nyata.

Dokumentasikan contract publik, perubahan behavior, langkah operasi, dan
keputusan yang sulit dibalik. Jangan menduplikasi kode, membuat folder kosong
sebagai placeholder, atau menambahkan abstraction tanpa kebutuhan domain.
