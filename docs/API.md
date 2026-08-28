# API Publik

API publik penuh belum menjadi scope release awal SakaSantri. Baseline routing
frontend memakai Laravel named routes melalui Ziggy untuk Inertia React.

## Konvensi Saat Ini

- Frontend routing: Ziggy conventional named routes.
- Authentication: Laravel Starter Kit + Fortify.
- Authorization: backend policy/permission milik module.
- Identifier resource aplikasi: ULID.
- Security authority: backend.

## Endpoint API v1 Aktif

| Method | Endpoint | Route name | Authorization | Idempotency | Response |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/audit-logs` | `api.v1.audit-logs.index` | Audit log view policy | Tidak | Envelope sukses dengan daftar audit dan pagination meta |
| GET | `/api/v1/audit-logs/{auditLog}` | `api.v1.audit-logs.show` | Audit log view policy | Tidak | Envelope sukses dengan detail audit |
| GET | `/api/v1/academic/periods/terms` | `api.v1.academic.periods.terms.index` | `academic_period.view` | Tidak | Envelope sukses dengan daftar term akademik dan pagination meta |
| POST | `/api/v1/academic/periods/terms` | `api.v1.academic.periods.terms.store` | `academic_period.manage` | Ya | Envelope sukses `201` dengan term akademik yang dibuat |
| PATCH | `/api/v1/academic/periods/terms/{term}` | `api.v1.academic.periods.terms.update` | `academic_period.manage` | Ya | Envelope sukses dengan term akademik yang diperbarui |
| GET | `/api/v1/academic/periods/years` | `api.v1.academic.periods.years.index` | `academic_period.view` | Tidak | Envelope sukses dengan daftar tahun akademik dan pagination meta |
| POST | `/api/v1/academic/periods/years` | `api.v1.academic.periods.years.store` | `academic_period.manage` | Ya | Envelope sukses `201` dengan tahun akademik yang dibuat |
| PATCH | `/api/v1/academic/periods/years/{year}` | `api.v1.academic.periods.years.update` | `academic_period.manage` | Ya | Envelope sukses dengan tahun akademik yang diperbarui |
| DELETE | `/api/v1/impersonation` | `api.v1.impersonation.destroy` | Authenticated user | Ya | Envelope sukses tanpa data |
| GET | `/api/v1/organization/units` | `api.v1.organization.units.index` | `organization.view` | Tidak | Envelope sukses dengan daftar unit dan pagination meta |
| POST | `/api/v1/organization/units` | `api.v1.organization.units.store` | `organization.manage` | Ya | Envelope sukses `201` dengan unit yang dibuat |
| PATCH | `/api/v1/organization/units/{unit}` | `api.v1.organization.units.update` | `organization.manage` | Ya | Envelope sukses dengan unit yang diperbarui |
| GET | `/api/v1/permissions` | `api.v1.permissions.index` | Access control view policy | Tidak | Envelope sukses dengan daftar permission dan pagination meta |
| GET | `/api/v1/roles` | `api.v1.roles.index` | Access control view policy | Tidak | Envelope sukses dengan daftar role dan pagination meta |
| POST | `/api/v1/roles` | `api.v1.roles.store` | Access control create policy | Ya | Envelope sukses `201` dengan role yang dibuat |
| GET | `/api/v1/roles/{role}` | `api.v1.roles.show` | Access control view policy | Tidak | Envelope sukses dengan detail role |
| PATCH | `/api/v1/roles/{role}` | `api.v1.roles.update` | Access control view policy dan mutation rule | Ya | Envelope sukses dengan role yang diperbarui |
| DELETE | `/api/v1/roles/{role}` | `api.v1.roles.destroy` | Access control view policy dan mutation rule | Ya | Envelope sukses tanpa data |
| GET | `/api/v1/system-settings` | `api.v1.system-settings.index` | System setting view policy | Tidak | Envelope sukses dengan daftar setting |
| PATCH | `/api/v1/system-settings/{key}` | `api.v1.system-settings.update` | System setting update policy | Ya | Envelope sukses dengan setting yang diperbarui |
| GET | `/api/v1/users` | `api.v1.users.index` | User view policy | Tidak | Envelope sukses dengan daftar user dan pagination meta |
| POST | `/api/v1/users` | `api.v1.users.store` | User create policy | Ya | Envelope sukses `201` dengan user yang dibuat |
| GET | `/api/v1/users/{user}` | `api.v1.users.show` | User view policy | Tidak | Envelope sukses dengan detail user |
| PATCH | `/api/v1/users/{user}` | `api.v1.users.update` | User mutate policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}` | `api.v1.users.destroy` | User delete policy | Ya | Envelope sukses tanpa data |
| POST | `/api/v1/users/{user}/impersonation` | `api.v1.users.impersonation.store` | User impersonation policy | Ya | Envelope sukses dengan state impersonation |
| POST | `/api/v1/users/{user}/permissions` | `api.v1.users.permissions.store` | User direct permission policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}/permissions/{permission}` | `api.v1.users.permissions.destroy` | User direct permission policy | Ya | Envelope sukses dengan user yang diperbarui |
| POST | `/api/v1/users/{user}/roles` | `api.v1.users.roles.store` | User role assignment policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}/roles/{role}` | `api.v1.users.roles.destroy` | User role assignment policy | Ya | Envelope sukses dengan user yang diperbarui |

Contoh named route frontend:

```ts
router.visit(route('students.show', student.id))
```

## Jika API Publik Ditambahkan

Sebelum menambah endpoint publik, buat atau perbarui specification/work item dan
catat contract berikut:

| Contract | Wajib dicatat |
| --- | --- |
| Base path | Prefix endpoint dan versioning bila ada |
| Authentication | Guard, token/session mechanism, dan CSRF/CORS bila relevan |
| Authorization | Permission/policy/resource rule backend |
| Request | DTO/schema, validasi, idempotency untuk mutation |
| Response | DTO/resource, pagination, error format |
| Audit | Event atau audit entry yang dibuat |
| Test | Focused API/feature test |

Endpoint tidak boleh ditambahkan hanya karena UI membutuhkan data internal.
Untuk Inertia, gunakan route dan controller Presentation module sesuai baseline.
