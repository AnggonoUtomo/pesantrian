# Implementation Plan - Identity, Akses CRUD, dan Avatar

## Preflight Sebelum Coding

- Jalankan `php artisan module:inspect System/UserManagement`.
- Jalankan `php artisan module:discover --json` dan `php artisan module:validate System/UserManagement --json`.
- Cocokkan `User`, UserManagement read contract, AccessControl public capability, AuditLog event consumer, migration users, dan route auth.
- Pastikan versi Media Library kompatibel dengan PHP 8.4 dan Laravel 13 sebelum dependency ditambahkan.

## Urutan Increment

### INC-001 - Kontrak data dan visual identitas - selesai

Kondisi awal: list/detail hanya memiliki data dasar. Perubahan: `UserData`, repository, resource, type TypeScript, tabel, dan modal detail membawa role efektif, `avatarUrl`, `emailVerified`, serta `lastLoginAt`. Avatar dan last login memakai fallback `null`; media dan migration belum dibuat. Alasan: operator perlu konteks sebelum mutation. Acceptance: seluruh state null terbaca dan aksi lama tidak hilang. Evidence: test presentasi baru serta `composer ci:check` lulus; browser runtime belum tersedia pada sesi ini.

### INC-002 - Akses awal saat membuat user

Kondisi awal: create hanya menerima nama, email, dan password. Perubahan: DTO, FormRequest, Action/Command, policy, dan modal tambah menerima role/status awal sesuai capability. Alasan: akses awal efisien tetapi tidak boleh menjadi privilege escalation. Acceptance: actor berwenang berhasil; actor tanpa hak dan role `SuperSystem` ditolak; kegagalan membatalkan mutation. Verifikasi: positive/negative feature test, transaction test, browser test.

### INC-003 - Media Library avatar

Kondisi awal: package dan media schema belum ada. Perubahan: install package, publish config/migration, register collection User, upload/remove Action, validasi, conversion, dan UI avatar. Alasan: lifecycle file terkelola. Acceptance: satu avatar tervalidasi dapat upload/ganti/hapus dan URL/path internal tidak bocor. Verifikasi: filesystem fake, media test, migration fresh, browser upload/error file.

### INC-004 - Aktivitas autentikasi

Kondisi awal: tidak ada timestamp login. Perubahan: migration `last_login_at`, listener successful login, perluasan query/detail, dan badge aktivitas. Alasan: konteks aktivitas tanpa mengubah security boundary autentikasi. Acceptance: login sukses memperbarui timestamp; login gagal tidak; data kosong memiliki fallback. Verifikasi: event/listener feature test, migration test, browser detail.

### INC-005 - Quality checkpoint dan dokumentasi

Kondisi awal: semua slice telah lolos focused test. Perubahan: quality gate, module validation, browser accessibility, dan dokumen. Alasan: menjaga traceability. Acceptance: tidak ada Open Risk implementasi tanpa owner atau keputusan. Verifikasi: `composer ci:check`, validation module, browser flow.

## Rollback Trace

- Migration `last_login_at` memiliki rollback sendiri sebelum deployment shared/production.
- Avatar memakai collection terpisah sehingga penghapusan avatar tidak mengubah data identitas user.
- Dependency package, config, dan migration dicatat dalam satu increment commit agar dapat ditelusuri.
