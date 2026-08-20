# Quality dan Verifikasi

Pilih pemeriksaan yang sesuai dengan perubahan. Mulai dari test paling spesifik
dan jalankan gate lebih luas hanya bila risikonya memerlukan.

| Perubahan            | Verifikasi minimum                                                      |
| -------------------- | ----------------------------------------------------------------------- |
| Dokumentasi          | Tautan Markdown dan `git diff --check`                                  |
| Backend behavior     | Focused Pest/PHPUnit test                                               |
| Boundary atau module | Focused test dan `php artisan module:validate {Domain}/{Module} --json` |
| Frontend behavior    | Focused Vitest, `npm run types:check`, dan lint relevan                 |
| UI/alur pengguna     | Focused browser test setelah test frontend lulus                        |
| Migration            | Focused migration test pada database disposable                         |
| API contract         | Focused API test dan pembaruan `API.md`                                 |

Gunakan `composer ci:check` untuk perubahan lintas area, perubahan berisiko
tinggi, sebelum release, atau ketika focused test tidak cukup membuktikan tidak
ada regresi. Jangan menjalankan full gate berulang tanpa perubahan kode.

Hasil verifikasi cukup dilaporkan sebagai command, status, dan temuan penting.
Output panjang tidak perlu disalin ke dokumentasi.
