# Tasks: Organization/Organization

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Focused test atau cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan panduan generator module
  dibaca.

## Increment 1: Dokumentasi module

- [x] Buat README module.
  - Acceptance: boundary, public boundary, data, permission, audit, dan
    verifikasi utama tertulis.
  - Verification: `git diff --check`.
- [x] Buat specification module.
  - Acceptance: scope, non-scope, contract, data, authorization, audit,
    dependency, acceptance criteria, dan risiko terbuka tertulis.
  - Verification: `git diff --check`.
- [x] Buat implementation plan module.
  - Acceptance: increment skeleton, data foundation, backend behavior, dan audit
    tersusun berurutan.
  - Verification: `git diff --check`.

## Increment 2: Skeleton module

- [x] Dry-run generator.
  - Acceptance: target `app/Modules/Organization/Organization`, diagnostics
    kosong, `directories: []`.
  - Verification:
    `php artisan module:make Organization Organization --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Organization Organization --force --yes --no-ansi`.
- [x] Validasi module registry.
  - Acceptance: semua module valid.
  - Verification: `php artisan module:validate --no-ansi`.

## Increment 3: Data foundation

- [x] Tambahkan migration `organization_units`.
  - Acceptance: ULID primary key, unique `code`, parent nullable, status, dan
    timestamp tersedia.
  - Verification: focused migration/model tests.
- [x] Tambahkan persistence minimum.
  - Acceptance: model/repository hanya dibuat bila dipakai oleh use case.
  - Verification: focused unit tests.
- [x] Tambahkan permission identity awal.
  - Acceptance: `organization.view` dan `organization.manage` valid.
  - Verification: focused permission identity test.

## Increment 4: Backend read/list dan create/update minimum

- [x] Tambahkan read/list unit.
  - Acceptance: actor dengan `organization.view` dapat membaca daftar unit.
  - Verification: focused feature test.
- [x] Tambahkan create/update unit.
  - Acceptance: actor dengan `organization.manage` dapat membuat dan mengubah
    unit; duplicate `code` ditolak.
  - Verification: focused feature test.
- [x] Tambahkan authorization failure coverage.
  - Acceptance: actor tanpa permission mendapat response forbidden.
  - Verification: focused feature test.

## Increment 5: Audit mutation

- [x] Tambahkan audit create/update unit.
  - Acceptance: mutation menghasilkan audit entry/event yang aman.
  - Verification: focused audit test.

## Hasil

- [x] Scope backend slice minimum selesai.
  - Perubahan: skeleton, data foundation, read/list, create/update, permission,
    dan audit minimum.
  - Verification:
    - `php artisan module:validate --no-ansi`
    - focused tests Organization
    - `php artisan starter:verify --no-ansi`
    - `git diff --check`
  - Risiko terbuka: UI dan hierarchy lanjutan tetap menunggu increment
    terpisah.

## Increment 6: UI/Inertia daftar unit organisasi

- [x] Tambahkan route dan controller Inertia untuk daftar unit.
  - Acceptance: actor dengan `organization.view` dapat membuka halaman daftar
    unit organisasi.
  - Verification: focused Inertia feature test.
- [x] Tambahkan halaman React daftar unit organisasi.
  - Acceptance: halaman menampilkan ringkasan, daftar, state kosong, dan
    pembatasan akses berbasis permission frontend sebagai UX saja.
  - Verification: `npm run build`.
- [x] Tambahkan filter/search dasar pada props halaman.
  - Acceptance: filter yang sama dengan backend list dapat diteruskan ke UI.
  - Verification: focused Inertia feature test.

## Increment 7: UI/Inertia create dan update unit organisasi

- [x] Tambahkan web mutation route untuk form Inertia.
  - Acceptance: actor dengan `organization.manage` dapat membuat dan mengubah
    unit melalui route web, sedangkan actor tanpa permission ditolak.
  - Verification: focused Inertia mutation feature test.
