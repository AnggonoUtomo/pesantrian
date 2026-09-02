# KelasRombel

Module `Academic/KelasRombel` adalah capability `Kelas / Rombel / Kurikulum`.

Scope runtime sudah memiliki schema inti, model Infrastructure, API read untuk
list/detail rombel, dan API mutation awal untuk kurikulum, tingkat kelas, serta
rombel. Public contract readiness sudah disiapkan pada module pemilik data agar
module ini tidak mengambil model Infrastructure dari `Organization`,
`AcademicPeriod`, `Pesantrian/Santri`, atau `HumanResource` secara langsung.

Endpoint awal:

- `GET /api/v1/academic/class-groups`
- `GET /api/v1/academic/class-groups/{classGroup}`
- `POST /api/v1/academic/class-groups/curricula`
- `PATCH /api/v1/academic/class-groups/curricula/{curriculum}`
- `POST /api/v1/academic/class-groups/levels`
- `PATCH /api/v1/academic/class-groups/levels/{level}`
- `POST /api/v1/academic/class-groups`
- `PATCH /api/v1/academic/class-groups/{classGroup}`

Dokumentasi kerja: `docs/modules/Academic/KelasRombel/`.
