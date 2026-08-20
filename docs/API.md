# API Publik

Semua endpoint berada di bawah `/api/v1`, memerlukan autentikasi, verifikasi
email, dan pembatasan `system-api`. Mutation juga memakai idempotency.

| Method | Endpoint                                        | Tujuan                       |
| ------ | ----------------------------------------------- | ---------------------------- |
| GET    | `/api/v1/users`                                 | List user                    |
| POST   | `/api/v1/users`                                 | Buat user                    |
| GET    | `/api/v1/users/{user}`                          | Detail user                  |
| PATCH  | `/api/v1/users/{user}`                          | Ubah user atau status        |
| DELETE | `/api/v1/users/{user}`                          | Hapus lunak user             |
| GET    | `/api/v1/roles`                                 | List role                    |
| GET    | `/api/v1/roles/{role}`                          | Detail role                  |
| POST   | `/api/v1/roles`                                 | Buat role                    |
| PATCH  | `/api/v1/roles/{role}`                          | Ubah role                    |
| DELETE | `/api/v1/roles/{role}`                          | Hapus role                   |
| POST   | `/api/v1/users/{user}/roles`                    | Tetapkan role                |
| DELETE | `/api/v1/users/{user}/roles/{role}`             | Cabut role                   |
| POST   | `/api/v1/users/{user}/permissions`              | Tetapkan permission langsung |
| DELETE | `/api/v1/users/{user}/permissions/{permission}` | Cabut permission langsung    |
| GET    | `/api/v1/permissions`                           | List permission              |
| POST   | `/api/v1/users/{user}/impersonation`            | Mulai impersonation          |
| DELETE | `/api/v1/impersonation`                         | Akhiri impersonation         |
| GET    | `/api/v1/audit-logs`                            | List audit log               |
| GET    | `/api/v1/audit-logs/{auditLog}`                 | Detail audit log             |
| GET    | `/api/v1/system-settings`                       | List system setting          |
| PATCH  | `/api/v1/system-settings/{key}`                 | Ubah system setting          |

Authorization spesifik tetap berada pada policy dan capability pemilik modul.
Perubahan endpoint harus memperbarui tabel ini dan test matriks route.
