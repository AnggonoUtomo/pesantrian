# Specification: Pesantrian/Santri

## Status

Active - UI create/update/convert. Schema, model Infrastructure, factory
minimum, API list/detail, create/update manual, konversi PPDB accepted melalui
public contract, lifecycle status, archive, restore, UI Inertia baca
daftar/detail, form create/update, dan aksi konversi PPDB accepted sudah
tersedia.

## Objective

Membangun module `Pesantrian/Santri` sebagai master data santri yang menjadi
rujukan operasional pesantren setelah calon santri diterima atau didaftarkan
secara administratif oleh admin.

## Scope

- Mencatat data induk santri minimum.
- Membuat nomor induk santri otomatis.
- Menyimpan link asal PPDB bila santri berasal dari pendaftaran accepted.
- Menyimpan snapshot wali minimum.
- Mengelola status lifecycle santri.
- Menyediakan read boundary ringkas untuk modul consumer.
- Menulis audit untuk mutasi signifikan.

## Non-Scope

- Portal publik wali atau santri.
- Master wali reusable penuh.
- Tagihan dan pembayaran santri.
- Penempatan kelas, rombel, asrama, atau kurikulum.
- Presensi, perizinan, kedisiplinan, tahfidz, kesehatan, pembinaan, prestasi,
  dan alumni detail.
- Upload dokumen atau file lampiran.

## Actor

- Admin pesantren.
- Operator PPDB.
- Operator data santri.
- Kepala unit atau staf yang diberi permission baca data santri.

## Use Case Baseline

### List dan Search Santri

Operator dapat melihat daftar santri, mencari berdasarkan NIS/nama, dan
memfilter status lifecycle serta unit organisasi.

### Detail Santri

Operator dapat membuka detail identitas, status, unit, asal PPDB, dan wali
snapshot minimum.

### Buat Santri dari PPDB Accepted

Operator memilih pendaftaran berstatus `accepted`, lalu sistem membuat data
induk santri. Proses ini idempotent: satu admission hanya boleh menghasilkan
satu santri.

### Buat Santri Manual

Admin dapat membuat data santri tanpa PPDB untuk kebutuhan migrasi awal atau
kasus administratif. Aksi ini wajib diaudit.

### Update Data Induk

Operator yang berwenang dapat memperbarui identitas dasar, unit utama, dan
kontak wali snapshot.

### Ubah Lifecycle

Operator yang berwenang dapat mengubah status menjadi active, inactive,
transferred, atau graduated dengan alasan yang tercatat.

### Archive dan Restore

Data santri dapat diarsipkan secara aman bila salah input atau tidak lagi
digunakan pada operasional aktif. Penghapusan permanen tidak menjadi baseline.

## Candidate Data Model

