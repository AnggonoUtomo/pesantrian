# Dokumentasi Module

## Lokasi

Setiap module yang baru dibuat atau mulai memperoleh pekerjaan signifikan
memakai folder:

```text
modules/{Domain}/{Module}/
|-- README.md
|-- specification.md
|-- plan.md
|-- tasks.md
|-- decisions/
`-- work-items/
```

Nama Domain dan Module mengikuti kode, misalnya `System/UserManagement`.
Folder work item memakai `kebab-case`, misalnya
`work-items/invitation-delivery/`.

Gunakan template pada `../templates/`. Tidak perlu membuat folder kosong untuk
module atau work item yang belum akan dikerjakan.

Struktur source module wajib mengikuti
[`../ARCHITECTURE.md`](../ARCHITECTURE.md) dan
[`../FOLDER-STRUCTURE.md`](../FOLDER-STRUCTURE.md).
