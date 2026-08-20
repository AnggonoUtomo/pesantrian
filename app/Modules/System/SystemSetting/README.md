# System/SystemSetting

SystemSetting mengelola registry, persistence, validasi, aktivasi, safe default,
dan audit konfigurasi runtime global.

## Boundary

- Public contract: `SystemSettingReader` dan `SettingDefinitionRegistrar`.
- Dependency: AccessControl dan AuditLog.
- Permission: `system_setting.view` dan `system_setting.manage`.
- Secret, token, password, credential, dan API key tidak boleh disimpan atau
  dikeluarkan sebagai plaintext. Nilai sensitif yang didukung registry disimpan
  terenkripsi dan output publik selalu teredaksi.
- Runtime memakai CQRS-lite tanpa Command Bus atau Queue/Job.

## Status

Implementasi selesai. Registry, persistence, public reader, mutation fail-closed,
audit, seeder, command, web/API, runtime consumer, adapter idempotency, session,
frontend, dan quality gate telah tersedia serta terverifikasi. Contract,
middleware, dan reservation lifecycle idempotency generik berada pada
`packages/StarterKit`; migration/table serta adapter policy retention/rate tetap
dimiliki SystemSetting.
