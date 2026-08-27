# Quality dan Verifikasi

Gunakan pemeriksaan yang proporsional dengan risiko. Mulai dari test paling
spesifik, lalu naik ke gate yang lebih luas ketika perubahan menyentuh boundary,
module, frontend, migration, atau release.

| Perubahan | Verifikasi minimum |
| --- | --- |
| Dokumentasi | `git diff --check` dan pemeriksaan placeholder/tautan Markdown yang relevan |
| Backend behavior | Focused unit/feature test pada behavior terkait |
| Boundary atau module | Focused test, `php artisan module:validate`, dan pemeriksaan dependency arah layer |
| Frontend behavior | Focused test bila ada, typecheck/lint, dan build sesuai risiko |
| UI/alur pengguna | Browser test setelah test frontend lulus |
| Migration | Migration test pada database disposable atau lingkungan lokal yang disetujui |
| API contract | Focused API test dan pembaruan `API.md` |
| Artisan/runtime | Command terkait, `php artisan optimize:clear`, dan command health project |

## Command Project

- Artisan health: `php artisan about --no-ansi`.
- Clear cache/config: `php artisan optimize:clear --no-ansi`.
- Module validation: `php artisan module:validate --no-ansi`.
- Starter foundation: `php artisan starter:verify --no-ansi`.
- Backend focused test: `php artisan test --filter=<NamaTest>`.
- Full backend test: `php artisan test`.
- Frontend build: `npm run build`.
- Frontend dev: `npm run dev`.
- Composer autoload check: `composer dump-autoload`.
- Diff hygiene: `git diff --check`.

## Prinsip

- Jangan menjalankan full gate berulang tanpa perubahan source.
- Jangan mengarang hasil test; laporkan command yang benar-benar dijalankan.
- Untuk module baru, verifikasi path, namespace, route, permission, migration,
  ServiceProvider, dan manifest module.
- Untuk table aplikasi, cek ULID primary identifier dan foreign ULID kompatibel.
- Untuk perubahan security-sensitive, verifikasi authorization backend, bukan
  hanya visibility frontend.
