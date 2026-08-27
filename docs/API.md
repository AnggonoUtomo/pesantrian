# API Publik

API publik penuh belum menjadi scope release awal SakaSantri. Baseline routing
frontend memakai Laravel named routes melalui Ziggy untuk Inertia React.

## Konvensi Saat Ini

- Frontend routing: Ziggy conventional named routes.
- Authentication: Laravel Starter Kit + Fortify.
- Authorization: backend policy/permission milik module.
- Identifier resource aplikasi: ULID.
- Security authority: backend.

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
