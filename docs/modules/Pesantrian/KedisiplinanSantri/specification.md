# Specification: Pesantrian/KedisiplinanSantri

## Status

Active - documentation baseline ready. Belum ada source code module yang dibuat
pada increment ini. Dokumen ini menjadi pegangan untuk implementasi skeleton,
data foundation, API, seeder, UI, dan QA browser berikutnya.

## Objective

Membangun module `Pesantrian/KedisiplinanSantri` untuk mencatat dan mengelola
pelanggaran/kedisiplinan santri secara internal/admin. Modul harus membantu
operator pemula mencatat kasus dengan struktur yang jelas, sekaligus menjaga
permission, audit, histori lifecycle, dan batas akses data.

## Scope

- Membuat catatan pelanggaran santri.
- Memilih kategori pelanggaran.
- Menentukan severity dan poin pelanggaran bila dipakai.
- Menyimpan kronologi, tanggal/waktu kejadian, lokasi, dan catatan awal.
- Submit catatan agar resmi masuk penanganan.
- Review atau validasi catatan oleh pembina/petugas berwenang.
- Menentukan tindakan pembinaan atau sanksi edukatif.
- Menyelesaikan kasus dengan catatan penyelesaian.
- Membatalkan catatan dengan alasan tanpa menghapus data.
- Menampilkan list, detail, filter, dan ringkasan kedisiplinan.
- Menyediakan demo seeder untuk uji lifecycle.
- Menyediakan UI Inertia untuk operator internal.

## Non-Scope

- Public form tanpa login.
- Portal wali/santri.
- Konseling psikologis mendalam.
- Rekam medis atau catatan kesehatan.
- Otomatis membuat pelanggaran dari PresensiSantri.
- Otomatis membuat pelanggaran dari PerizinanSantri.
- Denda/tagihan otomatis.
- Notifikasi WhatsApp/SMS/email otomatis.
- Upload lampiran bukti/surat kompleks.
- Poin pelanggaran bertingkat kompleks dengan rule naik status otomatis.
- Laporan BI kompleks.

## Actor

- SuperSystem.
- Operator Pesantrian.
- Musyrif/pembina asrama yang diberi permission.
- Guru atau wali kelas yang diberi permission.
- Petugas/pembina kedisiplinan yang diberi permission.
- Auditor/viewer yang diberi permission baca.

## Use Case Baseline

### Membuat Catatan Pelanggaran

Operator memilih santri aktif, kategori, severity, waktu kejadian, lokasi,
kronologi, dan catatan awal. Catatan dapat disimpan sebagai `draft`.

### Submit Catatan

Operator mengajukan catatan draft menjadi `submitted` agar bisa ditangani oleh
pembina/petugas berwenang.

### Review Catatan

Pembina memeriksa catatan, mengklarifikasi bila perlu, lalu mengubah status
menjadi `in_review`. Review meninggalkan actor, waktu, dan catatan.

### Menentukan Tindak Lanjut

Petugas menentukan tindakan pembinaan atau sanksi edukatif. Tindakan dapat
berupa nasihat, tugas pembinaan, pemanggilan wali, pengawasan khusus, atau
tindak lanjut lain yang disepakati pesantren.

### Menyelesaikan Kasus

Ketika tindakan selesai, petugas menutup kasus sebagai `resolved` dengan catatan
penyelesaian. Histori tetap tersimpan.

### Membatalkan Catatan

Catatan yang salah input atau tidak valid dapat diubah menjadi `void` dengan
alasan. Data tidak dihapus permanen pada baseline.

### Rekap Kedisiplinan

Operator melihat daftar dan ringkasan berdasarkan tanggal, status, severity,
kategori, santri, unit, dan pembina.

## Candidate Data Model

### `student_discipline_categories`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `code` | Kode kategori unique, contoh `TERLAMBAT` |
| `name` | Nama kategori tampil |
| `description` | Deskripsi singkat, nullable |
| `default_severity` | `minor`, `moderate`, atau `major` |
| `default_points` | Nullable; poin default bila pesantren memakai poin |
| `status` | `active` atau `archived` |
| `created_at`, `updated_at` | Timestamp standar |

