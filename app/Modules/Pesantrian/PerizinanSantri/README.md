# Perizinan Santri

Module `Pesantrian/PerizinanSantri` mengelola permohonan izin santri,
approval, check-out, return/check-in, pembatalan/arsip, dan histori izin.

Dokumentasi kerja aktif:

- `docs/modules/Pesantrian/PerizinanSantri/README.md`
- `docs/modules/Pesantrian/PerizinanSantri/specification.md`
- `docs/modules/Pesantrian/PerizinanSantri/plan.md`
- `docs/modules/Pesantrian/PerizinanSantri/tasks.md`

Increment aktif:

- Increment 2 menyiapkan skeleton module dan permission identity.
- Increment 3 menyiapkan readiness contract ke `ActiveStudentReader`,
  `ActiveEmployeeReader`, dan snapshot wali utama dari Santri.
- Increment 4 menyiapkan table bisnis `student_permits`,
  `student_permit_revisions`, record model, dan factory minimum.
- Increment 5 menyiapkan API read/list/detail internal dengan filter,
  pagination, summary keterlambatan, dan revision history.
- Increment 6 menyiapkan API create draft, update draft, submit, revision
  koreksi/submit, validasi santri aktif, validasi rentang, rule overlap izin
  aktif, dan audit create/update/submit.
- Approval, check-out/return, demo seeder, UI, dan integrasi awal ke
  PresensiSantri dikerjakan pada increment berikutnya.
