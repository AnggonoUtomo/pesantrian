# Laboratory Compliance & Evidence Platform

## Masalah

Laboratorium membutuhkan hubungan yang dapat ditelusuri antara aktivitas
pengujian, metode, personel, alat, sampel, hasil, laporan, dan evidence.
Rekaman yang terpisah menyulitkan validasi kesiapan dan pembuktian compliance.

## Tujuan

Membangun platform operasi laboratorium multi-scope berbasis SNI ISO/IEC 17025
yang menghasilkan evidence compliance dari proses operasional. Starter13 tetap
menjadi fondasi teknis reusable, bukan identitas produk akhir.

## Pengguna

- Administrator aplikasi.
- Manajer laboratorium, manajer teknis, dan quality manager.
- Petugas penerimaan sampel, analis, dan teknisi laboratorium.
- Reviewer, approver, dan auditor yang diizinkan.
- Developer, QA, DevOps, dan agent yang mengembangkan platform.

## Scope aktif

- Framework reusable pada `packages/StarterKit`.
- DDD-lite Modular Monolith pada `app/Modules`.
- Modul System: AccessControl, UserManagement, AuditLog, dan SystemSetting.
- Domain bisnis: Organization, Laboratory, dan Compliance.
- Operasi multi-scope dari konfigurasi metode hingga evidence dan laporan.
- API v1, frontend Inertia/React, serta quality gate lokal dan CI.

## Di luar scope

- AI dan intelligence pada Release 1.
- Workflow sertifikasi SNI ISO/IEC 17065 secara penuh.
- Integrasi eksternal dan abstraction tanpa consumer nyata.

## Kriteria keberhasilan

- Satu pengujian dapat ditelusuri dari sampel hingga report dan evidence.
- Histori resmi tidak berubah ketika master data diperbarui.
- Permission aplikasi dan authorization teknis sama-sama ditegakkan di backend.
- Scope kedua dapat ditambahkan sebagai data tanpa module produk baru.
- Module dapat ditemukan, divalidasi, diuji, dan ditinjau melalui UI.

Detail produk dan batas Release 1 berada di [Product Baseline](PRODUCT-BASELINE.md).
