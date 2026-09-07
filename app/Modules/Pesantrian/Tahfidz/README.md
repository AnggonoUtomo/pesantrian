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
- Seeder demo dan UI dikerjakan pada increment berikutnya.
