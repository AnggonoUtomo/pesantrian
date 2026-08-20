# ADR-0002: Ingestion Audit Synchronous melalui Integration Event

## Status

`Accepted` pada 6 Agustus 2026.

## Context

AuditLog membutuhkan aktivitas AccessControl dan UserManagement. Jika producer
mengimpor repository/model AuditLog, dependency akan terbalik atau melingkar.
Jika ingestion langsung memakai queue, mutation dapat terlihat berhasil sebelum
audit tersimpan dan failure worker dapat menghilangkan evidence keamanan.

Existing event impersonation adalah Domain Event internal dan belum memiliki
version, event ID, atau correlation ID. Event tersebut tidak boleh langsung
dianggap Integration Event.

## Decision

- AccessControl menerbitkan `AccessControlActivityOccurred`.
- UserManagement menerbitkan `UserManagementActivityOccurred`.
- Event berada pada public application communication boundary producer.
- Envelope memiliki `event_name`, `version`, `event_id`, `occurred_at`,
  `correlation_id`, actor, action, subject, reason, dan metadata aman.
- AuditLog bergantung pada public event producer dan mendaftarkan listener.
- Listener berjalan synchronous dan memanggil `RecordAuditEntry`.
- Failure pencatatan dipropagasikan untuk flow sensitif.
- `event_id` unique membuat duplicate delivery idempotent.
- Event impersonation lama tetap Domain Event internal untuk compatibility;
  producer juga menerbitkan Integration Event baru yang aman.
- Queue/Job tidak digunakan pada increment awal.

## Alternatives Considered

### Producer memanggil AuditRecorder langsung

Ditolak untuk AccessControl karena AuditLog juga membutuhkan authorization
AccessControl. Arah dependency dua sisi berisiko menjadi circular dependency.

### AuditLog mengimpor model atau repository producer

Ditolak karena melanggar private module boundary.

### Queued listener sejak awal

Ditolak karena belum ada dead-letter policy, retry ownership, dan kebutuhan
latency yang membenarkan eventual audit. Audit keamanan awal dipilih fail-closed.

## Consequences

### Positif

- producer tidak bergantung pada AuditLog;
- event lintas module memiliki contract versioned;
- mutation tidak sukses diam-diam saat audit gagal;
- duplicate event aman.

### Batasan

- latency pencatatan masuk ke request mutation;
- failure storage audit menggagalkan flow sensitif;
- perubahan ke queue nanti membutuhkan ADR, retry, idempotency, monitoring,
  dan dead-letter handling.

## Verification

- event envelope test;
- consumer integration test;
- duplicate event test;
- redaction test;
- failure propagation test;
- architecture test untuk dependency lintas module.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menerima integration event synchronous dan idempotency event ID |
