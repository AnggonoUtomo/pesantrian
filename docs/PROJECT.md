# Project Starter13

## Masalah

Tim membutuhkan starter kit Laravel yang sudah memiliki fondasi modular,
authorization, pengelolaan pengguna, audit, setting runtime, UI, dan quality
gate sehingga proyek baru tidak mengulang pekerjaan dasar.

## Tujuan

Menyediakan starter kit Laravel 13 yang reusable, aman, dapat diuji, dan mudah
dikembangkan secara incremental oleh developer maupun agent.

## Pengguna

- Developer yang membangun aplikasi berbasis Laravel dan React.
- QA yang memverifikasi behavior backend dan frontend.
- DevOps yang menjalankan CI dan deployment.
- Agent yang membantu perencanaan, implementasi, dan review.

## Scope aktif

- Framework reusable pada `packages/StarterKit`.
- DDD-lite Modular Monolith pada `app/Modules`.
- Modul System: AccessControl, UserManagement, AuditLog, dan SystemSetting.
- API v1, frontend Inertia/React, serta quality gate lokal dan CI.

## Di luar scope

- Modul bisnis khusus yang belum diminta.
- Abstraksi untuk consumer hipotetis.
- Integrasi eksternal tanpa kebutuhan dan owner yang jelas.

## Kriteria keberhasilan

- Modul dapat ditemukan dan divalidasi.
- Authorization tetap berada di backend dan batas modul terjaga.
- Perubahan behavior memiliki test yang proporsional.
- Alur pengguna penting dapat diuji melalui UI.
- Dokumentasi cukup untuk memulai pekerjaan tanpa membaca arsip lama.
