# PenerimaanSantri

Module `Pesantrian/PenerimaanSantri` adalah skeleton awal untuk PPDB /
Penerimaan Santri Baru.

## Status

- Runtime skeleton aktif dan bootable.
- Permission identity awal tersedia:
  - `penerimaan_santri.view`
  - `penerimaan_santri.manage`
  - `penerimaan_santri.decide`
- Data foundation minimum tersedia melalui table `student_admissions` dan model
  `StudentAdmissionRecord`.
- API read/list dan create/update minimum tersedia untuk flow internal/admin.
- API lifecycle decision tersedia untuk verify, accept, reject, dan cancel.
- Audit mutation aman tersedia untuk create/update dan lifecycle decision.
- Contract konversi accepted admission ke Santri sudah tersedia melalui
  `AcceptedAdmissionReader` untuk consumer nyata `Pesantrian/Santri`.
- UI/Inertia read page tersedia di route `pesantrian.admissions.index` dengan
  frontend canonical `resources/js/pages/Pesantrian/PenerimaanSantri/`.
- Menu sidebar namespace `Pesantrian` tersedia untuk actor berizin
  `penerimaan_santri.view`, `penerimaan_santri.manage`, atau
  `penerimaan_santri.decide`.
- UI create/edit pendaftaran internal tersedia untuk actor berizin
  `penerimaan_santri.manage` dan memakai API create/update dengan idempotency
  key.
- UI lifecycle pendaftaran tersedia untuk actor berizin
  `penerimaan_santri.decide` dan memakai API verify/accept/reject/cancel dengan
  idempotency key.
- UI detail pendaftaran tersedia dari daftar PPDB untuk actor berizin view.
- Automated QA UI PPDB sudah mencakup source guard, typecheck, lint, build,
  focused backend tests, dan smoke browser login.

## Acuan

Lihat dokumentasi kerja di `docs/modules/Pesantrian/PenerimaanSantri/`.
