# System/SystemSetting

SystemSetting adalah module keempat di dalam boundary `System`. Module ini
menjadi tempat penyimpanan dan aktivasi konfigurasi runtime global yang
tervalidasi, aman, dapat diaudit, dan dapat dibaca module lain melalui public
contract.

## Status

`Selesai dan telah melewati final quality gate`.

Module, persistence, public contract, mutation, audit, command, runtime
consumer, API, frontend vertical slice, dan test SystemSetting sudah tersedia.
Generator aktual menghasilkan `MODULE_CREATED`; registry module dan MySQL lokal
telah diverifikasi.

Kebijakan password runtime tersedia pada kategori Password:
`security.password.min_length`, `require_mixed_case`, `require_numbers`, dan
`require_symbols`. Nilai dibaca backend melalui `PasswordValidationRules` untuk
registration, reset password, dan penggantian password; frontend hanya menjadi
UI administrasi untuk SuperSystem.

Kategori Session mengelola lifetime session, sedangkan kategori Email mengelola
SMTP dan identitas pengirim. Pengelompokan UI diturunkan dari prefix key registry
tanpa mengubah key, contract, maupun nilai backend yang sudah berjalan.

Operator mengubah kategori melalui satu dialog dan satu alasan global. Hanya
nilai yang benar-benar berubah dikirim; seluruh batch divalidasi, diperiksa
konsistensinya, disimpan, dan diaudit secara atomik. Endpoint satu key tetap
tersedia bagi console/API yang sudah ada. Nilai SMTP sensitif tidak ditampilkan
atau diprefill pada form, dan hanya berubah jika operator mengisinya kembali.

Setiap kategori dan nilai aktif juga memiliki panduan operator berbahasa awam:
tujuan pengaturan, cara mengisi beserta contoh, dan peringatan dampak bila
relevan. Key teknis tetap terlihat sebagai referensi, tetapi bukan lagi satu-
satunya penjelasan bagi operator.

## Boundary Module

- Parent boundary: `System`.
- Target code path: `app/Modules/System/SystemSetting`.
- Namespace: `App\Modules\System\SystemSetting`.
- Owner capability: registry schema setting, persistence, validasi, aktivasi,
  safe default, diagnostic, dan tampilan pengaturan runtime global.
- Dependency langsung: `AccessControl` dan `AuditLog`.
- Data owner: tabel `system_settings` dan `idempotency_keys`.

SystemSetting bukan pengganti halaman pengaturan profile atau appearance pribadi
dari starter kit. Nilai branding dan appearance pada SystemSetting adalah
default global. Pilihan pribadi user tetap menjadi override untuk browser/user
tersebut.

## Dokumen di Folder Ini

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR-0001: Boundary, contract, dan audit](decisions/ADR-0001-SYSTEMSETTING-BOUNDARY-AND-CONTRACT.md)
5. [ADR-0002: Aktivasi runtime, cache, dan appearance](decisions/ADR-0002-RUNTIME-ACTIVATION-AND-APPEARANCE.md)
6. [Execution log](planning/execution-log.md)

## Prompt Generator dan Hasil yang Diharapkan

Prompt resmi, dry-run, actual command, expected output, dan struktur yang harus
terbentuk dijelaskan secara lengkap pada `implementation-plan.md` dan
`tasks.md`.

Ringkasan command:

```bash
php artisan module:make SystemSetting --domain=System --profile=default-v1 --dry-run --json
php artisan module:make SystemSetting --domain=System --profile=default-v1 --force --yes --json
```

Dry-run harus menghasilkan `MODULE_PREVIEWED` dan tidak mengubah filesystem.
Pembuatan aktual menghasilkan `MODULE_CREATED`. Struktur hasil generator lalu
dilengkapi secara incremental tanpa mengubah ownership module lain.

## Preflight 6 Agustus 2026

- Mode project: `module extension` pada existing Laravel starter kit.
- Laravel: `13.23.0`.
- PHP: `8.4.16`.
- Database: MySQL.
- Cache, queue, dan session lokal: database driver.
- Frontend: Inertia 3, React 19, TypeScript, Vite, Tailwind CSS, shadcn/ui,
  Framer Motion, dan Ziggy.
- Module valid: AccessControl, UserManagement, dan AuditLog.
- `module:inspect System/SystemSetting --json`: `MODULE_NOT_FOUND`, sesuai
  kondisi awal.
- Dry-run SystemSetting: `MODULE_PREVIEWED`, target
  `app/Modules/System/SystemSetting`, tanpa diagnostic dan tanpa file baru.

## Dokumen Authoritative

- [Aturan agent project](../../../AGENTS.md)
- [Functional requirements](../../../01-REQUIREMENTS/01.01-FUNCTIONAL-REQUIREMENTS.md)
- [Baseline specification](../../../01-REQUIREMENTS/01.05-BASELINE-SPECIFICATION.md)
- [Database design](../../../02-DESIGN/02.02-DATABASE-DESIGN.md)
- [Security design](../../../02-DESIGN/02.03-SECURITY-DESIGN.md)
- [UI/UX guideline](../../../02-DESIGN/02.05-UI-UX-GUIDELINE.md)
- [Folder structure](../../../03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md)
- [Generator specification](../../../03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md)
- [Module contract](../../../03-IMPLEMENTATION/03.07-MODULES.md)
- [Module communication](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Kernel SystemSetting](../../../07-KERNEL/07.07-SYSTEM-SETTING.md)

## Keputusan yang Diterapkan

- Arah dependency `SystemSetting -> AccessControl + AuditLog`.
- Mutation SystemSetting mencatat audit melalui public `AuditRecorder` secara
  synchronous dan fail-closed.
- Setiap audit perubahan membawa kategori dan deskripsi pengaturan yang aman,
  selain nilai sebelum serta sesudah. Nilai sensitif tetap diredaaksi sebelum
  meninggalkan boundary SystemSetting.
- Database menjadi sumber kebenaran. Increment awal memakai memoization per
  request, bukan cache nilai lintas request yang berisiko stale.
- Appearance pribadi tetap menjadi override; SystemSetting hanya menyediakan
  default global.
- Logo dan favicon memakai path aset lokal tervalidasi. Upload/media package
  tidak ditambahkan pada increment awal.

Kedua ADR berstatus `Accepted` pada 6 Agustus 2026.

## Cara Verifikasi Dokumentasi

1. Periksa seluruh tautan relatif pada folder ini.
2. Pastikan tidak ada checklist implementasi yang ditandai selesai sebelum ada
   evidence.
3. Pastikan ADR berstatus `Accepted` dan implementasi tetap sesuai keputusan.
4. Jalankan `git diff --check`.
5. Jalankan pencarian pola mojibake pada seluruh file di folder ini.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan paket dokumentasi pekerjaan SystemSetting |
| 1.1 | 2026-08-06 | Menutup implementasi dan mencatat quality gate SystemSetting |
| 1.2 | 2026-08-10 | Menambah editor kategori atomik dengan satu alasan global |
| 1.3 | 2026-08-10 | Menambah panduan operator untuk seluruh kategori dan nilai aktif |
| 1.4 | 2026-08-10 | Menambah konteks kategori dan nilai perubahan pada Audit Log |
