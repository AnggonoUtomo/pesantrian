# ADR-0001: Boundary Module Generator

## Status

Diterima.

## Context

Phase 2 sudah menyediakan manifest, permission identity, registry, dan command
read-only. Phase 3 membutuhkan generator yang membuat file module tanpa
merusak file existing atau meninggalkan output setengah jadi.

## Decision

Generator akan dipisah menjadi beberapa boundary:

1. Input contract dan validator.
2. Profile/stub resolver.
3. Plan dan conflict detector.
4. Staging writer.
5. Atomic promotion dan cleanup.
6. Console adapter.

Generator memakai public contract Phase 2. Ia tidak membuat duplicate validator
identity dan tidak menjalankan `module.php` saat generate.

Keputusan tambahan yang disetujui:

1. Profile pertama bernama `default-v1`.
2. Phase 3 awal hanya mendukung pembuatan module baru. Mode extension belum
   diaktifkan.
3. Generator memakai staging directory dengan ULID, memvalidasi seluruh output
   di staging, lalu memindahkan directory ke target melalui rename atomic. Jika
   proses gagal, staging dibersihkan. Target existing ditolak dan tidak
   dioverwrite.

## Consequences

- Dry-run dapat diuji tanpa filesystem mutation.
- Conflict dapat dilaporkan sebelum side effect.
- Staging memudahkan cleanup dan rollback.
- Profile versioning menambah disiplin perubahan output.
- Implementasi lebih banyak daripada generator yang langsung menulis file.

## Alternatives Rejected

- Menulis langsung ke `app/Modules`: berisiko meninggalkan output parsial.
- Overwrite default: dapat merusak pekerjaan developer.
- Menaruh business logic di generator: melanggar boundary framework.

## Open Decision

Nama profile pertama, mode extension, dan strategi rename atomic lintas Windows
sudah disetujui sebelum INC-002 dan INC-005. Keputusan baru yang mengubah
boundary harus dicatat melalui ADR tambahan.
