# KelasRombel

Source module mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md) dan
[`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas

- Namespace teknis: `Academic`
- Module teknis: `KelasRombel`
- Nama tampil: `Kelas / Rombel / Kurikulum`
- Source: `app/Modules/Academic/KelasRombel/`
- Candidate frontend: `resources/js/pages/Academic/KelasRombel/`
- Status: Active - data foundation

## Tujuan

Module `Academic/KelasRombel` menjadi konteks akademik operasional untuk
menempatkan santri ke kelas dan rombongan belajar pada tahun ajaran/semester
aktif. Module ini menjadi jembatan awal sebelum subject, jadwal, presensi
akademik, nilai, tahfidz berbasis kelompok, dan laporan akademik.

Nama tampil memakai Bahasa Indonesia agar operator pesantren mudah memahami
menu: `Kelas / Rombel / Kurikulum`.

## Boundary

### Dimiliki KelasRombel

- Master kurikulum minimum.
- Tingkat/kelas akademik, misalnya VII, VIII, IX, X, XI, XII, atau kelas diniyah
  sesuai kebutuhan pesantren.
- Rombongan belajar, misalnya VII-A atau X-IPA-1.
- Penempatan santri aktif ke rombel pada periode akademik.
- Riwayat pindah rombel minimum.
- Assignment wali kelas dari SDM/guru.
- Audit mutasi kurikulum, rombel, penempatan, dan wali kelas.

### Tidak Dimiliki KelasRombel

- Master mata pelajaran detail.
- Jadwal pelajaran mingguan.
- Presensi akademik harian.
- Nilai/rapor.
- Presensi kegiatan pesantren non-akademik.
- Data induk santri dan wali.
- Data pegawai/guru selain referensi wali kelas.
- Struktur unit organisasi.

## Dependency

- `Organization/Organization`: rujukan unit pendidikan pemilik kelas/rombel
  melalui `EducationUnitReader`.
- `Academic/AcademicPeriod`: rujukan tahun ajaran/semester aktif melalui
  `ActiveAcademicPeriodReader`.
- `Pesantrian/Santri`: rujukan santri aktif untuk penempatan melalui
  `ActiveStudentReader`.
- `HumanResource/HumanResource`: rujukan guru/staf sebagai wali kelas melalui
  `ActiveEmployeeReader`.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit perubahan data dan lifecycle.

Public read contract minimum dibuat pada Increment 3 karena
`Academic/KelasRombel` menjadi consumer nyata untuk selector/validasi. Consumer
tidak boleh mengimpor model Infrastructure module lain.

## Identifier

- Primary key teknis memakai ULID.
- Business identifier:
  - `curriculum_code`, misalnya `KUR-2026-MERDEKA`;
  - `class_code`, misalnya `VII` atau `X`;
  - `group_code`, misalnya `VII-A` atau `X-IPA-1`.

## Lifecycle

Status minimum:

- `draft`: data disiapkan dan belum dipakai operasional.
- `active`: rombel/kurikulum dipakai pada periode berjalan.
- `closed`: rombel/periode kelas ditutup dan tidak menerima penempatan baru.
- `archived`: data tidak tampil default, tetapi masih dapat dipulihkan bila
  diperlukan.

Archive/restore adalah state operasional aman. Penghapusan permanen tidak
menjadi baseline.

## Public Boundary Candidate

Candidate boundary dibuat saat consumer nyata muncul:

- lookup rombel aktif untuk selector presensi, tahfidz, dan laporan;
- lookup santri dalam rombel;
- query rombel per periode akademik dan unit;
- event penempatan santri ke rombel bila module downstream perlu merespons.

## UI Baseline

UI berada di `resources/js/pages/Academic/KelasRombel/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Academic/KelasRombel/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- KelasRombelFilters.tsx
    |-- KelasRombelTable.tsx
    |-- KelasRombelSummaryCards.tsx
    |-- CurriculumForm.tsx
    |-- ClassGroupForm.tsx
    |-- StudentPlacementForm.tsx
    `-- HomeroomAssignmentForm.tsx
```

Menu sidebar berada di namespace Academic dengan nama tampil
`Kelas / Rombel / Kurikulum`.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../AcademicPeriod/contracts/active-period.md`](../AcademicPeriod/contracts/active-period.md)
- [`../../Pesantrian/Santri/`](../../Pesantrian/Santri/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Academic KelasRombel --dry-run --json --no-ansi
php artisan module:make Academic KelasRombel --force --yes --json --no-ansi
php artisan module:validate --no-ansi
php artisan test --filter=KelasRombel --no-ansi
npm run types:check
npm run lint:check
npm run build
git diff --check
```
