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

## Revisi visual — warna terang dan sidebar footer

- [x] Background dashboard dikembalikan ke pola bersih.
  - Kondisi awal: increment sebelumnya menambahkan gradient besar, grid
    ambient, dan glow pada `theme-dashboard-shell`.
  - Perubahan: `resources/css/app.css` mengubah `theme-dashboard-shell` kembali
    memakai `var(--background)` tanpa pseudo-element grid, gradient background,
    atau glow. Depth card tetap dipertahankan melalui border, blur, shadow, dan
    hover lift yang ringan.
  - Alasan: user memilih pola dashboard shell yang bersih; warna terang cukup
    digunakan pada data dan komponen penting, bukan seluruh background.
  - Evidence: browser light dan dark menampilkan background tanpa pola kotak;
    card tetap memiliki pemisahan yang jelas dan tidak flat.
- [x] Warna chart, progress, dan badge dibuat lebih terang.
  - Kondisi awal: progress dan badge masih memakai `var(--primary)` yang sama
    sehingga status dan data kurang memiliki pembeda visual.
  - Perubahan: token `--dashboard-chart-cyan`, `--dashboard-chart-orange`,
    `--dashboard-chart-yellow`, `--dashboard-chart-blue`, dan
    `--dashboard-chart-red` ditambahkan. Progress coverage memakai cyan dan
    emerald, sedangkan badge role memakai emerald untuk protected dan blue
    untuk editable.
  - Alasan: mengikuti pola Dashboard Shell 7 yang memakai warna semantic
    terang pada icon, badge, dan grafik tanpa mengubah struktur AccessControl.
  - Evidence: browser dark menampilkan progress cyan, coverage cyan, badge
    semantic, dan console bersih. Type check, ESLint terarah, Prettier, dan
    `git diff --check` lulus.
- [x] Footer sidebar icon-only horizontal selesai.
  - Kondisi awal: sidebar hanya memiliki menu Platform dan tidak menyediakan
    shortcut pengaturan akun di bagian bawah.
  - Perubahan: `resources/js/components/app-sidebar.tsx` menambahkan
    `SidebarFooter` sebagai toolbar horizontal berisi icon `Profile`,
    `Security`, dan `Appearance`. Teks permanen dihapus; setiap icon tetap
    memiliki tooltip dan `<span className="sr-only">` untuk aksesibilitas.
    Route Ziggy dan active state dari `useCurrentUrl` tetap dipakai.
  - Alasan: shortcut akun tetap mudah ditemukan dengan footprint visual kecil,
    tanpa menambah baris teks di footer sidebar.
  - Acceptance: tiga shortcut tersusun horizontal, tidak menampilkan label
    visual, tooltip tetap muncul saat hover, nama link tetap terbaca oleh
    screen reader, dan active state tetap mengikuti URL.
  - Evidence: snapshot browser desktop tetap menampilkan nama link
    `Profile`, `Security`, dan `Appearance` untuk aksesibilitas; screenshot
    desktop menunjukkan label tidak tampil secara visual dan footer tersusun
    horizontal. Type check dan ESLint terarah lulus.

## Baseline visual baru — dashboard depth dan semantic icon

- [x] Visual depth dashboard selesai.
  - Kondisi awal: `system-dashboard-layout.tsx` memakai background dan border
    token biasa, sedangkan card dashboard terlihat datar karena semua surface
    memiliki perlakuan yang hampir sama.
  - Perubahan: `resources/css/app.css` memakai token
    `--dashboard-surface` dan `--dashboard-surface-strong`. Class
    `theme-dashboard-shell` memakai background bersih. Class `dashboard-card`
    tetap menambahkan translucent surface, blur, border accent, shadow lembut,
    dan hover lift 2px.
  - Alasan: dashboard perlu memiliki depth pada card tanpa membuat background
    menjadi landing page atau dipenuhi gradient dan grid.
  - Evidence: browser light dan dark menampilkan background bersih, card tetap
    terbaca, hover card tidak mengganggu layout, type check, ESLint terarah,
    Prettier terarah, build, dan `git diff --check` lulus. Viewport mobile
    tidak memiliki horizontal overflow.
