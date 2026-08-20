# Implementation Specification API System

## Status dan Authority

Status: `Siap untuk test-first pada seluruh endpoint baseline`.

Dokumen ini melengkapi payload implementation untuk matrix endpoint pada
[`02.01-API-SPEC.md`](../../../../02-DESIGN/02.01-API-SPEC.md). Jika ada
perbedaan, API spec global tetap menjadi authority. Ownership idempotency
generik mengikuti ADR-0003: contract, middleware, dan reservation lifecycle
berada pada `packages/StarterKit`; persistence serta policy adapter tetap
dimiliki SystemSetting.

## Aturan Umum

- Base path adalah `/api/v1` dan authentication memakai session internal.
- Content type request/response adalah JSON. Nama field API memakai
  `snake_case`.
- Identifier resource wajib ULID. Target yang tidak ada atau tidak terlihat
  dalam scope mengembalikan `404 RESOURCE_NOT_FOUND`.
- Collection memakai page pagination. Default `per_page` adalah `25`, maximum
  `100`, dan pilihan runtime harus tetap berasal dari port consumer.
- Semua endpoint memakai rate limiter `system-api`; default registry adalah 60
  request per menit per actor dan endpoint.
- Mutation wajib menerima `Idempotency-Key` sepanjang 1-120 karakter dengan
  allowlist `[A-Za-z0-9._:-]`. Key sama dan payload berbeda menghasilkan
  `409 IDEMPOTENCY_CONFLICT`.
- `X-Correlation-ID` boleh dikirim client jika berupa ULID. Server membuat ULID
  baru bila header tidak ada/invalid dan mengembalikannya pada header serta
  `meta.correlation_id`.
- Password, reset token, session ID, secret, credential, ciphertext, raw audit
  metadata, dan stack trace tidak pernah menjadi resource atau error response.

## Envelope Canonical

Success selalu memiliki empat field utama:

```json
{
  "success": true,
  "message": "Daftar user berhasil dibaca.",
  "data": [],
  "meta": {
    "correlation_id": "01K2..."
  }
}
```

Error selalu memiliki lima field utama:

```json
{
  "success": false,
  "message": "Request tidak valid.",
  "errors": {
    "email": ["Email sudah digunakan."]
  },
  "code": "VALIDATION_ERROR",
  "meta": {
    "correlation_id": "01K2..."
  }
}
```

Collection menambah `current_page`, `per_page`, `total`, dan `last_page` pada
`meta`. Field `errors` berupa object kosong untuk error tanpa field validation.

## Resource User

Response user:

```json
{
  "id": "01K2...",
  "name": "Operator Sistem",
  "email": "operator@example.test",
  "status": "active",
  "is_protected": false,
  "deleted_at": null,
  "roles": ["Operator"],
  "avatar_url": null,
  "email_verified": true,
  "last_login_at": null
}
```

| Method/path | Request/query | Authorization | Success | Negative contract |
| --- | --- | --- | --- | --- |
| `GET /users` | `page`, `per_page`, `search`, `filter[status]`, `filter[role]`, `filter[archive]`, `sort` allowlist `created_at,-created_at,name,-name` | `user.view`; scope policy | `200`, list user dan pagination meta | 401, 403, 422 query invalid |
| `GET /users/{user}` | Tidak ada body | `user.view`; resource scope | `200`, satu user | 401, 403, 404 |
| `POST /users` | `name` wajib 2-100; `email` wajib email/unique; `password` wajib sesuai password rule; `status` optional; `role` optional valid | `user.create` | `201`, user tanpa password | 401, 403, 409 duplicate, 422, 429 |
| `PATCH /users/{user}` | Minimal satu dari `name`, `email`, `status`; profile memakai `user.update`, status memakai `user.status.manage` | policy update/status; protected invariant | `200`, user terbaru | 401, 403, 404, 409, 422, 429 |
| `DELETE /users/{user}` | `reason` optional string max 500 | `user.delete`; self/protected denial | `200`, `data: null` | 401, 403, 404, 409, 429 |

Create/update/delete menghasilkan audit event. Password hanya diteruskan ke
Application Action dan dilarang masuk idempotency response, audit, atau log.

## Resource Role dan Permission

Role response:

```json
{
  "id": "01K2...",
  "name": "SecurityAdmin",
  "guard_name": "web",
  "permissions": ["user.view"],
  "is_protected": false
}
```

Permission response:

```json
{
  "id": "01K2...",
  "name": "user.view",
  "guard_name": "web",
  "module": "user",
  "label": "View"
}
```

| Method/path | Request/query | Authorization | Success | Negative contract |
| --- | --- | --- | --- | --- |
| `GET /roles` | `page`, `per_page`, `search`, `sort=name,-name` | role manage atau permission assign; privileged visibility policy | `200`, list role | 401, 403, 422 |
| `GET /roles/{role}` | Tidak ada body | policy role read | `200`, satu role | 401, 403, 404 |
| `POST /roles` | `name` wajib 2-100 dan pola role | `access_control.role.manage` | `201`, role | 401, 403, 409 duplicate, 422, 429 |
| `PATCH /roles/{role}` | `name` optional; `permissions` optional list unik permission valid; minimal satu | permission sesuai field dan policy resource | `200`, role terbaru | 401, 403, 404, 409, 422, 429 |
| `DELETE /roles/{role}` | Tidak ada body | `access_control.role.manage`; protected denial | `200`, `data: null` | 401, 403, 404, 409, 429 |
| `GET /permissions` | `page`, `per_page`, `search`, `filter[module]`, `sort=name,-name` | permission manage/assign; privileged visibility policy | `200`, list permission | 401, 403, 422 |

