# System/AuditLog

Dokumen alur code setelah implementasi tersedia pada
[03-1.AuditLog-code-flow](../03-1.AuditLog-code-flow/README.md).

AuditLog adalah module ketiga di dalam boundary `System`. Module ini menyimpan
aktivitas keamanan dan bisnis penting secara append-only agar perubahan dapat
ditelusuri tanpa membuka data sensitif.

## Status

`Selesai dan terverifikasi` pada 6 Agustus 2026.

Persetujuan user pada 6 Agustus 2026 mencakup specification, implementation
plan, tasks, dan ADR pada folder ini. Task 01 sampai Task 11 telah ditutup
berdasarkan test, browser test, MySQL migration, dan full CI.

## Boundary Module

- Parent boundary: `System`.
- Target code path: `app/Modules/System/AuditLog`.
- Namespace: `App\Modules\System\AuditLog`.
- Owner capability: pencatatan audit, redaksi metadata, pembacaan ter-scope,
  retensi minimum, dan public recording contract.
- Dependency: public authorization capability `AccessControl` serta public
  integration event `AccessControl` dan `UserManagement`.

AuditLog tidak mengambil alih role, user, session impersonation, atau model
private module lain.

## Kemampuan Awal

- menyimpan audit record dengan ULID;
- menolak update dan delete melalui model/repository aplikasi;
- menghapus metadata yang tidak diizinkan dan menyamarkan key sensitif;
- mencatat `event_id` serta `correlation_id` untuk idempotency dan penelusuran;
- menampilkan audit milik actor sendiri kepada auditor biasa;
- menampilkan seluruh audit kepada `SuperSystem`;
- menerima integration event synchronous dari AccessControl dan UserManagement;
- mencatat login sukses, logout, reset password sukses, dan verifikasi email;
- menyediakan halaman list, filter, pagination, detail modal, state kosong,
  loading, error, responsive, dan route Ziggy.

Workspace mengikuti komposisi visual UserManagement: ringkasan, shortcut
mandiri, pesan error halaman, lalu satu card riwayat aktivitas. Penyelarasan ini
memindahkan pemilih jumlah baris dari card filter ke footer tabel, berdampingan
dengan informasi halaman dan navigasi. Scope query, nilai pagination, dan detail
audit tetap sama.

UI Audit Log hanya menerima informasi yang dapat dipahami operator: actor,
aktivitas, subject, module, waktu, dan alasan. ULID, correlation ID, serta
metadata mentah tetap tersedia pada contract API internal untuk penelusuran
teknis, tetapi tidak dikirim ke halaman Inertia. Kolom waktu dapat diurutkan
dari terbaru atau terlama; default-nya terbaru.

Untuk perubahan SystemSetting, UI menerima ringkasan khusus yang aman: kategori
seperti `Pagination` atau `Email`, nama pengaturan berbahasa operator, serta
nilai sebelum dan setelah perubahan. Nilai SMTP atau nilai sensitif lain tetap
ditampilkan sebagai `Disamarkan`. Record lama yang hanya menyimpan `setting_key`
diterjemahkan dengan fallback baseline yang sama, sehingga riwayat tidak kembali
menampilkan key teknis.

Action, subject, dan module pada halaman diterjemahkan ke bahasa operator,
misalnya `system_setting.updated` menjadi `Pengaturan sistem diperbarui`.
Untuk autentikasi dan perubahan keamanan tertentu, dialog detail dapat
menampilkan ringkasan browser serta alamat IP yang disamarkan. User-agent
mentah, password, token, cookie, dan session ID tidak disimpan atau dikirim ke
UI.

## Urutan Baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR boundary dan retensi](decisions/ADR-0001-AUDITLOG-BOUNDARY-AND-RETENTION.md)
5. [ADR ingestion event](decisions/ADR-0002-SYNCHRONOUS-AUDIT-INGESTION.md)
6. [ADR minimasi context keamanan](decisions/ADR-0003-SECURITY-CONTEXT-MINIMIZATION.md)
7. [Execution log](planning/execution-log.md)

## Dokumen Terkait

- [Functional requirements](../../../01-REQUIREMENTS/01.01-FUNCTIONAL-REQUIREMENTS.md)
- [Database design](../../../02-DESIGN/02.02-DATABASE-DESIGN.md)
- [Security design](../../../02-DESIGN/02.03-SECURITY-DESIGN.md)
- [Module structure](../../../03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md)
- [Module contract](../../../03-IMPLEMENTATION/03.07-MODULES.md)
- [Test plan](../../../03-IMPLEMENTATION/03.10-TEST-PLAN.md)
- [Communication pattern](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [UserManagement](../02.UserManagement/README.md)

## Prompt Generator dan Hasil yang Diharapkan

Prompt, dry-run, command aktual, dan expected output ditulis lengkap pada
`implementation-plan.md` dan `tasks.md`. Dry-run harus menghasilkan
`MODULE_PREVIEWED` tanpa perubahan filesystem. Pembuatan aktual harus
menghasilkan `MODULE_CREATED` dengan profile `default-v1`.

## Cara Verifikasi

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/AuditLog --json
php artisan test --filter=AuditLog
npm run types:check
npm run lint
npm run build
composer ci:check
```

## Revision History

| Versi | Tanggal    | Perubahan                                                                        |
| ----- | ---------- | -------------------------------------------------------------------------------- |
| 1.0   | 2026-08-06 | Menetapkan scope, boundary, urutan baca, dan verifikasi AuditLog                 |
| 1.1   | 2026-08-06 | Menutup implementasi AuditLog dengan evidence backend, frontend, browser, dan CI |
| 1.2   | 2026-08-10 | Menyelaraskan komposisi workspace dengan UserManagement                          |
| 1.3   | 2026-08-10 | Memindahkan pemilih jumlah baris ke footer tabel                                 |
| 1.4   | 2026-08-10 | Menyembunyikan identifier teknis dari UI dan menambah sorting waktu              |
| 1.5   | 2026-08-10 | Menambah label operator serta konteks autentikasi yang diminimalkan              |
| 1.6   | 2026-08-10 | Menambah ringkasan perubahan SystemSetting yang aman dan mudah ditelusuri        |
