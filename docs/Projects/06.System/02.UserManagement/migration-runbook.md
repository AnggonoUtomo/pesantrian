# Runbook Migration UserManagement

Dokumen ini dipakai saat migration UserManagement dijalankan pada shared atau
production database. Jangan menjalankan perintah production dari workspace
lokal tanpa backup dan persetujuan operator database.

## Migration yang terlibat

Migration `2026_08_06_000000_add_lifecycle_columns_to_users_table` menambahkan:

- `users.status` dengan default `active`;
- `users.deleted_at` yang nullable;
- index untuk kedua kolom tersebut.

Migration tidak mengubah `users.id`, password, token, 2FA, atau tabel Passkey.

## Scope Migration Lanjutan

Migration shared/production belum dianggap selesai. Sebelum dijalankan pada
environment bersama atau production, harus ada rehearsal pada database yang
menyerupai target, backup yang dapat dipulihkan, pemeriksaan lock/downtime, dan
persetujuan operator. Workspace lokal tidak memiliki database production,
backup nyata, atau kewenangan untuk membuktikan langkah tersebut.

## Urutan aman

1. Pastikan release yang akan dijalankan sama dengan commit yang sudah lulus CI.
2. Buat backup database dan catat waktu serta lokasi backup.
3. Periksa migration tanpa menjalankan perubahan:

   ```bash
   php artisan migrate --pretend --env=production
   ```

4. Jalankan migration dengan persetujuan operator:

   ```bash
   php artisan migrate --force --env=production
   ```

5. Verifikasi status migration:

   ```bash
   php artisan migrate:status --env=production
   ```

6. Verifikasi aplikasi dapat login dan fitur Passkey/2FA tetap berjalan.
7. Simpan output status dan referensi backup pada catatan deployment.

## Rollback

Rollback hanya boleh dilakukan setelah dampak dan backup dikonfirmasi:

```bash
php artisan migrate:rollback --step=1 --force --env=production
```

Rollback migration ini menghapus `status` dan `deleted_at`. Jangan melakukan
rollback jika aplikasi sudah menulis data lifecycle baru tanpa rencana restore.

## Batasan

Workspace ini hanya memverifikasi fresh migration, upgrade simulation, dan
rollback pada environment testing. Eksekusi database shared/production tetap
memerlukan akses operator, backup nyata, dan persetujuan deployment.
