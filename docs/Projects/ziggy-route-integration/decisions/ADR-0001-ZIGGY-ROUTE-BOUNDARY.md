# ADR-0001: Ziggy Route Boundary

## Status

Diterima.

## Context

Project telah menghapus Wayfinder dan Laravel Boost, lalu memasang Ziggy sebagai
mekanisme route generation frontend. Integrasi saat ini sudah berhasil pada
type check dan production build, tetapi shared prop masih di-cast sebagai `any`
dan konfigurasi route belum memiliki exposure policy eksplisit.

## Decision

- Module owner: Fondasi Aplikasi.
- Public contracts: Shared Inertia prop `ziggy` dan typed `route()` adapter.
- Events: Tidak ada.
- Permission identity: Tidak ada.
- Data ownership: Laravel route registry menghasilkan metadata; frontend hanya konsumsi.
- Dependencies: `tightenco/ziggy`, `ziggy-js`, Inertia shared props.
- Batas keamanan: Ziggy hanya route URL generation; authentication dan authorization tetap diverifikasi backend.
- Kebijakan exposure: Gunakan allowlist route yang dipakai frontend. Route lain tidak dibagikan sebagai metadata Ziggy.
- `route().current()` belum menjadi kebutuhan contract; konfigurasi tidak perlu mengikuti navigasi saat ini.

## Consequences

### Positive

- Frontend tidak bergantung pada Wayfinder atau Laravel Boost.
- Route naming tetap bersumber dari Laravel.
- Boundary backend/frontend dapat diberi tipe dan diuji.
- Route metadata dapat dibatasi agar tidak mengirim seluruh registry ke browser.

### Negative

- Daftar route yang diekspos perlu dipelihara saat route UI bertambah.
- Adapter harus memperhatikan lifecycle shared props Inertia bila current-route helper digunakan.

## Verifikasi

- `php artisan route:list --json`
-

pm run types:check`
-

pm run build`

- Focused positive route-generation test.
- Focused negative authorization test.
