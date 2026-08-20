# Spesifikasi Integrasi Route Ziggy

## Scope

### Termasuk Dalam Scope

- Menetapkan contract konfigurasi route antara `HandleInertiaRequests` dan React.
- Menghilangkan `any` pada boundary Ziggy.
- Membatasi route metadata yang dikirim ke frontend sesuai kebutuhan UI.
- Menetapkan satu adapter route canonical untuk seluruh frontend.
- Menambahkan focused positive dan negative verification.

### Di Luar Scope

- Mengubah route backend atau middleware authorization.
- Mengganti Inertia atau library routing frontend.
- Membuat business module atau permission identity baru.
- Menambahkan dependency baru.

## Contract Kemampuan yang Sudah Ada

Laravel menghasilkan named route di server. `HandleInertiaRequests` membagikan
konfigurasi Ziggy sebagai shared Inertia prop. React menggunakan `ziggy-js` melalui
adapter `resources/js/lib/route.ts`. Backend tetap menjadi otoritas keamanan;
Ziggy hanya menghasilkan URL dan metadata route untuk UX.

## Kebutuhan Module

| ID      | Kebutuhan                                                                     | Prioritas | Penerimaan                                                                             |
| ------- | ----------------------------------------------------------------------------- | --------- | -------------------------------------------------------------------------------------- |
| REQ-001 | Frontend dapat menghasilkan URL untuk seluruh route yang memang digunakan UI. | Wajib     | Route generation positif lulus pada type check/build dan focused test.                 |
| REQ-002 | Boundary shared prop Ziggy tidak menggunakan `any`.                           | Wajib     | TypeScript check lulus tanpa cast `any` pada integrasi Ziggy.                          |
| REQ-003 | Route metadata frontend dibatasi melalui konfigurasi eksplisit.               | Wajib     | Route di luar allowlist tidak dikirim atau tidak tersedia di config frontend.          |
| REQ-004 | Adapter route hanya memiliki satu implementasi aktif dan terdokumentasi.      | Sebaiknya | Tidak ada commented-out implementation atau adapter duplikat.                          |
| REQ-005 | Perubahan tidak melemahkan authorization backend.                             | Wajib     | Negative verification membuktikan URL generation tidak memberi akses tanpa middleware. |

## Batas Module

- Owner: Fondasi Aplikasi.
- Public contract: Shared Inertia prop `ziggy` dan typed frontend `route()` adapter.
- Events: Tidak ada.
- Permissions: Tidak ada; Ziggy bukan authorization boundary.
- Data ownership: Route metadata dimiliki Laravel route registry; adapter dimiliki frontend foundation.
- Dependencies: Laravel route registry, `tightenco/ziggy`, `ziggy-js`, Inertia shared props.

## Keputusan yang Masih Terbuka

Keputusan INC-001: Ziggy menggunakan allowlist route yang dibutuhkan frontend. `route().current()` belum menjadi bagian contract.

| ID     | Question                                                                                | Impact                                                                | Owner            | Status                        |
| ------ | --------------------------------------------------------------------------------------- | --------------------------------------------------------------------- | ---------------- | ----------------------------- |
| OD-001 | Apakah route config menggunakan allowlist eksplisit atau `except` untuk route internal? | Menentukan daftar route yang dikirim ke browser dan biaya perawatan.  | Fondasi Aplikasi | Diputuskan: gunakan allowlist |
| OD-002 | Apakah `route().current()` diperlukan setelah navigasi Inertia?                         | Menentukan apakah config Ziggy perlu mengikuti props halaman terbaru. | Fondasi Aplikasi | Diputuskan: belum diperlukan  |
