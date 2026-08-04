# AccessControl

Module AccessControl pada domain System.

Module ini memiliki capability authorization publik, policy role, shared
authorization context, dan page frontend dasar.

## Seeder demo

Seeder module membuat lima permission dari `permissions.php`, role
`SuperSystem` dan `SecurityAdmin`, serta user demo berikut di luar environment
production:

- `super-system@example.test`
- `security-admin@example.test`

Password user demo dibaca dari `ACCESS_CONTROL_DUMMY_PASSWORD`. Jika nilainya
kosong, seeder memakai password acak. Untuk menjalankan:

```bash
php artisan access-control:seed
```

Jika user demo sudah pernah dibuat dan password perlu disetel ulang, isi
`ACCESS_CONTROL_DUMMY_PASSWORD` dengan password lokal pilihanmu, lalu jalankan
command tersebut. Password existing hanya diubah saat env itu terisi.

Command tersebut terdaftar dari `AccessControl\ServiceProvider`, sedangkan
`DatabaseSeeder` Laravel tidak mengelola data module. Pemanggilan class secara
langsung hanya dilakukan oleh focused test.

Menu sidebar `Access Control` hanya tampil untuk `SuperSystem` atau user dengan
permission `access_control.role.manage`. Backend tetap menjadi security
authority.

Perubahan permission role dikirim melalui `PUT
access-control.roles.permissions.update`. Policy backend mengizinkan role biasa
dan menolak role `SuperSystem`.

Halaman ringkasan system tersedia pada `GET /system/dashboard`. Halaman ini
memakai `system-dashboard-layout`; dashboard untuk module berikutnya dibuat di
namespace module masing-masing agar tidak mencampur halaman AccessControl.
