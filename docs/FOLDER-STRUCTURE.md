# Struktur Folder Canonical

Dokumen ini menentukan lokasi class dan artefak. Struktur bersifat canonical,
tetapi folder opsional hanya dibuat ketika memiliki isi dan concern nyata.

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
|-- Domain/                   # hanya bila ada aturan bisnis murni
|   |-- Contracts/            # opsional, contract bahasa domain
|   |-- Entities/
|   |-- Events/
|   |-- Exceptions/
|   |-- Services/
|   `-- ValueObjects/
|-- Infrastructure/
|   |-- Persistence/
|   |   |-- Models/
|   |   `-- Repositories/
|   |-- External/             # adapter layanan eksternal
|   |-- Observers/
|   |-- Providers/
|   `-- {Capability}/         # contoh: Runtime, Events, Authentication
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
|   |-- api.php
|   |-- channels.php
|   |-- console.php
|   `-- web.php
|-- README.md
|-- ServiceProvider.php
|-- module.json
|-- module.php
`-- permissions.php
```

Folder bertanda opsional dan subfolder capability tidak dibuat tanpa kebutuhan.
Gunakan nama capability yang menjelaskan adapter, seperti `Runtime`,
`Authentication`, `Monitoring`, atau `Events`; jangan gunakan nama umum yang
tidak menjelaskan tanggung jawab.

Generator `default-v1` merencanakan seluruh slot canonical tersebut dan dapat
membuat direktori kosong pada filesystem. Direktori kosong bukan kewajiban
arsitektur dan tidak boleh diisi `.gitkeep`, placeholder class, atau abstraction
tanpa behavior hanya agar ikut tersimpan di Git.

## Fungsi file root module

| File                  | Fungsi                                                                      |
| --------------------- | --------------------------------------------------------------------------- |
| `module.json`         | Identity, path, provider, dependency, dan source deklaratif module          |
| `module.php`          | Konfigurasi runtime milik module                                            |
| `permissions.php`     | Permission identity yang dimiliki module                                    |
| `ServiceProvider.php` | Composition root, binding port-adapter, route, migration, event, dan policy |
| `README.md`           | Tujuan, boundary, public API, dependency, operasi, dan verifikasi module    |

`module.json` bukan tempat konfigurasi runtime. `module.php` bukan manifest
kedua. `ServiceProvider.php` tidak boleh menjadi tempat business rule.

## Frontend module

```text
resources/js/pages/{Domain}/{Module}/
|-- components/
|-- pages/
|-- hooks/                    # opsional
|-- lib/                      # opsional, khusus module
`-- types.ts                  # bila module memiliki tipe frontend
```

Komponen reusable lintas module berada di `resources/js/components`. Utility
global berada di `resources/js/lib`. Jangan memindahkan logic authorization
backend ke frontend.

## Test executable saat ini

PHPUnit saat ini hanya menemukan test pada:

```text
tests/Feature/
tests/Unit/
```

Test browser berada di `tests/Browser` dan test frontend berada di
`tests/Frontend`. Gunakan nama test yang menyebut module atau capability agar
ownership tetap terlihat.

Generator `default-v1` masih merencanakan `Tests/Feature`, `Tests/Integration`,
dan `Tests/Unit` di dalam module. Lokasi tersebut belum dibaca oleh konfigurasi
PHPUnit saat ini. Karena itu, test module-local tidak boleh menjadi satu-satunya
test sampai konfigurasi runner dan generator diselaraskan melalui work item
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

Pekerjaan lintas module memakai `docs/work-items/{nama-pekerjaan}`. Folder kerja
memakai `kebab-case`; Domain dan Module mengikuti nama pada kode.

## Aturan pembuatan

- Jalankan inventory dan `module:inspect` sebelum membuat atau mengubah module.
- Gunakan generator untuk skeleton bila tersedia; tinjau dry-run sebelum menulis.
- Jangan menambahkan `.gitkeep` atau placeholder hanya untuk mempertahankan
  folder kosong.
- Jangan membuat class di luar lokasi canonical untuk menghindari membuat folder
  yang semestinya.
- Jika concern baru tidak cocok dengan struktur ini, buat ADR atau minta arahan
  sebelum coding.
