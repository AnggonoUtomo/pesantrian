# Runbook Upgrade Schema Integer ke ULID

Status: Siap untuk rehearsal  
Owner: Release owner dan DevOps  
Scope: database existing yang masih memakai integer

## Tujuan

Runbook ini mengubah identifier integer menjadi ULID tanpa kehilangan data dan
tanpa membuat relasi role, permission, user, passkey, atau session terputus.
Runbook ini belum dijalankan pada shared environment karena database tersebut
belum tersedia pada sesi kerja ini.

## Tabel yang terdampak

- `users.id`
- `sessions.user_id`
- `passkeys.id` dan `passkeys.user_id`
- `jobs.id`
- `roles.id` dan `permissions.id`
- `model_has_roles.role_id` dan `model_has_roles.model_id`
- `model_has_permissions.permission_id` dan `model_has_permissions.model_id`
- `role_has_permissions.role_id` dan `role_has_permissions.permission_id`

## Syarat sebelum eksekusi

- Backup PostgreSQL selesai dan restore ke database rehearsal lulus.
- Queue dan scheduler dihentikan atau di-drain.
- Maintenance window dan downtime disetujui.
- Aplikasi lama dan baru kompatibel dengan fase expand.
- Jumlah record setiap tabel dicatat sebelum migration.
- Tidak ada migration lain yang berjalan bersamaan.

## Prosedur rehearsal

1. Clone backup ke database rehearsal.
2. Simpan mapping immutable untuk setiap tabel yang memiliki ID:
   `legacy_integer_id` dan `new_ulid`.
3. Tambahkan kolom ULID sementara pada tabel parent.
4. Isi ULID dengan generator database atau aplikasi, lalu pastikan unik.
5. Tambahkan kolom ULID sementara pada seluruh child dan pivot.
6. Isi kolom child melalui mapping parent. Jangan menebak mapping berdasarkan
   urutan record.
7. Validasi jumlah record, uniqueness, dan seluruh foreign key hasil mapping.
8. Ganti foreign key dan primary key secara terkontrol dalam maintenance window.
9. Ubah konfigurasi/model aplikasi ke ULID.
10. Jalankan `php artisan migrate:status`, test schema, smoke test login,
    profile, passkey, queue, role, permission, dan authorization.
11. Simpan checksum schema, jumlah record, migration status, dan hasil smoke
    test sebagai release evidence.

## Validasi wajib

- Tidak ada nilai ULID kosong atau duplikat.
- Jumlah row sebelum dan sesudah sama.
- Semua child memiliki parent yang valid.
- `User` dapat login dan memperbarui profile.
- Role dan permission dapat dibaca serta di-assign.
- `SuperSystem` tetap terlindungi policy.
- Queue dapat membuat dan memproses job.
- Tidak ada integer ID yang tersisa pada tabel dalam scope.

## Rollback dan forward-fix

Rollback aplikasi tidak otomatis membatalkan perubahan database. Jika validasi
gagal setelah key diganti, hentikan traffic dan restore backup yang sudah diuji.
Jika restore tidak dipilih, gunakan forward-fix yang mengembalikan relasi dari
mapping immutable. Jangan menghapus mapping atau histori permission.

## Go / No-go

Go hanya jika backup restore, mapping, foreign key, jumlah record, smoke test,
dan owner approval semuanya tersedia. No-go jika ada data orphan, mapping
tidak lengkap, backup tidak dapat direstore, atau aplikasi lama belum kompatibel.
