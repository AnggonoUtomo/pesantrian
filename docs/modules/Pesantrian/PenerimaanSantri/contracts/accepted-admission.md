# Contract: Accepted Admission untuk Santri

## Status

Implemented untuk consumer `Pesantrian/Santri`.

Contract ini menjadi pegangan runtime saat module `Pesantrian/Santri`
membutuhkan data pendaftaran yang sudah diterima. Karena consumer nyata sudah
tersedia, `Pesantrian/PenerimaanSantri` mengekspor reader melalui public
Application contract dan binding Infrastructure internal.

## Tujuan

Memberikan boundary baca yang aman untuk mengambil pendaftaran santri berstatus
`accepted` tanpa mengekspos model Infrastructure
`StudentAdmissionRecord` kepada module lain.

Contract ini dipakai untuk kebutuhan konversi calon santri menjadi data induk
santri aktif, bukan untuk UI daftar PPDB atau laporan umum.

## Candidate Interface

Nama teknis runtime:

```php
namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts;

interface AcceptedAdmissionReader
{
    public function findAcceptedForConversion(string $admissionId): ?AcceptedAdmissionData;
}
```

Catatan:

- `admissionId` wajib ULID.
- Method hanya mengembalikan data bila pendaftaran berstatus `accepted`.
- Method mengembalikan `null` bila record tidak ditemukan, belum accepted, sudah
  cancelled/rejected, atau tidak layak dikonversi pada rule baseline.
- Interface berada di `Application/Contracts` sebagai public boundary module.
- Implementasi Eloquent, bila nanti dibuat, tetap berada di Infrastructure
  PenerimaanSantri.

## Candidate DTO

Nama teknis runtime:

```php
namespace App\Modules\Pesantrian\PenerimaanSantri\Application\DTO;

final readonly class AcceptedAdmissionData
{
    public function __construct(
        public string $admissionId,
        public string $registrationNo,
        public string $candidateName,
        public ?string $candidateGender,
        public ?string $candidateBirthPlace,
        public ?string $candidateBirthDate,
        public ?string $previousSchool,
        public ?string $targetUnitId,
        public string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public ?string $acceptedAt,
        public ?string $acceptedBy,
    ) {}
}
```

Field ini sengaja ringkas agar consumer `Pesantrian/Santri` hanya menerima data
yang dibutuhkan untuk membuat data induk awal.

## Data yang Boleh Dibaca Consumer

- `admission_id`
- `registration_no`
- identitas calon santri minimum:
  - nama;
  - jenis kelamin;
  - tempat lahir;
  - tanggal lahir;
  - sekolah asal;
- `target_unit_id` sebagai rujukan unit tujuan;
- snapshot wali minimum:
  - nama wali;
  - nomor telepon wali;
  - hubungan wali;
- metadata keputusan:
  - waktu diterima;
  - actor penerima.

## Data yang Tidak Diekspor

- Eloquent model `StudentAdmissionRecord`;
- query builder atau collection Infrastructure;
- audit metadata;
- notes internal bebas;
- detail checklist dokumen;
- data pembayaran/invoice;
- file dokumen atau lampiran;
- status selain `accepted`.

## Failure Semantics

Runtime contract memakai semantik berikut:

- `null`: pendaftaran tidak ditemukan atau belum eligible untuk konversi.
- Exception validasi hanya dipakai bila input contract invalid secara teknis,
  misalnya `admissionId` bukan ULID.
- Consumer `Pesantrian/Santri` yang memutuskan apakah `null` diterjemahkan
  menjadi `404`, `422`, atau pesan domain lain pada boundary-nya.

## Rule Eligibility Baseline

Pendaftaran dianggap eligible untuk dibaca oleh contract konversi bila:

- `status = accepted`;
- `decided_at` terisi;
- `decided_by` terisi;
- `candidate_name` dan `guardian_name` terisi;
- bila `registration_fee_required = true`, maka `registration_fee_status`
  harus `verified`;
- checklist dokumen tidak dipaksa lengkap pada contract awal karena baseline
  belum memiliki master dokumen wajib.

Rule dokumen wajib bisa diperketat setelah `Support/Document` atau konfigurasi
periode PPDB tersedia.

## Ownership

- `Pesantrian/PenerimaanSantri` memiliki data source dan contract baca accepted
  admission.
- `Pesantrian/Santri` memiliki proses pembuatan data induk santri aktif.
- `Pesantrian/WaliSantri` memiliki promosi snapshot wali menjadi master wali
  bila module tersebut tersedia.
- `Finance/StudentFinance` tetap memiliki invoice, pembayaran, dan tunggakan.

## Anti-Corruption Boundary

Consumer dilarang:

- membaca table `student_admissions` secara langsung;
- mengimpor `StudentAdmissionRecord`;
- bergantung pada nama kolom database PenerimaanSantri;
- mengubah status PPDB sebagai bagian dari konversi Santri tanpa use case
  PenerimaanSantri yang eksplisit.

Consumer wajib:

- memakai DTO contract;
- menyimpan referensi `admissionId` atau `registrationNo` sebagai trace asal;
- menangani hasil `null` sebagai kondisi bisnis yang normal.

## Verifikasi Saat Implementasi Nanti

Verifikasi runtime yang tersedia:

- accepted admission dapat dibaca melalui `AcceptedAdmissionReader`;
- submitted/verified/rejected/cancelled tidak dikembalikan;
- DTO tidak mengekspos model Infrastructure;
- biaya wajib yang belum verified tidak eligible;
- consumer tidak mengimpor namespace Infrastructure PenerimaanSantri.
