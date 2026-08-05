# Alur Frontend AccessControl

## 1. Page dan Layout

Entry page: `resources/js/pages/System/AccessControl/pages/Index.tsx`.

Layout bersama: `resources/js/layouts/system-dashboard-layout.tsx`.

Alurnya:

```text
Inertia response
    -> Index.tsx membaca typed props
    -> system-dashboard-layout membungkus page dan menampilkan footer identitas
    -> AccessControlHeader
    -> RoleControlCard
    -> PermissionModulePanel
    -> toast atau redirect result
```

Dashboard System memakai layout yang sama sebagai baseline visual. Entry page
dashboard berada pada `resources/js/pages/System/Dashboard.tsx`, sedangkan
widget-nya berada pada `SystemDashboardWidgets.tsx`.

## 2. Typed Props

File: `resources/js/pages/System/AccessControl/types.ts`.

Page menerima daftar role, permission group, role aktif, authorization context,
dan flash notification. TypeScript membantu memastikan page tidak mengandalkan
bentuk data yang tidak disediakan backend.

## 3. Visibility Menu

File utama:

- `resources/js/components/app-sidebar.tsx`;
- `resources/js/components/command-palette.tsx`.

Alurnya:

```text
auth.superSystem atau permission
    -> sidebar menyaring menu Access Control
    -> command palette menyaring item System
    -> route() dari Ziggy membuat URL frontend
```

Visibility ini hanya untuk UX. User yang mencoba URL atau mutation tetap
diperiksa backend.

## 4. Memilih Role

File: `components/RoleControlCard.tsx`.

Role dipilih melalui search dropdown autocomplete:

```text
input pencarian role
    -> filter daftar role di browser
    -> user memilih role
    -> activeRoleId berubah
    -> permission panel membaca permission role aktif
```

Shortcut pencarian dan close menu harus tetap dapat digunakan melalui keyboard.
Focus tidak boleh hilang saat dropdown dibuka.

## 5. Permission Panel

File: `components/PermissionModulePanel.tsx`.

Permission dikelompokkan berdasarkan module. Panel module dapat dibuka secara
terpisah agar membuka satu group tidak membuka group lain.

Alur checkbox:

```text
checkbox permission
    -> state permission role aktif berubah secara lokal
    -> UI menampilkan perubahan pending
    -> user menekan shortcut atau tombol simpan
    -> router.put(route(...), payload)
```

Permission `SuperSystem` ditampilkan sebagai protected state. Role biasa tetap
dapat memilih permission yang diizinkan.

## 6. Mutation dari Frontend

Route selalu dibuat dengan Ziggy:

```tsx
route('access-control.roles.store')
route('access-control.roles.permissions.update', roleId)
route('access-control.roles.destroy', roleId)
```

Komponen tidak membangun URL secara manual. Jika route belum masuk route list
Ziggy, error harus diperbaiki pada konfigurasi route, bukan dengan hardcoded URL.

## 7. Toast dan Flash Notification

File terkait:

- `resources/js/components/ui/sonner.tsx`;
- page AccessControl dan component mutation.

Alurnya:

```text
backend redirect dengan flash
    -> Inertia membawa flash props
    -> page memanggil Sonner
    -> success berwarna hijau
    -> error berwarna merah
    -> toast memiliki tombol X untuk ditutup manual
```

Notifikasi lama tidak boleh ditampilkan bersamaan dengan toast baru agar satu
mutation tidak menghasilkan dua pesan.

## 8. Loading, Empty, dan Error State

Page harus tetap dapat dibaca saat role belum tersedia, permission group kosong,
mutation sedang dikirim, backend mengembalikan validation error, atau user
tidak memiliki permission.

Backend denial ditampilkan sebagai halaman unauthorized atau error response
yang sesuai. Frontend tidak boleh menganggap hidden menu sebagai tanda akses
aman.

## 9. Tema dan Visual Baseline

AccessControl mengikuti baseline UI/UX project:

- sticky top navigation;
- sidebar dengan warna mengikuti theme palette;
- dark/light mode;
- surface card clean dengan rounded corner;
- icon semantic yang berbeda sesuai fungsi;
- warna hover dan active mengikuti theme;
- animasi `framer-motion` dipakai hemat;
- reduced motion tetap dihormati.

Layout module berikutnya harus menggunakan konsep ini sebagai baseline, tetapi
tidak menyalin business component AccessControl.

## 10. Verification Frontend

```bash
npm run lint:check
npm run format:check
npm run types:check
npm run build
```

Browser flow yang perlu ditinjau:

1. Login sebagai user yang memiliki permission AccessControl.
2. Buka `/system/access-control`.
3. Cari role melalui autocomplete.
4. Buka satu permission group tanpa membuka group lain.
5. Ubah permission role biasa dan simpan.
6. Pastikan toast success muncul satu kali dan dapat ditutup dengan X.
7. Coba mutation tanpa permission dan pastikan backend menolak.
8. Uji dark/light theme dan keyboard shortcut.
