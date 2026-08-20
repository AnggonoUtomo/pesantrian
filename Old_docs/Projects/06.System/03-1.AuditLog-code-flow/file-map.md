# Peta File Code AuditLog

## Identity dan Bootstrap

| Path | Tanggung jawab |
| --- | --- |
| `app/Modules/System/AuditLog/module.json` | Identity dan dependency deklaratif module |
| `app/Modules/System/AuditLog/module.php` | Retention serta aturan sanitasi metadata |
| `app/Modules/System/AuditLog/permissions.php` | Permission identity `audit_log.view` |
| `app/Modules/System/AuditLog/ServiceProvider.php` | Binding contract, listener event, migration, route, dan policy |

## Application

| Path | Tanggung jawab |
| --- | --- |
| `Application/Contracts/AuditRecorder.php` | Public Module API untuk mencatat audit |
| `Application/Contracts/AuditLogRepository.php` | Contract persistence dan read AuditLog |
| `Application/Actions/RecordAuditEntry.php` | Use case pencatatan audit synchronous |
| `Application/DTO/AuditEntryData.php` | Input typed untuk record audit |
| `Application/DTO/AuditLogFilter.php` | Filter typed untuk query list |
| `Application/DTO/AuditLogPage.php` | Hasil pagination typed |
| `Application/DTO/AuditRecordData.php` | Bentuk record typed untuk response |
| `Application/Listeners/RecordAccessControlActivity.php` | Consumer event AccessControl versi 1 |
| `Application/Listeners/RecordUserManagementActivity.php` | Consumer event UserManagement versi 1 |
| `Application/Queries/ListAuditLogs.php` | Query list dengan permission dan scope actor |
| `Application/Queries/GetAuditLog.php` | Query detail dengan permission dan scope actor |
| `Application/Services/MetadataRedactor.php` | Allowlist, redaction, dan pembatasan metadata |

## Infrastructure dan Database

| Path | Tanggung jawab |
| --- | --- |
| `Infrastructure/Persistence/Models/AuditRecord.php` | Model ULID append-only |
| `Infrastructure/Persistence/Repositories/EloquentAuditLogRepository.php` | Persistence idempotent dan query ter-scope |
| `Database/Migrations/*_create_audit_logs_table.php` | Schema MySQL audit log |
| `Database/Seeders/AuditLogSeeder.php` | Data demo aman dan idempotent |

## Producer Integration Event

| Path | Tanggung jawab |
| --- | --- |
| `AccessControl/Application/Contracts/AccessControlActivityPublisher.php` | Contract publisher aktivitas AccessControl |
| `AccessControl/Application/Events/AccessControlActivityOccurred.php` | Event publik AccessControl versi 1 |
| `AccessControl/Infrastructure/Events/LaravelAccessControlActivityPublisher.php` | Adapter dispatch event AccessControl |
| `UserManagement/Application/Contracts/UserManagementActivityPublisher.php` | Contract publisher aktivitas UserManagement |
| `UserManagement/Application/Events/UserManagementActivityOccurred.php` | Event publik UserManagement versi 1 |
| `UserManagement/Infrastructure/Events/LaravelUserManagementActivityPublisher.php` | Adapter dispatch event UserManagement |

## Presentation Backend

| Path | Tanggung jawab |
| --- | --- |
| `Presentation/Controllers/AuditLogController.php` | Orchestration halaman Inertia dan detail web |
| `Presentation/Controllers/AuditLogApiController.php` | Orchestration API internal v1 |
| `Presentation/Requests/AuditLogFilterRequest.php` | Validasi dan normalisasi filter |
| `Presentation/Resources/AuditLogResource.php` | Bentuk response JSON |
| `Presentation/Policies/AuditLogPolicy.php` | Authorization resource AuditLog |
| `Routes/web.php` | Route halaman dan detail web |
| `Routes/api.php` | Route API internal v1 |

## Frontend dan Runtime

| Path | Tanggung jawab |
| --- | --- |
| `resources/js/pages/System/AuditLog/pages/Index.tsx` | Page dan koordinasi state AuditLog |
| `resources/js/pages/System/AuditLog/components/AuditLogSummaryCards.tsx` | Ringkasan record pada halaman |
| `resources/js/pages/System/AuditLog/components/AuditLogFilterBar.tsx` | Filter list dan reset |
| `resources/js/pages/System/AuditLog/components/AuditLogTable.tsx` | Tabel desktop, kartu mobile, empty state, dan pagination |
| `resources/js/pages/System/AuditLog/components/AuditLogDetailDialog.tsx` | Dialog detail read-only |
| `resources/js/pages/System/AuditLog/types.ts` | Contract TypeScript halaman |
| `resources/js/layouts/system-dashboard-layout.tsx` | Shell bersama namespace System |
| `resources/js/components/app-sidebar.tsx` | Visibility menu Audit Log |
| `resources/js/components/command-palette.tsx` | Akses cepat menuju Audit Log |
| `resources/js/ziggy.ts` | Daftar route web AuditLog untuk Ziggy |
| `database/seeders/DatabaseSeeder.php` | Entry point global seeder module |

## Test

| Path | Fokus |
| --- | --- |
| `tests/Unit/AuditLogContractTest.php` | DTO, contract, dan sanitasi metadata |
| `tests/Feature/AuditLogIntegrationEventTest.php` | Event versioning, fail-closed, dan idempotency |
| `tests/Feature/AuditLogPersistenceTest.php` | Schema, append-only, scope, dan filter |
| `tests/Feature/AuditLogPresentationTest.php` | Route, API, authorization, dan response |
| `tests/Feature/AuditLogArchitectureTest.php` | Boundary dan dependency module |
| `tests/Feature/AuditLogSeederTest.php` | Seeder global, focused, dan idempotency |
| `tests/Feature/ZiggyRouteTest.php` | Ketersediaan route Ziggy AuditLog |

## Boundary Komunikasi

| Konsep | Status pada AuditLog |
| --- | --- |
| Public Module API | `AuditRecorder` |
| Mutation | `RecordAuditEntry` sebagai Application Action synchronous |
| Read | Application Query, repository contract, dan typed DTO |
| Integration Event | Consumer event publik AccessControl dan UserManagement versi 1 |
| Command Bus dan Queue/Job | Tidak dipakai karena audit harus fail-closed dalam transaksi producer |
| Facade dan Shared Kernel domain | Tidak digunakan |
| Pola keseluruhan | CQRS-lite dengan fondasi enterprise terdokumentasi |