Permission identity tetap dimiliki module asal. API tidak membuat atau mengubah
permission identity arbitrer pada baseline ini.

## Assignment Role dan Direct Permission

| Method/path | Request | Authorization | Success | Negative contract |
| --- | --- | --- | --- | --- |
| `POST /users/{user}/roles` | `{ "role": "SecurityAdmin" }` | `access_control.role.assign`; target/role policy | `200`, user terbaru | 401, 403, 404, 409, 422, 429 |
| `DELETE /users/{user}/roles/{role}` | Tidak ada body | sama dengan assign | `200`, user terbaru | 401, 403, 404, 409, 429 |
| `POST /users/{user}/permissions` | `{ "permission": "user.view" }` | `access_control.permission.assign`; target policy | `200`, user terbaru | 401, 403, 404, 409, 422, 429 |
| `DELETE /users/{user}/permissions/{permission}` | Tidak ada body | sama dengan assign | `200`, user terbaru | 401, 403, 404, 409, 429 |

Assignment berulang boleh menghasilkan resource yang sama, tetapi tetap memakai
idempotency key dan tidak boleh membuat audit duplikat. Target user atau role
`SuperSystem` mengikuti policy privileged dan tidak boleh bocor lewat 403/404.

## Impersonation

| Method/path | Request | Authorization | Success | Negative contract |
| --- | --- | --- | --- | --- |
| `POST /users/{user}/impersonation` | `reason` wajib 10-500 karakter | `user.impersonate`; target active/non-self/non-SuperSystem | `200`, indicator actor/target non-sensitive | 401, 403, 404, `IMPERSONATION_REASON_REQUIRED`, `IMPERSONATION_TARGET_FORBIDDEN`, 409, 429 |
| `DELETE /impersonation` | Tidak ada body | session impersonation aktif | `200`, `data: null` | 401, 409 bila tidak aktif, 429 |

Response start hanya membawa `active`, actor display name, target display name,
dan correlation ID. Session key, token, cookie, permission snapshot, serta actor
ID internal tidak dikirim.

## AuditLog Existing API

`GET /audit-logs` menerima `page`, `per_page`, `search`, `module`, `action`,
`date_from`, `date_to`, dan `sort_direction`. Response list memakai AuditLog
resource aman serta pagination pada `meta`, bukan nested envelope kedua.
`GET /audit-logs/{auditLog}` mengembalikan satu resource sesuai actor scope.
Raw metadata, event ID, actor ID, subject ID, dan correlation internal tidak
masuk resource operator.

## SystemSetting Existing API

`GET /system-settings` mengembalikan list typed. Setting sensitif selalu
`value: null`, `default_value: null`, `sensitive: true`, dan `has_value`.
`PATCH /system-settings/{key}` menerima:

```json
{
  "value": 60,
  "reason": "Menyesuaikan kapasitas API."
}
```

Mutation mewajibkan SuperSystem, idempotency key, rate limit, reason, dan audit
fail-closed. Unknown key menjadi 404; nilai tidak valid menjadi 422; storage
tidak tersedia menjadi error aman tanpa ciphertext atau exception mentah.

## Error Mapping

| Kondisi | HTTP/code |
| --- | --- |
| Authentication tidak ada | `401 UNAUTHENTICATED` |
| Permission/policy ditolak | `403 FORBIDDEN` |
| Target tidak ada/tidak terlihat | `404 RESOURCE_NOT_FOUND` |
| Duplicate/state conflict | `409 CONFLICT` |
| Idempotency payload berbeda/in-flight | `409 IDEMPOTENCY_CONFLICT` |
| Validation/FormRequest | `422 VALIDATION_ERROR` |
| Rate limit | `429 RATE_LIMITED` dengan `retry_after` non-sensitive |
| Storage dependency unavailable | `503 SERVICE_UNAVAILABLE` |
| Exception tidak terduga | `500 INTERNAL_ERROR` tanpa detail internal |

## Acceptance Test Matrix

- Setiap endpoint memiliki positive test dan minimal authentication,
  authorization, validation, serta not-found negative test yang relevan.
- Mutation memiliki replay, payload mismatch, rate limit, audit, dan redaction
  test.
- User protected/self, role protected, permission invalid, scope AuditLog, dan
  target impersonation SuperSystem memiliki focused denial test.
- Contract test tidak bergantung pada urutan field JSON, tetapi memastikan
  envelope, HTTP status, correlation ID, pagination, dan tidak adanya field
  sensitif.
- `route:list --path=api/v1 --json`, module validation, PHPStan, frontend type
  check, dan browser network/console verification menjadi evidence akhir.

## Rollback dan Batasan

- Endpoint baru dapat dihapus per module tanpa mengubah Application Action.
- Response factory tidak boleh mengambil ownership business error atau DTO.
- Perubahan nama field/envelope memerlukan migration client serta revision
  specification.
- Mutation API memakai capability idempotency public `packages/StarterKit` dan
  tidak boleh mengimpor middleware/repository private SystemSetting. Jika
  adapter persistence tidak tersedia, mutation gagal tertutup dengan
  `503 SERVICE_UNAVAILABLE`.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.1 | 2026-08-14 | Menerapkan keputusan ADR-0003 untuk ownership idempotency framework dan adapter SystemSetting. |
| 1.0 | 2026-08-14 | Melengkapi schema endpoint, envelope, security, error, dan acceptance test baseline. |
