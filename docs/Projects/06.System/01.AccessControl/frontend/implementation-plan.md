# Implementation Plan Frontend AccessControl

## Urutan

1. Inventory route, layout, component UI, type, dan Ziggy yang sudah tersedia.
2. Buat contract props dan type ULID string.
3. Buat page `System/AccessControl/Index.tsx`.
4. Buat header, shortcut panel, role card, permission panel, summary, empty
   state, dan dialog.
5. Hubungkan action dengan route Ziggy dan Inertia form.
6. Tambahkan loading, error, success, protected role, dan read-only state.
7. Jalankan TypeScript check, build, browser test, dan accessibility scan.
8. Tinjau checklist dan dokumentasi sebelum module dinyatakan selesai.

## Adaptasi dari FrontendContoh

Nama komponen dari contoh dipertahankan sebagai pola karena mudah ditelusuri,
tetapi data disesuaikan:

| Contoh | Implementasi project |
|---|---|
| `RoleOption.id: number` | `RoleOption.id: string` ULID |
| permission `roles.manage` | `access_control.role.manage` |
| `auth.super` | `auth.superSystem` |
| URL literal access control | route Ziggy bernama |
| state lokal tanpa server error | Inertia form errors dan processing |

## Verification command

```bash
npm run types:check
npm run build
php artisan test
```

Browser verification menggunakan Chrome DevTools MCP dan accessibility check
yang tersedia pada project.

## Execution evidence terbaru

- `RoleController` memiliki middleware untuk view, dashboard, create, update,
  dan delete.
- `AddRoleDialog` dan `DeleteRoleDialog` memakai route Ziggy serta Inertia
  mutation. Field nama role memiliki label, validasi server, processing state,
  dan error alert.
- Role `SuperSystem` tidak dapat dihapus atau diubah permission-nya. UI hanya
  membantu visibility; middleware dan policy tetap memutuskan akses.
- Full test suite `php artisan test` lulus dengan 113 test dan 374 assertion.
- `npm run types:check`, `npm run lint:check`, `npm run build`, dan
  `git diff --check` lulus.
- Browser mobile 375x812, dialog tambah role, protected state, console, dan
  Lighthouse sudah ditinjau. Lighthouse mobile mendapat skor 100 untuk
  accessibility, best practices, SEO, dan agentic browsing.
- `vite.config.ts` memisahkan dependency vendor sehingga build tidak lagi
  menghasilkan warning chunk di atas 500 kB.

## Increment adaptasi visual semantic glow

Increment ini mengadaptasi teknik visual dari Ryan Dash dan Ryan RL tanpa
menyalin layout, identitas, atau gradient background referensi.

### Keputusan visual

- Background aplikasi tetap bersih dan mengikuti theme palette.
- Surface card dashboard dibuat lebih netral agar warna semantic mudah dibaca.
- Warna terang hanya dipakai pada icon, badge, progress, dan garis accent card.
- Glow dibatasi pada elemen semantic dengan opacity rendah. Glow besar pada
  background halaman tidak digunakan.
- Mode light memakai tint pastel dan shadow tipis. Mode dark memakai warna
  semantic lebih terang dengan glow lembut.
- Tidak ada dependency frontend baru.

### Urutan increment

1. Perkuat token `--dashboard-accent`, background icon, border icon, garis
   accent card, progress, dan glow ringan pada `resources/css/app.css`.
2. Hilangkan collision class bawaan `Badge` dan `AvatarFallback` pada
   `SystemDashboardWidgets.tsx` agar semantic style benar-benar diterapkan.
3. Verifikasi computed style light dan dark. Badge `Editable` harus blue,
   `Protected` harus emerald, dan icon metric kecil tidak boleh kembali ke
   `bg-muted`.
4. Jalankan type check, ESLint, format check, build, browser console,
   responsive review, dan accessibility scan.

### Acceptance criteria

- Tidak ada gradient, grid, atau ambient glow pada background halaman.
- Icon memiliki background accent sekitar 18-22%, border accent yang jelas,
  foreground terang, dan glow tipis yang tidak mengganggu teks.
- Badge role memiliki background, border, foreground, dan glow sesuai status;
  class bawaan `bg-primary` tidak menimpa hasil akhir.
- Progress dan garis atas card terlihat hidup, tetapi card tetap tenang.
- Light dan dark mode tetap memiliki kontras yang layak.
- Layout, route, data, permission visibility, dan authorization tidak berubah.

### Execution evidence

- `resources/css/app.css` membuat surface card lebih netral, memperkuat tint
  icon menjadi 20%, border icon menjadi 35%, serta menambah glow ringan pada
  icon, badge, progress, dan garis accent card.
