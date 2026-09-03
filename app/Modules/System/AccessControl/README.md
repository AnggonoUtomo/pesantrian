# AccessControl

Module AccessControl pada domain System.

Module ini memiliki capability authorization publik, policy role, shared
authorization context, dan page frontend dasar.

## Seeder demo

Seeder module membaca seluruh permission identity dari module valid melalui
`ModuleRegistry`, lalu membuat role `SuperSystem`, `SecurityAdmin`, dan role
operator demo berikut di luar environment production:

- `super-system@example.test`
- `security-admin@example.test`
- `operator-ppdb@example.test`
- `operator-santri@example.test`
- `operator-akademik@example.test`
- `operator-sdm@example.test`
- `auditor@example.test`
- `viewer@example.test`

Role operator yang disediakan:

- `OperatorPPDB`: uji PPDB sampai keputusan pendaftaran.
- `OperatorSantri`: uji data induk santri, wali, lifecycle, dan arsip.
- `OperatorAkademik`: uji periode akademik, kelas/rombel, placement, dan wali
  kelas.
- `OperatorSDM`: uji data pegawai dan penugasan unit.
- `Auditor`: uji audit trail dan akses baca lintas module.
- `Viewer`: uji mode baca data operasional.

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

## Boundary code

`RoleController` hanya menjadi orchestration layer. Query dashboard berada di
`Application/Queries`, data page memakai DTO, validasi mutation berada di
`Presentation/Requests`, dan side effect role berada di `Application/Actions`.
Pemeriksaan authorization use-case dilakukan ulang oleh
`AuthorizeRoleMutation`; policy tetap menjadi pemeriksaan route/resource.

Perubahan permission role dikirim melalui `PUT
access-control.roles.permissions.update`. Policy backend mengizinkan role biasa
dan menolak role `SuperSystem`.

Halaman ringkasan system tersedia pada `GET /system/dashboard`. Halaman ini
memakai `system-dashboard-layout`; dashboard untuk module berikutnya dibuat di
namespace module masing-masing agar tidak mencampur halaman AccessControl.
