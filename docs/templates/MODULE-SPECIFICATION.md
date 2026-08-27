# Specification: [Nama Module]

## Status

Draft | Disetujui | Implemented

## Tujuan dan scope

[Behavior yang menjadi tanggung jawab module.]

## Arsitektur

- Hexagon: `[Domain/Module]`.
- Inbound adapter: [HTTP, console, queue, atau lainnya].
- Use case/inbound port: [Action, Command, atau Query].
- Outbound port: [Application/Contracts yang dibutuhkan].
- Outbound adapter: [Infrastructure adapter yang mengimplementasikan port].
- Composition root: `[Domain/Module]/ServiceProvider.php`.

## Di luar scope

- [Behavior yang tidak dimiliki module.]

## Contract

- Input: [DTO/request/command].
- Output: [DTO/response/event].
- Failure: [error atau exception publik].

## Data

- [Entity, tabel, ownership migration, dan identifier.]

## Authorization dan audit

- [Permission, policy, resource rule, dan event yang diaudit.]

## UI

- [Page, mekanisme route, state loading/empty/error, dan accessibility.]

## Dependency

- [Public dependency dan alasan.]

## Acceptance criteria

- [ ] [Behavior positif.]
- [ ] [Behavior negatif atau failure handling.]

## Risiko terbuka

- [Risiko yang belum ditutup dan owner keputusannya.]