### `students`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `student_no` | Nomor induk santri, nama tampil NIS, unique |
| `admission_id` | Nullable, referensi asal PPDB |
| `registration_no` | Nullable, snapshot nomor pendaftaran PPDB |
| `full_name` | Nama lengkap santri |
| `preferred_name` | Nama panggilan, nullable |
| `gender` | Nilai terbatas sesuai vocabulary baseline |
| `birth_place` | Nullable |
| `birth_date` | Nullable |
| `previous_school` | Nullable, dari PPDB atau input manual |
| `primary_unit_id` | Nullable/required sesuai kesiapan Organization |
| `entry_date` | Tanggal masuk |
| `status` | active/inactive/transferred/graduated |
| `status_reason` | Nullable |
| `status_changed_at` | Nullable |
| `status_changed_by` | Nullable actor id |
| `archived_at` | Nullable |
| `archived_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `student_guardians`

Table ini adalah snapshot/kontak minimum dalam boundary Santri, bukan master
`Pesantrian/WaliSantri` penuh.

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `student_id` | FK ke `students` |
| `guardian_name` | Nama wali |
| `guardian_phone` | Nullable |
| `guardian_relation` | Ayah/Ibu/Wali/Lainnya |
| `is_primary` | Wali utama |
| `is_emergency_contact` | Kontak darurat |
| `notes` | Nullable, tidak untuk data sensitif |
| `created_at`, `updated_at` | Timestamp standar |

## Conversion Rule dari PPDB

Santri dapat dibuat dari PPDB bila:

- admission berstatus `accepted`;
- admission eligible menurut contract
  `AcceptedAdmissionReader::findAcceptedForConversion`;
- admission belum pernah dikonversi menjadi santri;
- data minimum `candidateName` dan `guardianName` tersedia;
- actor memiliki permission konversi.

Data yang disalin:

- `registrationNo` menjadi trace PPDB;
- identitas calon menjadi identitas awal santri;
- `targetUnitId` menjadi unit utama bila valid;
- data wali menjadi snapshot `student_guardians`;
- metadata accepted menjadi bagian dari audit atau trace.

`Pesantrian/Santri` tidak boleh membaca `StudentAdmissionRecord` atau table
`student_admissions` secara langsung.

## Permission Candidate

- `santri.view`
- `santri.manage`
- `santri.lifecycle`
- `santri.archive`

Backend tetap menjadi authority permission. Frontend hanya menyembunyikan atau
menampilkan aksi untuk UX.

## Audit Candidate

Audit minimum:

- `santri.student.created`
- `santri.student.created_from_admission`
- `santri.student.updated`
- `santri.student.lifecycle_changed`
- `santri.student.archived`
- `santri.student.restored`

Audit tidak boleh menyimpan payload sensitif berlebihan.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/Santri/`.

Struktur komponen wajib menjaga halaman index tetap tipis:

```text
resources/js/pages/Pesantrian/Santri/
├── pages/
│   ├── Index.tsx
│   └── Show.tsx
└── components/
    ├── SantriFilters.tsx
    ├── SantriTable.tsx
    ├── SantriSummaryCards.tsx
    ├── SantriForm.tsx
    ├── SantriGuardianForm.tsx
    └── SantriLifecyclePanel.tsx
```

## Acceptance Criteria

- [ ] Module skeleton `Pesantrian/Santri` valid menurut module registry.
- [x] Nomor induk santri auto-generated dan unique.
- [x] Santri dapat dibuat manual oleh admin berwenang.
- [x] Santri dapat dibuat dari PPDB accepted melalui public contract PPDB.
- [x] Admission yang sudah dikonversi tidak dapat dikonversi ulang.
- [x] Snapshot wali minimum tersimpan.
- [x] List/search/filter santri tersedia.
- [x] Detail santri tersedia.
- [x] Lifecycle active/inactive/transferred/graduated tersedia dan diaudit.
- [x] Archive/restore aman dan diaudit.
- [x] UI Inertia list/detail tersedia dengan filter, pagination, empty state,
  dan sidebar namespace Pesantrian.
- [x] UI create/update manual tersedia dengan form data induk dan wali snapshot.
- [x] UI konversi accepted admission tersedia tanpa membaca Infrastructure PPDB.
- [x] Consumer tidak mengimpor Infrastructure Santri atau PPDB.
- [x] Focused tests backend Santri lulus.
- [x] Focused tests presentasi Santri, typecheck, lint, dan build frontend
  lulus.

## Keputusan Baseline

Tidak ada blocking question untuk mulai coding baseline. Keputusan awal:

- Format nomor induk final memakai prefix `NIS`, misalnya `NIS-0001`.
- Direct create manual dibuka untuk admin/internal dengan permission dan audit.
- Snapshot wali tetap berada di Santri sampai kebutuhan master
  `Pesantrian/WaliSantri` nyata.
- Field identitas tambahan seperti NIK/NISN ditunda dari baseline awal karena
  lebih sensitif dan butuh kebijakan data yang lebih jelas.
