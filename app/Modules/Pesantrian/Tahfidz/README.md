# Tahfidz

Module `Pesantrian/Tahfidz` mengelola program, target, setoran, murojaah,
review, dan progres hafalan santri.

Dokumentasi kerja aktif:

- `docs/modules/Pesantrian/Tahfidz/README.md`
- `docs/modules/Pesantrian/Tahfidz/specification.md`
- `docs/modules/Pesantrian/Tahfidz/plan.md`
- `docs/modules/Pesantrian/Tahfidz/tasks.md`

Increment aktif:

- Increment 2 menyediakan skeleton module, permission identity, route placeholder,
  dan wiring permission ke seeder AccessControl.
- Increment 3 memastikan contract lintas module untuk santri aktif, pembimbing
  aktif, dan periode aktif sudah tersedia tanpa coupling ke Infrastructure
  module dependency.
- Increment 4 menyediakan table foundation, record model, dan factory minimum
  untuk program, target, setoran/murojaah, dan histori revisi.
- Increment 5 menyediakan read query, repository, resource, request filter, dan
  API baca untuk list/detail setoran tahfidz.
- Increment 6 menyediakan request validation, Application action, API mutation,
  snapshot santri/periode, dan audit untuk program serta target hafalan.
- Increment 7 menyediakan request validation, Application action, API mutation,
  snapshot santri/pembimbing, dan audit untuk setoran hafalan baru serta
  murojaah.
- Increment 8 menyediakan lifecycle review accepted/needs_revision, void wajib
  alasan, revision history, dan audit lifecycle setoran.
- Increment 9 menyediakan `TahfidzDemoSeeder` idempotent yang dipanggil dari
  `DatabaseSeeder`, berisi program, target, setoran accepted/submitted/
  needs_revision/void, murojaah, dan revision history.
- Increment 10 menyediakan route web Inertia, page list/detail, komponen
  filter/table/card/summary/pagination, dan menu sidebar namespace Pesantrian
  untuk `Tahfidz / Hafalan`.
- Increment 11 menyediakan route web mutation dan dialog UI untuk buat/ubah
  program, buat/ubah target, buat/ubah setoran, review, dan void setoran.
- Increment 12 menyediakan browser QA desktop/mobile untuk list, filter, detail,
  dialog lifecycle utama, accessibility gate, dan console bersih; user manual
  lifecycle juga sudah diperbarui.
