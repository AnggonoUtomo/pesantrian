# Specification Phase 3 Module Generator

## Scope

### In Scope

- Command `module:make` dengan output human-readable dan `--json`.
- Profile generator versioned dan deterministic.
- Validasi nama, domain, namespace, path, provider, dan permission key.
- Dry-run tanpa perubahan file.
- Conflict detection sebelum side effect.
- Staging directory dan atomic promotion.
- Cleanup staging saat gagal.
- Generate `module.json`, `module.php`, provider, `permissions.php`, README,
  test, dan struktur DDD-lite dasar.
- Test positive, negative, conflict, dry-run, dan failure cleanup.

### Out of Scope

- Business logic AccessControl atau UserManagement.
- Migration business module.
- Mengubah module existing tanpa mode extension yang disetujui.
- Eksekusi `module.php` saat proses generate.
- Wayfinder atau Laravel Boost.

## Existing Capability Contract

Generator wajib memakai `ModuleManifest`, `PermissionIdentity`, dan
`ModuleRegistry` dari `packages/StarterKit`. Generator tidak boleh membuat
validasi identity kedua yang berbeda dari contract tersebut.

## Requirements

| ID | Requirement | Priority | Acceptance |
|---|---|---|---|
| REQ-001 | Input module memiliki format yang jelas | Must | Nama, domain, profile, dan opsi invalid ditolak sebelum side effect |
| REQ-002 | Output memakai profile versioned | Must | Profile yang dipakai tercatat dan menghasilkan struktur deterministik |
| REQ-003 | Generator mendukung dry-run | Must | Dry-run hanya menampilkan rencana tanpa membuat file |
| REQ-004 | Konflik dideteksi lebih awal | Must | Existing path, file, identity, atau permission key menghasilkan diagnostic |
| REQ-005 | Promosi output aman | Must | Output dibuat di staging lalu dipromosikan secara atomic |
| REQ-006 | Kegagalan tidak meninggalkan staging | Must | Staging dibersihkan setelah failure |
| REQ-007 | Output ter-sanitasi | Must | Tidak ada secret, credential, forbidden dependency, atau payload sensitif |
| REQ-008 | Output dapat diverifikasi | Must | JSON memiliki code, data, diagnostics, dan exit code stabil |
| REQ-009 | Generator tidak overwrite default | Must | File existing ditolak kecuali mode extension disetujui |
| REQ-010 | Extension dan overwrite eksplisit | Must | Extension hanya membuat file baru; overwrite wajib memakai guard lengkap dan backup |
| REQ-011 | Preflight compatibility | Must | Namespace, manifest, profile, struktur, dan registry diperiksa sebelum mutation |
| REQ-012 | Overwrite terbatas | Must | Hanya file dalam plan profile yang boleh ditimpa; business logic dan file custom tidak di-merge |

## Module Boundary

- Owner: `packages/StarterKit` untuk engine; module owner untuk generated module.
- Public contract: generator input DTO, plan DTO, result DTO, `ModuleManifest`,
  `PermissionIdentity`.
- Data ownership: generator hanya membuat struktur; module memiliki file hasil.
- Dependencies: Laravel Console, filesystem abstraction, existing registry,
  dan profile/stub engine.

## Golden Output Structure `default-v1`

Profile `default-v1` wajib menghasilkan struktur canonical berikut. Empat
folder `Application`, `Domain`, `Infrastructure`, dan `Presentation` bukan
output final yang berdiri sendiri; masing-masing harus memiliki subfolder yang
ditentukan baseline folder structure.

```text
app/Modules/{Domain}/{Module}/
├── Application/
│   ├── Actions/
│   ├── DTO/
│   ├── Queries/
│   ├── Services/
│   └── Contracts/
├── Domain/
│   ├── Contracts/
│   ├── Entities/
│   ├── Events/
│   ├── Exceptions/
│   ├── Services/
│   └── ValueObjects/
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/
│   │   └── Repositories/
│   ├── Observers/
│   ├── Providers/
│   └── External/
├── Presentation/
│   ├── Controllers/
│   ├── Policies/
│   ├── Requests/
│   └── Resources/
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   └── Seeders/
├── Routes/
│   ├── api.php
│   ├── web.php
│   ├── console.php
│   └── channels.php
├── Tests/
│   ├── Feature/
│   ├── Integration/
│   └── Unit/
├── module.json
├── module.php
├── permissions.php
├── ServiceProvider.php
└── README.md
```

Folder dan file tersebut menjadi golden structure yang harus diuji melalui
structure test. Generator boleh menambahkan file implementasi setelah profile
menentukan kebutuhan, tetapi tidak boleh menghilangkan boundary canonical.

Baseline yang menjadi acuan: `docs/03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md`.

## Keputusan yang Sudah Disetujui

- Profile pertama: `default-v1`.
- Mode extension dan overwrite diaktifkan melalui ADR-0002 dengan guard
  `--extension`, `--overwrite`, `--force`, dan `--yes`.
- `--dry-run --json` wajib dijalankan dan ditinjau sebelum overwrite.
- Promotion memakai staging directory ULID, validasi hasil staging, rename
  atomic ke target, dan cleanup saat gagal.

## Open Decisions

| ID | Question | Impact | Owner | Status |
|---|---|---|---|---|
| OD-001 | Apakah profile berikutnya membutuhkan perubahan contract? | Menentukan migration profile | Tech Lead | Open |
| OD-002 | Kapan mode extension boleh diaktifkan? | Mempengaruhi authorization dan conflict | Tech Lead | Open |
| OD-003 | Apakah deployment non-Windows membutuhkan adapter promotion khusus? | Mempengaruhi portability | Tech Lead | Open |
