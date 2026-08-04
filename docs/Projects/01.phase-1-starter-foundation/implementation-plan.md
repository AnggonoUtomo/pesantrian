# Rencana Implementasi Phase 1 Starter Foundation

## Strategi

Setiap increment mengikuti urutan: tinjau checklist, specify, implementasi
kecil, test, review, dokumentasi, dan verifikasi ulang.

## Increment

| Increment | Ruang Lingkup                     | Bergantung Pada  | Verifikasi                          | Status  |
| --------- | --------------------------------- | ---------------- | ----------------------------------- | ------- |
| INC-001   | Inventory dan verification matrix | -                | Review dokumen dan command          | Selesai |
| INC-002   | Dependency dan forbidden package  | INC-001          | Composer, npm, scan source          | Selesai |
| INC-003   | Database dan service runtime      | INC-001          | Health check terarah                | Selesai |
| INC-004   | Command `starter:verify`          | INC-002, INC-003 | Command JSON dan test               | Selesai |
| INC-005   | Quality gate foundation           | INC-004          | Pint, test, lint, type check, build | Selesai |
| INC-006   | Penutupan dan evidence            | INC-005          | Review checklist dan docs           | Selesai |

## Detail Pelaksanaan Increment

### INC-001 — Inventory dan verification matrix

- Kondisi awal: starter kit dan Ziggy sudah ada, tetapi belum ada bukti terpusat
  untuk seluruh foundation.
- File ditinjau: `composer.json`, `package.json`, `.env`, `.env.example`,
  `config/database.php`, `config/cache.php`, `config/queue.php`,
  `bootstrap/app.php`, dan struktur `app/`.
- Perubahan: membuat dokumen project Phase 1 dan mencatat versi, package,
  service, serta gap runtime.
- Alasan: generator tidak boleh dibangun berdasarkan asumsi project kosong.
- Evidence: PHP 8.4.16, Laravel 13.23.0, Node 24.12.0, npm 11.6.4.

### INC-002 — Dependency dan forbidden package

- Kondisi awal: Ziggy tersedia; Spatie Permission dan Redis client PHP belum
  tersedia; konfigurasi memakai `phpredis`.
- File diubah: `composer.json`, `composer.lock`, `.env.example`.
- Perubahan: menambah `spatie/laravel-permission:8.3.0` dan
  `predis/predis:3.5.1`; default Redis client menjadi `predis`.
- Alasan: permission adalah dependency baseline dan Predis berjalan tanpa
  extension `redis`.
- Evidence: Composer install sukses dan scan dependency/source bersih.

### INC-003 — Database dan service runtime

- Kondisi awal: `.env` memakai MySQL dan migration table belum tersedia.
- File diubah: `.env` lokal, tidak dimasukkan ke Git.
- Perubahan: mengarahkan `.env` ke PostgreSQL port 5432, menjalankan migration,
  dan membuat storage link.
- Alasan: runtime harus sesuai baseline PostgreSQL/Redis.
- Evidence: PostgreSQL terkoneksi, Redis `PONG`, cache round-trip `ok`,
  migration selesai, storage link dan ULID berhasil.

### INC-004 — Command `starter:verify`

- Kondisi awal: command belum tersedia.
- File dibuat: `app/Console/Commands/VerifyStarterFoundation.php` dan
  `tests/Feature/StarterFoundationVerificationTest.php`.
- Perubahan: command memeriksa versi, extension, package, database, Redis,
  storage, ULID, dan forbidden dependency. Tersedia output biasa, `--json`,
  serta exit code sukses/gagal.
- Alasan: verification harus bisa diulang developer dan CI.
- Evidence: command menghasilkan `STARTER_VERIFIED` dengan `failed: 0`.

### INC-005 — Quality gate foundation

- File diperiksa: source PHP/TS/React, konfigurasi, dan test.
- Perubahan: memperbaiki format EOF pada `tests/Feature/ZiggyRouteTest.php`.
- Alasan: quality gate harus bersih sebelum phase ditutup.
- Evidence: Pint, Pest, ESLint, TypeScript, CSR build, dan SSR build lulus.

### INC-006 — Penutupan dan evidence

- File diubah: dokumen pada folder project Phase 1.
- Perubahan: memperbarui status task, milestone, execution log, keputusan,
  dan risiko berdasarkan hasil command nyata.
- Alasan: status selesai harus dapat diaudit tanpa membaca percakapan agent.
- Evidence: `git diff --check` bersih dan checklist Phase 1 lengkap.

## Technical Tasks

- [x] Catat inventory project dan environment.
    - File: dokumen Phase 1, `composer.json`, `package.json`, `.env`.
    - Hasil: versi dan stack utama tercatat.
- [x] Verifikasi package baseline dan dependency terlarang.
    - File: `composer.json`, `composer.lock`, `.env.example`, source/config.
    - Hasil: Spatie Permission dan Predis tersedia; forbidden dependency bersih.
- [x] Verifikasi database, Redis, cache, queue, session, storage, dan ULID.
    - Command: `starter:verify`, migration, cache round-trip, migration status.
    - Hasil: PostgreSQL, Redis, migration, storage, dan ULID lulus.
- [x] Sediakan `starter:verify`.
    - File: command dan test feature.
    - Hasil: output human-readable/JSON dan exit code tersedia.
- [x] Jalankan quality gate.
    - Hasil: Pint, Pest 43/150, ESLint, TypeScript, CSR build, SSR build lulus.
- [x] Perbarui checklist dan execution evidence.
    - File: task, plan, roadmap, discovery, milestones, dan log.
    - Hasil: status selesai memiliki alasan dan bukti.
