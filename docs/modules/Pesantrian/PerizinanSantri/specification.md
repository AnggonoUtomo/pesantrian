# Specification: Pesantrian/PerizinanSantri

## Status

Active - documentation baseline ready. Belum ada source code module yang dibuat
pada increment ini. Dokumen ini menjadi pegangan untuk implementasi skeleton,
data foundation, API, seeder, UI, dan QA browser berikutnya.

## Objective

Membangun module `Pesantrian/PerizinanSantri` untuk mencatat dan mengelola
permohonan izin santri secara internal/admin. Modul harus mudah dipakai operator
pemula, tetapi tetap menjaga validasi backend, permission, audit, dan histori
lifecycle.

## Scope

- Membuat permohonan izin santri.
- Memilih jenis izin dan rentang waktu izin.
- Menyimpan tujuan, alasan, kontak wali snapshot, dan catatan petugas.
- Submit permohonan untuk direview.
- Approve atau reject permohonan dengan alasan/catatan.
- Mencatat santri keluar/check-out.
- Mencatat santri kembali/check-in.
- Membatalkan izin dengan alasan tanpa menghapus data.
- Menampilkan list, detail, filter, dan ringkasan izin.
- Menyediakan demo seeder untuk uji lifecycle.
- Menyediakan UI Inertia untuk operator internal.

## Non-Scope

- Public form tanpa login.
- Portal wali/santri.
- Tanda tangan digital.
- Approval bertingkat kompleks.
- Notifikasi WhatsApp/SMS/email otomatis.
- Upload lampiran surat izin/surat sakit.
- Rekam medis klinik.
- Otomatis membuat entry PresensiSantri.
- Otomatis membuat pelanggaran kedisiplinan.
- Denda/tagihan karena terlambat kembali.
- Laporan BI kompleks.

## Actor

- SuperSystem.
- Operator Pesantrian.
- Musyrif/pembina asrama yang diberi permission.
- Petugas atau approver yang diberi permission.
- Auditor/viewer yang diberi permission baca.

## Use Case Baseline

### Membuat Permohonan Izin

Operator memilih santri aktif, jenis izin, rentang waktu, tujuan, alasan, dan
kontak wali snapshot bila ada. Permohonan awal dapat disimpan sebagai `draft`.

### Submit Permohonan

Operator mengajukan permohonan draft menjadi `submitted` agar bisa direview.

### Review Izin

Petugas berwenang menyetujui (`approved`) atau menolak (`rejected`) permohonan.
Keputusan wajib meninggalkan actor, waktu, dan catatan.

### Check-out Santri

Jika izin disetujui dan santri benar-benar keluar/pulang, petugas mencatat
status `checked_out`.

### Check-in / Kembali

Ketika santri kembali, petugas mencatat waktu kembali aktual, kondisi kembali,
dan catatan bila terlambat atau perlu tindak lanjut.

### Membatalkan Izin

Permohonan yang salah input atau tidak jadi dipakai dapat diubah menjadi `void`
dengan alasan. Data tidak dihapus permanen pada baseline.

### Rekap Izin

Operator melihat daftar dan ringkasan izin berdasarkan tanggal, status, jenis
izin, santri, dan keterlambatan kembali.

## Candidate Data Model

### `student_permits`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `permit_no` | Nomor izin unique, contoh `IZN-000001` |
| `student_id` | Rujukan santri dari public contract |
| `student_no` | Snapshot NIS |
| `student_name` | Snapshot nama santri |
| `permit_type` | `leave`, `home_visit`, `sick`, atau `activity` |
| `starts_at` | Waktu mulai izin |
| `ends_at` | Batas waktu kembali |
| `destination` | Tujuan izin, nullable |
| `reason` | Alasan izin |
| `guardian_name` | Snapshot wali, nullable |
| `guardian_phone` | Snapshot kontak wali, nullable |
| `status` | Status lifecycle izin |
| `submitted_at` | Nullable |
| `submitted_by` | Nullable actor id |
| `reviewed_at` | Nullable |
| `reviewed_by` | Nullable actor id |
| `review_note` | Catatan approve/reject |
| `checked_out_at` | Nullable |
| `checked_out_by` | Nullable actor id |
| `returned_at` | Nullable |
| `returned_by` | Nullable actor id |
| `return_note` | Catatan kembali |
| `voided_at` | Nullable |
| `voided_by` | Nullable actor id |
| `void_reason` | Nullable |
| `created_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `student_permit_revisions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `permit_id` | FK ke `student_permits` |
| `reason` | Alasan perubahan lifecycle/koreksi |
| `changed_by` | Actor id |
| `changed_at` | Timestamp perubahan |
| `summary` | Ringkasan perubahan tanpa payload sensitif berlebihan |

