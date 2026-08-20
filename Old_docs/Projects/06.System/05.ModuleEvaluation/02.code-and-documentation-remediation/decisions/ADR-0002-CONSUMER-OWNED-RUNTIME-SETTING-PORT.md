# ADR-0002: Consumer-owned Runtime-setting Port

## Status

`Accepted - 14 Agustus 2026.`

## Context

UserManagement dan AuditLog saat ini mengimpor contract SystemSetting untuk
pagination, invitation expiry, dan data runtime presentation. Dependency itu
tidak tercatat pada manifest kedua consumer. Menambahkan dependency langsung ke
SystemSetting juga tidak valid karena urutan baseline menempatkan SystemSetting
setelah AuditLog dan SystemSetting sudah bergantung pada AuditLog.

Runtime setting tetap boleh disimpan oleh SystemSetting, tetapi vocabulary,
default, validation, dan behavior harus dimiliki module yang menggunakan nilai
tersebut. Consumer harus tetap berfungsi dengan default aman ketika
SystemSetting disabled atau tidak tersedia.

## Decision

1. UserManagement memiliki port application kecil untuk kebutuhan runtime
   UserManagement, termasuk pagination dan invitation expiry yang benar-benar
   dipakai. Port tidak mengembalikan DTO atau model SystemSetting.
2. AuditLog memiliki port application kecil untuk kebutuhan runtime AuditLog,
   termasuk pagination yang benar-benar dipakai. Retention hanya masuk port
   ketika ada consumer runtime nyata.
3. Setiap consumer menyediakan default adapter miliknya sendiri. Nilai default,
   validation, dan fallback memakai vocabulary consumer dan dapat bekerja tanpa
   SystemSetting.
4. SystemSetting menyediakan adapter yang mengimplementasikan port milik
   UserManagement dan AuditLog. Adapter menerjemahkan registry SystemSetting ke
   tipe consumer tanpa mengekspos `SettingValueData`, repository, atau model
   persistence SystemSetting.
5. Provider consumer mendaftarkan default adapter lebih dahulu. Provider
   SystemSetting yang berada paling akhir boleh mengganti binding dengan adapter
   runtime ketika SystemSetting valid dan enabled.
6. UserManagement dan AuditLog dilarang mengimpor namespace private maupun
   public SystemSetting. Arah compile-time dependency adapter adalah
   `SystemSetting -> consumer contract`, sesuai urutan manifest.
7. Manifest tetap acyclic:
   `AccessControl -> UserManagement -> AuditLog -> SystemSetting`. Tidak ada
   dependency balik dari consumer ke SystemSetting.
8. Nilai invalid, storage failure, decrypt failure, atau SystemSetting disabled
   menghasilkan default aman milik consumer. Diagnostic tidak boleh membawa
   raw value, ciphertext, secret, atau exception mentah.
9. Request-scoped memoization boleh dipakai di adapter SystemSetting, tetapi
   tidak boleh mengubah ownership port atau membuat consumer bergantung pada
   cache implementation SystemSetting.

## Boundary

```text
UserManagement Application Contract <- default adapter UserManagement
                                   <- adapter SystemSetting

AuditLog Application Contract      <- default adapter AuditLog
                                   <- adapter SystemSetting
```

Contract hanya membawa scalar/value object typed yang diperlukan consumer.
Contract tidak menjadi API baca generik untuk seluruh registry setting.

## Acceptance Criteria

- Tidak ada import `UserManagement -> SystemSetting` atau
  `AuditLog -> SystemSetting`.
- Pagination serta invitation expiry sama dengan runtime registry ketika
  SystemSetting enabled dan valid.
- Default consumer aktif ketika SystemSetting disabled, binding tidak tersedia,
  nilai invalid, atau storage gagal.
- Graph manifest tetap acyclic dan urutan baseline tidak berubah.
- Contract test mencakup parity adapter, default adapter, invalid value,
  disabled module, dan redaction diagnostic.
- Architecture test menolak import tersembunyi serta dependency manifest yang
  membuat cycle.

## Alternatives Considered

### Consumer bergantung langsung pada SystemSetting

Ditolak karena membalik dependency order, menyembunyikan coupling pada manifest,
dan dapat membuat cycle `AuditLog <-> SystemSetting`.

### Contract runtime global pada framework package

Ditolak karena `packages/StarterKit` adalah framework reusable, bukan Shared
Kernel business. Contract global juga membuat ownership default serta
validation kabur.

### Membaca `config()` atau database langsung dari consumer

Ditolak karena melewati typed boundary, redaction, fallback, dan ownership
SystemSetting. Consumer hanya boleh memakai port miliknya.

## Consequences

- Ada adapter dan contract tambahan, tetapi arah dependency dapat diuji dan
  module consumer tetap mandiri.
- SystemSetting mengetahui contract consumer yang dilayaninya. Hal ini sesuai
  posisinya sebagai module paling akhir pada dependency order.
- Menambah setting runtime baru memerlukan perubahan pada port owner, default
  adapter, adapter SystemSetting, test parity, dan dokumentasi dalam increment
  yang sama.

## Rollback

Refactor dilakukan per consumer. Jika adapter SystemSetting gagal parity,
consumer tetap memakai default adapter dan binding override dapat dibatalkan
tanpa mengembalikan import langsung ke SystemSetting. ADR tetap dipertahankan;
perubahan arah dependency memerlukan ADR superseding.

## References

- [Implementation plan](../implementation-plan.md)
- [Task checklist](../tasks.md)
- [Pola komunikasi module](../../../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Service Container](../../../../../07-KERNEL/07.02-SERVICE-CONTAINER.md)
- [ADR komunikasi module](../../../../../05-DECISIONS/ADR/05.05-ADR-0003-MODULE-COMMUNICATION-AND-EXECUTION.md)

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-14 | Menerima consumer-owned runtime-setting port dan adapter SystemSetting. |
