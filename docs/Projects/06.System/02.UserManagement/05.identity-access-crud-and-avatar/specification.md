# Specification - Identity, Akses CRUD, dan Avatar

## Sumber Acuan

- `docs/AGENTS.md`: boundary module, authorization, UI, migration, dan seeder.
- `docs/03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md`: CQRS-lite dan komunikasi lintas module.
- `docs/Projects/06.System/02.UserManagement/README.md`: boundary dan scope UserManagement yang sudah berjalan.
- [Spatie Media Library - model preparation](https://spatie.be/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model) dan [media collections](https://spatie.be/docs/laravel-medialibrary/v11/working-with-media-collections/defining-media-collections): dasar `HasMedia`, `InteractsWithMedia`, dan single-file collection.

## Kondisi Awal

- Tabel dan modal user saat ini hanya memakai `name`, `email`, dan password.
- `users.email_verified_at` sudah tersedia dari Laravel, tetapi belum ditampilkan sebagai informasi operasi.
- Belum ada `last_login_at`, media collection avatar, atau package Media Library pada project.
- Role assignment memakai public capability AccessControl; UserManagement tidak boleh mengakses private model atau repository AccessControl.

## Scope

### 1. Identitas dan tampilan CRUD

Tabel user menampilkan:

| Data | Sumber | Perilaku |
| --- | --- | --- |
| Avatar | collection `avatar` atau fallback inisial | aman bila belum ada file |
| Nama dan email | tabel `users` | email tetap unik |
| Role efektif | read contract AccessControl | hanya untuk informasi dan akses yang sah |
| Status | lifecycle UserManagement | badge accent sesuai status |
| Verifikasi email | `email_verified_at` | badge terverifikasi/belum terverifikasi |
| Terakhir login | `last_login_at` | tampilkan `Belum pernah login` bila `null` |
| Aksi | policy dan permission | tetap mengikuti state arsip dan perlindungan |

Dialog memakai tiga bagian: **Identitas** (avatar, nama, email), **Akses** (role dan status), serta **Aktivitas** (verifikasi, last login, created/updated, dan arsip pada detail). Password hanya ada saat tambah user.

### 2. Role dan status awal

- Role awal opsional dapat dipilih saat create hanya bila actor memiliki `user.create` dan capability penugasan role AccessControl mengizinkannya.
- Role `SuperSystem` tidak boleh dipilih melalui flow ini.
- Status awal menggunakan `active`, `inactive`, atau `suspended`.
- Actor tanpa `user.status.manage` hanya dapat membuat user dengan status `active`; backend menolak payload status lain, walaupun payload dibuat manual.
- Create user, status awal, dan role awal harus atomik: kegagalan role atau status membatalkan pembuatan user.

### 3. Avatar dengan Media Library

- `User` akan mengimplementasikan `HasMedia` dan memakai `InteractsWithMedia`.
- Collection `avatar` menyimpan satu file (`singleFile`); upload baru mengganti avatar lama.
- Validasi menerima JPEG, PNG, atau WebP maksimal 2 MB.
- Conversion `avatar-thumb` berukuran 256 x 256 dibuat agar daftar user ringan.
- Response hanya mengirim URL avatar terotorisasi atau fallback inisial; tidak mengirim path storage internal.
- Delete avatar menghapus media collection tanpa menghapus user.

### 4. Verifikasi email dan terakhir login

- `email_verified_at` dimiliki flow Laravel/Fortify. UserManagement hanya membaca serta menampilkan statusnya pada increment ini.
- User baru direct-password dimulai belum terverifikasi. Tidak ada bypass atau tombol admin untuk mengubah verifikasi email.
- Migration UserManagement menambah `users.last_login_at` nullable timestamp.
- Listener autentikasi sukses mencatat timestamp server ke `last_login_at`. Middleware request tidak boleh dipakai agar aktivitas halaman tidak dianggap login baru.
- Event login tidak mencatat password, token, atau payload sensitif ke audit.

## Boundary dan Contract

- Query: perluasan typed `UserData`/read contract untuk avatar URL, role, status, verifikasi email, dan last login.
- Mutation: Action/Command create user diperluas dengan DTO identity dan akses awal. Controller hanya menerima FormRequest dan meneruskan ke Application boundary.
- Lintas module: role list dan penugasan role tetap melalui public `RoleCatalogCapability` dan `RoleAssignmentCapability` AccessControl.
- Domain Event tidak diperlukan untuk render avatar atau detail user.
- Integration Event login hanya ditambah bila consumer AuditLog serta failure contract nyata.
- Queue/Job belum diperlukan. Conversion avatar synchronous untuk batas awal lalu dievaluasi jika volume upload meningkat.

## Acceptance Criteria

- UI tabel dan tiga modal menampilkan data identitas serta akses dengan fallback aman untuk avatar dan last login kosong.
- Backend mengizinkan atau menolak role/status awal berdasarkan permission dan policy, bukan hanya menyembunyikan field frontend.
- Avatar hanya satu per user, tervalidasi, dapat diganti, dan tidak membocorkan path storage.
- User baru tidak otomatis menjadi email verified.
- Login berhasil memperbarui `last_login_at`; login gagal tidak mengubahnya.
- Positive dan negative test, module validation, type/lint/format, PHPStan, dan browser flow untuk modal lulus.

## Non-goal

- Profile atau Employee sebagai data organisasi.
- Invitation email dan email resend.
- Multi-role penuh, revoke role per-item, atau pengelolaan password operator.
- Penyimpanan avatar eksternal/CDN dan responsive images.
