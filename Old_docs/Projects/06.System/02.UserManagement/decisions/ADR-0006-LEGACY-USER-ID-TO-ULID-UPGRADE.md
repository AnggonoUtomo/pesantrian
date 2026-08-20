# ADR-0006 - Upgrade Identifier User Lama ke ULID

## Status

`Accepted - 20 Agustus 2026.`

## Context

Release awal pada commit `430212f` membuat `users.id`, `sessions.user_id`,
`passkeys.id`, dan `passkeys.user_id` sebagai angka `BIGINT`. Baseline sekarang
mewajibkan ULID. Fresh install sudah membuat identifier ULID, tetapi database
yang berasal dari release awal tetap membutuhkan jalur upgrade data.

Mengubah migration awal tidak memperbaiki database yang sudah berjalan. Jalur
upgrade juga tidak boleh memutus relasi session dan Passkey atau menghasilkan
pemetaan ULID kembali ke angka yang tidak deterministik.

## Keputusan

1. Migration
   `2026_08_04_235959_migrate_legacy_user_ids_to_ulids.php` menjadi jalur
   upgrade forward-only untuk database MySQL lama.
2. Fresh install yang sudah memakai ULID melewati migration ini tanpa perubahan.
3. Upgrade membuat pemetaan sementara untuk identifier user dan Passkey,
   membangun ulang tabel dengan identifier ULID, lalu memindahkan relasi session
   dan Passkey berdasarkan pemetaan tersebut.
4. Migration memeriksa jumlah record, relasi yatim, dan tabel sementara sebelum
   dinyatakan selesai.
5. Write traffic harus dihentikan selama upgrade. Backup yang sudah diuji
   restore wajib tersedia sebelum migration dijalankan.
6. `down()` tidak mengubah ULID kembali menjadi `BIGINT`. Jika upgrade gagal,
   operator memulihkan backup. Jika aplikasi sudah kembali menerima write,
   pemulihan dilakukan melalui forward-fix yang direview.

## Alasan

- Satu identity canonical menghindari dual identifier pada model dan public API.
- Forward-only mencegah pemetaan balik yang dapat salah dan merusak relasi.
- No-op pada fresh install menjaga satu rangkaian migration untuk instalasi baru
  dan upgrade historis.
- Pemetaan sementara membuat perubahan relasi dapat diverifikasi sebelum tabel
  lama diganti.

## Konsekuensi

- Upgrade legacy membutuhkan maintenance window dan backup/restore procedure.
- Rollback Artisan tidak tersedia untuk perubahan identifier ini.
- Database selain MySQL yang masih memakai identifier angka ditolak. Operator
  harus membuat prosedur khusus dan ADR pengganti.
- Fixture release lama dan lane CI MySQL wajib dipertahankan selama jalur upgrade
  ini masih didukung.

## Alternatif yang Ditolak

- Mengubah migration awal saja: tidak memengaruhi database existing.
- Mempertahankan `BIGINT` bersama ULID: memperbesar public contract dan risiko
  salah memakai identifier.
- Membuat `down()` ULID ke angka: tidak ada pemetaan balik yang aman setelah
  record baru dibuat.

## Evidence

- `tests/Fixtures/Database/mysql-legacy-bigint.sql` merekonstruksi schema release
  awal dengan dua user, satu session, dan satu Passkey.
- Rehearsal MySQL lokal terisolasi menjalankan migration dan global seeder dua
  kali. `tools/ci/verify-legacy-user-upgrade.php` membuktikan dua user, satu
  session, dan satu Passkey tetap terhubung sebagai ULID.
- Job `mysql-upgrade` pada `.github/workflows/tests.yml` mengulang upgrade dan
  hanya menyimpan schema tanpa data selama 14 hari.
- Persetujuan arah forward-only diberikan user pada 20 Agustus 2026.
