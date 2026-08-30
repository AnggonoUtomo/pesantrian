# Specification: Pesantrian/PenerimaanSantri

## Status

Draft - dokumentasi awal sebelum coding. Module belum dibuat di source runtime.

## Tujuan dan Scope

Bangun module `Pesantrian/PenerimaanSantri` sebagai pintu PPDB / Penerimaan
Santri Baru. Module ini menangani calon santri dari pendaftaran awal sampai
keputusan diterima, ditolak, atau dibatalkan.

Scope slice awal:

- module skeleton dan metadata;
- table calon santri/pendaftaran minimum dengan ULID;
- business identifier `registration_no`;
- data calon santri minimum;
- data wali minimum pada formulir pendaftaran;
- status pendaftaran;
- permission `penerimaan_santri.view`, `penerimaan_santri.manage`, dan
  `penerimaan_santri.decide`;
- read/list pendaftaran;
- create/update pendaftaran minimum;
- lifecycle status pendaftaran minimum;
- audit mutation;
- UI/Inertia read page setelah backend foundation valid.

## Arsitektur

- Hexagon: `app/Modules/Pesantrian/PenerimaanSantri`.
- Inbound adapter:
  - HTTP API;
  - web route Inertia read page pada increment UI.
- Use case awal:
  - `ListPendaftaranSantri`;
  - `CreatePendaftaranSantri`;
  - `UpdatePendaftaranSantri`;
  - `VerifyPendaftaranSantri`;
  - `AcceptPendaftaranSantri`;
  - `RejectPendaftaranSantri`;
  - `CancelPendaftaranSantri`.
- Candidate public contract:
  - `AcceptedAdmissionReader`;
  - `AcceptedAdmissionData`;
  - `ConvertAdmissionToSantri` hanya bila module `Pesantrian/Santri` sudah
    menjadi consumer nyata.
- Outbound port:
  - repository pendaftaran bila query/mutation membutuhkan boundary eksplisit.
- Outbound adapter:
  - Eloquent model/repository di Infrastructure.
- Composition root:
  - `app/Modules/Pesantrian/PenerimaanSantri/ServiceProvider.php`.

Domain dibuat ketika rule lifecycle pendaftaran mulai bernilai. Jika rule masih
CRUD sederhana, implementasi awal boleh berada di Application dengan test yang
ketat.

## Di Luar Scope

- Data induk santri aktif final.
- Master wali santri penuh.
- Akun login wali/santri.
- Invoice resmi, kuitansi, VA, QRIS, payment gateway, rekonsiliasi pembayaran,
  dan tunggakan.
- Upload dan arsip file dokumen lengkap.
- Seleksi kompleks multi-tahap.
- Tes masuk, jadwal wawancara, dan scoring detail.
- Alokasi kelas, rombel, asrama, atau tahfidz final.
- Public registration form tanpa login.
- Export/import data pendaftaran.

## Contract

### Input Pendaftaran Minimum

- `registration_no`: string wajib, unik, dibuat otomatis oleh sistem, contoh
  `SNTR-0001`;
- `registration_period`: string atau reference periode PPDB, opsional pada
  slice awal;
- `candidate_name`: string wajib;
- `candidate_gender`: male/female atau vocabulary final yang disetujui;
- `candidate_birth_place`: string opsional;
- `candidate_birth_date`: date opsional;
- `previous_school`: string opsional;
- `target_unit_id`: ULID opsional ke Organization;
- `guardian_name`: string wajib pada slice awal;
- `guardian_phone`: string opsional;
- `guardian_relation`: ayah/ibu/wali atau vocabulary final yang disetujui;
- `registration_fee_required`: boolean opsional, mengikuti konfigurasi periode
  PPDB;
- `registration_fee_amount`: decimal opsional bila biaya pendaftaran wajib;
- `registration_fee_status`: not_required/pending/verified/rejected;
- `document_checklist`: daftar item dokumen dan status verifikasi minimum;
- `status`: draft/submitted/verified/accepted/rejected/cancelled;
- `notes`: string opsional dan tidak boleh memuat data sensitif berlebihan.

### Output Read/List Minimum

- `id`;
- `registration_no`;
- `candidate_name`;
- `candidate_gender`;
- `target_unit`;
- `guardian_name`;
- `guardian_phone`;
- `status`;
- `registered_at`;
- `decided_at`.

### Failure

- Validasi gagal: `422`.
- Actor tidak punya permission: `403`.
- Record tidak ditemukan: `404`.
- Duplicate `registration_no`: `422`.
- Target unit tidak valid atau tidak aktif: `422`.
- Status transition tidak valid: `422`.

## Lifecycle

Status awal yang disarankan:

```text
draft
submitted
verified
accepted
rejected
cancelled
```

Transisi minimum:

```text
draft -> submitted
submitted -> verified
verified -> accepted
verified -> rejected
draft/submitted/verified -> cancelled
```

Rule awal:

- accepted/rejected/cancelled adalah terminal untuk slice awal;
- pendaftaran accepted tidak otomatis membuat Santri sampai consumer
  `Pesantrian/Santri` tersedia;
- data wali pada PPDB disimpan sebagai snapshot pendaftaran sampai module
  `Pesantrian/WaliSantri` tersedia;
- PPDB awal bersifat internal/admin; public registration form tanpa login
  ditunda sampai flow internal dan security boundary stabil;
