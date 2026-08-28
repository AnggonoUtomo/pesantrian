# Candidate Contract: Active Academic Period

## Status

Candidate, belum menjadi runtime public contract sampai consumer nyata
disetujui.

## Tujuan

Contract ini menjadi pegangan awal untuk consumer seperti `Academic/Academic`,
`Finance/StudentFinance`, dan `Platform/Reporting` ketika mereka perlu membaca
periode akademik aktif tanpa mengambil model Eloquent atau repository privat
module `Academic/AcademicPeriod`.

Active period pada slice awal diputuskan global untuk seluruh aplikasi. Scope
per unit organisasi ditunda sampai ada kebutuhan nyata.

## Prinsip Boundary

- Consumer tidak boleh memakai
  `Infrastructure\Models\AcademicYearRecord`,
  `Infrastructure\Models\AcademicTermRecord`, atau repository Infrastructure
  AcademicPeriod secara langsung.
- Consumer membaca periode aktif melalui contract/query Application yang
  dipublish oleh module AcademicPeriod.
- Output berupa DTO ringkas dan immutable, bukan Eloquent model.
- Tidak ada mutation lewat contract ini; mutation tetap melalui use case
  `activate` dan `close` milik AcademicPeriod.
- Jika nanti active period perlu scoped per unit, perubahan dilakukan secara
  additive dengan parameter optional baru dan compatibility plan.

## Candidate Interface

Nama kandidat:

```php
App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader
```

Method kandidat:

```php
public function current(): ?ActiveAcademicPeriodData;
```

Alasan belum dibuat di source:

- Belum ada consumer runtime yang disetujui.
- `GetCurrentAcademicTerm` dan endpoint
  `GET /api/v1/academic/periods/terms/current` sudah cukup untuk slice internal
  dan API saat ini.
- Membuat contract tanpa consumer berisiko menjadi abstraction placeholder.

## Candidate DTO

Nama kandidat:

```php
App\Modules\Academic\AcademicPeriod\Application\DTO\ActiveAcademicPeriodData
```

Field minimum:

| Field | Type | Keterangan |
| --- | --- | --- |
| `academicYearId` | `string` ULID | ID tahun akademik aktif |
| `academicYearCode` | `string` | Contoh `2026-2027` |
| `academicYearName` | `string` | Nama baca tahun akademik |
| `termId` | `string` ULID | ID term aktif |
| `termCode` | `string` | Contoh `2026-2027-GANJIL` |
| `termName` | `string` | Nama baca term |
| `termSequence` | `int` | Urutan term dalam tahun akademik |
| `startsOn` | `string` date `YYYY-MM-DD` | Awal term aktif |
| `endsOn` | `string` date `YYYY-MM-DD` | Akhir term aktif |
| `status` | `string` | Harus `active` untuk data yang dikembalikan |

Tidak masuk DTO minimum:

- daftar semua term;
- audit metadata;
- permission actor;
- object organization unit;
- Eloquent relation;
- payload lifecycle mutation.

## Semantik Return

| Kondisi | Return |
| --- | --- |
| Ada term dengan `status=active` dan `is_active=true` | `ActiveAcademicPeriodData` |
| Belum ada active period global | `null` |
| Data tidak konsisten, misalnya lebih dari satu active term | throw domain/application exception pada implementasi final atau dilindungi oleh invariant repository |

Pada slice saat ini, invariant satu active term dijaga oleh use case
`ActivateAcademicTerm`.

## Candidate HTTP Read Contract

Endpoint API yang sudah tersedia dan dapat menjadi referensi shape response:

```text
GET /api/v1/academic/periods/terms/current
Route name: api.v1.academic.periods.terms.current
Authorization: academic_period.view
```

Response saat active period tersedia:

```json
{
  "success": true,
  "message": "Term akademik aktif berhasil dibaca.",
  "data": {
    "id": "01K...",
    "academic_year_id": "01K...",
    "code": "2026-2027-GANJIL",
    "name": "Semester Ganjil",
    "sequence": 1,
    "starts_on": "2026-07-01",
    "ends_on": "2026-12-31",
    "status": "active",
    "is_active": true,
    "created_at": "...",
    "updated_at": "..."
  },
  "meta": {
    "correlation_id": "01K..."
  }
}
```

Response saat belum ada active period:

```json
{
  "success": true,
  "message": "Term akademik aktif berhasil dibaca.",
  "data": null,
  "meta": {
    "correlation_id": "01K..."
  }
}
```

HTTP endpoint ini bukan pengganti public contract antar module. Untuk consumer
internal PHP, gunakan candidate interface di atas ketika consumer pertama
disetujui.

## Consumer Candidate

| Consumer | Kebutuhan | Catatan |
| --- | --- | --- |
| `Academic/Academic` | default tahun/term untuk kelas, rombel, jadwal, attendance | Consumer paling mungkin menjadi pemicu implementasi runtime contract |
| `Finance/StudentFinance` | periode aktif untuk fee definition, invoice issue, aging/reporting | Butuh kesepakatan apakah invoice mengikuti term aktif atau bisa memilih periode manual |
| `Platform/Reporting` | filter default dashboard dan laporan | Bisa membaca read model/projection nanti |

## Rencana Implementasi Saat Consumer Disetujui

1. Tambahkan `ActiveAcademicPeriodData` bila field minimum di atas masih cukup.
2. Tambahkan `ActiveAcademicPeriodReader` di `Application/Contracts`.
3. Implementasikan reader via Application query/repository yang sudah menjaga
   output DTO.
4. Bind contract di `ServiceProvider.php`.
5. Tambahkan focused contract/query test yang memastikan:
   - return DTO saat active term ada;
   - return `null` saat active term belum ada;
   - tidak mengekspos model Infrastructure;
   - tidak membuat dependency konkret lintas module.
6. Consumer pertama memakai contract tersebut lewat dependency injection.

## Open Questions

- Apakah consumer pertama adalah `Academic/Academic` atau
  `Finance/StudentFinance`?
- Apakah Finance nantinya wajib memakai active term atau boleh memilih term
  lain secara eksplisit untuk backdate/payment adjustment?
- Apakah scoped per unit organisasi dibutuhkan setelah struktur operasional
  nyata dipakai?
