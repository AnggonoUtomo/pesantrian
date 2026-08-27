# Quality dan Verifikasi

Ganti contoh command dengan script yang benar-benar tersedia pada project.
Mulai dari test paling spesifik dan gunakan gate luas sesuai risiko.

| Perubahan            | Verifikasi minimum                                    |
| -------------------- | ----------------------------------------------------- |
| Dokumentasi          | Tautan Markdown dan `git diff --check`                |
| Backend behavior     | Focused unit/feature test                             |
| Boundary atau module | Architecture test dan module validation bila tersedia |
| Frontend behavior    | Focused component test, typecheck, dan lint           |
| UI/alur pengguna     | Focused browser test setelah test frontend lulus      |
| Migration            | Focused migration test pada database disposable       |
| API contract         | Focused API test dan pembaruan `API.md`               |

## Command project

- Backend focused test: `[command]`.
- Frontend focused test: `[command]`.
- Typecheck/lint/build: `[command]`.
- Module validation: `[command atau tidak tersedia]`.
- Full quality gate: `[command]`.

Full gate digunakan untuk perubahan lintas area, risiko tinggi, atau sebelum
release. Jangan menjalankannya berulang tanpa perubahan source.
