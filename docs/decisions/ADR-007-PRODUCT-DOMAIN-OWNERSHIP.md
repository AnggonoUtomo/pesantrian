# ADR-007: Domain Ownership Laboratory Compliance Platform

## Status

Accepted

## Tanggal

2026-08-20

## Konteks

Draft arsitektur Laboratory Compliance Platform mengelompokkan seluruh module
di bawah segment `Platform` dan pada bagian lain masih memakai placeholder
literal `namespace`. Starter13 sudah memakai struktur
`app/Modules/{Domain}/{Module}` dan memiliki empat module fondasi aktif di
domain `System`.

Mengganti module fondasi atau memasukkan semua capability ke domain generik
akan mengaburkan ownership serta menambah migrasi yang tidak memberi nilai
produk.

## Keputusan

- Pertahankan `System/AccessControl`, `System/UserManagement`,
  `System/AuditLog`, dan `System/SystemSetting`.
- Tempatkan capability struktur organisasi pada domain `Organization`.
- Tempatkan operasi pengujian dan kontrol sumber daya laboratorium pada domain
  `Laboratory`.
- Tempatkan standard, evidence, dan capability compliance pada domain
  `Compliance`.
- Jangan gunakan `Platform` atau `namespace` sebagai domain source aplikasi.
- Module baru tetap mengikuti `app/Modules/{Domain}/{Module}` dan hanya dibuat
  setelah specification-nya disetujui.

Pemetaan awal module tercatat dalam `../PRODUCT-BASELINE.md` dan
`../MODULES.md`. Boundary detail dapat disempurnakan melalui ADR baru tanpa
memindahkan module secara spekulatif.

## Alternatif

- Semua module di `Platform`: ditolak karena segment tersebut tidak menjelaskan
  ownership bisnis dan bertentangan dengan pembagian domain starter13.
- Memindahkan module fondasi dari `System`: ditolak karena module sudah aktif,
  valid, dan dapat digunakan sebagai capability publik.
- Satu domain per module: ditolak karena menambah segment tanpa memperjelas
  bounded area.

## Konsekuensi

- Positif: module fondasi dapat dipakai ulang tanpa migrasi namespace.
- Positif: dependency dan dokumentasi pekerjaan dapat dibaca berdasarkan area
  bisnis.
- Negatif: alur end-to-end akan melintasi beberapa domain dan membutuhkan
  contract publik yang kecil serta eksplisit.
- Negatif: boundary `AuditLog` dan `AuditTrail` harus diputuskan sebelum
  capability compliance tersebut diimplementasikan.

## Verifikasi

- Module baru berada pada salah satu domain yang disetujui.
- `module.json`, namespace, path, dan provider konsisten dengan domainnya.
- Tidak ada source module pada `app/Modules/Platform` atau
  `app/Modules/namespace`.
- Dependency lintas module hanya melalui public boundary yang diizinkan.

## Referensi

- [Product Baseline](../PRODUCT-BASELINE.md)
- [Arsitektur](../ARCHITECTURE.md)
- [Struktur Folder](../FOLDER-STRUCTURE.md)
- [Daftar Modul](../MODULES.md)
