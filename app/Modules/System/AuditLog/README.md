# System/AuditLog

Module ini menyimpan histori aktivitas sensitif dari AccessControl dan
UserManagement. Record bersifat append-only, metadata disaring, dan akses baca
selalu dibatasi pada backend.

## Boundary

- namespace: `App\Modules\System\AuditLog`;
- tabel milik module: `audit_logs`;
- permission: `audit_log.view`;
- public API: `AuditRecorder` dengan DTO typed;
- dependency: public capability AccessControl serta public integration event
  AccessControl dan UserManagement;
- mode ingestion: synchronous dan fail-closed.

Module ini tidak memiliki route update atau delete. `SuperSystem` dapat membaca
seluruh record. Actor lain yang memiliki permission hanya dapat membaca record
miliknya sendiri.

## Struktur Utama

- `Application/Actions/RecordAuditEntry.php`: mutation CQRS-lite;
- `Application/Queries`: list dan detail tanpa side effect;
- `Application/Services/MetadataRedactor.php`: allowlist dan redaksi recursive;
- `Infrastructure/Persistence`: model append-only dan repository Eloquent;
- `Presentation`: policy, request, resource, controller, route web/API;
- `Database`: migration dan seeder module-local.

## Command

```bash
php artisan module:inspect System/AuditLog --json
php artisan test --filter=AuditLog
php artisan db:seed --class="App\Modules\System\AuditLog\Database\Seeders\AuditLogSeeder"
```

Bootstrap utama tetap memakai:

```bash
php artisan migrate:fresh --seed
```

Command terakhir bersifat destruktif dan hanya boleh digunakan pada database
development/test yang memang boleh dibuat ulang.

## Dokumentasi

Dokumen keputusan, specification, implementation plan, tasks, dan execution log
lama diarsipkan pada `Old_docs/Projects/06.System/03.AuditLog`.
