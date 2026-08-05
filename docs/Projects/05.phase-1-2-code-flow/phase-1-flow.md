# Alur Code Phase 1

## 1. Kondisi Awal

Project dimulai dari Laravel React starter kit yang sudah memiliki Laravel,
Inertia, React, TypeScript, Vite, dan struktur aplikasi dasar. Phase 1 tidak
membuat module bisnis dan tidak membuat generator.

File acuan:

- `composer.json`: dependency PHP dan script Composer.
- `package.json`: dependency frontend dan script npm.
- `.env.example`: contoh konfigurasi runtime.
- `config/database.php`, `config/cache.php`, dan `config/queue.php`:
  konfigurasi service Laravel.

## 2. Dependency Baseline

Urutan perubahan:

1. `spatie/laravel-permission` ditambahkan untuk capability permission.
2. `predis/predis` ditambahkan agar koneksi Redis tidak bergantung pada
   extension `redis` di environment lokal.
3. Default Redis diarahkan ke Predis melalui `.env.example`.
4. `composer.lock` diperbarui agar versi dependency tercatat.

Phase 2 membutuhkan permission identity dan registry yang dapat berjalan di
environment Laravel baseline.

## 3. Runtime Foundation

`.env` lokal diarahkan ke PostgreSQL. File ini tidak dimasukkan ke Git karena
berisi konfigurasi lokal.

Runtime diverifikasi dengan langkah berikut:

1. PostgreSQL dapat dihubungi.
2. Redis merespons `PONG`.
3. Cache dapat menyimpan dan membaca kembali nilai.
4. Migration dapat dijalankan.
5. Storage link tersedia.
6. `Str::ulid()` menghasilkan ULID yang valid.

## 4. Verification Command

File utama: `app/Console/Commands/VerifyStarterFoundation.php`.

Alur method `handle()`:

```text
handle()
  -> check versi PHP dan Laravel
  -> check extension PostgreSQL dan package baseline
  -> checkDatabase()
  -> checkRedis()
  -> checkStorage()
  -> checkUlid()
  -> checkForbiddenDependencies()
  -> susun payload
  -> tampilkan human-readable atau JSON
  -> kembalikan exit code 0 atau 1
```

Method `check()` menyimpan hasil dengan bentuk sederhana:

```php
[
    'status' => 'passed',
    'message' => '...',
]
```

Jika semua check lulus, command mengembalikan `STARTER_VERIFIED`. Jika ada
yang gagal, command mengembalikan `STARTER_VERIFICATION_FAILED` dan exit code
`1`. Pola ini kemudian dipakai lagi oleh command Phase 2.

Test utama: `tests/Feature/StarterFoundationVerificationTest.php`.

## 5. Quality Gate Phase 1

- Pint untuk format PHP.
- Pest untuk behavior aplikasi.
- ESLint untuk JavaScript/TypeScript.
- TypeScript check.
- Build client dan SSR.

Detail command dan hasil disimpan pada
`docs/Projects/01.phase-1-starter-foundation/planning/execution-log.md`.

## 6. Hasil Phase 1

Phase 1 menghasilkan fondasi yang sudah diketahui kondisinya. Phase 2 tidak
perlu menebak versi Laravel, dependency, database, Redis, storage, atau quality
gate dasar.