- [x] Semantic color icon dashboard selesai.
  - Kondisi awal: semua icon memakai `bg-primary/10 text-primary`, sehingga
    fungsi setiap metric sulit dibedakan secara cepat.
  - Perubahan: `SystemDashboardWidgets.tsx` memakai accent class
    `dashboard-card--blue`, `dashboard-card--cyan`,
    `dashboard-card--violet`, dan `dashboard-card--emerald`. Class
    `dashboard-icon` memberi background, border, dan warna icon berdasarkan
    accent semantic.
  - Alasan: icon perlu membantu user memahami kategori metric tanpa
    menambahkan teks atau dekorasi yang berlebihan.
  - Evidence: browser menampilkan icon Total role berwarna blue, Permission
    cyan, Permission insight violet, dan status role emerald pada mode light
    maupun dark. Console browser tidak memiliki error atau warning.

**Batasan verifikasi:** Lighthouse Accessibility, Best Practices, SEO, dan
Agentic Browsing memberi nilai 100 pada snapshot mobile.

**Batasan:** palette saat ini disimpan per browser melalui `localStorage`, belum
disimpan ke profil user atau database. Jika nanti diperlukan sinkronisasi lintas
perangkat, perlu contract backend dan keputusan baru.

## Increment visual semantic glow

- [x] Token visual semantic diperkuat.
  - Kondisi awal: background icon hanya memakai accent 13%, border 25%, dan
    tidak memiliki outer glow. Card masih terlalu dekat dengan hue palette
    sehingga beberapa warna semantic terlihat flat pada dark mode.
  - Perubahan: `resources/css/app.css` memperkuat icon, badge,
    progress, dan garis accent card dengan opacity terkontrol. Surface card
    dibuat lebih netral tanpa mengubah background aplikasi. Accent memakai
    pasangan warna yang berdekatan agar garis card dan progress terlihat hidup.
  - Alasan: teknik Ryan RL menghasilkan kesan hidup melalui kontras surface,
    tint warna, border, dan blur kecil, bukan melalui glow besar.
  - Acceptance: background halaman tetap bersih; icon dan badge lebih hidup
    pada light/dark; teks tetap mudah dibaca.
  - Evidence: computed style browser membuktikan icon memakai tint 20%, border
    35%, dan glow 12% pada light serta 18% pada dark. Background halaman tetap
    tanpa background image. Lighthouse mobile memberi nilai 100 untuk seluruh
    kategori yang diperiksa.
- [x] Collision class komponen diselesaikan.
  - Kondisi awal: `Badge` membawa `bg-primary text-primary-foreground` sehingga
    class semantic blue/emerald tidak muncul pada runtime. `AvatarFallback`
    membawa `bg-muted` yang menimpa background icon metric kecil.
  - Perubahan: `SystemDashboardWidgets.tsx` memakai variant/class yang
    tidak bertabrakan dan memberi accent semantic yang eksplisit untuk metric.
    Icon metric kecil tidak lagi memakai `AvatarFallback`. Badge memakai
    `variant="outline"` dan foreground dari token `--badge-foreground`.
  - Alasan: semantic style harus terbukti melalui computed style, bukan hanya
    tercantum pada nama class source.
  - Acceptance: `Editable` blue, `Protected` emerald, dan icon metric kecil
    memiliki background semantic pada runtime.
  - Evidence: computed style runtime menunjukkan badge `Editable` berwarna
    blue dan `Protected` berwarna emerald pada light/dark. Icon metric memakai
    blue, violet, amber, dan emerald. TypeScript dan ESLint terarah lulus.
- [x] Quality gate dan evidence selesai.
  - Kondisi awal: perubahan visual baru belum memiliki evidence final.
  - Verifikasi: jalankan type check, lint terarah, format check, build,
    `git diff --check`, browser responsive, console, dan Lighthouse.
  - Acceptance: gate file terdampak lulus, console tidak memiliki error, dan
    tidak ada horizontal overflow. Warning baseline yang tidak berasal dari
    increment wajib dicatat.
  - Evidence: `npm run types:check`, ESLint terarah, Prettier terarah,
    `npm run format:check`, `npm run build`, `git diff --check`, dan
    `php artisan module:discover --json` lulus. Viewport 500x844 tidak memiliki
    overflow horizontal. Lighthouse snapshot mobile mendapat nilai 100 pada
    Accessibility, Best Practices, SEO, dan Agentic Browsing. Console bersih.