### `student_discipline_cases`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `case_no` | Nomor kasus unique, contoh `DIS-000001` |
| `student_id` | Rujukan santri dari public contract |
| `student_no` | Snapshot NIS |
| `student_name` | Snapshot nama santri |
| `unit_id` | Nullable/snapshot unit bila tersedia |
| `unit_name` | Nullable/snapshot nama unit |
| `category_id` | FK ke `student_discipline_categories` |
| `category_name` | Snapshot nama kategori |
| `severity` | `minor`, `moderate`, atau `major` |
| `points` | Nullable |
| `occurred_at` | Waktu kejadian |
| `location` | Lokasi kejadian, nullable |
| `description` | Kronologi/catatan kejadian |
| `reported_by` | Nullable actor id |
| `assigned_employee_id` | Nullable pembina/petugas |
| `assigned_employee_name` | Snapshot nama pembina/petugas |
| `status` | Status lifecycle kasus |
| `submitted_at` | Nullable |
| `reviewed_at` | Nullable |
| `reviewed_by` | Nullable actor id |
| `review_note` | Catatan review |
| `action_plan` | Tindakan pembinaan/sanksi edukatif |
| `action_assigned_at` | Nullable |
| `resolved_at` | Nullable |
| `resolved_by` | Nullable actor id |
| `resolution_note` | Catatan penyelesaian |
| `voided_at` | Nullable |
| `voided_by` | Nullable actor id |
| `void_reason` | Alasan void |
| `created_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `student_discipline_revisions`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `case_id` | FK ke `student_discipline_cases` |
| `reason` | Alasan perubahan lifecycle/koreksi |
| `changed_by` | Actor id |
| `changed_at` | Timestamp perubahan |
| `summary` | Ringkasan perubahan tanpa payload sensitif berlebihan |

## Rule Baseline

- Hanya santri aktif yang boleh dibuatkan catatan baru.
- Kategori aktif wajib dipilih.
- Severity wajib salah satu dari `minor`, `moderate`, atau `major`.
- Poin boleh null, tetapi jika diisi tidak boleh negatif.
- `occurred_at` tidak boleh jauh melewati waktu sekarang.
- Submit hanya dari `draft`.
- Review hanya dari `submitted`.
- Penentuan tindakan hanya dari `submitted` atau `in_review`.
- Resolve hanya boleh jika tindakan/catatan penyelesaian tersedia.
- Void wajib alasan dan tidak menghapus data.
- Catatan `resolved` dan `void` dianggap final untuk lifecycle baseline.
- Backend menjadi authority status, permission, dan validasi. Frontend hanya
  membantu UX.

## Category Baseline

Kategori demo awal:

| Code | Nama tampil | Severity default | Poin default |
| --- | --- | --- | --- |
| `TERLAMBAT` | Terlambat kegiatan | `minor` | 5 |
| `TANPA_IZIN` | Meninggalkan kegiatan tanpa izin | `moderate` | 15 |
| `TATA_TERTIB` | Melanggar tata tertib | `minor` | 10 |
| `KAMAR_ASRAMA` | Pelanggaran asrama/kamar | `moderate` | 15 |
| `BERAT` | Pelanggaran berat | `major` | 50 |

Kategori final boleh disesuaikan saat implementasi, tetapi harus tetap
terstruktur agar laporan dan pembinaan tidak bergantung pada teks bebas.

## Integration Candidate

| Source/Consumer | Kebutuhan | Status baseline |
| --- | --- | --- |
| Santri | Membaca santri aktif dan snapshot NIS/nama | Dibutuhkan sejak awal |
| HumanResource | Membaca pembina/petugas aktif | Dibutuhkan saat assignment/review |
| PresensiSantri | Membaca alfa/terlambat sebagai kandidat kasus | Ditunda; tidak otomatis membuat pelanggaran |
| PerizinanSantri | Membaca terlambat kembali sebagai kandidat kasus | Ditunda; tidak otomatis membuat pelanggaran |
| WaliSantri | Menampilkan kontak wali untuk tindak lanjut | Ditunda sampai master wali dibuat |
| PembinaanSantri | Menerima tindak lanjut konseling/pembinaan | Ditunda sampai module pembinaan dibuat |
| Notification | Mengirim info ke wali/petugas | Ditunda |
| Document | Lampiran bukti | Ditunda |

## Contract Readiness

- Kandidat santri aktif memakai contract
  `App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader`.
- Kandidat pembina/petugas aktif memakai contract
  `App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader`.
- Module tidak membaca model Infrastructure milik Santri, HumanResource,
  PresensiSantri, atau PerizinanSantri secara langsung.
- Public contract keluar dari KedisiplinanSantri belum dibuat pada baseline
  dokumentasi karena belum ada consumer nyata.

## Permission Candidate

- `kedisiplinan_santri.view`
- `kedisiplinan_santri.manage`
- `kedisiplinan_santri.review`
- `kedisiplinan_santri.resolve`
- `kedisiplinan_santri.archive`

Permission dapat disederhanakan saat implementasi bila terlalu granular, tetapi
backend tetap menjadi sumber otorisasi.

## Audit Candidate

- `kedisiplinan_santri.case.created`
- `kedisiplinan_santri.case.updated`
- `kedisiplinan_santri.case.submitted`
- `kedisiplinan_santri.case.reviewed`
- `kedisiplinan_santri.case.action_assigned`
- `kedisiplinan_santri.case.resolved`
- `kedisiplinan_santri.case.voided`
- `kedisiplinan_santri.category.created`
- `kedisiplinan_santri.category.updated`
- `kedisiplinan_santri.category.archived`

Audit menyimpan ringkasan perubahan dan correlation id, bukan payload lengkap
yang tidak perlu.

## API Candidate

Endpoint API internal mengikuti envelope API canonical:

- `GET /api/v1/pesantrian/student-discipline-categories`
- `POST /api/v1/pesantrian/student-discipline-categories`
- `PATCH /api/v1/pesantrian/student-discipline-categories/{category}`
- `PATCH /api/v1/pesantrian/student-discipline-categories/{category}/archive`
- `GET /api/v1/pesantrian/student-discipline-cases`
- `GET /api/v1/pesantrian/student-discipline-cases/{case}`
- `POST /api/v1/pesantrian/student-discipline-cases`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}/submit`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}/review`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}/assign-action`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}/resolve`
- `PATCH /api/v1/pesantrian/student-discipline-cases/{case}/void`

Route web Inertia memakai nama `pesantrian.student-discipline.*` agar Ziggy
stabil di frontend.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/KedisiplinanSantri/`.

