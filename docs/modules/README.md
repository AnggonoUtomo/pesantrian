# Dokumentasi Module

Folder ini berisi dokumentasi module yang sudah mulai dikerjakan atau memiliki
keputusan signifikan.

## Lokasi

Setiap module memakai pola:

```text
docs/modules/<Namespace>/<Module>/
|-- README.md
|-- specification.md
|-- plan.md
|-- tasks.md
|-- decisions/
`-- work-items/
```

Nama `Namespace` dan `Module` mengikuti source code, misalnya
`StudentAffairs/Student` atau `Finance/StudentFinance`. Folder work item memakai
`kebab-case`, misalnya `work-items/register-new-student/`.

Gunakan template pada `../templates/`. Tidak perlu membuat folder kosong untuk
module atau work item yang belum akan dikerjakan.

## Isi Minimum

- Tanggung jawab module dan boundary data.
- Public contract, DTO, event, route, permission, atau command yang diekspos.
- Dependency nyata dan alasan dependency tersebut.
- Migration/table ownership dan strategi ULID.
- Authorization, audit, dan risiko runtime penting.
- Verifikasi utama untuk module.

Struktur source module wajib mengikuti
[`../ARCHITECTURE.md`](../ARCHITECTURE.md) dan
[`../FOLDER-STRUCTURE.md`](../FOLDER-STRUCTURE.md).
