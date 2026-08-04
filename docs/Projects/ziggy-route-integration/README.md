# Project: Integrasi Route Ziggy

## Konteks Project

| Item               | Nilai                      |
| ------------------ | -------------------------- |
| Slug               | ziggy-route-integration    |
| Source Repository  | C:/laragon/www/starter13   |
| Mode               | Starter Kit yang Sudah Ada |
| Laravel            | 13.23.0                    |
| Starter Kit Status | Terpasang                  |
| Owner              | Fondasi Aplikasi           |
| Status             | Selesai                    |

## Ringkasan Intake

- Kemampuan starter kit yang sudah ada: Laravel React Starter Kit dengan Inertia, React, TypeScript, Vite, Fortify, Passkeys, dan settings flow.
- Module yang sudah ada: Belum ada module domain pada `app/Modules`; aplikasi masih berada pada starter-kit foundation.
- Package yang sudah ada: `tightenco/ziggy:^2.6` dan `ziggy-js:^2.6.3` sudah terpasang.
- Perubahan yang diminta: Audit dan hardening konfigurasi serta adapter Ziggy untuk frontend route generation.
- Dokumen acuan: `docs/AGENTS.md`, `docs/README.md`, `docs/06-FRAMEWORK/`, `docs/07-KERNEL/`, dan template project documentation.
- Di luar scope: Pengembangan business module, perubahan authorization domain, dan penggantian Inertia.

## Bukti Saat Ini

- `php artisan route:list --json` berhasil.
- `npm run types:check` berhasil.
- `npm run build` berhasil.
- Backend membagikan `ziggy` melalui `HandleInertiaRequests`.
- Frontend memakai adapter `resources/js/lib/route.ts`.
- Audit menemukan cast `as any`, konfigurasi Ziggy default tanpa whitelist/except, dan commented-out implementation lama.
- Tidak ditemukan referensi Wayfinder atau Laravel Boost.
- `config/ziggy.php` membatasi metadata ke 35 route UI.
- Test route positif/negatif, build SSR, dan browser verification lulus.

## Daftar Module

| Module                  | Status    | Owner            | Contract/Dependency              | Catatan                                                      |
| ----------------------- | --------- | ---------------- | -------------------------------- | ------------------------------------------------------------ |
| Starter Kit Foundation  | Sudah ada | Fondasi Aplikasi | Laravel, Inertia, React, Ziggy   | Menyediakan runtime dan route consumers.                     |
| Ziggy Route Integration | Selesai   | Fondasi Aplikasi | Inertia shared props, `ziggy-js` | Bukan business module; infrastruktur frontend lintas bagian. |

## Aturan Kerja

Gunakan baseline global sebagai source of truth. Jangan membangun ulang capability
existing tanpa keputusan eksplisit. Implementasi mengikuti incremental workflow,
mempertahankan perubahan user yang tidak terkait, dan setiap task wajib mencatat
kondisi awal, file, perubahan, alasan, evidence, dan risiko. Commit, branch, atau
instalasi dependency hanya dilakukan dengan permintaan eksplisit.
