# KelasRombel

Module `Academic/KelasRombel` adalah capability `Kelas / Rombel / Kurikulum`.

Scope runtime sudah memiliki schema inti, model Infrastructure, API read untuk
list/detail rombel, API mutation awal untuk kurikulum, tingkat kelas, rombel,
serta API penempatan/pindah/keluar santri. Public contract readiness sudah
disiapkan pada module pemilik data agar module ini tidak mengambil model
Infrastructure dari `Organization`, `AcademicPeriod`, `Pesantrian/Santri`, atau
`HumanResource` secara langsung.

Endpoint awal:

- `GET /api/v1/academic/class-groups`
- `GET /api/v1/academic/class-groups/{classGroup}`
- `POST /api/v1/academic/class-groups/curricula`
- `PATCH /api/v1/academic/class-groups/curricula/{curriculum}`
- `POST /api/v1/academic/class-groups/levels`
- `PATCH /api/v1/academic/class-groups/levels/{level}`
- `POST /api/v1/academic/class-groups`
- `PATCH /api/v1/academic/class-groups/{classGroup}`
- `POST /api/v1/academic/class-groups/{classGroup}/students`
- `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/transfer`
- `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/remove`

Dokumentasi kerja: `docs/modules/Academic/KelasRombel/`.
