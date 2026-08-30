# Struktur Folder Canonical

Struktur ini mengikuti baseline SakaSantri. Folder opsional hanya dibuat ketika
memiliki isi dan concern nyata.

## Backend Module

```text
app/Modules/<Namespace>/<Module>/
|-- module.json
|-- module.php
|-- permissions.php
|-- ServiceProvider.php
|-- README.md
|-- Application/
|   |-- Actions/
|   |-- DTO/
|   `-- Services/
|-- Domain/
|   |-- Contracts/
|   |-- Entities/
|   |-- Events/
|   |-- Services/
|   `-- ValueObjects/
|-- Infrastructure/
|   |-- Models/
|   |-- Repositories/
|   |-- Observer/
|   `-- Providers/
|-- Presentation/
|   |-- Controllers/
|   |-- Requests/
|   `-- Resources/
|-- Database/
|   |-- Migrations/
|   |-- Seeders/
|   `-- Factories/
`-- Routes/
    |-- web.php
    |-- api.php
    |-- console.php
    `-- channels.php
```

Folder berikut optional/on-demand:

```text
Application/Commands/
Application/Queries/
Domain/Policies/
Domain/Specifications/
Domain/Exceptions/
Infrastructure/Adapters/
Infrastructure/Integrations/
```

Jangan menambahkan `.gitkeep`, placeholder class, atau abstraction tanpa
behavior hanya untuk mempertahankan folder.

## Artefak Root Module

| Artefak | Path |
| --- | --- |
| Manifest module | `app/Modules/<Namespace>/<Module>/module.json` |
| Runtime config | `app/Modules/<Namespace>/<Module>/module.php` |
| Permission identity | `app/Modules/<Namespace>/<Module>/permissions.php` |
| Composition root | `app/Modules/<Namespace>/<Module>/ServiceProvider.php` |
| Dokumentasi module | `app/Modules/<Namespace>/<Module>/README.md` |

## Frontend Page Module

```text
resources/js/
|-- app.tsx
|-- components/
|   |-- ui/
|   `-- shared/
|-- layouts/
|-- hooks/
|-- lib/
|-- types/
`-- pages/
    |-- System/
    |-- Organization/
    |-- Academic/
    |-- HumanResource/
    |-- auth/
    |-- settings/
    `-- errors/
```

Per module frontend:

```text
resources/js/pages/<Namespace>/<Module>/
|-- pages/
|-- components/
|-- hooks/
|-- types/
`-- schemas/
```

shadcn/ui berada di `resources/js/components/ui`. Komponen business-specific
berada di `resources/js/pages/<Namespace>/<Module>/components/` atau
`components/shared` jika benar-benar lintas domain.

## Tests

```text
tests/
`-- Modules/
    |-- Student/
    |-- Academic/
    |-- StudentFinance/
    `-- Dormitory/
```

Gunakan test yang paling spesifik terlebih dahulu. Jika generator atau test
runner project belum selaras dengan struktur ini, catat sebagai gap dan
selesaikan melalui work item terpisah.

## Dokumentasi Pekerjaan

```text
docs/modules/<Namespace>/<Module>/
|-- README.md
|-- specification.md
|-- plan.md
|-- tasks.md
|-- decisions/
`-- work-items/<nama-pekerjaan>/
    |-- README.md
    |-- plan.md
    `-- tasks.md
```

Pekerjaan lintas module memakai `docs/work-items/<nama-pekerjaan>/`.

## Aturan Pembuatan

- Inventarisasi module dan generator sebelum mengubah struktur.
- Gunakan module generator project ketika tersedia.
- Generator minimum harus menerima namespace dan module, misalnya
  `php artisan module:make Pesantrian Santri` untuk module baru, atau nama
  teknis existing seperti `php artisan module:make Academic AcademicPeriod`
  ketika menjaga compatibility source.
- Generator tidak membuat `Adapters`, `Integrations`, `Commands`, atau
  `Queries` secara default.
- Migration module berada di `Database/Migrations`.
- Table aplikasi menggunakan ULID sebagai primary identifier.