- `SystemDashboardWidgets.tsx` mengganti icon metric kecil dari
  `AvatarFallback` menjadi container semantic. Badge memakai `variant="outline"`
  dan foreground semantic agar style bawaan tidak menimpa warna status.
- Computed style browser membuktikan badge `Editable` memakai blue dan badge
  `Protected` memakai emerald pada mode light dan dark.
- Viewport mobile 500x844 tidak memiliki overflow horizontal. Lighthouse
  snapshot mobile memberi nilai 100 untuk Accessibility, Best Practices, SEO,
  dan Agentic Browsing.
- `npm run types:check`, ESLint terarah, Prettier terarah, `npm run build`, dan
  `git diff --check` lulus.
- `npm run format:check` global lulus setelah empat file frontend yang memang
  terdeteksi tidak rapi diformat ulang.
- `vite.config.ts` memakai `preload: false` untuk Instrument Sans karena preload
  WOFF2 dev server tidak cocok dengan asset WOFF yang dipilih CSS. Font tetap
  dimuat melalui CSS dan warning preload tidak muncul lagi.
- Console browser tidak memiliki error atau warning setelah reload.
- `Coverage goal` menghapus `bg-muted/20` dan memakai surface semantic dashboard;
  computed style light dan dark sekarang konsisten dengan card dashboard lain.

## Increment baseline sidebar dan AccessControl

Increment ini menerapkan pola visual System Dashboard ke shell sidebar dan
halaman AccessControl agar dapat dipakai sebagai baseline module System
berikutnya.

### Perubahan

- `resources/js/components/app-sidebar.tsx` menambahkan scope
  `dashboard-sidebar` pada shell, header, content, dan footer.
- `resources/css/app.css` menambahkan surface sidebar lebih dalam, border,
  shadow, active/hover state, dan warna semantic icon untuk menu utama serta
  footer shortcut.
- `Index.tsx` memakai `dashboard-toolbar` untuk action role.
- `RoleControlCard.tsx` memakai `dashboard-card--blue`, icon `UsersRound`,
  `dashboard-subcard`, `dashboard-control`, dan badge protected.
- `PermissionModulePanel.tsx` memakai `dashboard-card--violet`, icon
  `ShieldCheck`, subcard group dengan icon `KeyRound`, serta status semantic.
- Contrast teks role summary diperkuat dari `text-muted-foreground` menjadi
  `text-foreground/70` setelah Lighthouse menemukan rasio 4.39:1.

### Acceptance criteria dan evidence

- Sidebar tidak lagi flat: surface, border, active, hover, dan icon semantic
  terlihat pada light/dark.
- AccessControl memakai surface dan semantic icon yang sama dengan dashboard.
- Viewport mobile 500x844 tidak memiliki horizontal overflow.
- Console browser tidak memiliki error atau warning.
- Lighthouse mobile mendapat Accessibility, Best Practices, SEO, dan Agentic
  Browsing 100.
- `npm run types:check`, ESLint terarah, Prettier terarah, dan
  `git diff --check` lulus. `npm run build` dijalankan setelah increment
  selesai.

## Increment neutral card surface

Increment ini mengganti tint card AccessControl menjadi surface neutral seperti
Dashboard Shell 01. Uji visual menunjukkan tint accent, walaupun sudah kecil,
masih membuat card light terlihat kurang clean.

### Perubahan

- `RoleControlCard.tsx` dan `PermissionModulePanel.tsx` memakai class
  `dashboard-module-card` pada main card.
- `resources/css/app.css` menetapkan `--dashboard-surface` dan
  `--dashboard-surface-strong` menjadi bone white pada light dan charcoal
  netral pada dark. Aturan tint background card module dihapus.
- `RoleControlCard.tsx` dan `PermissionModulePanel.tsx` memakai
  `rounded-2xl` pada main card dan `rounded-xl` pada subcard.
- Accent tetap dipakai oleh border, garis atas card, icon, badge, dan state
  interaksi sehingga card tidak kehilangan identitas module.
- Teks bantuan role memakai `text-foreground/70` agar tetap terbaca di atas
  surface tint.

### Acceptance criteria dan evidence

- Card role, permission, dan subcard memakai surface bone white/charcoal seperti
  Dashboard Shell 01.
- Tepian main card dan subcard tidak lagi tajam.
- Icon, badge, garis card, dan state interaksi tetap memakai warna semantic.
- Viewport mobile tidak memiliki horizontal overflow dan console bersih.
- Lighthouse mobile mendapat Accessibility, Best Practices, SEO, dan Agentic
  Browsing 100.
- `npm run types:check`, `npm run build`, dan `git diff --check` lulus.