Acceptance UI awal:

- index tetap minimal dan delegasi ke komponen;
- list mendukung search/filter tanggal, status, severity, kategori, dan
  pagination;
- detail menampilkan santri, kategori, kronologi, lifecycle, tindakan, dan
  riwayat perubahan;
- form mutation berada di folder `components`;
- review/assign action/resolve/void memakai confirmation atau dialog alasan;
- browser QA desktop/mobile wajib dilakukan.

## Demo Seeder Candidate

Demo seeder wajib idempotent dan tidak berjalan di `production`.

Data demo minimum:

- kategori pelanggaran aktif;
- catatan draft;
- catatan submitted;
- catatan in_review;
- catatan action_assigned;
- catatan resolved;
- catatan void;
- variasi severity ringan/sedang/berat;
- variasi santri aktif dan pembina/petugas.

## Open Questions

- Format nomor kasus final akan memakai setting global apa? Kandidat awal:
  `DIS-xxxx`.
- Apakah sistem poin langsung dipakai di UI awal atau cukup disimpan sebagai
  field opsional dulu?
- Apakah kategori pelanggaran boleh diedit bebas oleh operator, atau hanya
  SuperSystem/admin tertentu?
- Apakah kasus resolved boleh dibuka ulang, atau koreksi dibuat sebagai catatan
  baru/revisi saja?
