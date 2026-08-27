# ADR-001: DDD-lite Modular Monolith dengan Hexagonal Architecture

## Status

Accepted

## Tanggal

2026-08-27

## Konteks

SakaSantri adalah platform operasional pesantren untuk satu yayasan dengan
banyak unit. Sistem perlu mengelola banyak capability seperti access control,
organisasi, santri, wali, SDM, akademik, asrama, keuangan, dokumen, komunikasi,
pelaporan, dan audit.

Kebutuhan ini memerlukan batas module yang jelas, tetapi belum membutuhkan
microservices. Pengembangan juga harus tetap produktif untuk incremental
release.

## Keputusan

SakaSantri memakai DDD-lite Modular Monolith dengan Hexagonal Architecture.
Setiap module berada di `app/Modules/<Namespace>/<Module>/` dan menjadi bounded
capability dengan ownership data, rule, permission, route, migration, dan
ServiceProvider sendiri.

Arah dependency:

```text
Presentation -> Application -> Domain
Infrastructure -> Application -> Domain
```

Public boundary lintas module memakai contract, DTO, query/service eksplisit,
atau event yang memiliki consumer nyata. Composition root berada pada
`ServiceProvider.php` module.

## Alternatif

- CRUD monolith datar: ditolak karena ownership data dan rule lintas area akan
  cepat kabur.
- Microservices: ditolak untuk release awal karena menambah biaya operasional,
  deployment, observability, dan distributed consistency terlalu dini.
- Tactical DDD penuh: ditolak sebagai default karena dapat menciptakan ceremony
  dan abstraction sebelum rule domain cukup matang.

## Konsekuensi

- Module boundary dan dependency lebih mudah diaudit.
- Business rule dapat diuji tanpa adapter HTTP atau persistence.
- Direct mutation lintas module dilarang; perlu contract/event eksplisit.
- Tim harus disiplin mencegah folder kosong, generic repository, dan CQRS
  ceremony pada CRUD sederhana.

## Verifikasi

- Domain tidak bergantung pada framework atau layer luar.
- Application tidak mengimpor Infrastructure.
- Controller tidak menjalankan persistence atau business mutation langsung.
- Import lintas module hanya memakai public boundary yang disetujui.
- `php artisan module:validate --no-ansi` dijalankan saat module berubah.

## Referensi

- [`../SakaSantri_Architecture_Baseline_v0.1-r1-ID.md`](../SakaSantri_Architecture_Baseline_v0.1-r1-ID.md)
- [`../ARCHITECTURE.md`](../ARCHITECTURE.md)
- [`../FOLDER-STRUCTURE.md`](../FOLDER-STRUCTURE.md)
