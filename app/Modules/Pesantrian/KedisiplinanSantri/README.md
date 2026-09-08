# Pelanggaran / Kedisiplinan

Module `Pesantrian/KedisiplinanSantri` mengelola catatan pelanggaran,
kategori, poin/tingkat, tindakan pembinaan, penyelesaian, dan histori
kedisiplinan santri.

Dokumentasi kerja aktif:

- `docs/modules/Pesantrian/KedisiplinanSantri/README.md`
- `docs/modules/Pesantrian/KedisiplinanSantri/specification.md`
- `docs/modules/Pesantrian/KedisiplinanSantri/plan.md`
- `docs/modules/Pesantrian/KedisiplinanSantri/tasks.md`

Increment aktif:

- Increment 2 menyediakan skeleton module, permission identity, route
  placeholder, dan wiring permission ke seeder AccessControl.
- Increment 3 berikutnya memastikan contract lintas module untuk santri aktif
  dan pembina/petugas aktif tersedia tanpa coupling ke Infrastructure module
  dependency.
