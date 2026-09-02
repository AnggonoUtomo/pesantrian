# KelasRombel

Module `Academic/KelasRombel` adalah capability `Kelas / Rombel / Kurikulum`.

Scope runtime sudah memiliki schema inti, model Infrastructure, dan API read
untuk list/detail rombel. Public contract readiness sudah disiapkan pada module
pemilik data agar module ini tidak mengambil model Infrastructure dari
`Organization`, `AcademicPeriod`, `Pesantrian/Santri`, atau `HumanResource`
secara langsung.

Endpoint awal:

- `GET /api/v1/academic/class-groups`
- `GET /api/v1/academic/class-groups/{classGroup}`

Dokumentasi kerja: `docs/modules/Academic/KelasRombel/`.
