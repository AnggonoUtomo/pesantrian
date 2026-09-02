# KelasRombel

Module `Academic/KelasRombel` adalah capability `Kelas / Rombel / Kurikulum`.

Scope runtime sudah memiliki schema inti, model Infrastructure, API read untuk
list/detail rombel, API mutation awal untuk kurikulum, tingkat kelas, rombel,
API penempatan/pindah/keluar santri, API wali kelas, serta archive/restore
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
- `POST /api/v1/academic/class-groups/{classGroup}/students`
- `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/transfer`
- `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/remove`
- `POST /api/v1/academic/class-groups/{classGroup}/homerooms`
- `PATCH /api/v1/academic/class-groups/{classGroup}/homerooms/{homeroom}/end`
- `PATCH /api/v1/academic/class-groups/{classGroup}/archive`
- `PATCH /api/v1/academic/class-groups/{classGroup}/restore`

Dokumentasi kerja: `docs/modules/Academic/KelasRombel/`.
