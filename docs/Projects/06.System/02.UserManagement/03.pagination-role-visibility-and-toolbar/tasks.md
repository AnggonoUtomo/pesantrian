# Tasks: Pagination, Role Efektif, dan Toolbar User

## Task 00 — Dokumentasi dan preflight

- [x] Scope dan urutan ditetapkan.
  - Kondisi awal: filter selesai, tetapi query mengembalikan seluruh user tanpa
    metadata pagination; role efektif belum ditampilkan.
  - Perubahan: membuat folder increment 03 dengan specification, plan, task,
    dan log.
  - Alasan: poin 1–3 ADR-0004 saling bergantung dan harus diverifikasi satu
    per satu.
  - Evidence: ADR-0004 serta source filter/read contract ditinjau.

## Task 01 — Pagination server-side

- [x] Pagination selesai.
  - Kondisi awal: `UserRepository::list()` mengembalikan `list<UserData>`.
  - Perubahan: mengganti read contract menjadi `PaginatedUserData`, menambah
    `page`/`per_page` pada request dan filter DTO, menerapkan Eloquent
    `paginate()`, metadata Inertia, serta kontrol jumlah baris dan previous/next
    pada `UserTable`.
  - Acceptance: query/page/per_page typed, validasi, meta, UI navigation, dan
    filter persist.
  - Evidence: focused feature test membuktikan halaman kedua dengan
    `per_page=5` dan filter; invalid `page=0`/`per_page=15` ditolak. Browser
    membuka `?per_page=5`, menampilkan 5 user per halaman, metadata 59 user
    development, kontrol 5 baris, dan console bersih. Lint dan type check
    lulus.

## Task 02 — Role efektif

- [ ] Role efektif selesai.
  - Kondisi awal: tabel dan detail belum membawa daftar role user.
  - Perubahan: belum dilakukan.
  - Evidence: menunggu Task 01.

## Task 03 — Toolbar dan shortcut

- [ ] Toolbar dan shortcut selesai.
  - Kondisi awal: toolbar belum mengenal pagination.
  - Perubahan: belum dilakukan.
  - Evidence: menunggu Task 02.
