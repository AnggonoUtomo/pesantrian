# Ziggy Route Integration Implementasi Plan

## Strategi Pengiriman

Urutan setiap increment: specify, plan, implement, test, review, document,
verify. Implementasi dibatasi pada foundation route integration dan tidak
mencampur perubahan business module.

## Tahapan

| Increment | Scope | Depends On | Penerimaan | Verifikasi | Status |
|---|---|---|---|---|---|
| INC-001 | Finalisasi contract dan kebijakan route yang dibagikan | Discovery | Allowlist route dan kebutuhan `route().current()` diputuskan | Review specification/ADR | Selesai |
| INC-002 | Typed Ziggy boundary dan adapter route utama | INC-001 | Tidak ada `any`; route consumers tetap compile | `npm run types:check`, `npm run build`, `npm run lint:check`, test Laravel | Selesai |
| INC-003 | Verifikasi route dan dokumentasi evidence | INC-002 | Verifikasi positif/negatif tersedia | `php artisan test`, `npm run build:ssr`, Chrome DevTools | Selesai |

## Technical Tasks

- [x] Konfirmasi daftar package dan route yang sudah ada.
- [x] Tentukan batas shared prop Ziggy dan adapter frontend.
- [x] Tentukan kebijakan allowlist route.
- [x] Ganti cast tipe Ziggy yang tidak aman.
- [x] Hapus kode adapter yang dikomentari.
- [x] Tambahkan verifikasi positif dan negatif yang terarah.
- [x] Verifikasi build frontend dan perbarui bukti pelaksanaan.
