# Presensi Santri

Module `Pesantrian/PresensiSantri` mencatat sesi presensi dan detail
kehadiran santri untuk konteks kelas/rombel, asrama, dan kegiatan umum
pesantren.

Dokumentasi kerja aktif:

- `docs/modules/Pesantrian/PresensiSantri/README.md`
- `docs/modules/Pesantrian/PresensiSantri/specification.md`
- `docs/modules/Pesantrian/PresensiSantri/plan.md`
- `docs/modules/Pesantrian/PresensiSantri/tasks.md`

Increment aktif:

- Increment 11 sudah menutup baseline operasional: skeleton module, permission
  identity, readiness contract kandidat presensi, migration, record model,
  factory minimum, API read/list/detail, mutation, lifecycle submit/revisi/void,
  demo seeder, UI Inertia, dan QA browser desktop/mobile.
- Integrasi read-only awal ke Perizinan Santri sudah tersedia melalui public
  contract PerizinanSantri, tetapi entry presensi belum otomatis berubah menjadi
  izin. Relasi otomatis ke Kesehatan/Klinik dan Pelanggaran/Kedisiplinan belum
  dibuat karena module tersebut belum tersedia.
