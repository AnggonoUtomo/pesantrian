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
- Increment 3 memastikan contract lintas module untuk santri aktif dan
  pembina/petugas aktif tersedia tanpa coupling ke Infrastructure module
  dependency, melalui query `ListDisciplineStudentCandidates` dan
  `ListDisciplineOfficerCandidates`.
- Increment 4 menyediakan table foundation `student_discipline_categories`,
  `student_discipline_cases`, `student_discipline_revisions`, record model,
  factory minimum, dan migration loading dari ServiceProvider.
- Increment 5 menyediakan API baca kategori, list kasus dengan filter dan
  pagination, detail kasus dengan lifecycle/action/resolution/revision history,
  serta port `StudentDisciplineReadRepository`.
- Increment 6 menyediakan mutation kategori: create, update, archive, request
  validation, audit activity, dan route idempotent untuk operasi tulis.
- Increment 7 menyediakan mutation kasus awal: create draft, update draft,
  submit draft, snapshot santri/kategori/pembina, revision history, audit
  activity, dan nomor kasus otomatis `DIS-xxxxxx`.
- Increment 8 menyediakan review kasus submitted menjadi `in_review` dan
  penetapan tindakan pembinaan menjadi `action_assigned`, termasuk actor,
  timestamp lifecycle, revision, audit, dan validasi pembina aktif.
- Increment 9 menyediakan penyelesaian kasus `action_assigned` menjadi
  `resolved` dan pembatalan kasus non-final menjadi `void` tanpa menghapus
  data, termasuk actor, timestamp lifecycle, revision, dan audit.
- Increment 10 menyediakan demo seeder idempotent untuk kategori dan kasus
  lifecycle `draft`, `submitted`, `in_review`, `action_assigned`, `resolved`,
  dan `void`.
- Increment 11 menyediakan UI/Inertia read-only untuk list dan detail kasus,
  filter tanggal/status/tingkat/kategori/search, summary cards, pagination,
  sidebar menu Pesantrian, dan whitelist route Ziggy.
- Increment 12 menyediakan UI mutation untuk create/update draft, submit,
  review, assign action, resolve, dan void melalui dialog permission-aware
  dengan redirect/flash Inertia dari route web.
