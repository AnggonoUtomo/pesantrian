# Project Documentation

## Panduan Belajar Code

- [Alur Code Phase 1 sampai Phase 3](05.phase-1-2-code-flow/README.md)
- [System/AccessControl](06.System/01.AccessControl/README.md)
- [Alur Code System/AccessControl](06.System/01-1.AccessControl-code-flow/README.md)
- [System/UserManagement](06.System/02.UserManagement/README.md)
  - [01. Filter daftar user](06.System/02.UserManagement/01.user-list-filters/README.md)
- [System/AuditLog](06.System/03.AuditLog/README.md)
- [Alur Code System/AuditLog](06.System/03-1.AuditLog-code-flow/README.md)
- [System/SystemSetting](06.System/04.SystemSetting/README.md)
- [Evaluasi ulang module System](06.System/05.ModuleEvaluation/README.md)
- [Phase 3 Module Generator](03.phase-3-module-generator/README.md)

## Document Information

| Item    | Value     |
| ------- | --------- |
| Version | 1.8       |
| Status  | Accepted  |
| Owner   | Tech Lead |

Folder ini berisi dokumentasi spesifik project turunan atau module extension.
Baseline global berada pada folder 00–07 dan tidak disalin ke setiap project.
Folder ini dirancang untuk ditempatkan di dalam `docs/Projects/` pada project
Laravel. Gunakan relative link; jangan referensikan nama workspace lokal.

Pembuatan module baru mengikuti [standar module global](../AGENTS.md) dan
[prosedur module baseline](../03-IMPLEMENTATION/03.07-MODULES.md). Dokumen
module wajib dibuat dan ditinjau sebelum generator atau coding dimulai.

## Project Intake

Identifikasi source code, versi Laravel/PHP, starter kit, package, module,
migration, route, permission, event, capability existing, mode project, scope,
owner, acceptance criteria, dan target release.

Gunakan Projects/_TEMPLATE sebagai starting point. Nama folder menggunakan
project slug lowercase kebab-case, misalnya document-management atau attendance.

## Incremental Rule

Setiap perubahan dipecah menjadi increment kecil. Setiap increment memiliki task,
acceptance criteria, focused test, verification evidence, dan execution log.
Gunakan skill incremental-implementation.

## Revision History

| Version | Date       | Description            |
| ------- | ---------- | ---------------------- |
| 1.4     | 2026-08-06 | Menetapkan prosedur standar pembuatan module |
| 1.5     | 2026-08-06 | Menambahkan index AuditLog dan rencana SystemSetting |
| 1.6     | 2026-08-06 | Menandai implementasi SystemSetting selesai |
| 1.7     | 2026-08-06 | Menambahkan dokumentasi evaluasi ulang module System |
| 1.8     | 2026-08-06 | Menambahkan draft filter daftar user pada UserManagement |
