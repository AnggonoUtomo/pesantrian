# Tasks: Pesantrian/PenerimaanSantri

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Focused test atau cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan roadmap module dibaca.
- [x] Coding ditahan sampai user menginstruksikan mulai coding lagi.
- [x] Keputusan awal ditentukan: nomor pendaftaran auto-generate, wali snapshot
  dulu, PPDB public form ditunda.
- [x] Keputusan biaya dan dokumen ditentukan: biaya pendaftaran opsional
  dicatat sederhana di PPDB; dokumen pendaftaran berupa checklist verifikasi
  tanpa upload file pada baseline awal.

## Increment 1: Dokumentasi module

- [x] Buat README module.
  - Acceptance: boundary, public boundary, data, permission, audit, operasi,
    dan verifikasi utama tertulis.
  - Verification: `git diff --check`.
- [x] Buat specification module.
  - Acceptance: scope, non-scope, contract, lifecycle, authorization, audit,
    dependency, acceptance criteria, dan risiko terbuka tertulis.
  - Verification: `git diff --check`.
- [x] Buat implementation plan module.
  - Acceptance: increment skeleton, data foundation, backend behavior,
    lifecycle, audit, candidate conversion contract, dan UI read page tersusun
    berurutan.
  - Verification: `git diff --check`.

## Increment 2: Skeleton module

- [x] Dry-run generator.
  - Acceptance: target `app/Modules/Pesantrian/PenerimaanSantri`, diagnostics
    kosong, dan tidak ada folder optional placeholder.
  - Verification:
    `php artisan module:make Pesantrian PenerimaanSantri --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Pesantrian PenerimaanSantri --force --yes --no-ansi`.
- [x] Validasi module registry.
  - Acceptance: semua module valid.
  - Verification: `php artisan module:validate --no-ansi`.

## Increment 3: Data foundation

- [x] Tambahkan migration pendaftaran/calon santri.
  - Acceptance: ULID primary key, unique `registration_no`, candidate identity,
    wali snapshot, target unit, status, administrasi biaya sederhana, checklist
    dokumen, dan timestamp tersedia.
  - Verification: focused migration/model tests.
- [x] Tambahkan persistence minimum.
  - Acceptance: model/repository hanya dibuat bila dipakai oleh use case.
  - Verification: focused unit/feature tests.
- [x] Tambahkan permission identity awal.
  - Acceptance: `penerimaan_santri.view`, `penerimaan_santri.manage`, dan
    `penerimaan_santri.decide` valid.
  - Verification: focused permission identity test.
  - Catatan: permission identity awal sudah diselesaikan pada Increment 2.

## Increment 4: Backend read/list dan create/update minimum

- [x] Tambahkan read/list pendaftaran.
  - Acceptance: actor dengan `penerimaan_santri.view` dapat membaca daftar
    pendaftaran.
  - Verification: focused feature test.
- [x] Tambahkan create/update pendaftaran.
  - Acceptance: actor dengan `penerimaan_santri.manage` dapat membuat dan
    mengubah pendaftaran; `registration_no` dibuat otomatis dan tetap unik.
  - Verification: focused feature test.
- [x] Tambahkan field administrasi biaya pendaftaran sederhana.
  - Acceptance: PPDB dapat mencatat biaya tidak wajib/wajib, nominal, dan status
    administrasi tanpa membuat invoice Finance.
  - Verification: focused feature test.
- [x] Tambahkan checklist dokumen pendaftaran minimum.
  - Acceptance: PPDB dapat mencatat status item dokumen dan verifikasi tanpa
    upload file.
  - Verification: focused feature test.
- [x] Tambahkan authorization failure coverage.
  - Acceptance: actor tanpa permission mendapat response forbidden.
  - Verification: focused feature test.

## Increment 5: Lifecycle status pendaftaran

- [ ] Tambahkan verify pendaftaran.
  - Acceptance: actor dengan `penerimaan_santri.decide` dapat mengubah
    submitted menjadi verified.
  - Verification: focused lifecycle feature test.
- [ ] Tambahkan accept/reject pendaftaran.
  - Acceptance: actor dengan `penerimaan_santri.decide` dapat menerima atau
    menolak pendaftaran verified.
  - Verification: focused lifecycle feature test.
- [ ] Tambahkan cancel pendaftaran.
  - Acceptance: draft/submitted/verified dapat dibatalkan sesuai rule awal.
  - Verification: focused lifecycle feature test.
- [ ] Jaga terminal state.
  - Acceptance: accepted/rejected/cancelled tidak bisa dimutasi melalui
    transition yang tidak valid.
  - Verification: focused lifecycle feature test.

## Increment 6: Audit mutation

- [ ] Tambahkan audit create/update pendaftaran.
  - Acceptance: mutation menghasilkan audit entry/event yang aman.
  - Verification: focused audit test.
- [ ] Tambahkan audit lifecycle pendaftaran.
  - Acceptance: verify/accept/reject/cancel menghasilkan audit entry/event yang
    aman.
  - Verification: focused audit test.

## Increment 7: Candidate conversion contract ke Santri

- [ ] Dokumentasikan query/contract accepted admission.
  - Acceptance: contract menjelaskan input/output/failure tanpa mengekspos model
    Infrastructure.
  - Verification: `git diff --check`.
- [ ] Implementasikan contract hanya jika consumer `Pesantrian/Santri`
  disetujui.
  - Acceptance: consumer memakai DTO/query public boundary, bukan Eloquent model
    PenerimaanSantri.
  - Verification: focused contract/query tests.

## Increment 8: UI/Inertia read page

- [ ] Tambahkan route web Inertia PenerimaanSantri.
  - Acceptance: actor dengan `penerimaan_santri.view` dapat membuka halaman
    pendaftaran; actor tanpa permission ditolak backend.
  - Verification: focused presentation test.
- [ ] Tambahkan frontend module canonical.
  - Acceptance: page berada di
    `resources/js/pages/Pesantrian/PenerimaanSantri/pages/Index.tsx`, komponen
    business-specific berada di
    `resources/js/pages/Pesantrian/PenerimaanSantri/components/`, dan
    `Index.tsx` tetap minimal.
  - Verification: `npm run types:check`, `npm run lint:check`,
    dan `npm run build`.
- [ ] Tambahkan menu sidebar namespace Pesantrian.
  - Acceptance: menu PPDB muncul untuk actor berizin
    `penerimaan_santri.view`, `penerimaan_santri.manage`, atau
    `penerimaan_santri.decide`.
  - Verification: focused sidebar/Ziggy tests.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