- [x] OPEN RISK Coverage goal dan warning tooling ditutup.
  - Kondisi awal: panel `Coverage goal` memakai `bg-muted/20` sehingga dark mode
    terlihat transparan dan tidak match dengan surface dashboard. Class
    `bg-card` bawaan `Card` juga menimpa token `--dashboard-surface`. Console
    menampilkan tiga warning preload font, dan `npm run format:check` global
    gagal pada empat file frontend.
  - Perubahan: `SystemDashboardWidgets.tsx` menghapus `bg-muted/20` dari panel
    Coverage goal. `resources/css/app.css` menambahkan selector surface card
    yang lebih spesifik agar `dashboard-card` mengalahkan `bg-card`. Pada
    `vite.config.ts`, preload Instrument Sans dimatikan karena asset preload
    WOFF2 tidak sama dengan asset WOFF yang dipilih CSS. Empat file frontend
    yang gagal format dirapikan dengan Prettier.
  - Alasan: surface semantic harus konsisten pada light/dark, preload yang
    tidak dipakai harus dihilangkan, dan quality gate global harus dapat
    dijalankan tanpa noise baseline.
  - Acceptance: Coverage goal memakai surface yang sama dengan dashboard,
    font tetap dimuat, tidak ada console error/warning, dan format check global
    lulus.
  - Evidence: computed style browser menunjukkan surface Coverage goal
    konsisten pada light dan dark. Tidak ada preload font tersisa; font dimuat
    melalui CSS. Console kosong, `npm run format:check` lulus, dan Lighthouse
    mobile memberi nilai 100 pada seluruh kategori.

## Increment baseline sidebar dan AccessControl

- [x] Sidebar memakai baseline visual dashboard.
  - Kondisi awal: sidebar masih memakai surface dan state bawaan
    `bg-sidebar`, sehingga terlihat flat dan icon menu belum memiliki pembeda
    semantic yang cukup.
  - Perubahan: `app-sidebar.tsx` menambahkan scope `dashboard-sidebar` pada
    shell. `resources/css/app.css` menambahkan surface sidebar lebih dalam,
    border, shadow, active/hover state, separator header/footer, dan warna icon
    cyan/violet untuk menu utama serta blue/violet/emerald untuk footer.
  - Alasan: sidebar adalah shell bersama untuk System Dashboard dan seluruh
    module System, sehingga visualnya harus menjadi bagian dari baseline yang
    sama.
  - Acceptance: active dan hover terlihat pada light/dark, icon memiliki warna
    semantic, footer tetap icon-only horizontal, dan tooltip/accessibility tetap
    tersedia.
  - Evidence: computed style dark menunjukkan surface sidebar
    `oklch(0.1584 0.01232 145)`, border dan glow aktif. Icon menu serta footer
    memiliki warna semantic berbeda. Snapshot browser tetap membaca link
    Profile, Security, dan Appearance.
- [x] Warna icon Appearance pada footer tidak lagi kuning.
  - Kondisi awal: icon footer `Appearance` memakai token chart yellow sehingga
    terlihat terlalu mencolok dibanding Profile dan Security.
  - Perubahan: `resources/css/app.css` mengganti warna icon footer item ketiga
    menjadi emerald `oklch(0.62 0.17 160)` pada light dan
    `oklch(0.78 0.18 160)` pada dark.
  - Alasan: emerald memberi variasi semantic yang lebih tenang dan tetap
    berbeda dari blue Profile serta violet Security.
  - Acceptance: icon Appearance tidak kuning, tetap terlihat pada light/dark,
    tooltip dan link tidak berubah.
  - Evidence: computed style footer pada light membaca emerald
    `oklch(0.62 0.17 160)` dan pada dark membaca
    `oklch(0.78 0.18 160)`. Console bersih, footer tetap memuat tiga link
    icon-only, dan Lighthouse mobile seluruh kategori 100.
