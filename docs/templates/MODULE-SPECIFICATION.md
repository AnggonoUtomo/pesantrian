# Specification: {Namespace}/{Module}

## Status

Draft | Disetujui | Implemented

## Tujuan dan Scope

{Behavior yang menjadi tanggung jawab module.}

## Arsitektur

- Hexagon: `app/Modules/{Namespace}/{Module}`.
- Inbound adapter: {HTTP, console, queue, event listener, atau lainnya}.
- Use case/inbound port: {Action, Command, atau Query}.
- Outbound port: {Application/Contracts atau Domain/Contracts yang dibutuhkan}.
- Outbound adapter: {Infrastructure adapter yang mengimplementasikan port}.
- Composition root: `app/Modules/{Namespace}/{Module}/ServiceProvider.php`.

## Di Luar Scope

- {Behavior yang tidak dimiliki module.}

## Contract

- Input: {DTO/request/command}.
- Output: {DTO/response/event}.
- Failure: {error, exception, validation, atau authorization failure publik}.

## Data

- Entity/table: {entity, tabel, dan ownership migration}.
- Identifier: ULID primary identifier untuk table aplikasi.
- Historical traceability: {snapshot/status/periode yang harus dipertahankan}.

## Authorization dan Audit

- Permission: `{module.permission}`.
- Policy/resource rule: {domain eligibility atau business rule}.
- Audit/event: {event yang dicatat atau didengar}.

## UI

- Page: `resources/js/modules/{module-slug}/pages/{Page}.tsx`.
- Routing: Ziggy named routes.
- State: {loading, empty, error, success, authorization state}.
- Accessibility: {keyboard, label, focus, atau kebutuhan khusus}.

## Dependency

- {Public dependency dan alasan dependency tersebut diperlukan.}

## Acceptance Criteria

- [ ] {Behavior positif yang dapat diverifikasi.}
- [ ] {Behavior negatif atau failure handling yang dapat diverifikasi.}

## Risiko Terbuka

- {Risiko yang belum ditutup dan owner keputusannya.}
