# Execution Log - Identity, Akses CRUD, dan Avatar

## 2026-08-10 - Perencanaan awal

- Kondisi awal diperiksa pada `System/UserManagement`.
- `php artisan module:inspect System/UserManagement` berhasil dan module berstatus `enabled`.
- `php artisan module:validate System/UserManagement --json` menghasilkan `MODULE_VALID` tanpa diagnostic.
- `php artisan module:discover --json` menemukan AccessControl, UserManagement, AuditLog, dan SystemSetting tanpa diagnostic.
- `composer.json` belum memiliki `spatie/laravel-medialibrary`.
- Pencarian kode tidak menemukan `HasMedia`, `InteractsWithMedia`, `last_login`, atau kolom activity login yang sudah dipakai.
- `email_verified_at` sudah dimiliki model/tabel user bawaan Laravel.

## Keputusan yang Belum Dieksekusi

- Tidak ada kode, migration, dependency, atau konfigurasi yang diubah pada tahap dokumentasi ini.
- Disk avatar dan kontrak URL media tetap Open Decision pada ADR-0005.
- Implementasi menunggu konfirmasi user untuk setiap increment agar penyesuaian UI/UX dapat dilakukan sambil berjalan.

Catatan historis: keputusan tersebut ditutup setelah implementasi diverifikasi
dan user menerima ADR-0005 pada 14 Agustus 2026.

## 2026-08-10 - INC-001 kontrak dan visual identity

- Kondisi awal: `UserData` hanya membawa identitas dasar, status, perlindungan,
  dan arsip. Tabel belum menampilkan role efektif, verifikasi email, atau
  aktivitas login. Modal detail hanya memiliki satu kelompok informasi.
- Perubahan backend:
  - `Application/DTO/UserData.php` menambah `roles`, `avatarUrl`,
    `emailVerified`, dan `lastLoginAt` dengan default aman.
  - `EloquentUserRepository.php` eager-load relation role dan membangun
    `list<string>` role agar tidak terjadi N+1 query pada tabel.
  - `UserResource.php` mengirim field identity/access baru ke Inertia.
- Perubahan frontend:
  - `types.ts` menyamakan contract TypeScript dengan resource.
  - `UserTable.tsx` menampilkan avatar fallback inisial, role efektif, badge
    verifikasi email, serta fallback `Belum pernah login`.
  - `UserViewDialog.tsx` dipisah menjadi Identitas, Akses, dan Aktivitas tanpa
    menambah mutation baru.
- Test baru pada `UserManagementPresentationTest.php` membuktikan role,
  avatar `null`, email belum verified, dan last login `null` diterima sebagai
  read model yang aman.
- Quality evidence: `composer ci:check` lulus: ESLint, Prettier, TypeScript,
  Pint, PHPStan, dan Pest (`260` test, `1225` assertion).
- Temuan quality gate awal: PHPStan menolak `array<int, mixed>` untuk `roles`.
  Penyebab diperbaiki dengan method `roleNames()` yang membangun list string
  nyata; tidak memakai ignore atau baseline. Test generator sempat gagal karena
  folder probe sementara dari diagnosis manual, kemudian lulus setelah probe
  tepat tersebut dibersihkan dan full quality gate dijalankan ulang.
- Batas verifikasi: Chrome DevTools MCP tidak tersedia sebagai callable tool di
  sesi ini. Browser runtime, console, dan accessibility belum diklaim lulus.

## 2026-08-10 - Perbaikan responsive tabel identity

- Temuan user: setelah INC-001 tabel UserManagement tidak lagi nyaman pada
  layar kecil.
- Penyebab: `UserTable.tsx` menaikkan `min-width` tabel dari `680px` menjadi
  `980px` saat tiga kolom informasi baru ditambahkan.
- Perbaikan: lebar minimum dikembalikan ke baseline `680px`. Kolom Role,
  Verifikasi, dan Terakhir login hanya dirender pada breakpoint `xl`; data yang
  sama tetap tersedia pada modal detail di semua ukuran layar.
- Evidence: `npm run lint:check`, `npm run types:check`, dan
  `php artisan test tests/Feature/UserManagementPresentationTest.php` lulus
  (`15` test, `129` assertion).
- Quality gate penuh juga lulus setelah isolasi test: `composer ci:check`
  menghasilkan `260` test, `1225` assertion, serta PHPStan, Pint, ESLint,
  Prettier, dan TypeScript lulus.

