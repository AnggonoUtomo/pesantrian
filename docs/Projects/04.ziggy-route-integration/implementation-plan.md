# Rencana Implementasi Integrasi Route Ziggy

## Strategi Pengiriman

Setiap increment mengikuti urutan: tinjau checklist, specify, implementasi
kecil, test, review, dokumentasi, dan verifikasi ulang. Scope dibatasi pada
route integration foundation.

## Tahapan

| Increment | Scope                                | Bergantung Pada | Penerimaan                                    | Verifikasi                    | Status  |
| --------- | ------------------------------------ | --------------- | --------------------------------------------- | ----------------------------- | ------- |
| INC-001   | Contract dan kebijakan route         | Discovery       | Allowlist dan `route().current()` diputuskan  | Review spec/ADR               | Selesai |
| INC-002   | Typed shared prop dan adapter        | INC-001         | Tidak ada `any`; consumer compile             | TypeScript, lint, build, test | Selesai |
| INC-003   | Verifikasi route dan hardening build | INC-002         | Positif/negatif dan browser evidence tersedia | Pest, SSR build, DevTools     | Selesai |

## Detail Pelaksanaan Increment

### INC-001 — Contract dan kebijakan route

- Kondisi awal: konfigurasi Ziggy default belum memiliki allowlist; kebutuhan
  `route().current()` belum diputuskan.
- File ditinjau: `config/ziggy.php`, route list Laravel,
  `HandleInertiaRequests`, `resources/js/lib/route.ts`, dan package Ziggy.
- Perubahan: menetapkan allowlist route UI dan memutuskan `route().current()`
  belum menjadi contract.
- Alasan: frontend hanya membutuhkan URL UI; metadata route internal tidak perlu
  dikirim ke browser.
- Evidence: route list berhasil dibaca dan keputusan dicatat pada specification
  serta ADR-0001.

### INC-002 — Typed shared prop dan adapter

- Kondisi awal: boundary Ziggy memakai cast `any`, adapter memiliki implementasi
  lama yang dikomentari, dan lifecycle `app.tsx` pernah memakai setup custom.
- File diubah: `config/ziggy.php`, `resources/js/lib/ziggy.ts`,
  `resources/js/types/global.d.ts`, `resources/js/lib/route.ts`,
  `resources/js/app.tsx`.
- Perubahan: menambah allowlist 35 route, typed `ZiggyConfig`, typed shared prop,
  satu adapter canonical, dan inisialisasi dari `page.props.ziggy` melalui
  `withApp()` standar Inertia.
- Alasan: menghilangkan cast tidak aman dan menjaga lifecycle standar Inertia.
- Evidence: ESLint, TypeScript, build frontend, dan test Laravel lulus.

### INC-003 — Verifikasi route dan hardening build

- Kondisi awal: belum ada test positif/negatif untuk allowlist route dan masih
  ada warning font/SSR build.
- File diubah: `tests/Feature/ZiggyRouteTest.php`, `vite.config.ts`, dan
  adapter route.
- Perubahan: menambah test route UI/internal, menonaktifkan fallback font
  optimization yang tidak perlu, mematikan SSR sourcemap, dan menghapus dead
  code adapter.
- Alasan: route exposure dan build warning harus dapat diverifikasi otomatis.
- Evidence: Pest 41 test/145 assertion saat integrasi Ziggy, SSR build bersih,
  browser `/` dan `/login` tampil, link route benar, console bersih.

## Technical Tasks

- [x] Konfirmasi package, route, shared prop, dan adapter existing.
    - File: `composer.json`, `package.json`, route list, middleware, adapter.
    - Hasil: boundary dan consumer terdokumentasi.
- [x] Tentukan allowlist route dan keputusan `route().current()`.
    - File: `config/ziggy.php`, specification, ADR-0001.
    - Hasil: 35 route UI dibagikan; route internal tidak dibagikan.
- [x] Hilangkan `any` pada boundary Ziggy.
    - File: `resources/js/lib/ziggy.ts`, `global.d.ts`, `app.tsx`.
    - Hasil: typed shared prop dan page props digunakan.
- [x] Bersihkan adapter route.
    - File: `resources/js/lib/route.ts`.
    - Hasil: hanya satu implementasi canonical tanpa dead code.
- [x] Tambahkan verifikasi positif dan negatif.
    - File: `tests/Feature/ZiggyRouteTest.php`.
    - Hasil: route UI ada; route internal/storage/up tidak masuk allowlist.
- [x] Verifikasi build dan browser.
    - File: `vite.config.ts`, execution log.
    - Hasil: lint, type check, CSR/SSR build, dan Chrome DevTools lulus.
