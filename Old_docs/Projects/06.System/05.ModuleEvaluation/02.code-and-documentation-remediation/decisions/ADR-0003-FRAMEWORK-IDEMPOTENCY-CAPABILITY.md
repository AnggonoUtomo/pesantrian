# ADR-0003: Framework Idempotency Capability

## Status

`Accepted - 14 Agustus 2026.`

## Context

SystemSetting saat ini memiliki seluruh mekanisme idempotency API: contract
repository, reservation lifecycle, middleware, persistence Eloquent, dan tabel
`idempotency_keys`. Route mutation AccessControl dan UserManagement juga wajib
memakai idempotency. Mengimpor middleware atau contract private SystemSetting
akan membuat dependency tersembunyi dari module yang boot lebih awal ke module
yang boot paling akhir.

Reservation lifecycle tidak memuat aturan bisnis SystemSetting. Capability ini
merupakan mekanisme HTTP generik yang dapat dipakai semua module. Walaupun
demikian, tabel sudah menjadi bagian schema SystemSetting dan nilai retention
serta rate limit tetap dikelola melalui registry SystemSetting.

## Decision

1. Contract repository, DTO reservation/decision, exception, reservation
   lifecycle, dan middleware idempotency generik dimiliki
   `packages/StarterKit`.
2. Package tidak boleh mengimpor namespace `App` atau module bisnis. Module
   memakai public contract framework melalui dependency injection dan alias
   middleware stabil.
3. Migration serta model tabel `idempotency_keys` tetap dimiliki SystemSetting.
   Migration tetap berada di
   `app/Modules/System/SystemSetting/Database/Migrations` dan tidak disalin ke
   migration global maupun package.
4. SystemSetting menyediakan adapter Eloquent untuk contract repository
   framework. Adapter tersebut menjadi satu-satunya bagian yang mengetahui
   model dan tabel milik SystemSetting.
5. Framework memiliki contract typed untuk policy runtime API. Policy membawa
   `idempotencyRetentionHours` dan `rateLimitPerMinute` dengan default aman.
   SystemSetting menyediakan adapter yang membaca kedua nilai dari registry.
6. Registrasi rate limiter `system-api` berada pada composition/framework
   boundary dan mengambil policy dari container saat request dijalankan. Dengan
   begitu limiter tidak bergantung pada urutan boot provider SystemSetting.
7. Jika adapter persistence tidak tersedia, mutation yang mewajibkan
   idempotency gagal tertutup dengan `503 SERVICE_UNAVAILABLE`. Middleware tidak
   boleh melewati reservation secara diam-diam. Endpoint read tetap dapat
   bekerja dengan default policy framework.
8. Replay hanya menyimpan response JSON yang telah disanitasi. Password, token,
   secret, credential, authorization, cookie, session, dan API key wajib
   direduksi sebelum persistence. Diagnostic tidak membawa payload mentah.
9. Prune command, scheduler, migration, dan lifecycle data tetap dioperasikan
   SystemSetting karena module tersebut merupakan owner persistence.

## Boundary

```text
packages/StarterKit
  IdempotencyRepository contract
  RuntimeApiPolicy contract + safe default
  IdempotencyManager + middleware
            ^
            |
SystemSetting
  EloquentIdempotencyRepository
  SystemSettingRuntimeApiPolicy
  model + migration + prune command
```

AccessControl dan UserManagement hanya merujuk alias middleware atau public
namespace framework. Keduanya tidak mengimpor SystemSetting.

## Acceptance Criteria

- Tidak ada contract, DTO, manager, exception, atau middleware idempotency
  generik yang tersisa pada namespace private SystemSetting.
- `packages/StarterKit` tidak mengimpor namespace `App`.
- Adapter Eloquent dan policy SystemSetting memenuhi contract framework.
- Migration, model, prune command, dan scheduler tetap dimiliki SystemSetting.
- Replay, payload mismatch, in-flight conflict, expiry, rollback ketika response
  gagal disimpan, rate limit, redaction, serta failure 503 memiliki test.
- Module validation tetap lulus dan dependency graph tidak berubah.
- Mutation AccessControl/UserManagement dapat memakai capability tanpa direct
  dependency ke SystemSetting.

## Alternatives Considered

### Menjaga seluruh capability di SystemSetting

Ditolak karena route module lain harus mengimpor implementation private atau
menambah dependency balik yang bertentangan dengan boot order baseline.

### Memindahkan migration dan tabel ke package

Ditolak karena memindahkan ownership schema yang sudah jelas, memperbesar
upgrade migration package, dan mengurangi keterlacakan rollback data.

### Membuat salinan middleware pada setiap module

Ditolak karena reservation, canonical payload hash, replay, redaction, dan
failure contract dapat berbeda antar module.

### Melanjutkan mutation tanpa idempotency saat SystemSetting disabled

Ditolak karena mengubah jaminan API secara diam-diam. Failure 503 lebih aman
dan dapat didiagnosis tanpa membuat mutation duplikat.

## Consequences

- Framework package bertambah satu capability HTTP reusable dan satu policy
  runtime typed.
- SystemSetting tetap menjadi owner schema serta operator retention/rate, tetapi
  tidak lagi menjadi owner algoritme idempotency generik.
- Aplikasi harus menjaga binding adapter SystemSetting untuk menyediakan
  persistence mutation. Default framework hanya menjaga boot/read path dan
  menghasilkan failure tertutup ketika persistence tidak tersedia.
- Perubahan schema idempotency tetap mengikuti migration dan rollback
  SystemSetting. Perubahan algoritme/contract mengikuti versioning package.

## Rollback

Perubahan dapat dibatalkan dengan mengembalikan binding dan namespace lama
selama belum ada route module lain yang memakai capability. Migration dan data
tidak perlu dipindah atau dihapus karena ownership tabel tidak berubah. Setelah
capability dipakai lintas module, perubahan boundary memerlukan ADR superseding.

## References

- [Implementation specification API](../api-implementation-specification.md)
- [Implementation plan](../implementation-plan.md)
- [Task checklist](../tasks.md)
- [Pola komunikasi module](../../../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Service Container](../../../../../07-KERNEL/07.02-SERVICE-CONTAINER.md)

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-14 | Menerima capability idempotency framework dengan persistence dan policy adapter milik SystemSetting. |