- [x] Block visual header sidebar dihilangkan.
  - Kondisi awal: header sidebar memiliki background, border bawah, dan shadow
    pada tombol logo sehingga terlihat seperti block terpisah dari shell.
  - Perubahan: `resources/css/app.css` membuat header dan menu button header
    transparan, menghapus border permanen, serta menghapus shadow. Hover tetap
    diwariskan dari state sidebar umum.
  - Alasan: shell terlihat lebih ringan dan konsisten dengan layout clean yang
    sudah disepakati.
  - Acceptance: header tidak memiliki block background/border saat normal,
    logo tetap tampil, link dashboard tetap berfungsi, dan hover tetap jelas.
  - Evidence: computed style browser, console, dan Lighthouse perlu lulus pada
    light/dark.
- [x] Palette `Neutral` dijadikan default.
  - Kondisi awal: fallback hook dan snapshot server masih memakai `Urban` saat
    browser belum memiliki `theme-palette` atau nilainya tidak valid.
  - Perubahan: `use-theme-palette.ts` mengganti initial state, fallback
    storage, dan server snapshot menjadi `neutral`.
  - Alasan: Neutral paling sesuai dengan baseline surface bone white/charcoal
    dan menjadi titik awal yang clean untuk user baru.
  - Acceptance: user baru menerima `Neutral`, pilihan tersimpan tetap dipakai,
    dan palette invalid kembali ke `Neutral`.
  - Evidence: type check, browser initialization, console, build, dan diff
    check lulus.
- [x] Checkbox permission memakai accent semantic.
  - Kondisi awal: checkbox permission hanya memakai style native dengan class
    `rounded`, sehingga warna checked belum mengikuti accent module.
  - Perubahan: `PermissionModulePanel.tsx` menambahkan class
    `dashboard-permission-checkbox dashboard-accent--cyan`. `resources/css/app.css`
    menambahkan `accent-color`, border cyan, hover, focus ring, dan disabled
    state.
  - Alasan: state permission checked harus mudah terlihat dan konsisten dengan
    icon serta subcard permission pada light/dark.
  - Acceptance: checkbox checked memiliki accent cyan, focus dapat terlihat,
    disabled tetap jelas sebagai read-only, dan behavior permission tidak
    berubah.
  - Evidence: computed style browser, console, Lighthouse, type check, build,
    dan diff check lulus.
- [x] Icon judul Permission Module memakai accent berbeda.
  - Kondisi awal: icon `ShieldCheck` pada judul Permission Module memakai
    violet yang sama dengan main card sehingga hierarki visual kurang jelas.
  - Perubahan: `PermissionModulePanel.tsx` mengubah icon judul menjadi
    `dashboard-accent--rose`. Main card tetap violet dan checklist tetap cyan.
  - Alasan: rose memberi penanda visual khusus untuk area permission tanpa
    mengubah arti warna panel dan group permission.
  - Acceptance: icon judul berbeda dari panel dan checklist, tetap terbaca pada
    light/dark, dan tidak mengubah behavior authorization.
  - Evidence: computed style browser, console, Lighthouse, build, dan diff
    check lulus.
- [x] Group permission memiliki accent icon berbeda dan dropdown collapsed.
  - Kondisi awal: icon key pada group `AccessControl` dan `System` sama-sama
    cyan. Checklist seluruh group langsung tampil saat halaman dibuka.
  - Perubahan: `PermissionModulePanel.tsx` menambahkan mapping accent
    `AccessControl` violet dan `System` emerald. Header group menjadi button
    dengan `aria-expanded` dan `aria-controls`; state awal `expandedGroups`
    kosong sehingga semua checklist collapsed. Klik header membuka atau
    menutup checklist group.
  - Alasan: pengguna dapat membedakan sumber permission dengan cepat dan
    halaman tidak terlalu padat saat pertama dibuka.
  - Acceptance: default tidak menampilkan checklist, klik header menampilkan
    checklist group yang dipilih, icon group berbeda, checkbox tetap dapat
    diubah sesuai authorization, dan keyboard focus terlihat.
  - Evidence: browser snapshot sebelum klik tidak menampilkan checkbox,
    setelah klik header checklist tampil. Console, Lighthouse, type check,
    build, dan diff check lulus.
