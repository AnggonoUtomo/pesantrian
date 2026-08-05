# Tasks Frontend AccessControl

## Task 01 — Contract dan inventory frontend

**Tujuan:** memastikan struktur frontend mengikuti baseline dan contoh.

**Files:** `resources/js/pages/System/AccessControl/`, Ziggy route, shared
types, `FrontendContoh/access-control/`, dan dokumen frontend.

**Acceptance criteria:**

- [x] Folder dan ownership page ditetapkan.
- [x] Props, role, permission group, dan ULID string typed.
- [x] Route page memiliki nama Ziggy yang disepakati.
- [x] Menu sidebar memakai route Ziggy dan visibility permission.

**Evidence:** `resources/js/pages/System/AccessControl/` dibuat dengan type
ULID string; route `access-control.index` terdaftar dan masuk `config/ziggy.php`;
`resources/js/components/app-sidebar.tsx` menampilkan menu hanya untuk
`SuperSystem` atau `access_control.role.manage`. Focused page test dan TypeScript
check lulus.

## Task 02 — Page dan workspace role/permission

**Tujuan:** membuat halaman utama dan komponen role/permission.

**Acceptance criteria:**

- [x] Header, role card, dan permission module panel tersedia.
- [x] Role dapat dipilih dan permission dikelompokkan berdasarkan module.
- [x] `SuperSystem` ditandai protected pada data page.
- [x] Checkbox role biasa dapat dipilih atau dibatalkan oleh actor berizin.
- [x] Checkbox `SuperSystem` disabled dan menampilkan alasan read-only.
- [x] Shortcut, summary, dialog tambah role, dan dialog hapus role tersedia.
  - Kondisi awal: page hanya memiliki pemilih role dan panel permission; create
    dan delete role belum memiliki endpoint maupun dialog.
  - Perubahan: menambahkan `AddRoleDialog`, `DeleteRoleDialog`, route Ziggy
    create/delete, validasi nama role, confirmation, processing, dan error
    state.
  - Alasan: actor berizin perlu menyelesaikan lifecycle role dari UI tanpa
    melewati policy server.
  - Evidence: test create/delete positif dan negative lulus; browser dapat
    membuka dialog tambah role dan memfokuskan field nama.

**Evidence:** page `Index.tsx` dapat dibuka pada browser; snapshot DOM
menampilkan role `SecurityAdmin`, module `Access Control`, dan permission
`access_control.role.manage`. Snapshot sidebar mobile juga menampilkan link
`Access Control` untuk actor berpermission.

## Task 03 — Form, state, dan authorization visibility

**Tujuan:** memastikan UX mutation dan state aman.

**Acceptance criteria:**

- [x] Add/delete role memakai dialog dan confirmation.
  - Kondisi awal: method `RoleController::store()` hanya placeholder dan belum
    ada method `destroy()`.
  - Perubahan: role baru divalidasi unik pada guard `web`, role dapat dihapus,
    dan `SuperSystem` ditolak server-side.
  - Alasan: tombol frontend harus memiliki contract backend yang nyata dan
    aman.
  - Evidence: `AccessControlPageTest` lulus untuk create, delete, actor tanpa
    permission, dan perlindungan `SuperSystem`.
- [x] Loading, empty, error, success, dan read-only state tersedia untuk flow
  penyimpanan permission.
- [x] Permission visibility memakai shared authorization context.
- [x] Backend tetap menolak request yang tidak berwenang.

**Catatan implementasi:** checkbox permission mengubah state lokal lalu dapat
disimpan melalui `PUT access-control.roles.permissions.update`. Policy server
menolak role `SuperSystem`; positive/negative feature test endpoint lulus.

## Task 04 — Browser dan accessibility verification

**Tujuan:** membuktikan module dapat ditinjau langsung di browser.

**Acceptance criteria:**

- [x] Critical flow role dan permission lulus di browser.
  - Evidence: browser menampilkan role `SecurityAdmin`, `SuperSystem`,
    permission group, dialog tambah role, dan protected state.
- [x] Keyboard navigation dan focus state lulus.
  - Evidence: field nama role otomatis fokus saat dialog dibuka dan tombol
    dialog memiliki accessible name.
- [x] Responsive desktop/mobile ditinjau.
  - Evidence: viewport mobile 375x812 tetap menampilkan action, role card,
    dan permission panel tanpa error.
