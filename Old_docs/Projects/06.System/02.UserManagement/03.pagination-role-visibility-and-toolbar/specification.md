# Specification: Pagination, Role Efektif, dan Toolbar User

## Owner dan Contract

- Owner: `System/UserManagement`.
- Authorization: `user.view` tetap menjadi security boundary.
- Route query additive:

```text
GET /system/users?search=&status=&role=&archive=&page=&per_page=
```

`page` adalah integer minimal `1`. `per_page` hanya menerima `5`, `10`, `25`,
atau `50`; default `25`. Semua filter lama dipertahankan ketika halaman atau
jumlah baris berubah.

## Task 01: Pagination

- Repository mengembalikan typed paginated read model berisi `data`, `meta`,
  dan link navigation; bukan array tanpa metadata.
- Query Eloquent memakai pagination server-side dan mengurutkan nama secara
  stabil.
- Controller mengirim data serta meta ke Inertia.
- Tabel memiliki previous/next, nomor halaman, total hasil, dan select jumlah
  baris. Tombol mengikuti loading state global.
- Nilai page/per_page tidak valid ditolak request.

## Task 02: Role efektif

- `UserData` membawa daftar role efektif bertipe.
- Repository mencegah N+1 dengan eager loading relasi role.
- Tabel memakai badge ringkas; modal detail memperlihatkan seluruh role.

## Task 03: Toolbar dan shortcut

- Toolbar menyatukan filter, jumlah baris, total, serta navigation pagination.
- `/` tetap fokus search. Shortcut baru hanya boleh fokus filter atau pindah
  halaman dan tidak aktif ketika user sedang mengetik.
- `Ctrl/Cmd+K` tetap untuk command palette global.

## Acceptance

- [x] Filter lama tetap berlaku pada semua halaman.
  - Evidence: focused UserManagement presentation test memverifikasi query
    search/status/role/archive dipertahankan saat page dan per_page berubah.
- [x] Pagination tidak memuat seluruh daftar ke frontend.
  - Evidence: `UserListFilter` dan repository memakai pagination server-side;
    props Inertia hanya memuat data halaman aktif serta meta pagination.
- [x] `per_page` dan `page` invalid ditolak.
  - Evidence: request test menolak `page=0` dan nilai per_page di luar opsi
    runtime SystemSetting.
- [x] Role efektif tidak menghasilkan dependency private lintas module.
  - Evidence: UserManagement memakai public AccessControl capability; module
    validation dan architecture test lulus tanpa import private.
- [x] Toolbar responsif, accessible, memakai Ziggy, toast/loading baseline, dan
  lolos browser test.
  - Evidence: browser `/system/users` memverifikasi toolbar pagination, sort
    waktu input, loading filter, dan console tanpa error/warning.