- [x] Dropdown permission dibuat accordion dan hover mengikuti palette.
  - Kondisi awal: state memakai `Set`, sehingga lebih dari satu group dapat
    terbuka bersamaan. Hover header memakai accent cyan subcard, bukan primary
    palette aktif.
  - Perubahan: `PermissionModulePanel.tsx` mengganti state menjadi satu
    `expandedGroup` sehingga hanya satu group terbuka pada satu waktu.
    `resources/css/app.css` memakai `--primary` untuk background, text, dan
    focus outline hover header.
  - Alasan: interaksi lebih mudah dipahami dan hover harus konsisten dengan
    theme palette yang dipilih user.
  - Acceptance: membuka AccessControl menutup System, membuka System menutup
    AccessControl, klik group terbuka menutupnya, dan hover mengikuti primary
    palette pada light/dark.
  - Evidence: browser state accordion, computed style hover, console,
    Lighthouse, type check, build, dan diff check lulus.
- [x] Tinggi subcard permission tidak lagi ikut stretch.
  - Kondisi awal: saat satu group permission dibuka, subcard group lain dalam
    grid ikut mengikuti tinggi row dan menampilkan ruang kosong.
  - Perubahan: `PermissionModulePanel.tsx` menambahkan `items-start` pada grid
    group permission agar setiap subcard memakai tinggi kontennya sendiri.
  - Alasan: card yang tertutup harus tetap compact dan tidak terlihat seperti
    ikut terbuka.
  - Acceptance: membuka satu group tidak mengubah tinggi visual group lain,
    accordion tetap eksklusif, dan checklist yang dibuka tetap dapat digunakan.
  - Evidence: browser membandingkan tinggi subcard sebelum/sesudah group dibuka,
    console, Lighthouse, type check, build, dan diff check lulus.
- [x] Halaman AccessControl memakai pola dashboard System.
  - Kondisi awal: role panel dan permission panel masih memakai `bg-card` polos;
    group permission belum memiliki icon dan toolbar action belum memiliki
    surface semantic.
  - Perubahan: `Index.tsx` memakai `dashboard-toolbar`. `RoleControlCard.tsx`
    memakai panel blue, icon `UsersRound`, control semantic, dan subcard role.
    `PermissionModulePanel.tsx` memakai panel violet, icon `ShieldCheck`,
    subcard cyan dengan icon `KeyRound`, badge amber, dan message semantic.
  - Alasan: AccessControl harus dapat menjadi contoh visual yang dapat diikuti
    module System berikutnya tanpa membuat layout baru yang terpisah.
  - Acceptance: panel dan icon konsisten dengan dashboard, permission checkbox
    tetap dapat diuji, state protected/read-only tetap terlihat, dan backend
    authorization tidak berubah.
  - Evidence: browser light/dark menunjukkan panel netral dengan accent blue,
    violet, dan cyan. Viewport 500x844 tidak overflow. Lighthouse mobile
    Accessibility, Best Practices, SEO, dan Agentic Browsing mendapat nilai
    100. Console tidak memiliki error/warning.
- [x] Card dan subcard memakai surface neutral Dashboard Shell 01.
  - Kondisi awal: tint accent 3% pada light dan 6% pada dark masih membuat
    card AccessControl terlihat berwarna, terutama pada mode light.
  - Perubahan: `resources/css/app.css` menetapkan `--dashboard-surface` dan
    `--dashboard-surface-strong` menjadi bone white pada light serta charcoal
    netral pada dark. Aturan tint background pada `dashboard-module-card` dan
    subcard dihapus. Main card memakai `rounded-2xl`, subcard memakai
    `rounded-xl`. Accent tetap dipakai pada icon, badge, garis card, dan state
    interaksi.
  - Alasan: mengikuti pola clean Dashboard Shell 01 tanpa menghilangkan
    identitas warna semantic pada elemen fungsi.
  - Acceptance: main card dan subcard memakai surface bone white/charcoal,
    tepian membulat, warna semantic tetap terlihat pada elemen pendukung,
    tidak overflow di mobile, dan teks tetap memenuhi kontras minimum.
  - Evidence: computed style browser light membaca card `oklch(0.985 0.008 90)`
    dan dark membaca card `oklch(0.19 0.012 260)`. Console bersih, Lighthouse mobile
    seluruh kategori 100, `npm run types:check`, `npm run build`, dan
    `git diff --check` lulus.

