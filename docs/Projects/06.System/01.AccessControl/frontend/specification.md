# Specification Frontend AccessControl

## Objective

Menyediakan halaman role dan permission yang dapat digunakan langsung melalui
browser dengan tampilan responsif, state yang jelas, dan permission visibility
yang aman.

## Acuan contoh

Pola dari `FrontendContoh/access-control` dipakai sebagai referensi struktur:

- header dengan icon dan ringkasan halaman;
- shortcut panel untuk action penting;
- role control card untuk memilih role dan menampilkan ringkasan;
- permission module panel dengan accordion dan checkbox;
- dialog tambah role dan hapus role;
- empty workspace saat belum ada role;
- summary box untuk jumlah permission.
- menu sidebar `Access Control` untuk user yang memiliki capability.
- layout module `system-dashboard-layout` dengan pola header, grid content, dan
  footer dashboard.
- halaman system dashboard pada route `system.dashboard`.
- satu halaman login System pada route `system.login` untuk semua module di
  dalam namespace System.
- halaman `errors/unauthorized` untuk response authorization `403`.
- sticky top navigation mengikuti pola `dashboard-shell-01`, berisi toggle
  sidebar, breadcrumb, language menu, notification action, dan profile menu.
- komponen dashboard: statistics cards, insight card, coverage card, metrics
  card, activity table, dropdown action, progress visual, dan footer.

Contoh tidak disalin mentah. Identifier, route, type, permission key, dan data
harus disesuaikan dengan contract AccessControl saat ini.

## Route dan data

Route frontend memakai Ziggy. Target route page:

```text
GET /system/access-control
```

Dashboard system memakai route:

```text
GET /system/dashboard
```

Route `/dashboard` juga menjadi alias halaman system dashboard agar menu
Dashboard bawaan starter kit tidak membuka placeholder lama.

Mutation permission role memakai route Ziggy berikut:

```text
PUT /system/access-control/roles/{role}/permissions
```

Props Inertia minimum:

```ts
interface AccessControlPageProps {
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
    selectedRoleId: string | null;
    auth: SharedData['auth'];
}
```

Semua identifier role dan permission menggunakan ULID string. Permission
visibility memakai `auth.permissions`, `auth.roles`, dan `auth.superSystem`.
Frontend hanya mengatur visibility/UX; backend tetap memeriksa authorization.

## Acceptance criteria

- User berwenang dapat membuka halaman AccessControl.
- User tanpa permission menerima response server `403`.
- Role tampil dalam role control card dan dapat dipilih.
- Permission dikelompokkan berdasarkan module.
- Role protected `SuperSystem` tidak memiliki action destructive.
- User tanpa permission update hanya dapat melihat permission.
- Loading, empty, error, dan success feedback tersedia.
- Role biasa dapat menyimpan perubahan permission; `SuperSystem` ditolak oleh
  policy server-side.
- Layout dapat dipakai pada desktop dan viewport mobile.
- Critical flow dapat dijalankan dengan keyboard.
- Route frontend menggunakan Ziggy dan tidak memakai Wayfinder.
- Menu sidebar hanya terlihat untuk `SuperSystem` atau permission
  `access_control.role.manage`.
- Layout module tidak membuat `AppLayout` atau sidebar aplikasi kedua.
- Data dashboard berasal dari role dan permission AccessControl; tidak memakai
  data sales contoh yang tidak terkait module.
- Dashboard module baru dibuat di namespace page module masing-masing dan dapat
  memakai `system-dashboard-layout` sebagai layout bersama.
- Login hanya memiliki satu pintu System; AccessControl dan UserManagement
  bukan area login terpisah.
- Authentication tetap memakai Fortify yang sama. Akses setiap fitur tetap
  diperiksa middleware dan policy berdasarkan role/permission.
- Top navigation menjadi bagian dari `AppLayout` global agar semua halaman
  System dan module mendapat header yang konsisten.
- Halaman Appearance menyediakan pemilih theme palette yang mengubah warna
  utama aplikasi tanpa mengubah pilihan dark/light.
