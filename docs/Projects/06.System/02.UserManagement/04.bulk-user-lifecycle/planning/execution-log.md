# Execution Log: Bulk Lifecycle User

## 2026-08-10 — Preflight

- Source dibaca: `AGENTS.md`, `docs/AGENTS.md`, README docs, ADR lifecycle, action single lifecycle, controller, repository, policy, route, test, dan tabel frontend UserManagement.
- Mode project: `module extension` pada Laravel 13, PHP 8.4, MySQL, Redis, Inertia React, TypeScript, Ziggy, dan Spatie Permission.
- Temuan: capability single lifecycle sudah memiliki authorization dan audit; bulk operation memerlukan preflight koleksi target agar tidak partial.
- Keputusan: user menyetujui operasi atomik dengan toast error bila ada target tidak valid.
- Risiko: force delete irreversible. Dialog konfirmasi, permission terpisah, state archive, dan transaction wajib dipertahankan.

## 2026-08-10 - Implementasi dan quality gate

- Perubahan Application: `BulkUserLifecycle` melakukan authorization, preflight seluruh `UserData`, lalu menjalankan soft delete atau force delete dalam satu transaction melalui `UserRepository` contract. Setiap target menerbitkan audit event dengan correlation ID batch yang sama.
- Perubahan Presentation: request memvalidasi `user_ids`; controller memberi flash toast sukses atau error; route bulk didaftarkan sebelum `/{user}`; dan route ditambahkan ke allowlist Ziggy.
- Perubahan frontend: selection per halaman, selection header, toolbar bulk, dialog konfirmasi, dan loading state ditambahkan pada `UserTable`.
- Temuan/fix: PHPStan menemukan closure void dan type collection pagination. Closure sekarang mengembalikan ID target setelah mutation, sedangkan adapter pagination memakai `array_values()` untuk memenuhi contract `list<UserData>`.
- Evidence:

  ```bash
  php artisan test tests/Feature/UserManagementPresentationTest.php tests/Feature/UserManagementInfrastructureTest.php tests/Unit/UserManagementApplicationTest.php
  composer types:check
  npm run lint:check
  npm run types:check
  php artisan route:list --name=system.users.bulk
  php artisan module:validate System/UserManagement --json
  git diff --check
  ```

  Hasil akhir: `composer ci:check` lulus dengan 259 test dan 1206 assertion,
  PHPStan 0 error, ESLint, Prettier, TypeScript, dan Pint lulus. Dua route bulk
  terdaftar, module valid, serta diff check bersih.
- Risiko terbuka: browser controller tidak memiliki browser tersedia pada sesi ini. Uji visual, keyboard, dialog, dan console browser harus dilakukan saat browser pengujian tersedia.

## 2026-08-10 - Penyelarasan tombol berdasarkan filter arsip

- Perubahan: toolbar bulk pada `UserTable` memakai `filters.archive` sebagai guard visibility. Daftar awal dan `User aktif` hanya menawarkan `Arsipkan terpilih`; `Arsip saja` hanya menawarkan `Hapus permanen terpilih`.
- Alasan: operator tidak perlu melihat aksi force delete di daftar awal atau aksi archive pada daftar arsip.
- Evidence: TypeScript dan ESLint dijalankan setelah perubahan ini.