- [x] Tambahkan dialog create/update unit pada halaman React.
  - Acceptance: dialog memakai label aksesibel, menampilkan error validasi,
    loading state, dan menutup setelah submit berhasil.
  - Verification: `npm run build`.
- [x] Hubungkan kontrol UI dengan permission.
  - Acceptance: tombol tambah/edit hanya tampil sebagai UX untuk actor dengan
    `organization.manage`; backend tetap menjadi authority.
  - Verification: focused source/UI test dan build.

## Increment 8: Hierarchy parent unit

- [x] Kirim parent option ke halaman Inertia.
  - Acceptance: halaman menerima daftar unit aktif sebagai kandidat parent.
  - Verification: focused Inertia feature test.
- [x] Tampilkan parent unit pada daftar.
  - Acceptance: table dan card mobile menampilkan induk unit bila tersedia.
  - Verification: focused source/UI test dan `npm run build`.
- [x] Tambahkan field parent pada dialog create/update.
  - Acceptance: user dapat memilih tanpa induk atau parent unit lain; edit tidak
    menawarkan unit yang sedang diedit sebagai parent.
  - Verification: focused source/UI test, mutation feature test, dan
    `npm run build`.

## Increment 9: Archive unit organisasi aman

- [x] Tambahkan web mutation archive.
  - Acceptance: actor dengan `organization.manage` dapat mengarsipkan unit
    aktif secara non-destruktif; record tidak dihapus.
  - Verification: focused Inertia mutation feature test.
- [x] Lindungi unit parent yang masih memiliki child aktif.
  - Acceptance: unit dengan child aktif tidak dapat diarsipkan sampai child
    ditangani lebih dulu.
  - Verification: focused Inertia mutation feature test.
- [x] Tambahkan dialog archive pada UI/Inertia.
  - Acceptance: tombol archive hanya tampil sebagai UX untuk actor dengan
    `organization.manage`, dialog menjelaskan archive non-destruktif, memiliki
    loading state dan error state.
  - Verification: focused source/UI test dan `npm run build`.

## Increment 10: Refactor komponen UI unit organisasi

- [x] Pecah `Index.tsx` menjadi page container dan komponen presentational.
  - Acceptance: `Index.tsx` hanya menangani auth, state dialog, filter routing,
    dan komposisi layout; daftar, filter, summary, empty state, access denied,
    dan header action berada di `components/`.
  - Verification: focused source/UI test dan `npm run build`.
- [x] Pertahankan behavior UI yang sudah ada.
  - Acceptance: permission UX, create/update/archive dialog, parent display,
    filter, dan state kosong tetap memakai kontrak route/props yang sama.
  - Verification: focused Inertia feature test.

## Increment 11: Sidebar menu per namespace

- [x] Tambahkan menu Organization pada sidebar.
  - Acceptance: `Unit Organisasi` tampil untuk actor dengan
    `organization.view`, `organization.manage`, atau `superSystem`.
  - Verification: focused source/UI test dan `npm run build`.
- [x] Kelompokkan menu sidebar berdasarkan namespace module.
  - Acceptance: menu utama sidebar dirender sebagai group namespace seperti
    `System` dan `Organization`, bukan daftar flat.
  - Verification: focused source/UI test.
- [x] Sinkronkan Ziggy route untuk menu Organization.
  - Acceptance: frontend mendapat route `organization.units.index`,
    `organization.units.store`, `organization.units.update`, dan
    `organization.units.archive`.
  - Verification: `php artisan test --filter=ZiggyRouteTest`.

## Increment 12: Pagination UI unit organisasi

- [x] Tambahkan kontrol pagination pada daftar unit organisasi.
  - Acceptance: user dapat melihat range data, halaman aktif, total halaman,
    tombol `Sebelumnya`/`Berikutnya`, dan memilih jumlah baris per halaman.
  - Verification: focused source/UI test dan `npm run build`.
- [x] Pertahankan filter saat berpindah halaman.
  - Acceptance: pagination memakai route `organization.units.index` dengan
    search, filter status, filter type, sort, page, dan per_page yang aktif.
  - Verification: focused source/UI test.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
