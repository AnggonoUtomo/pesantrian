# ADR-006: DDD-lite Modular Monolith dengan Hexagonal Architecture

## Status

Accepted

## Tanggal

2026-08-20

## Konteks

Starter13 memerlukan batas module yang jelas tanpa kompleksitas tactical DDD
penuh. Tanpa arah dependency dan lokasi canonical, business rule mudah bocor ke
controller, persistence, atau module lain.

## Keputusan

Setiap module menjadi satu hexagon dengan layer Domain, Application,
Infrastructure, dan Presentation. Arah dependency mengikuti
`Presentation -> Application -> Domain` dan
`Infrastructure -> Application -> Domain`.

Public boundary lintas module dibatasi pada `Application/Contracts`,
`Application/DTO`, dan `Application/Events`. Binding port ke adapter dilakukan
oleh `ServiceProvider.php` sebagai composition root.

Folder opsional hanya dibuat ketika ada concern nyata. Lokasi class mengikuti
[`../FOLDER-STRUCTURE.md`](../FOLDER-STRUCTURE.md).

## Konsekuensi

- Business rule dapat diuji tanpa adapter HTTP atau persistence.
- Implementasi framework dan package dapat diganti melalui port.
- Cross-module dependency lebih mudah divalidasi.
- Pembuatan port dan layer memerlukan disiplin agar tidak menjadi abstraction
  tanpa kebutuhan.

## Verifikasi

- Application tidak mengimpor Infrastructure.
- Controller tidak menjalankan persistence atau business mutation langsung.
- Import lintas module hanya memakai public boundary yang disetujui.
- Struktur module mengikuti lokasi canonical dan tidak memiliki placeholder.
