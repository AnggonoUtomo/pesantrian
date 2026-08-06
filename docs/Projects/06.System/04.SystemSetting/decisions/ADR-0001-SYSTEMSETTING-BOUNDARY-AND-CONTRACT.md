# ADR-0001: Boundary, Public Contract, dan Audit SystemSetting

## Status

`Accepted` pada 6 Agustus 2026.

## Context

Project membutuhkan konfigurasi runtime global untuk rate limit, idempotency,
session, branding, monitoring, RTO, dan RPO. Jika setiap consumer membaca tabel
atau config sendiri, validasi, default, activation, dan failure behavior akan
berbeda.

SystemSetting harus memakai authorization AccessControl dan seluruh mutation
wajib dicatat AuditLog. Berbeda dengan AccessControl, arah dependency
SystemSetting ke AuditLog tidak membuat dependency melingkar karena AuditLog
tidak membutuhkan SystemSetting untuk core ingestion saat ini.

## Decision

- Module berada di `app/Modules/System/SystemSetting`.
- Manifest memiliki dependency langsung `AccessControl` dan `AuditLog`.
- SystemSetting memiliki tabel `system_settings` dan `idempotency_keys`.
- Definisi key, type, default, validator, owner, dan description berada pada
  registry code yang versioned.
- Public Module API terdiri dari `SystemSettingReader` dan
  `SettingDefinitionRegistrar` dengan DTO typed.
- Consumer tidak boleh mengakses Eloquent model atau repository SystemSetting.
- Writer tetap internal dan dijalankan melalui `UpdateSystemSetting` Action.
- Mutation hanya boleh dilakukan actor `SuperSystem`, membutuhkan reason, dan
  diperiksa pada middleware, policy, serta Application Action.
- Mutation memanggil public `AuditRecorder` di dalam transaksi database yang
  sama. Failure audit menggagalkan mutation.
- AuditLog metadata allowlist ditambah untuk key serta before/after setting yang
  sudah disanitasi.
- Permission identity `system_setting.view` dan `system_setting.manage` tetap
  dibuat untuk discovery. Policy tetap menolak non-SuperSystem walaupun
  permission tersebut salah diberikan.
- Runtime memakai CQRS-lite. Command Bus, Queue/Job, Facade, Shared Kernel, dan
  Integration Event baru tidak dibuat pada increment awal.

## Alternatives Considered

### Module consumer membaca tabel SystemSetting langsung

Ditolak karena membocorkan private persistence dan membuat validasi/default
berbeda pada setiap consumer.

### SystemSetting menerbitkan Integration Event untuk AuditLog

Ditolak untuk increment awal. Public `AuditRecorder` sudah tersedia, dependency
tidak melingkar, dan mutation membutuhkan hasil audit langsung. Event baru tidak
memiliki consumer selain AuditLog.

### Audit dicatat setelah response berhasil

Ditolak karena setting sensitif dapat berubah tanpa evidence ketika pencatatan
audit gagal.

### Memberi akses berdasarkan permission saja

Ditolak karena requirement menetapkan perubahan hanya untuk `SuperSystem`.
Permission salah assign tidak boleh memperluas akses.

## Consequences

### Positif

- Satu registry menjadi sumber schema/default.
- Consumer memakai contract typed dan tidak terikat Eloquent.
- Mutation mempunyai authorization berlapis dan audit fail-closed.
- Dependency module dapat ditemukan melalui manifest dan architecture test.

### Batasan

- Failure database AuditLog juga menggagalkan perubahan setting.
- Semua consumer yang memerlukan setting harus bergantung pada public reader.
- Perubahan schema/key memerlukan migration data dan pembaruan registry.
- Command console mutation harus menerima actor `SuperSystem` yang valid.

## Verification

- Manifest/dependency dan architecture test.
- Contract test reader/registrar.
- Negative test direct private dependency.
- Policy dan Action test untuk non-SuperSystem.
- Test reason wajib, before/after audit, rollback saat audit gagal, dan redaction.
- Test duplicate registry key dan unknown key.

## Keputusan User

Disetujui pada 6 Agustus 2026. User meminta seluruh dokumen SystemSetting
dianggap diterima dan implementasi dapat diselesaikan tanpa konfirmasi tambahan.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Mengusulkan boundary, public contract, authorization, dan audit SystemSetting |
| 1.1 | 2026-08-06 | Menerima keputusan untuk dilaksanakan |