- biaya pendaftaran bersifat opsional per periode PPDB. PPDB hanya menyimpan
  status administrasi dan nominal sederhana; invoice resmi dan payment
  lifecycle tetap menjadi ownership `Finance/StudentFinance`;
- dokumen pendaftaran bersifat checklist verifikasi pada baseline awal. Upload
  dan arsip file digital menunggu boundary `Support/Document`;
- perubahan status wajib mencatat actor dan waktu keputusan;
- status transition yang tidak valid ditolak oleh Application/Domain rule.

## Authorization dan Audit

- `penerimaan_santri.view`: membaca daftar/detail pendaftaran.
- `penerimaan_santri.manage`: create/update draft/submitted.
- `penerimaan_santri.decide`: verify/accept/reject/cancel.
- Backend permission wajib dicek di controller atau middleware route.
- Audit mutation minimum:
  - `penerimaan_santri.registration.created`;
  - `penerimaan_santri.registration.updated`;
  - `penerimaan_santri.registration.verified`;
  - `penerimaan_santri.registration.accepted`;
  - `penerimaan_santri.registration.rejected`;
  - `penerimaan_santri.registration.cancelled`.

Metadata audit tidak boleh memuat password, token, credential, atau payload
sensitif. Nomor telepon wali boleh dicatat hanya bila relevan dan tidak
dibocorkan ke log teknis.

## UI

UI awal dibuat setelah backend foundation valid:

- Page canonical:
  `resources/js/pages/Pesantrian/PenerimaanSantri/pages/Index.tsx`.
- Komponen business-specific berada di:
  `resources/js/pages/Pesantrian/PenerimaanSantri/components/`.
- `Index.tsx` wajib minimal sebagai komposer page/layout.
- Routing memakai Ziggy named routes.
- Backend tetap menjadi authority permission.
- Read page minimum mencakup filter search/status/unit, empty state, pagination,
  dan authorization UX.

UI mutation penuh dapat dipisah menjadi increment setelah read page stabil.

## Dependency

- `Organization/Organization`: target unit pendaftaran.
- `HumanResource/HumanResource`: opsional untuk petugas/verifikator bila
  dibutuhkan; tidak wajib pada slice awal karena actor dapat berasal dari user.
- `Pesantrian/WaliSantri`: candidate consumer/promotion data wali setelah module
  tersedia.
- `Pesantrian/Santri`: consumer utama untuk konversi pendaftaran accepted
  menjadi data induk santri.
- `Support/Document`: future dependency untuk lampiran dokumen pendaftaran.
- `Finance/StudentFinance`: future dependency bila biaya PPDB ditagihkan.
- `System/AccessControl`: permission backend.
- `System/AuditLog`: audit recorder existing.

Tidak ada dependency ke Asrama, Academic, Tahfidz, Kedisiplinan, Kesehatan,
atau Keuangan pada slice awal.

## Acceptance Criteria

- [ ] Module `Pesantrian/PenerimaanSantri` dapat dibuat oleh generator dan
  valid.
- [ ] Migration pendaftaran minimum memakai ULID.
- [ ] `registration_no` unik dan stabil sebagai business identifier.
- [ ] Permission `penerimaan_santri.view`, `penerimaan_santri.manage`, dan
  `penerimaan_santri.decide` tersedia.
- [ ] Actor berizin dapat list/create/update pendaftaran minimum.
- [ ] Actor tanpa permission ditolak backend.
- [ ] Duplicate `registration_no` ditolak.
- [ ] Biaya pendaftaran opsional dapat dicatat sebagai status administrasi
  sederhana tanpa membuat invoice Finance.
- [ ] Checklist dokumen pendaftaran minimum dapat dicatat dan diverifikasi
  tanpa upload file.
- [ ] Status transition minimum berjalan dan invalid transition ditolak.
- [ ] Mutation mencatat audit/event aman.
- [ ] Candidate conversion contract ke Santri terdokumentasi tanpa direct access
  ke model Infrastructure.
- [ ] `php artisan module:validate --no-ansi` lulus.
- [ ] Focused tests PenerimaanSantri lulus.

## Keputusan Awal

- Nomor pendaftaran dibuat otomatis oleh sistem. Baseline awal memakai
  konfigurasi nomor di `System/SystemSetting`, contoh prefix `SNTR` dengan
  sequence auto-generate untuk bagian `-xxxx`.
- Data wali pada PPDB disimpan sebagai snapshot pendaftaran dulu. Master
  `Pesantrian/WaliSantri` dibuat/dipromosikan setelah module WaliSantri
  tersedia.
- PPDB release awal adalah flow internal/admin. Public registration form tanpa
  login ditunda sampai validasi, rate limit, anti-spam, dan boundary keamanan
  siap.
- Biaya pendaftaran bersifat opsional per periode PPDB. Pada baseline awal,
  PPDB hanya menyimpan status administrasi dan nominal biaya pendaftaran
  sederhana. Invoice resmi, pembayaran, kuitansi, tunggakan, payment gateway,
  dan rekonsiliasi tetap menjadi ownership `Finance/StudentFinance`.
- Dokumen pendaftaran pada baseline awal berupa checklist verifikasi. Upload
  dan arsip file digital ditunda sampai boundary `Support/Document` atau
  document storage siap.
- Status accepted dapat mensyaratkan data calon lengkap, wali snapshot lengkap,
  dokumen wajib verified, dan biaya pendaftaran verified bila periode PPDB
  mewajibkan biaya.
