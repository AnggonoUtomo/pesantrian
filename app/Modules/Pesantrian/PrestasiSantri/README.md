# Prestasi

Module `Pesantrian/PrestasiSantri` mengelola catatan prestasi santri, kategori
prestasi, status pengajuan, verifikasi, dan pembatalan aman.

Dokumentasi kerja aktif:

- `docs/modules/Pesantrian/PrestasiSantri/README.md`
- `docs/modules/Pesantrian/PrestasiSantri/specification.md`
- `docs/modules/Pesantrian/PrestasiSantri/plan.md`
- `docs/modules/Pesantrian/PrestasiSantri/tasks.md`

Increment aktif:

- Increment 2 menyiapkan skeleton module dan permission identity.
- Increment 3 menyiapkan readiness contract ke `ActiveStudentReader`,
  `ActiveEmployeeReader`, dan `ActiveAcademicPeriodReader`.
- Increment 4 menyiapkan table bisnis kategori, catatan prestasi, dan
  revision history.
- Increment 5 menyiapkan API read/list untuk kategori, daftar catatan prestasi,
  dan detail catatan prestasi.
- Increment 6 menyiapkan mutation kategori dan draft prestasi, termasuk
  snapshot santri/pembina/periode aktif, revision history, audit activity, dan
  nomor prestasi otomatis `PRS-xxxxxx`.
- Increment 7 menyiapkan submit, verifikasi, dan minta revisi dengan revision
  history serta audit lifecycle.
- Increment 8 menyiapkan void/pembatalan aman dengan alasan wajib, permission
  sensitif `prestasi_santri.archive`, revision history, audit lifecycle, dan
  tanpa menghapus catatan prestasi.
