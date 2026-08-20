# Implementation Plan: Security Hardening System

## Preflight

- Authoritative source: `AGENTS.md`, pola authorization, evaluasi module, dan ADR-0001 increment ini.
- Module terdampak: AccessControl, UserManagement, AuditLog, serta bootstrap authentication aplikasi.
- Acceptance: login/impersonation nonaktif ditolak; policy permission sync tepat; reason secret tidak tersimpan; mass mutation audit tidak diperkenankan oleh architecture test.

## Urutan

1. Tambahkan test gagal untuk lifecycle login dan impersonation UserManagement.
2. Implementasikan authentication callback dan middleware session lifecycle.
3. Tambahkan test gagal dan implementasi permission sync AccessControl.
4. Tambahkan test gagal dan validasi reason AuditLog.
5. Tambahkan architecture test append-only, lalu jalankan focused suite dan CI.

## Risiko

- Middleware logout harus mengecualikan guest dan route login agar tidak loop.
- Perubahan permission dapat membatasi operator yang sebelumnya hanya memiliki `role.manage`; seeder dan test harus mencerminkan vocabulary baru.
- Detector reason hanya menarget pola credential umum; tidak boleh mengklaim dapat mengenali seluruh secret.