## Increment palette default Appearance

- [x] Palette netral default ditambahkan pada Appearance.
  - Kondisi awal: Appearance hanya menyediakan palette project seperti Urban,
    Forest, Ocean, dan palette berwarna lain. Pilihan netral `slate`, `gray`,
    `zinc`, `neutral`, dan `stone` belum tersedia.
  - Perubahan: `resources/js/hooks/use-theme-palette.ts` menambahkan lima
    palette netral dengan swatch dan type `ThemePalette` yang ikut diperbarui
    secara otomatis. `resources/css/app.css` menambahkan token primary,
    accent, ring, dan foreground untuk mode light serta dark pada kelima
    palette tersebut.
  - Alasan: user perlu pilihan theme default yang lebih tenang untuk UI
    dashboard, tanpa menghapus palette project yang sudah digunakan.
  - Acceptance: lima palette baru tampil pada halaman Appearance, dapat dipilih,
    tersimpan di `localStorage`, dan tetap bekerja setelah reload serta saat
    mode light/dark diganti.
  - Evidence: Appearance menampilkan 17 tombol palette. Browser berhasil
    memilih `Slate`, membaca `data-theme="slate"`, membaca primary light
    `oklch(0.45 0.05 255)` dan primary dark `oklch(0.72 0.06 255)`.
    Console bersih. Lighthouse mobile seluruh kategori 100. `npm run
    types:check`, `npm run format:check`, `npm run build`, dan
    `git diff --check` lulus.

## Increment perbaikan route Ziggy

- [x] Route mutation AccessControl tersedia pada daftar route Ziggy.
  - Kondisi awal: route backend `access-control.roles.store` dan
    `access-control.roles.destroy` sudah terdaftar di `routes/web.php`, tetapi
    keduanya belum masuk daftar `only` pada `config/ziggy.php`. Saat tombol
    Tambah role dijalankan, frontend melempar error bahwa route
    `access-control.roles.store` tidak ada di route list.
  - Perubahan: `config/ziggy.php` menambahkan route `access-control.roles.store`
    dan `access-control.roles.destroy`. `tests/Feature/ZiggyRouteTest.php`
    menambahkan assertion regression untuk kedua route mutation tersebut.
  - Alasan: setiap route yang dipanggil frontend melalui helper Ziggy wajib
    tersedia pada konfigurasi route yang dibagikan ke Inertia.
  - Acceptance: route store, destroy, dan update permission tersedia pada
    payload Ziggy; dialog Tambah role dapat dibuka tanpa error route; middleware
    dan otorisasi backend tetap tidak berubah.
  - Evidence: test RED gagal sebelum whitelist diperbaiki. Setelah perubahan,
    `php artisan test tests/Feature/ZiggyRouteTest.php` lulus 2/2 test dengan
    11 assertion. Browser membaca ketiga route dari payload Ziggy, dialog
    Tambah role berhasil terbuka, dan tidak ada error/warning console.

## Increment notifikasi mutation dengan Sonner

- [x] Toast Sonner diaktifkan untuk operasi AccessControl.
  - Kondisi awal: komponen `Toaster` dan hook `useFlashToast` sudah aktif pada
    shell aplikasi, tetapi `RoleController` masih memakai session `status` biasa
    sehingga operasi tambah role, hapus role, dan simpan permission tidak
    menghasilkan toast. Error frontend hanya ditampilkan sebagai pesan inline.
  - Perubahan: `RoleController.php` memakai `Inertia::flash('toast', ...)`
    untuk tiga operasi sukses. `Index.tsx` mengimpor `toast` dari `sonner` dan
    menampilkan `toast.error` untuk kegagalan simpan permission, tambah role,
    dan hapus role. Pesan inline untuk error operasi role tetap menjadi fallback;
    permission tidak lagi menampilkan notifikasi inline setelah increment
    berikutnya.
    `AccessControlPageTest.php` menambahkan assertion terhadap payload flash
    sukses pada ketiga mutation.
  - Alasan: notifikasi hasil operasi harus konsisten dengan Sonner global dan
    tidak bergantung pada pesan status yang tidak dibaca hook frontend.
  - Acceptance: operasi sukses mengirim payload `flash.toast` bertipe success,
    kegagalan mutation menampilkan toast error, dan otorisasi serta behavior
    mutation tidak berubah.
  - Evidence: test RED awal gagal pada tiga assertion flash. Setelah perubahan,
    `php artisan test tests/Feature/AccessControlPageTest.php` lulus 12/12 test
    dengan 59 assertion. `npm run types:check`, `npm run format:check`, dan
    `git diff --check` lulus.