- Theme palette tersimpan di browser dan tetap aktif setelah halaman dimuat
  ulang.

## Boundaries

- Always: backend authority, ULID string, typed props, Ziggy, accessibility.
- Ask first: perubahan struktur halaman, permission visibility, dan action role.
- Never: hardcode bypass authorization, import private repository module lain,
  secret di props, atau URL route string tersebar.

## Theme palette

Palette diambil dari referensi `FrontendContoh/app.css` dan memakai atribut
`data-theme` pada elemen `html`. Palette yang tersedia adalah `urban`,
`slate`, `gray`, `zinc`, `neutral`, `stone`, `graphite`, `mist`, `harbor`,
`quartz`, `aurora`, `saffron`, `ruby`, `forest`, `ocean`, `plum`, dan `copper`.

`slate`, `gray`, `zinc`, `neutral`, dan `stone` adalah palette netral default
yang mengikuti pola shadcn. Palette project seperti `Urban`, `Forest`, dan
`Ocean` tetap dipertahankan untuk pilihan yang lebih berwarna.

State palette disimpan pada `localStorage` dengan key `theme-palette`. Fitur ini
hanya mengubah warna tampilan. Pilihan mode `light`, `dark`, atau `system` tetap
dikelola terpisah oleh `use-appearance`.

Jika belum ada pilihan tersimpan atau nilainya tidak valid, palette default yang
dipakai adalah `neutral`. Nilai palette yang sudah tersimpan tidak diubah.

## Baseline visual semantic

Dashboard memakai background bersih tanpa gradient besar, pola grid, atau glow
ambient. Surface card dibuat lebih netral agar warna semantic mudah dibedakan
dari theme palette.

Warna semantic diterapkan dengan aturan berikut:

- icon memakai tint background sekitar 18-22%, border sekitar 32-38%, warna
  foreground yang jelas, dan glow tipis;
- badge memakai warna yang sesuai status, termasuk foreground, border,
  background, dan glow ringan;
- progress memakai gradient pendek antarwarna yang masih satu konteks;
- garis accent card boleh memakai dua warna yang berdekatan;
- mode light dan dark mempertahankan arti warna yang sama dengan tingkat
  kontras yang sesuai;
- efek visual tidak boleh mengubah route, data, permission visibility, atau
  authorization backend.

Teknik visual ini diadaptasi dari referensi, tetapi layout dan identitas visual
AccessControl tetap milik project ini.

## Baseline penerapan module System

AccessControl dan module System berikutnya memakai `system-dashboard-layout` dan
shell sidebar aplikasi yang sama. Panel utama memakai `dashboard-card`, panel
turunan memakai `dashboard-subcard`, dan icon fungsi memakai
`dashboard-icon` dengan warna semantic.

Card module dan subcard memakai surface neutral seperti Dashboard Shell 01:
bone white pada mode light dan charcoal netral pada mode dark. Main card
memakai radius besar, subcard radius sedang. Warna accent tidak dipakai
sebagai background card; accent hanya dipakai pada icon, badge, progress,
grafik, garis card, dan state interaksi.

Aturan ini menjaga pengalaman antar module tetap konsisten:

- sidebar lebih dalam dari topnav, tetapi tetap memakai theme palette;
- active dan hover sidebar memiliki border, indikator, dan glow ringan;
- warna icon menunjukkan fungsi, bukan dekorasi acak;
- light dan dark mode memakai hue semantic yang sama;
- perubahan visual tidak boleh mengubah permission, route, atau security
  boundary.

## Shortcut keyboard AccessControl

Halaman AccessControl menyediakan shortcut untuk mempercepat pekerjaan user:

- `R` atau `/` membuka pencarian role;
- `Shift+S` menyimpan perubahan permission role aktif tanpa mengambil alih
  shortcut simpan halaman dari browser;
- `Esc` menutup pencarian role.

Shortcut hanya aktif saat fokus tidak berada pada input, textarea, select,
combobox, atau elemen editable. Informasi shortcut ditampilkan pada strip
bantuan di atas panel Role dan Permission.
