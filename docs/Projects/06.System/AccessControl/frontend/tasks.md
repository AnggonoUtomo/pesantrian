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
- [ ] Shortcut, summary, dialog tambah role, dan dialog hapus role tersedia.

**Evidence:** page `Index.tsx` dapat dibuka pada browser; snapshot DOM
menampilkan role `SecurityAdmin`, module `Access Control`, dan permission
`access_control.role.manage`. Snapshot sidebar mobile juga menampilkan link
`Access Control` untuk actor berpermission.

## Task 03 — Form, state, dan authorization visibility

**Tujuan:** memastikan UX mutation dan state aman.

**Acceptance criteria:**

- [ ] Add/delete role memakai dialog dan confirmation.
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

- [ ] Critical flow role dan permission lulus di browser.
- [ ] Keyboard navigation dan focus state lulus.
- [ ] Responsive desktop/mobile ditinjau.
- [ ] Console error dan network error tidak tersisa.
- [ ] Accessibility scan relevan lulus.

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
- [x] Welcome memiliki card System dan Access Control yang menuju `system.login`.
- [x] Halaman login menampilkan konteks area System tanpa membuat authentication
  backend terpisah.
- [x] Halaman Unauthorized memiliki visual state 403, tombol beranda, login
  ulang, dan navigasi kembali.
- [x] Top nav sticky mengikuti pola dashboard shell dan tidak hanya memakai
  sidebar.
- [ ] Positive dan negative browser flow lulus.
- [ ] Permission visibility tidak menjadi security boundary.
- [x] Endpoint persistence permission role memiliki positive dan negative test.
- [x] Dokumentasi dan execution evidence diperbarui.

**Evidence layout terbaru:** `system-dashboard-layout.tsx` menyediakan pola
header dashboard, content area max-width, dan footer module. `Index.tsx` hanya
menggunakan layout ini di dalam `AppLayout` global sehingga sidebar tidak
terduplikasi. TypeScript, Prettier, ESLint source, dan build lulus. Browser
review terbaru tertahan karena credential user demo lokal tidak cocok; hal ini
tidak berkaitan dengan layout.

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