- [x] Console error dan network error tidak tersisa.
  - Evidence: Chrome DevTools console tidak memiliki error/warning.
- [x] Accessibility scan relevan lulus.
  - Evidence: Lighthouse mobile mendapat Accessibility 100, Best Practices
    100, SEO 100, dan Agentic Browsing 100.

## Final quality checkpoint

- [x] Type check dan frontend build lulus untuk increment page dasar.
- [x] Browser snapshot desktop/mobile dan console review lulus untuk page dasar.
- [x] Browser menegaskan `SecurityAdmin` editable dan `SuperSystem` disabled.
- [x] Page tidak memasang `AppLayout` kedua; layout berasal dari resolver
  `createInertiaApp`.
- [x] Layout module dipisahkan menjadi `system-dashboard-layout` sesuai
  namespace `System/AccessControl`.
- [x] Seluruh komponen pola dashboard diadaptasi ke data AccessControl:
  statistics cards, insight, coverage, metrics, activity table, dropdown, dan
  progress visual.
- [x] Halaman `System/Dashboard` dibuat terpisah dari page AccessControl.
- [x] Menu `System Dashboard` dan route Ziggy `system.dashboard` tersedia.
- [x] Route `/dashboard` diarahkan ke page system dashboard sehingga placeholder
  bawaan tidak lagi tampil.
- [x] Welcome hanya menyediakan satu link login System ke `system.login`.
  - Kondisi awal: card System dan card Access Control sama-sama menuju
    `system.login`, sehingga AccessControl terlihat seperti area login sendiri.
  - Perubahan: card System menjadi satu-satunya link login; AccessControl dan
    User Management ditampilkan sebagai module di dalam System.
  - Alasan: authentication cukup memiliki satu pintu, sedangkan pembatasan
    fitur dilakukan oleh role, permission, middleware, dan policy.
  - Evidence: `resources/js/pages/welcome.tsx` hanya memiliki satu link dengan
    `route('system.login')`; route module tetap dilindungi `auth` dan
    authorization server-side.
- [x] Halaman login menampilkan konteks System tanpa membuat authentication
  backend terpisah.
- [x] Halaman Unauthorized memiliki visual state 403, tombol beranda, login
  ulang, dan navigasi kembali.
- [x] Top nav sticky mengikuti pola dashboard shell dan tidak hanya memakai
  sidebar.
- [x] Positive dan negative browser flow lulus.
  - Evidence: actor berizin dapat melihat action role; `SuperSystem` tidak
    mendapat action hapus dan checkbox permission disabled.
- [x] Permission visibility tidak menjadi security boundary.
  - Evidence: endpoint create, delete, sync permission, dan dashboard tetap
    diuji melalui middleware/policy server-side.
- [x] Endpoint persistence permission role memiliki positive dan negative test.
- [x] Dokumentasi dan execution evidence diperbarui.

**Evidence layout terbaru:** `system-dashboard-layout.tsx` menyediakan pola
header dashboard, content area max-width, dan footer module. `Index.tsx` hanya
menggunakan layout ini di dalam `AppLayout` global sehingga sidebar tidak
terduplikasi. TypeScript, ESLint, build, browser review, dan Lighthouse mobile
lulus.

**Evidence top navigation:** `app-sidebar-header.tsx` sekarang memakai posisi
`sticky top-0`, backdrop, toggle sidebar, breadcrumb, language dropdown,
notification action, dan profile dropdown. Komponen ini berada di `AppLayout`
global sehingga tidak menggandakan header pada page module. `AppSidebarLayout`
memakai `overflow-x-clip` agar tidak membuat scroll container yang mematahkan
perilaku sticky.

**Evidence komponen dashboard:** `SystemDashboardWidgets.tsx` menyediakan
statistics grid, permission insight, permission coverage, authorization
metrics, dan role permission activity table. Komponen memakai `Card`, `Badge`,
`Avatar`, `DropdownMenu`, icon, native table, CSS progress bar, dan CSS conic
gradient sehingga tidak menambah dependency chart/table baru.

**Evidence system dashboard:** `resources/js/pages/System/Dashboard.tsx`
menggunakan `system-dashboard-layout` dan widget dashboard. Backend route
`system.dashboard` memakai middleware permission AccessControl; actor tanpa
permission menerima `403`. Dashboard module berikutnya dapat dibuat di bawah
namespace module masing-masing dengan pola layout yang sama.

