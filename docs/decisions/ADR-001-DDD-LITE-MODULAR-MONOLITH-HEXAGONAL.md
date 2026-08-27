# ADR-001: DDD-lite Modular Monolith dengan Hexagonal Architecture

## Status

Accepted

## Tanggal

YYYY-MM-DD

## Konteks

Project memerlukan batas module dan arah dependency yang jelas tanpa kompleksitas
tactical DDD penuh.

## Keputusan

Setiap module menjadi satu hexagon dengan Domain, Application, Infrastructure,
dan Presentation sesuai kebutuhan nyata. Arah dependency mengikuti
`Presentation -> Application -> Domain` dan
`Infrastructure -> Application -> Domain`.

Public boundary lintas module menggunakan contract, DTO, atau event pada
Application. Composition root menghubungkan port dengan adapter.

## Konsekuensi

- Business rule dapat diuji tanpa adapter HTTP atau persistence.
- Implementasi framework dapat diganti melalui port.
- Tim harus mencegah abstraction dan folder tanpa kebutuhan.

## Verifikasi

- Domain tidak bergantung pada framework atau layer luar.
- Application tidak mengimpor Infrastructure.
- Controller tidak menjalankan persistence atau business mutation langsung.
- Import lintas module hanya memakai public boundary yang disetujui.
