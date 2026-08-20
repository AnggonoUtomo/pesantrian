# Alur Backend AuditLog

## 1. Bootstrap Module

Laravel mendaftarkan `AuditLog\ServiceProvider` melalui
`bootstrap/providers.php`.

```text
bootstrap/providers.php
    -> AuditLog\ServiceProvider
    -> bind AuditLogRepository ke EloquentAuditLogRepository
    -> bind AuditRecorder ke RecordAuditEntry
    -> daftarkan MetadataRedactor
    -> daftarkan listener Integration Event
    -> boot migration, route, dan policy
```

Provider hanya melakukan wiring. Aturan sanitasi, authorization, query, dan
persistence tetap berada pada layer pemiliknya.

## 2. Identity dan Konfigurasi

File identity utama:

- `module.json` menyatakan module `System/AuditLog` serta dependency
  `System/AccessControl` dan `System/UserManagement`;
- `permissions.php` memiliki permission `audit_log.view`;
- `module.php` menyimpan retention, allowlist metadata, pola data sensitif, dan
  batas ukuran metadata.

Konfigurasi tersebut dibaca oleh runtime module dan tidak diduplikasikan di
controller atau frontend.

## 3. Aktivitas dari Producer

AccessControl dan UserManagement memiliki publisher publik masing-masing.
Application Action producer memanggil publisher setelah mutation berhasil,
tetapi masih berada di dalam transaksi database yang sama.

```text
Application Action producer
    -> mulai transaksi database
    -> jalankan mutation
    -> AccessControlActivityPublisher atau UserManagementActivityPublisher
    -> dispatch Integration Event versi 1
    -> listener AuditLog
    -> simpan record audit
    -> commit transaksi
```

Jika publisher, listener, sanitasi, atau persistence gagal, transaksi dilempar
kembali dan mutation producer tidak di-commit. Ini adalah kontrak fail-closed.

## 4. Integration Event

Event publik yang dikonsumsi:

- `AccessControlActivityOccurred`;
- `UserManagementActivityOccurred`.

Setiap event membawa identity minimal seperti `eventId`, `correlationId`, actor,
action, subject, reason, metadata, dan `version`. Listener
`RecordAccessControlActivity` atau `RecordUserManagementActivity` menolak nama
event dan versi yang tidak didukung sebelum membuat `AuditEntryData`.

Integration Event digunakan karena fakta berasal dari boundary module lain.
AuditLog tidak mengimpor model, repository, policy, atau service private milik
producer.

## 5. Mencatat Audit

Public contract `AuditRecorder` diimplementasikan oleh `RecordAuditEntry`.

```text
listener
    -> AuditRecorder::record(AuditEntryData)
    -> RecordAuditEntry
    -> rapikan action, module, subject, reason, dan correlation ID
    -> MetadataRedactor::sanitize(metadata)
    -> AuditLogRepository::record(data)
    -> EloquentAuditLogRepository
    -> audit_logs
```

`MetadataRedactor` menerapkan allowlist key, menyamarkan key sensitif, membatasi
kedalaman struktur, panjang string, jumlah item, dan ukuran payload. Password,
token, secret, dan credential tidak boleh masuk ke record atau diagnostic.

Repository memakai `event_id` unik dan `createOrFirst()`. Event yang sama dapat
diproses ulang tanpa membuat record kedua, termasuk saat terjadi race sederhana.

## 6. Persistence Append-only

Migration module membuat tabel `audit_logs` dengan ULID, `event_id` unik,
referensi actor yang dapat menjadi null, metadata JSON, index pencarian, dan
`created_at`. Tabel tidak memiliki `updated_at` karena record bukan data yang
diedit.

Model `AuditRecord` menolak operasi update dan delete dengan
`ImmutableAuditRecord`. Retention atau arsip production harus memakai proses
khusus yang terpisah dari operasi CRUD aplikasi.

Migration tetap dimiliki module dan dimuat melalui `loadMigrationsFrom()`.
Seeder module dipanggil oleh `DatabaseSeeder` global sesuai dependency order.

## 7. Query dan Authorization

Query read menggunakan:

- `ListAuditLogs` untuk filter dan pagination;
- `GetAuditLog` untuk satu record;
- `AuditLogRepository` sebagai read contract typed;
- `AuthorizationCapability` milik AccessControl untuk keputusan akses.

```text
HTTP request
    -> auth dan verified middleware
    -> controller middleware can:viewAny
    -> AuditLogPolicy
    -> Application Query
    -> AuthorizationCapability
    -> tentukan scope read
       -> SuperSystem: seluruh record
       -> actor biasa: record dengan actor_id miliknya
    -> repository
    -> typed DTO
```

Permission `audit_log.view` tetap wajib. Status `SuperSystem` hanya memperluas
scope menjadi seluruh record, sedangkan user biasa tidak dapat membaca record
actor lain walaupun mengetahui ULID record tersebut.

## 8. Presentation Web dan API

Route web:

```text
GET /system/audit-logs
    -> AuditLogController@index
    -> ListAuditLogs
    -> Inertia System/AuditLog/pages/Index

GET /system/audit-logs/{auditLog}
    -> AuditLogController@show
    -> GetAuditLog
    -> JSON detail untuk dialog
```

Route API internal:

```text
GET /api/v1/audit-logs
GET /api/v1/audit-logs/{auditLog}
    -> AuditLogApiController
    -> Application Query
    -> AuditLogResource
```

Controller hanya memvalidasi request melalui `AuditLogFilterRequest`, memanggil
query, dan membentuk response. Query Eloquent dan aturan scope tidak ditulis di
controller.

## 9. Seeder

`AuditLogSeeder` hanya membuat data demo pada environment non-production. Seeder
memakai `AuditRecorder`, bukan menulis model secara langsung, sehingga sanitasi
dan idempotency tetap diuji melalui jalur aplikasi yang sama.

## Verification Backend

```bash
php artisan module:inspect System/AuditLog
php artisan module:validate System/AuditLog --json
php artisan test --filter=AuditLog
composer ci:check
```

Focused test penting:

- `tests/Feature/AuditLogIntegrationEventTest.php`;
- `tests/Feature/AuditLogPersistenceTest.php`;
- `tests/Feature/AuditLogPresentationTest.php`;
- `tests/Feature/AuditLogArchitectureTest.php`;
- `tests/Feature/AuditLogSeederTest.php`;
- `tests/Unit/AuditLogContractTest.php`.
