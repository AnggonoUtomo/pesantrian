# Implementation Plan: Evaluasi Ulang Module System

## Status

`Berjalan: inventory selesai, AccessControl menjadi review pertama.`

## Urutan Kerja

1. **Baseline dan inventory**: baca dokumen/module lalu jalankan discovery, validation, list, dan inspect.
2. **AccessControl**: review role, permission, SuperSystem, policy, capability, UI, mutation, event, dan test.
3. **UserManagement**: review lifecycle user, role assignment, impersonation, scope restore, invitation, dan multi-role.
4. **AuditLog**: review ingestion, redaction, scope, retention, filter, pagination, dan detail UI.
5. **SystemSetting**: review registry, runtime consumer, safe default, command, dan pengalaman UI.
6. **Peta lintas module**: kelompokkan temuan menjadi perbaikan internal, fitur module, atau fitur lintas module.

Setiap tahap harus selesai dengan temuan ber-evidence dan checkpoint sebelum tahap berikutnya dimulai.

## Checkpoint

- Tidak ada dependency concrete lintas module.
- Kandidat feature memiliki owner, authorization, audit, UI, test, dan rollback trace.
- Perubahan public contract, schema, permission, event, atau dependency memerlukan ADR sebelum coding.

## Verifikasi per Module

```bash
php artisan module:inspect System/{Module} --json
php artisan test --filter={Module}
npm run lint:check
npm run types:check
```

Browser review dilakukan pada alur UI: light/dark, responsive, permission visibility, console, network, keyboard, dan aksesibilitas.

## Open Decision

Prioritas fitur baru belum diputuskan. Evaluasi akan menyajikan rekomendasi dan dampaknya terlebih dahulu; coding hanya dimulai setelah backlog atau ADR relevan disetujui.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menetapkan tahapan review dan checkpoint lintas module |
