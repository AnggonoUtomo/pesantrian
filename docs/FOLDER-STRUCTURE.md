# Struktur Folder Canonical

Sesuaikan struktur ini dengan source code dan generator project sebelum baseline
diaktifkan. Folder opsional hanya dibuat ketika memiliki isi dan concern nyata.

## Backend module

```text
app/Modules/{Domain}/{Module}/
|-- Application/
|   |-- Actions/
|   |-- Commands/             # opsional
|   |-- Contracts/
|   |-- DTO/
|   |-- Events/               # opsional
|   |-- Exceptions/           # opsional
|   |-- Listeners/            # opsional
|   |-- Queries/
|   `-- Services/             # opsional
|-- Domain/                   # bila ada aturan bisnis murni
|   |-- Contracts/            # opsional
|   |-- Entities/
|   |-- Events/
|   |-- Exceptions/
|   |-- Services/
|   `-- ValueObjects/
|-- Infrastructure/
|   |-- Persistence/
|   |   |-- Models/
|   |   `-- Repositories/
|   |-- External/
|   `-- {Capability}/
|-- Presentation/
|   |-- Console/Commands/
|   |-- Controllers/
|   |-- Middleware/
|   |-- Policies/
|   |-- Requests/
|   |-- Resources/
|   `-- Support/
|-- Database/
|   |-- Factories/
|   |-- Migrations/
|   `-- Seeders/
|-- Routes/
|-- README.md
`-- [manifest, config, permission, dan composition root project]
```

Catat nama file root module yang benar untuk project target:

| Artefak             | Path project                        |
| ------------------- | ----------------------------------- |
| Manifest module     | `[path atau tidak digunakan]`       |
| Runtime config      | `[path atau tidak digunakan]`       |
| Permission identity | `[path atau tidak digunakan]`       |
| Composition root    | `[path ServiceProvider/alternatif]` |

## Frontend module

```text
[frontend-root]/{Domain}/{Module}/
|-- components/
|-- pages/
|-- hooks/                    # opsional
|-- lib/                      # opsional
`-- types.ts                  # opsional
```

## Test executable

Isi berdasarkan konfigurasi test runner, bukan asumsi:

```text
[path unit test]
[path feature/integration test]
[path frontend test]
[path browser test]
```

Test yang dibuat generator harus berada pada path yang benar-benar ditemukan
runner. Jika tidak selaras, catat sebagai risiko dan perbaiki melalui work item
terpisah.

## Dokumentasi pekerjaan

```text
docs/modules/{Domain}/{Module}/
|-- README.md
|-- specification.md
|-- plan.md
|-- tasks.md
|-- decisions/
`-- work-items/{nama-pekerjaan}/
    |-- README.md
    |-- plan.md
    `-- tasks.md
```

Pekerjaan lintas module memakai `docs/work-items/{nama-pekerjaan}`.

## Aturan pembuatan

- Inventarisasi module dan generator sebelum mengubah struktur.
- Tinjau dry-run generator bila tersedia.
- Jangan menambahkan `.gitkeep`, placeholder class, atau abstraction tanpa
  behavior hanya untuk mempertahankan folder.
- Jika concern tidak cocok dengan struktur, buat ADR atau minta arahan sebelum
  coding.
