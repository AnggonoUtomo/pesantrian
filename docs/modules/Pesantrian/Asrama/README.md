# Asrama

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `Asrama`
- Nama tampil: `Asrama`
- Source: `app/Modules/Pesantrian/Asrama/`
- Frontend: `resources/js/pages/Pesantrian/Asrama/`
- Status: Active - backend lifecycle dan demo seeder

## Tujuan

Module `Pesantrian/Asrama` mengelola struktur asrama, kamar, kapasitas, musyrif,
dan penempatan santri di kamar. Module ini dibuka setelah `Santri` dan
`KelasRombel` karena keduanya sudah menyediakan data santri aktif dan pola
penempatan berbasis periode/history.

Nama tampil dibuat sederhana: `Asrama`, karena operator pesantren biasanya
memahami istilah ini tanpa perlu istilah teknis seperti dormitory.

## Boundary

### Dimiliki Asrama

- Data bangunan/kompleks asrama.
- Data kamar.
- Kapasitas kamar.
- Status aktif/nonaktif/arsip asrama dan kamar.
- Penempatan santri ke kamar.
- Riwayat pindah kamar dan keluar kamar.
- Penugasan musyrif atau pembina asrama.
- Ringkasan keterisian kamar.
- Audit mutasi asrama, kamar, penempatan, transfer, dan musyrif.

### Tidak Dimiliki Asrama

- Lifecycle utama santri seperti aktif, pindah, atau lulus.
- Data induk santri dan wali.
- Jadwal akademik, kelas, rombel, atau kurikulum.
- Presensi kegiatan harian secara detail.
- Perizinan pulang/keluar/sakit.
- Kedisiplinan, kesehatan, konseling, dan prestasi.
- Inventaris aset kamar seperti kasur/lemari secara detail.

## Dependency

- `Organization/Organization`: rujukan unit asrama atau lokasi organisasi.
- `Pesantrian/Santri`: lookup santri aktif untuk penempatan.
- `HumanResource/HumanResource`: lookup pegawai aktif untuk musyrif/pembina.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit perubahan data dan placement.

Asrama tidak boleh membaca model Infrastructure module lain secara langsung.
Lookup lintas module harus memakai public contract yang sudah ada atau contract
baru hanya jika consumer nyata dibutuhkan.

Contract readiness awal:

- `Organization/Organization\Application\Contracts\DormitoryUnitReader` untuk
  memilih unit organisasi bertipe `dormitory`.
- `Pesantrian/Santri\Application\Contracts\ActiveStudentReader` dipakai untuk
  santri aktif; DTO-nya membawa `gender` agar rule asrama putra/putri dapat
  divalidasi tanpa membaca model Santri.
- `HumanResource/HumanResource\Application\Contracts\ActiveEmployeeReader`
  dipakai untuk selector musyrif/pembina aktif.

## Data Foundation

Increment data foundation sudah menyiapkan table:

- `dormitories` untuk data asrama.
- `dormitory_rooms` untuk kamar dan kapasitas.
- `student_room_placements` untuk riwayat tinggal santri di kamar.
- `dormitory_supervisor_assignments` untuk riwayat musyrif/pembina.

Semua table memakai ULID. Satu placement aktif per santri dijaga melalui
`active_student_key` nullable unique; placement lama disimpan sebagai histori
dengan `active_student_key = null`.

## Backend Read/List

Increment read/list menyediakan endpoint API internal:

- `GET /api/v1/pesantrian/asrama`
- `GET /api/v1/pesantrian/asrama/{dormitory}`

Endpoint memakai permission `asrama.view`, envelope API canonical, pagination,
search, filter `unit_id`, `gender_policy`, `status`, serta status arsip.
Detail asrama memuat kamar, kapasitas, keterisian aktif, placement aktif, dan
musyrif aktif.

## Mutation Asrama dan Kamar

Increment mutation awal menyediakan endpoint internal:

- `POST /api/v1/pesantrian/asrama`
- `PATCH /api/v1/pesantrian/asrama/{dormitory}`
- `POST /api/v1/pesantrian/asrama/{dormitory}/rooms`
- `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}`

Mutation memakai permission `asrama.manage`, idempotency middleware, validation
request, application action, repository port, dan audit `Asrama`. Unit asrama
wajib merujuk `organization_units.type = dormitory`.

## Lifecycle Operasional

Alur awal:

```text
Asrama aktif
    -> Kamar aktif dengan kapasitas
    -> Santri aktif ditempatkan
    -> Santri bisa pindah kamar
    -> Placement lama ditutup sebagai riwayat
    -> Santri bisa keluar dari kamar
```

Satu santri hanya boleh memiliki satu placement kamar aktif pada waktu yang
sama. Perpindahan kamar tidak menghapus riwayat lama.

Endpoint lifecycle backend yang sudah tersedia:

- Penempatan santri:
  `POST /api/v1/pesantrian/asrama/{dormitory}/placements`
- Transfer kamar:
  `PATCH /api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/transfer`
- Keluar kamar:
  `PATCH /api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/remove`
- Penugasan musyrif/pembina:
  `POST /api/v1/pesantrian/asrama/{dormitory}/supervisors`
- Akhir tugas musyrif/pembina:
  `PATCH /api/v1/pesantrian/asrama/{dormitory}/supervisors/{assignment}/end`
- Archive/restore asrama:
  `PATCH /api/v1/pesantrian/asrama/{dormitory}/archive`
  dan `PATCH /api/v1/pesantrian/asrama/{dormitory}/restore`
- Archive/restore kamar:
  `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/archive`
  dan `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/restore`

## Data Demo

`AsramaDemoSeeder` menyediakan data demo idempotent untuk uji manual:

- `DEMO-ASR-PUTRA` dan `DEMO-ASR-PUTRI` sebagai asrama aktif.
- `DEMO-ASR-RENOVASI` sebagai asrama nonaktif/arsip.
- Kamar demo `DEMO-KMR-*`, termasuk satu kamar arsip/renovasi.
- Placement santri aktif, riwayat pindah kamar, dan riwayat keluar kamar.
- Musyrif/pembina aktif dan assignment historis yang sudah selesai.

Seeder memakai data demo dari Organization, Santri, dan HumanResource. Jika
dependency demo belum ada, seeder berhenti aman tanpa membuat data yatim.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/Asrama/`.

Halaman index dan detail harus dijaga tetap tipis. Komponen business-specific
ditempatkan pada folder `components/`, mengikuti pola module sebelumnya:

```text
resources/js/pages/Pesantrian/Asrama/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- AsramaFilters.tsx
    |-- AsramaTable.tsx
    |-- AsramaSummaryCards.tsx
    |-- AsramaForm.tsx
    |-- KamarForm.tsx
    |-- PenempatanSantriForm.tsx
    `-- MusyrifAssignmentForm.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil `Asrama`.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian Asrama --dry-run --json --no-ansi
php artisan module:make Pesantrian Asrama --force --yes --no-ansi
php artisan module:validate
php artisan test --filter=Asrama
php artisan test tests\Feature\AsramaApiTest.php --no-ansi
php artisan test tests\Feature\AsramaMutationApiTest.php --no-ansi
php artisan test tests\Feature\AsramaPlacementApiTest.php --no-ansi
php artisan test tests\Feature\AsramaSupervisorArchiveApiTest.php --no-ansi
php artisan test tests\Feature\BusinessDemoSeederTest.php --no-ansi
npm run types:check
npm run lint:check
npm run build
```
