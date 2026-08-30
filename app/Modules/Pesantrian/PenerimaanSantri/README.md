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
- Candidate conversion contract ke Santri terdokumentasi; runtime contract
  ditunda sampai `Pesantrian/Santri` tersedia sebagai consumer nyata.
- UI belum dibuat.

## Acuan

Lihat dokumentasi kerja di `docs/modules/Pesantrian/PenerimaanSantri/`.