## Task 05 — Theme palette pada Appearance

**Tujuan:** menambahkan pilihan warna tema yang dapat ditinjau dan digunakan
langsung melalui halaman Appearance.

**Kondisi awal:** halaman Appearance hanya memiliki pilihan `Light`, `Dark`, dan
`System`. CSS belum memiliki state palette yang dapat dipilih dari UI.

**Perubahan:**

- [x] Scope task selesai.
  - Kondisi awal: `resources/js/components/appearance-tabs.tsx` hanya mengatur
    mode tampilan.
  - Perubahan: `resources/js/components/theme-palette.tsx` menampilkan dua
    belas pilihan palette dengan swatch, status terpilih, label aksesibel, dan
    `data-test`.
  - Alasan: user perlu melihat dan mengganti warna utama tanpa masuk ke source
    code.
  - Evidence: snapshot browser menampilkan palette `Urban` sampai `Copper`.
- [x] State palette selesai.
  - Kondisi awal: `use-appearance` hanya menyimpan mode light/dark/system.
  - Perubahan: `resources/js/hooks/use-theme-palette.ts` menyimpan pilihan
    pada `localStorage` key `theme-palette`, menerapkan `data-theme`, dan
    memulihkan pilihan saat aplikasi dimuat.
  - Alasan: pilihan warna harus konsisten setelah navigasi dan reload.
  - Evidence: setelah memilih `Ruby`, browser membaca
    `data-theme="ruby"`, `--primary: oklch(0.52 0.16 18)`, dan pilihan tetap
    aktif setelah reload.
- [x] CSS palette selesai.
  - Kondisi awal: `resources/css/app.css` hanya memiliki warna default dan
    dark mode umum.
  - Perubahan: selector `[data-theme]` dan `.dark[data-theme]` ditambahkan
    untuk 12 palette dengan warna primary, accent, ring, dan foreground.
  - Alasan: satu state palette harus berdampak ke komponen Tailwind yang
    memakai token warna aplikasi.
  - Evidence: computed style browser berubah dari palette default ke palette
    Ruby tanpa error console.
- [x] Integrasi Appearance selesai.
  - Kondisi awal: halaman `settings/appearance` belum memiliki section palette.
  - Perubahan: section `Theme palette` ditambahkan setelah `AppearanceTabs`.
  - Alasan: pengaturan warna ditempatkan bersama pengaturan light/dark.
  - Evidence: `npm run types:check`, `npm run lint:check`,
    `npm run format:check`, dan `npm run build` lulus.
- [x] Background dan hover sidebar selesai.
  - Kondisi awal: palette hanya mengubah primary dan accent sehingga background
    serta hover sidebar masih memakai warna netral yang sama.
  - Perubahan: `resources/css/app.css` memakai `color-mix` untuk menghasilkan
    `background`, `card`, `muted`, `sidebar`, dan `sidebar-accent` dari warna
    palette aktif. Selector `.dark[data-theme]` menyediakan versi gelap yang
    tetap lembut.
  - Alasan: background dan hover sidebar harus terasa satu tema tanpa kontras
    berlebihan.
  - Evidence: browser membaca `--background`, `--sidebar`, dan
    `--sidebar-accent` bertema; sidebar ter-render dengan warna baru dan console
    tidak memiliki error atau warning.
- [x] Hierarchy top nav dan sidebar selesai.
  - Kondisi awal: warna sidebar terlalu dekat dengan top nav sehingga layout
    terlihat flat.
  - Perubahan: token `--sidebar` dibuat sedikit lebih gelap dari
    `--background` pada light dan dark mode, `--sidebar-accent` memakai
    campuran primary untuk hover, dan menu aktif mendapat indikator garis
    primary di sisi kiri.
  - Alasan: sidebar perlu memiliki kedalaman visual tanpa kontras yang tajam.
  - Evidence: browser menunjukkan warna sidebar dark lebih gelap daripada top
    nav pada palette Forest, console bersih, dan `npm run format:check`,
    `npm run build`, serta `git diff --check` lulus.
