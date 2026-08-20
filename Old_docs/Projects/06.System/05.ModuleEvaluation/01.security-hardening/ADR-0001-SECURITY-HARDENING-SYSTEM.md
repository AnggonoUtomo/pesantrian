# ADR-0001 - Hardening Security System

## Status

`Accepted - 10 Agustus 2026.`

## Context

Evaluasi ulang menemukan status user belum menjadi security boundary login,
vocabulary permission AccessControl tidak selaras, dan free-text reason dapat
menyimpan secret pada AuditLog yang append-only.

## Keputusan

1. Fortify hanya menerima user berstatus `active` dan tidak soft-deleted. Pesan penolakan tetap netral seperti kredensial salah.
2. Middleware web memeriksa user terautentikasi pada request berikutnya. Jika status bukan `active` atau user diarsipkan, sesi di-logout dan request tidak boleh meneruskan akses area terautentikasi.
3. Impersonation hanya dapat menarget user `active`, tidak diarsipkan, dan bukan `SuperSystem`.
4. `access_control.role.manage` digunakan untuk membuat atau menghapus role. `access_control.permission.assign` digunakan untuk sinkronisasi permission pada role. `access_control.permission.manage` dideprekasi dari runtime sampai pemilik capability katalog permission nyata tersedia.
5. Reason yang sesuai pola secret ditolak sebelum record AuditLog dibuat. Nilai secret tidak disamarkan lalu disimpan karena audit bersifat append-only.
6. Model/repository AuditLog hanya mendukung create/read pada application code. Pencegahan mass mutation di source ditambah melalui test arsitektur. Hak database minimum tetap ditetapkan melalui runbook deployment.

## Konsekuensi

- User nonaktif dapat keluar pada request web berikutnya; tidak ada janji revocation instan untuk koneksi yang tidak membuat request.
- Operator harus memakai permission assign khusus untuk mengubah permission role.
- Input reason sensitif menghasilkan validation/error aman dan tidak ada audit record baru.
- Hardening database writer production tidak dapat dibuktikan dari workspace; menjadi risiko operasi dengan owner DevOps.
