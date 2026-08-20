# Task Phase 1 Starter Foundation

| ID       | Increment | Task                 | Acceptance                                                     | Verifikasi                            | Status   |
| -------- | --------- | -------------------- | -------------------------------------------------------------- | ------------------------------------- | -------- |
| TASK-001 | INC-001   | Inventory foundation | Versi, stack, package, service tercatat                        | Review inventory                      | Selesai  |
| TASK-002 | INC-002   | Dependency baseline  | Package wajib dan forbidden package terverifikasi              | Composer, npm, scan                   | Selesai  |
| TASK-003 | INC-003   | Runtime service      | MySQL, Redis, cache, queue, session, storage, ULID teruji | Health check dan migration            | Selesai  |
| TASK-004 | INC-004   | Verification command | Command tersedia dalam output biasa dan JSON                   | `starter:verify`                      | Selesai  |
| TASK-005 | INC-005   | Quality gate         | Semua gate relevan lulus                                       | Pint, Pest, ESLint, TypeScript, build | Selesai  |
| TASK-006 | INC-006   | Tutup Phase 1        | Evidence, risiko, dan docs lengkap                             | Review akhir                          | Selesai  |
| TASK-007 | INC-007   | Hardening foundation | Constraint, command, scanner, sanitasi, test, dan docs selaras | Full CI dan command JSON              | Selesai  |

## Detail Task

### TASK-001 — Inventory foundation

- [x] Scope task selesai.
    - Kondisi awal: belum ada verification matrix Phase 1.
    - File dibuat: delapan dokumen pada folder `phase-1-starter-foundation`.
    - File ditinjau: `composer.json`, `package.json`, `.env`, `.env.example`,
      `config/`, `bootstrap/app.php`, dan struktur `app/`.
    - Perubahan: menetapkan mode `Existing Starter Kit` dan mencatat versi serta
      gap foundation.
    - Alasan: Phase 2 tidak boleh dimulai dengan asumsi project kosong/sehat.
    - Evidence: PHP 8.4.16, Laravel 13.23.0, Node 24.12.0, npm 11.6.4.

### TASK-002 — Dependency baseline

- [x] Scope task selesai.
    - Kondisi awal: Spatie Permission belum ada dan PHP extension `redis` tidak
      tersedia; `.env` memakai `phpredis`.
    - File diubah: `composer.json`, `composer.lock`, `.env.example`.
    - Perubahan: menambah `spatie/laravel-permission:8.3.0` dan
      `predis/predis:3.5.1`; default Redis client menjadi `predis`.
    - Alasan: permission adalah dependency baseline; Predis cocok tanpa extension
      `redis`.
    - Evidence: Composer install sukses; scan dependency/source bersih dari
      Wayfinder dan Laravel Boost.

### TASK-003 — Runtime service

- [x] Scope task selesai.
    - Kondisi awal: `.env` memakai MySQL dan database baru belum memiliki
      migration table.
    - File diubah: `.env` lokal, tidak di-commit.
    - Perubahan: memakai MySQL port 3306, menjalankan
      `php artisan migrate --force`, dan memastikan `public/storage` valid.
    - Alasan: runtime harus sesuai baseline MySQL/Redis.
    - Evidence: driver `mysql`, Redis `PONG`, cache database `ok`, migration
      status `Ran`, storage tersedia, dan ULID valid.

### TASK-004 — Verification command

- [x] Scope task selesai.
    - Kondisi awal: `php artisan starter:verify` belum terdaftar.
    - File dibuat: `app/Console/Commands/VerifyStarterFoundation.php` dan
      `tests/Feature/StarterFoundationVerificationTest.php`.
    - Perubahan: menambah check versi, package, extension, database, Redis,
      storage, ULID, forbidden dependency, output JSON, dan exit code.
    - Alasan: verification harus konsisten dan dapat dipakai CI.
    - Evidence: output berisi `STARTER_VERIFIED`, `failed: 0`; test positif dan
      negatif lulus; secret tidak dicetak.

### TASK-005 — Quality gate

- [x] Scope task selesai.
    - File diperiksa: source PHP/React/TypeScript, konfigurasi, dan test.
    - Perubahan: memperbaiki EOF pada `tests/Feature/ZiggyRouteTest.php`.
    - Alasan: Pint harus bersih sebelum phase ditutup.
    - Evidence: Pint lulus; Pest 43 test/150 assertion; ESLint, TypeScript, CSR
      build, dan SSR build lulus.

### TASK-006 — Penutupan Phase 1

- [x] Scope task selesai.
    - File diubah: dokumen pada folder `phase-1-starter-foundation`.
    - Perubahan: status task/milestone menjadi selesai dan execution evidence
      dilengkapi.
    - Alasan: engineer berikutnya dapat memahami hasil tanpa membaca chat agent.
    - Evidence: `git diff --check` bersih dan tidak ada checklist kosong.

## Definisi Selesai

- [x] Scope task selesai.
    - Setiap task memiliki kondisi awal, file, perubahan, alasan, dan evidence.
- [x] Test positif dan negatif tersedia.
    - Positif: `starter:verify` lulus seluruh pemeriksaan.
    - Negatif: output tidak membocorkan forbidden dependency atau application key.
