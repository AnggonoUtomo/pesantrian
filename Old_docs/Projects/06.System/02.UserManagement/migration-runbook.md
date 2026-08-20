# Runbook Migration UserManagement

Dokumen ini dipakai saat migration UserManagement dijalankan pada shared atau
production database. Jangan menjalankan perintah production dari workspace
lokal tanpa backup dan persetujuan operator database.

## Migration yang terlibat

Ada dua jenis perubahan yang perlu dibedakan:

1. `2026_08_04_235959_migrate_legacy_user_ids_to_ulids.php` mengubah database
   release lama dari identifier `BIGINT` menjadi ULID. Relasi session dan
   Passkey dipindahkan melalui pemetaan sementara. Migration ini no-op pada
   fresh install yang sudah memakai ULID.
2. `2026_08_06_000000_add_lifecycle_columns_to_users_table.php` menambahkan
   `users.status`, `users.deleted_at`, dan index terkait setelah identity sudah
   ULID.

Nilai password, remember token, data 2FA, session payload, dan credential
Passkey dipindahkan tanpa dicetak. Nilai tersebut tidak boleh masuk evidence.

## Scope Migration Lanjutan

Rehearsal fresh dan upgrade legacy sudah lulus pada database MySQL lokal yang
terisolasi. Eksekusi shared/production tetap belum dianggap selesai karena
workspace tidak memiliki backup target, bukti restore, atau persetujuan
operator database.

## Urutan aman

1. Pastikan release sama dengan commit yang sudah lulus seluruh required check.
2. Identifikasi tipe `users.id`. Jika masih integer, jadwalkan maintenance
   window dan hentikan seluruh write traffic sebelum melanjutkan.
3. Buat backup database, uji restore ke target terisolasi, lalu catat release
   identifier dan referensi backup tanpa menyalin credential ke log.
4. Jalankan lane upgrade pada salinan database atau fixture yang setara.
5. Periksa migration tanpa menjalankan perubahan:

   ```bash
   php artisan migrate --pretend --env=production
   ```

6. Jalankan migration dengan persetujuan operator:

   ```bash
   php artisan migrate --force --env=production
   ```

7. Verifikasi status migration:

   ```bash
   php artisan migrate:status --env=production
   ```

8. Jalankan verifier upgrade yang disetujui pada target rehearsal. Pada
   production, lakukan pemeriksaan jumlah record dan relasi tanpa menampilkan
   data sensitif.
9. Verifikasi login, logout, session, reset password, Passkey, dan 2FA.
10. Aktifkan kembali write traffic setelah health check lulus.
11. Simpan output status, jumlah record, release identifier, dan referensi
    backup pada catatan deployment.

## Rollback

Upgrade `BIGINT` ke ULID bersifat forward-only. Method `down()` akan menolak
rollback karena ULID baru tidak dapat dipetakan kembali ke angka secara aman.

- Jika migration gagal sebelum traffic dibuka, hentikan release dan pulihkan
  backup yang sudah diuji.
- Jika traffic sudah dibuka atau data ULID baru sudah ditulis, jangan jalankan
  rollback schema. Buat forward-fix yang direview dan pertahankan audit release.
- Migration lifecycle lain hanya boleh di-rollback sesuai urutan migration dan
  setelah dampak data diperiksa. `--step=1` tidak boleh dianggap otomatis
  menunjuk migration identifier.

## Batasan

Workspace memverifikasi fresh install dan upgrade fixture release lama pada
environment testing. Workspace tidak membuktikan ukuran data production,
durasi lock, maintenance window, restore backup nyata, replikasi, atau behavior
provider database terkelola.

## Evidence Rehearsal Lokal

- Fresh rehearsal 10 Agustus 2026 menjalankan `migrate:fresh --seed`, seed
  kedua, rollback migration media terakhir, dan migrate/seed ulang. Seluruh
  migration kembali berstatus `Ran`.
- Upgrade rehearsal 20 Agustus 2026 mengimpor
  `tests/Fixtures/Database/mysql-legacy-bigint.sql`, menjalankan migration dan
  seed dua kali, lalu menjalankan `tools/ci/verify-legacy-user-upgrade.php`.
- Hasil upgrade: dua user, satu session, dan satu Passkey terpelihara sebagai
  ULID; tidak ada relasi yatim atau tabel pemetaan sementara yang tersisa.
- Database rehearsal memakai nama unik, diperiksa agar bukan database default,
  lalu dihapus setelah verifikasi.
- Evidence ini tidak menggantikan backup/restore, pengukuran lock, downtime,
  dan persetujuan operator shared/production.

## Keputusan Terkait

- [ADR-0006: Upgrade Identifier User Lama ke ULID](decisions/ADR-0006-LEGACY-USER-ID-TO-ULID-UPGRADE.md)
