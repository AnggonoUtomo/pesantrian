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
- Increment 7 menyiapkan API approve/reject, validasi decision, revision
  decision, dan audit approve/reject.
- Increment 8 menyiapkan API check-out, return/check-in, void, validasi status
  lifecycle operasional, revision, summary keterlambatan return, dan audit
  check-out/return/void.
- Increment 9 menyiapkan demo seeder idempotent untuk status
  `draft/submitted/approved/rejected/checked_out/returned/void`, snapshot wali,
  actor demo, dan revision history demo.
- UI dan integrasi awal ke PresensiSantri dikerjakan pada increment berikutnya.