- [x] Konsistensi hover seluruh palette selesai.
  - Kondisi awal: `urban`, `quartz`, dan `aurora` belum memiliki override
    primary pada dark mode sehingga hover sidebar memakai warna default.
  - Perubahan: override `.dark[data-theme]` dilengkapi untuk seluruh 12
    palette, termasuk `urban`, `quartz`, dan `aurora`. Dark hover memakai
    campuran primary palette ke surface sidebar agar perbedaan hue tetap
    terlihat pada background gelap.
  - Perbaikan lanjutan: aturan generic tidak lagi menimpa `--accent` milik
    palette setelah selector khusus dark mode.
  - Alasan: hover dan active state harus mengikuti palette yang dipilih pada
    semua kombinasi light/dark.
  - Evidence: browser menguji seluruh 12 nilai `data-theme`; Urban, Ruby,
    Forest, Ocean, dan Plum menghasilkan hue hover yang berbeda. Quartz tetap
    netral sesuai palette-nya. Build dan format lulus.
- [x] Separator hover sidebar selesai.
  - Kondisi awal: hover link sidebar hanya mengubah background dan warna teks.
  - Perubahan: `resources/js/components/nav-main.tsx` menambahkan separator
    `|` setelah icon. Separator muncul saat pointer hover dan tersembunyi pada
    sidebar mode icon.
  - Alasan: menambah detail visual kecil agar hubungan icon dan label lebih
    mudah terlihat.
  - Evidence: browser menunjukkan separator dengan `display: block` dan
    `opacity: 1` saat link di-hover; lint, type check, build, dan diff check
    lulus.
- [x] Dark background mengikuti palette selesai.
  - Kondisi awal: dark mode masih memakai background generic sehingga beberapa
    palette terlihat netral walaupun accent sudah berubah.
  - Perubahan: `resources/css/app.css` menambahkan surface dark khusus untuk
    seluruh 12 palette pada `--background`, `--card`, `--popover`, dan
    `--sidebar`.
  - Alasan: background, card, popover, dan sidebar harus membawa hue theme yang
    sama; sidebar tetap dibuat sedikit lebih gelap dari background.
  - Evidence: browser membaca background Forest `oklch(0.18 0.014 145)` dan
    sidebar sebagai campuran warna Forest dengan hitam. Format, build, dan diff
    check lulus.
- [x] Active dan active-hover sidebar selesai.
  - Kondisi awal: active link dan active-hover memakai token yang sama; selector
    active juga menimpa hover sehingga warna hover tidak mengikuti accent theme.
  - Perubahan: token `--sidebar-active` dipakai untuk active normal. Selector
    active-hover dipaksa memakai `--sidebar-accent` dan
    `--sidebar-accent-foreground`.
  - Alasan: active normal perlu soft, sedangkan active-hover harus terlihat
    responsif dan tetap mengikuti palette.
  - Evidence: browser Forest dark menunjukkan active-hover sama dengan
    `--sidebar-accent` `oklch(0.31 0.055 135)`; format, build, dan diff check
    lulus.
- [x] Light hover sidebar terlihat sesuai theme.
  - Kondisi awal: hover light terlalu pucat karena accent terlalu dekat dengan
    background sidebar.
  - Perubahan: `--sidebar-accent` light memakai campuran primary yang lebih
    terlihat, tetapi tetap soft; foreground hover ikut primary theme. Menu
    navigasi settings memakai `hover:bg-primary/20`, `hover:text-primary`, dan
    shadow tipis.
  - Alasan: user perlu melihat respons hover pada light mode tanpa membuat
    sidebar terlalu kontras.
  - Evidence: browser Forest light menghasilkan hover sidebar
    `oklch(0.852872 0.0271387 145)` dan hover menu `Appearance` memakai primary
    Forest 20% dengan shadow; format, build, dan diff check lulus.
- [x] Active-hover menu Appearance selesai.
  - Kondisi awal: menu Appearance yang sedang aktif masih memakai tint active
    biasa saat pointer diarahkan ke link.
  - Perubahan: active normal memakai `bg-primary/10`, sedangkan active-hover
    memakai `bg-primary/30` dengan teks primary.
  - Alasan: state aktif dan active-hover perlu terlihat berbeda saat ditinjau.
  - Evidence: browser Forest light menunjukkan active-hover memakai primary
    Forest 30%; lint, type check, build, dan diff check lulus.
