# Temuan Evaluasi: AuditLog

## Evidence Review

- `php artisan module:inspect System/AuditLog --json` menghasilkan module valid tanpa diagnostic.
- `php artisan test --filter=AuditLog` lulus: 23 test dan 180 assertion.
- Browser `http://starter13.test/system/audit-logs` pada desktop light memuat summary, filter, tabel, pagination, dan detail action.
- Integration event AccessControl serta UserManagement memiliki nama dan versi yang diperiksa listener. Metadata memakai allowlist, redaksi recursive, batas depth, dan batas ukuran.

## Required 01 — Free-text reason belum terlindungi dari secret

- Kondisi awal: `MetadataRedactor` meredaksi nilai metadata berdasarkan nama key sensitif seperti `password`, `token`, dan `secret`. `reason` pada record audit hanya dibersihkan dari HTML, karakter kontrol, dan dipotong panjangnya.
- Temuan: `sanitizeReason()` tetap menyimpan isi reason apa adanya. Reason impersonation berasal dari input user; operator dapat tidak sengaja memasukkan token, password, atau credential ke histori append-only.
- Dampak: aturan project yang melarang sensitive payload pada log dapat dilanggar oleh input valid. Data sulit diperbaiki karena audit memang append-only.
- Rekomendasi: tetapkan policy reason bebas rahasia melalui ADR; implementasikan detector/pattern redaction minimum dan pesan UI yang jelas. Jangan menyimpan nilai secret asli; audit cukup menyimpan marker redaksi atau menolak input yang cocok dengan pola credential.
- Owner: AuditLog untuk sanitasi persistence; UserManagement untuk validasi UX pada reason impersonation; SystemSetting bukan owner secret audit.
- Acceptance implementasi nanti: test reason berisi password, bearer token, API key, dan cookie; database serta response audit tidak memuat nilai asli.

## Required 02 — Append-only belum menjadi kontrol storage yang kuat

- Kondisi awal: `AuditRecord` menolak `update()` dan `delete()` melalui model event, dan application module tidak menyediakan route mutation record.
- Temuan: model memakai `$guarded = []`; mass update/delete Eloquent dan akses database writer dapat melewati model event. Tidak ada restriction database atau prosedur operasi yang membatasi writer audit.
- Dampak: append-only hanya terjamin pada jalur model normal. Code masa depan atau credential database aplikasi yang terlalu luas dapat mengubah/menghapus histori tanpa audit.
- Rekomendasi: pilih kontrol sesuai environment melalui ADR operasi: akun database runtime minimum privilege, pemisahan credential writer bila tersedia, dan larangan mass mutation pada architecture test/static scan. Database trigger hanya dipakai bila kebutuhan compliance memang mengharuskan enforcement storage.
- Acceptance implementasi nanti: documented privilege/rehearsal untuk production, architecture test untuk melarang mutation massal di code application, dan test normal model tetap immutable.

## Optional 01 — Field filter memiliki id dan name

- Kondisi awal: halaman AuditLog menyediakan input module, action, tanggal mulai, dan tanggal selesai.
- Kondisi perbaikan: empat field filter memakai `id` dan `name` unik.
- Perubahan: `AuditLogFilterBar.tsx` memakai `id` unik dan `name` pada seluruh
  field filter yang relevan.
- Evidence penutupan: Chrome DevTools memuat `/system/audit-logs`,
  accessibility tree mengenali seluruh field filter, dan console tidak
  memiliki error atau warning.
- Status: ditutup.

## Optional 02 — Retensi satu tahun belum memiliki lifecycle operation

- Kondisi awal: `module.php` menetapkan `retention_days` sebesar 365 dan baseline meminta penyimpanan minimal satu tahun.
- Temuan: belum ditemukan command/job, policy purge, legal hold, atau test yang membuktikan record tidak dipangkas sebelum batas retensi.
- Dampak: angka konfigurasi belum cukup menjadi proses operasi ketika volume audit bertambah.
- Rekomendasi: jangan menghapus record otomatis saat ini. Siapkan feature retention terpisah yang memerlukan keputusan legal/compliance, dry-run report, backup, dan approval operasi.
- Owner: AuditLog bersama operasi deployment.

## FYI — Boundary utama sudah sehat

- Query memakai pagination dan scope backend: SuperSystem melihat seluruh audit; auditor lain hanya record miliknya.
- Consumer integration event fail-closed; test membuktikan mutation role rollback bila storage audit gagal.
- API dan Inertia hanya menyediakan read flow. Tidak ada route update atau delete audit record.

## Status Rekomendasi

Required 01 ditutup pada increment `01.security-hardening`: reason yang tampak
seperti credential ditolak sebelum persistence. Required 02 ditingkatkan pada
code boundary dengan allowlist fillable dan architecture test; hardening hak
database production tetap membutuhkan ADR/runbook operasi.