## Rule Baseline

- Hanya santri aktif yang boleh dibuatkan izin baru.
- `starts_at` harus sebelum `ends_at`.
- Izin tidak boleh overlap untuk santri yang sama pada status aktif
  (`submitted`, `approved`, `checked_out`).
- Submit hanya dari `draft`.
- Approve/reject hanya dari `submitted`.
- Check-out hanya dari `approved`.
- Return/check-in hanya dari `checked_out`.
- Void wajib alasan dan tidak menghapus data.
- Izin `rejected`, `returned`, dan `void` dianggap final untuk lifecycle
  baseline.
- Status returned yang melewati `ends_at` harus bisa ditandai terlambat pada
  read model, tetapi tidak otomatis membuat pelanggaran.
- Backend menjadi authority status, permission, dan validasi. Frontend hanya
  membantu UX.

## Permit Type Baseline

| Type | Nama tampil | Catatan |
| --- | --- | --- |
| `leave` | Izin keluar | Keluar area pesantren sementara. |
| `home_visit` | Izin pulang | Pulang ke rumah/wali dengan batas kembali. |
| `sick` | Izin sakit | Izin karena sakit; bukan rekam medis. |
| `activity` | Izin kegiatan | Kegiatan khusus/lomba/acara keluarga. |

## Integration Candidate

| Consumer | Kebutuhan | Status baseline |
| --- | --- | --- |
| PresensiSantri | Membaca izin approved agar entry bisa diberi status izin | Ditunda sampai PerizinanSantri punya data/API stabil |
| KesehatanSantri | Membaca izin sakit atau rujukan klinik | Ditunda sampai module kesehatan dibuat |
| KedisiplinanSantri | Membaca keterlambatan kembali | Ditunda; tidak otomatis membuat pelanggaran |
| Notification | Mengirim info approval ke wali/operator | Ditunda |
| Document | Lampiran surat izin/sakit | Ditunda |

## Permission Candidate

- `perizinan_santri.view`
- `perizinan_santri.manage`
- `perizinan_santri.approve`
- `perizinan_santri.checkout`
- `perizinan_santri.return`
- `perizinan_santri.archive`

Permission dapat disederhanakan saat implementasi bila terlalu granular, tetapi
backend tetap menjadi sumber otorisasi.

## Audit Candidate

- `perizinan_santri.permit.created`
- `perizinan_santri.permit.updated`
- `perizinan_santri.permit.submitted`
- `perizinan_santri.permit.approved`
- `perizinan_santri.permit.rejected`
- `perizinan_santri.permit.checked_out`
- `perizinan_santri.permit.returned`
- `perizinan_santri.permit.voided`

Audit menyimpan ringkasan perubahan dan correlation id, bukan payload lengkap
yang tidak perlu.

## API Candidate

Endpoint API internal mengikuti envelope API canonical:

- `GET /api/v1/pesantrian/student-permits`
- `GET /api/v1/pesantrian/student-permits/{permit}`
- `POST /api/v1/pesantrian/student-permits`
- `PATCH /api/v1/pesantrian/student-permits/{permit}`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/submit`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/approve`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/reject`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/checkout`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/return`
- `PATCH /api/v1/pesantrian/student-permits/{permit}/void`

Route web Inertia memakai nama `pesantrian.student-permits.*` agar Ziggy stabil
di frontend.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/PerizinanSantri/`.

Acceptance UI awal:

- index tetap minimal dan delegasi ke komponen;
- list mendukung search/filter tanggal, jenis izin, status, dan pagination;
- detail menampilkan lifecycle, data santri, waktu izin, dan catatan;
- form mutation berada di folder `components`;
- approve/reject/check-out/return/void memakai confirmation atau dialog alasan;
- browser QA desktop/mobile wajib dilakukan.

## Demo Seeder Candidate

Demo seeder wajib idempotent dan tidak berjalan di `production`.

Data demo minimum:

- izin pulang approved;
- izin keluar submitted;
- izin sakit rejected;
- izin checked_out;
- izin returned, termasuk satu contoh terlambat;
- izin void;
- variasi santri aktif dan petugas approver.

## Open Questions

- Format nomor izin final akan memakai setting global apa? Kandidat awal:
  `IZN-xxxx`.
- Apakah approval cukup satu level dulu, atau perlu role approver khusus per
  unit/asrama?
- Apakah izin sakit baseline boleh dibuat dari operator biasa, atau harus
  menunggu module Kesehatan/Klinik untuk validasi klinik?
- Saat integrasi dengan PresensiSantri dibuat, apakah status izin otomatis
  mengisi presensi atau hanya menjadi saran/kandidat?