- [x] Active-hover link Dashboard selesai.
  - Kondisi awal: link Dashboard memakai state active sidebar umum yang belum
    memiliki perbedaan soft dan strong seperti menu Appearance.
  - Perubahan: token `--sidebar-active` dipakai untuk active normal dan token
    `--sidebar-active-hover` dipakai untuk active-hover pada link sidebar.
    Font active normal dan active-hover memakai `var(--primary)` seperti menu
    Appearance.
  - Alasan: Dashboard perlu memiliki hierarchy state yang sama dengan menu
    settings dan tetap mengikuti primary theme.
  - Evidence: browser Forest light menunjukkan active-hover Dashboard memakai
    campuran primary 30% dengan font primary. Active normal juga menggunakan
    font primary; lint, type check, build, dan diff check lulus.

## Baseline topnav — command palette

- [x] Command palette tersedia pada topnav global.
  - Kondisi awal: topnav belum memiliki pencarian menu terpusat.
  - Perubahan: `resources/js/components/command-palette.tsx` menambahkan
    dialog pencarian, shortcut `Ctrl+K`/`⌘K`, filter keyword, empty state, dan
    navigasi menggunakan route Ziggy. Palette ditempatkan di action group kanan
    topnav, tepat sebelum tombol theme.
  - Alasan: user dapat berpindah ke halaman penting tanpa membuka sidebar;
    menu Access Control tetap disaring berdasarkan permission server context.
  - Evidence: browser snapshot menampilkan System Dashboard, Access Control,
    Profil, Appearance, dan Security; query `access` hanya menampilkan Access
    Control; shortcut `Control+K` membuka dialog.
- [x] Command palette mengikuti baseline accessibility dan theme.
  - Kondisi awal: komponen baru belum memiliki evidence browser.
  - Perubahan: dialog memiliki title/description, input berlabel, listbox,
    keyboard focus, hover/focus state, dan token warna theme.
  - Evidence: Lighthouse mobile Accessibility 100, Best Practices 100, SEO
    100, Agentic Browsing 100; console browser bersih.
- [x] Framer Motion terpasang dan diterapkan pada command palette.
  - Kondisi awal: project belum memiliki dependency `framer-motion` dan hasil
    pencarian command palette belum memiliki transisi masuk, keluar, atau
    perubahan posisi.
  - Perubahan: `package.json` dan `package-lock.json` menambahkan
    `framer-motion`. `resources/js/components/command-palette.tsx` memakai
    `MotionConfig`, `AnimatePresence`, `motion.button`, dan `motion.div` untuk
    transisi hasil pencarian serta empty state. Komponen juga membaca
    `prefers-reduced-motion` dan mematikan transisi saat user memintanya.
  - Alasan: interaksi pencarian menjadi lebih mudah dipahami tanpa mengubah
    route, permission, atau security boundary backend.
  - Acceptance: dependency dapat di-resolve, hasil pencarian tetap bisa
    dipakai dengan keyboard, animasi tidak mengubah filter permission, dan
    reduced motion tidak memaksa animasi.
  - Evidence: `npm install framer-motion` selesai dengan 0 vulnerability;
    `npm run types:check`, `npm run lint:check`, `npm run build`, dan
    `git diff --check` lulus. Lighthouse mobile tetap memberi Accessibility
    100, Best Practices 100, SEO 100, dan Agentic Browsing 100. Saat reduced
    motion aktif, browser memakai elemen HTML biasa tanpa `motion.*` dan
    console tidak memiliki error atau warning. Saat reduced motion dimatikan,
    browser menampilkan elemen animasi dan console tetap bersih.

**Batasan animasi:** implementasi pertama hanya diterapkan pada command palette.
Animasi module berikutnya harus memakai token dan pola yang sama, menghormati
`prefers-reduced-motion`, serta tidak menambahkan animasi pada state otorisasi
yang dapat membingungkan user.

**Status OPEN RISK:** ditutup. Warning reduced motion tidak lagi muncul pada
browser karena mode reduced motion menggunakan HTML biasa, sedangkan mode
normal tetap menggunakan Framer Motion.

**Batasan:** palette saat ini disimpan per browser melalui `localStorage`, belum
disimpan ke profil user atau database. Jika nanti diperlukan sinkronisasi lintas
perangkat, perlu contract backend dan keputusan baru.