- [x] Dampak keamanan dan runtime ditinjau.
    - Backend tetap security authority.
    - Output verification tidak mencetak password atau application key.
    - MySQL dan Redis diuji pada runtime lokal.
- [x] Evidence tersimpan.
    - Command, hasil penting, dan risiko dicatat pada execution log.
- [x] Dokumentasi diperbarui.
    - Plan, task, roadmap, discovery, milestones, dan log saling konsisten.
- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
    - Checklist awal menentukan scope.
    - Checklist akhir diperiksa setelah test dan build selesai.

## TASK-007 — Hardening framework foundation

- [x] Constraint PHP diselaraskan ke PHP 8.4.
    - Kondisi awal: root dan package StarterKit memakai constraint berbeda.
    - Perubahan: root memakai `^8.4`; package StarterKit sudah memakai `^8.4`.
    - Evidence: `composer.lock` diperbarui dan `composer ci:check` lulus.
- [x] Command `starter:diagnose` dan `starter:health` tersedia.
    - Kondisi awal: dokumentasi menyebut command, runtime belum mendaftarkannya.
    - Perubahan: menambah command berbasis service check bersama dengan output
      JSON/human-readable dan exit code stabil.
    - Evidence: dua command terdaftar, output `STARTER_DIAGNOSED` dan
      `STARTER_HEALTHY`, serta feature test lulus.
- [x] Cakupan `starter:verify` selaras dengan dokumentasi.
    - Kondisi awal: check frontend stack dan approved package belum lengkap.
    - Perubahan: menambah check Inertia, React, TypeScript, Vite, Tailwind,
      shadcn/ui, MySQL, dan package baseline.
    - Evidence: JSON command menunjukkan 0 pemeriksaan gagal, termasuk driver
      MySQL, stack frontend, dan package baseline.
- [x] Scanner forbidden dependency recursive dan diagnostic aman.
    - Kondisi awal: `glob('**/*.php')` tidak menjamin scan nested dan exception
      database/Redis dapat membawa detail internal.
    - Perubahan: memakai recursive directory iterator dan pesan diagnostic
      generik tanpa credential, SQL, host detail, atau absolute path.
    - Evidence: unit test fixture nested lulus; exception runtime memakai pesan
      generik; scan source tidak menemukan dependency terlarang.
- [x] Test package/framework foundation ditambah.
    - Kondisi awal: test package hanya memeriksa provider dan installed package.
    - Perubahan: menambah test contract command, scanner, sanitasi, dan JSON.
    - Evidence: focused test lulus; full CI lulus dengan 154 test dan 589
      assertion tanpa warning.
- [x] Mojibake dokumen foundation dibersihkan.
    - Kondisi awal: beberapa dokumen memiliki karakter hasil encoding yang rusak.
    - Perubahan: mengganti ke karakter ASCII/UTF-8 yang benar pada dokumen yang
      relevan.
     - Evidence: pencarian mojibake pada folder docs tidak menemukan hasil.

## TASK-008 — Migrasi baseline database ke MySQL

- [x] Runtime utama diarahkan ke MySQL.
  - Kondisi awal: `.env`, `.env.example`, default database, queue database,
    health check, dan CI masih memakai PostgreSQL atau SQLite sebagai default.
  - Perubahan: default runtime menjadi `mysql`, `.env.example` memakai port
    `3306` dan user `root`, queue batching/failed jobs memakai MySQL, serta CI
    memakai service MySQL 8.
  - Alasan: MySQL ditetapkan sebagai database utama project dan harus konsisten
    dari Laragon sampai quality gate CI.
  - Evidence: `pdo_mysql` tersedia; MySQL 8.0.30 merespons; database `starter13`
    tersedia; `php artisan migrate --force` menyelesaikan migration pending.

- [x] Dukungan test tetap terisolasi.
  - Kondisi awal: PHPUnit memakai SQLite in-memory untuk menghindari test saling
    mengubah database development.
  - Perubahan: PHPUnit tetap memakai SQLite in-memory; health check membedakan
    pengecualian testing ini dari driver runtime MySQL.
  - Alasan: test tidak boleh bergantung pada data lokal atau menghapus database
    development, sedangkan runtime dan CI tetap menguji MySQL.
  - Evidence: `starter:verify --json` pada runtime MySQL lulus dengan
    `mysql_extension`, `mysql_driver`, dan koneksi database aktif.

- [x] Dokumentasi baseline database diselaraskan.
  - File: `docs/AGENTS.md`, `docs/README.md`, design database, environment,
    CI/CD, test plan, Phase 1, code flow, dan runbook module terkait.
  - Perubahan: PostgreSQL tidak lagi disebut sebagai database utama; tipe JSON
    dan aturan schema disesuaikan dengan MySQL.
  - Evidence: pencarian referensi baseline menunjukkan MySQL sebagai database
    utama; riwayat lama tetap diberi konteks sebagai catatan historis.

- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
  - Sebelum: scope mencakup runtime, test isolation, CI, migration, dan docs.
  - Sesudah: kode, konfigurasi, test, CI, dan dokumen sudah diperiksa; fresh
    migration belum dijalankan karena bersifat destruktif terhadap database lokal.
