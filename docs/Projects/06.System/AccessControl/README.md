# AccessControl Module

Module ini menjadi pemilik capability authorization publik untuk module lain.
Module berada pada domain `System` dan tidak membagikan detail model,
repository, atau adapter internalnya secara langsung.

## Identitas module

- Domain: `System`
- Module: `AccessControl`
- Namespace: `App\Modules\System\AccessControl`
- Path: `app/Modules/System/AccessControl`
- Permission owner: `AccessControl`
- Status: `Discovery`

## Urutan baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR namespace](decisions/001-access-control-namespace.md)

## Dokumen terkait

- [Module Contracts](../../../06-FRAMEWORK/06.02-MODULE-CONTRACTS.md)
- [Module Registry](../../../06-FRAMEWORK/06.03-MODULE-REGISTRY.md)
- [Authorization](../../../07-KERNEL/07.04-AUTHORIZATION.md)
- [Phase 3 Module Generator](../../03.phase-3-module-generator/README.md)

## Batas module

- `AccessControl` menjadi pemilik capability permission resolution.
- Module lain menggunakan public contract atau capability yang disetujui.
- Model dan repository internal tidak boleh diimpor langsung oleh module lain.
- Backend tetap menjadi security authority.
- UI hanya memakai authorization context untuk visibility dan UX.

## Cara verifikasi awal

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
```

Verifikasi implementasi akan ditambahkan setelah module mulai dibuat.