## 2026-08-10 - Perbaikan containment layout System

- Temuan lanjutan user: halaman masih melebar pada zoom Chrome 100% setelah
  kolom tabel disembunyikan.
- Penyebab: `SidebarInset`, `SystemDashboardLayout`, dan area `main` adalah
  flex item tanpa `min-w-0`. Konten tabel yang lebar memaksa shell halaman ikut
  melebar sehingga `overflow-x-auto` pada tabel tidak menjadi containment.
- Perbaikan: ketiga flex boundary diberi `min-w-0`. Overflow horizontal kini
  tetap berada di area tabel, bukan pada seluruh halaman System.
- Evidence: Prettier, `npm run lint:check`, `npm run types:check`, dan test
  presentasi UserManagement lulus (`15` test, `129` assertion).
- Batas: Chrome DevTools MCP tidak diekspos sebagai callable tool pada sesi
  agent ini dan Chrome berjalan tanpa remote-debugging port. Visual browser
  nyata masih perlu dikonfirmasi setelah reload oleh user.

## 2026-08-10 - Penutupan evidence browser dan aktivitas login

- Kondisi awal: Chrome DevTools melaporkan tiga issue form identifier dari
  elemen native tersembunyi milik Radix Select. Read model juga selalu
  mengirim `lastLoginAt: null` karena belum ada persistence activity login.
- Perubahan: `UserTable.tsx` menambahkan `id` pada trigger serta `name` pada
  root Select status, role, arsip, dan pagination; `last_login_at` ditambah
  melalui migration owner UserManagement. Listener `Illuminate\Auth\Events\Login`
  hanya memperbarui timestamp untuk `App\Models\User`; repository memetakan
  nilai ISO-8601 ke `UserData` dan resource Inertia.
- Alasan: browser harus memiliki identifier pada control yang benar-benar
  dirender, sedangkan aktivitas login perlu berasal dari event autentikasi
  sukses tanpa menyimpan credential atau payload login.
- Evidence: `php artisan migrate --force` menjalankan migration baru;
  `AuthenticationTest` membuktikan login sukses mengisi timestamp dan login
  gagal tidak mengubahnya. `php artisan test` fokus UserManagement/Auth lulus
  46 test/271 assertion; ESLint, TypeScript, Pint, dan `npm run build` lulus.
  Setelah hard reload, Chrome DevTools pada `/system/users` tidak menemukan
  console error, warning, issue, atau control form tanpa `id`/`name`.
- Hasil: batas browser INC-001 dan aktivitas login pada INC-005 tertutup.
  INC-002 (role/status awal atomik) serta INC-004 (avatar Media Library)
  masih merupakan pekerjaan implementation scope tersendiri dan belum
  dinyatakan selesai.

## 2026-08-10 - Role/status awal dan avatar privat

- Perubahan: pembuatan user menerima status dan role awal; action menjalankan
  assignment role dalam transaction yang sama. Avatar memakai collection
  `avatar` single-file pada disk `local` private, migration ULID milik module,
  request JPEG/PNG/WebP maksimal 2 MB, route upload/hapus berpolicy `update`,
  serta endpoint baca berpolicy `view` tanpa membocorkan path storage.
- Evidence: `UserManagementAvatarTest` membuktikan upload, replace,
  penolakan tipe/ukuran, dan penghapusan; focused suite lulus 34 test/209
  assertion. Pint, ESLint, TypeScript, dan Vite build lulus.
- Batas browser: browser tersedia tetapi sesi System telah berakhir dan tidak
  ada kredensial pengujian yang dapat digunakan ulang. Halaman login memuat
  tanpa console error/warning; interaksi upload avatar belum diklaim lulus
  pada browser nyata.

## 2026-08-14 - Penutupan keputusan ADR-0005

- Kondisi awal: ADR-0005 masih `Proposed` dan disk/URL avatar masih tercatat
  sebagai Open Decision, sedangkan implementasi sudah memakai disk `local`
  privat dan route module berpolicy `view`.
- Perubahan: user menerima ADR-0005. Status diubah menjadi `Accepted`; keputusan
  disk privat dan URL terotorisasi dicatat sebagai batas keamanan.
- Evidence: `config/media-library.php`, `config/filesystems.php`,
  `UserController::avatar()`, route UserManagement, dan
  `UserManagementAvatarTest` cocok dengan keputusan tersebut.
- Risiko: perpindahan ke public disk atau URL public harus melalui ADR pengganti.
