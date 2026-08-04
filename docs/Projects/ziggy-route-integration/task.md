# Rencana Task Integrasi Route Ziggy

Task harus kecil, dapat diverifikasi, dan tidak mencampur kemampuan yang tidak
berkaitan. Setiap task memiliki kriteria penerimaan, test, bukti, dan status.

| ID | Tahap | Task | Bergantung Pada | Kriteria Penerimaan | Verifikasi | Status |
|---|---|---|---|---|---|---|
| TASK-001 | INC-001 | Putuskan kebijakan route yang dibagikan | Discovery | Allowlist dan alasannya terdokumentasi | Review specification/ADR | Selesai |
| TASK-002 | INC-002 | Penguatan typed shared prop Ziggy | TASK-001 | `any` di batas Ziggy hilang | `npm run types:check`, `npm run lint:check` | Selesai |
| TASK-003 | INC-002 | Bersihkan adapter route utama | TASK-002 | Hanya satu implementasi aktif | ESLint dan build frontend | Selesai |
| TASK-004 | INC-003 | Tambahkan verifikasi route positif | TASK-003 | Route UI utama menghasilkan URL valid | Test terarah dan build | Selesai |
| TASK-005 | INC-003 | Tambahkan verifikasi authorization negatif | TASK-003 | Ziggy tidak menjadi bypass keamanan | `php artisan test` terarah | Selesai |

## Definisi Selesai

- [x] Scope task selesai.
- [x] Test positif dan negatif yang relevan tersedia.
- [x] Dampak authorization dan keamanan ditinjau.
- [x] Bukti verifikasi tersimpan.
- [x] Dokumentasi dan log pelaksanaan diperbarui.