- [x] Toast memiliki warna semantic sebagai baseline awal.
  - Kondisi awal: toast masih memakai background `popover` dan border umum,
    sehingga success dan error sulit dibedakan secara visual.
  - Perubahan: `resources/css/app.css` menambahkan style berdasarkan
    `data-type` Sonner. Success memakai cyan dashboard, error memakai
    destructive, warning memakai chart amber, dan info memakai primary. Semua
    warna menggunakan `color-mix` dengan `popover` agar tetap lembut pada light
    dan dark mode.
  - Alasan: hasil operasi harus cepat dikenali dengan warna semantic yang
    berbeda dari surface biasa.
  - Acceptance: toast success/error/warning/info memiliki warna border,
    background, dan icon yang berbeda; teks tetap terbaca pada light/dark; dan
    layout toast tidak berubah.
  - Evidence: `npm run types:check`, `npm run format:check`, dan
    `git diff --check` lulus. Implementasi warna ini kemudian diperkuat menjadi
    background solid pada increment berikutnya. Browser tidak menghasilkan
    error atau warning.
- [x] Notifikasi inline permission dihapus dan role memakai search combobox.
  - Kondisi awal: `PermissionModulePanel.tsx` menampilkan `saveStatus` sebagai
    pesan inline setelah save, bersamaan dengan toast Sonner dari backend.
    `RoleControlCard.tsx` memakai elemen `select` sehingga role harus dicari
    dari daftar native yang panjang.
  - Perubahan: prop dan state `saveStatus` serta dua pesan inline dihapus.
    Error mutation tetap memakai `toast.error`. `RoleControlCard.tsx` mengganti
    select dengan combobox custom yang memiliki input pencarian, listbox,
    filter nama role, pilihan aktif, klik di luar untuk menutup, dan Escape
    untuk menutup. `resources/css/app.css` menambahkan hover dan selected state
    combobox yang mengikuti primary palette.
  - Alasan: hasil permission cukup diberitahukan satu kali melalui Sonner, dan
    pencarian role menjadi lebih cepat bagi user dengan banyak role.
  - Acceptance: tidak ada pesan inline sukses/gagal permission, combobox
    memiliki `aria-expanded` dan `aria-haspopup=listbox`, query menyaring role,
    pilihan role mengubah permission panel, Escape/click luar menutup menu, dan
    keyboard focus tetap terlihat.
  - Evidence: browser berhasil menyaring query `test` menjadi satu option,
    memilih role tersebut, mengecek pesan inline lama tidak dirender, dan
    console bersih. `npm run types:check`, `npm run format:check`, dan
    `git diff --check` lulus.
- [x] Background toast dibuat solid dan tidak mengikuti palette theme.
  - Kondisi awal: background toast memakai `color-mix` dengan token theme,
    sehingga success dan error terlihat seperti surface biasa dan kurang jelas.
  - Perubahan: `resources/css/app.css` menetapkan warna tetap untuk toast
    success hijau, error merah, warning amber, dan info biru. Border, icon, dan
    foreground juga memakai warna kontras yang tetap sama pada light/dark.
    `!important` dipakai hanya pada rule toast agar tidak ditimpa variable
    background bawaan Sonner.
  - Alasan: notifikasi harus langsung dikenali sebagai status operasi, bukan
    terlihat seperti card biasa yang berubah mengikuti theme.
  - Acceptance: success memiliki background hijau solid, error merah solid,
    teks dan icon terbaca, dan hasilnya konsisten pada mode light maupun dark.
  - Evidence: `git diff --check` lulus; computed style browser untuk selector
    toast menunjukkan background fixed semantic dan console tetap bersih.
