# Presensi Santri

Source module akan mengikuti [`docs/ARCHITECTURE.md`](../../../ARCHITECTURE.md)
dan [`docs/FOLDER-STRUCTURE.md`](../../../FOLDER-STRUCTURE.md).

## Identitas Module

- Namespace teknis: `Pesantrian`
- Module teknis: `PresensiSantri`
- Nama tampil: `Presensi Santri`
- Candidate source: `app/Modules/Pesantrian/PresensiSantri/`
- Candidate frontend: `resources/js/pages/Pesantrian/PresensiSantri/`
- Status: Active - lifecycle UI ready

## Tujuan

Module `Pesantrian/PresensiSantri` menjadi pencatatan kehadiran santri untuk
kegiatan operasional pesantren. Pada baseline awal, presensi dibuat sederhana:
operator memilih konteks presensi, memilih tanggal/sesi, lalu mengisi status
kehadiran santri.

Nama tampil memakai Bahasa Indonesia agar mudah dipahami operator:
`Presensi Santri`.

## Boundary

### Dimiliki PresensiSantri

- Sesi presensi santri.
- Detail kehadiran per santri.
- Status kehadiran minimum: hadir, izin, sakit, alfa, dan terlambat.
- Catatan keterlambatan atau alasan sederhana.
- Revisi presensi dengan jejak audit.
- Rekap presensi per tanggal, santri, kelas/rombel, asrama, atau kegiatan.

### Tidak Dimiliki PresensiSantri

- Data induk santri dan wali.
- Penempatan santri ke kelas/rombel.
- Penempatan santri ke asrama/kamar.
- Approval perizinan santri.
- Rekam medis klinik.
- Pelanggaran/kedisiplinan akibat alfa/terlambat.
- Jadwal pelajaran detail.
- Nilai/rapor.

## Dependency

- `Pesantrian/Santri`: sumber santri aktif untuk selector dan validasi.
- `Academic/KelasRombel`: sumber rombel dan anggota rombel untuk presensi
  kelas.
- `Pesantrian/Asrama`: sumber asrama/kamar dan penghuni aktif untuk presensi
  asrama.
- `HumanResource/HumanResource`: kandidat petugas/guru/musyrif bila diperlukan
  sebagai pencatat atau penanggung jawab.
- `System/AccessControl`: otorisasi backend.
- `System/AuditLog`: audit pembuatan, submit, dan revisi presensi.
- `Pesantrian/PerizinanSantri`: sumber read-only izin santri yang sudah
  approved/berjalan/selesai pada tanggal presensi. Presensi membaca melalui
  public contract PerizinanSantri, bukan model Infrastructure.

Dependency lintas module wajib melalui public contract/Application DTO yang
tersedia. PresensiSantri tidak boleh membaca model Eloquent Infrastructure
module lain secara langsung.

## Contract Readiness

Sumber kandidat santri untuk presensi awal:

- Selector umum santri aktif memakai
  `Pesantrian/Santri::ActiveStudentReader`.
- Roster rombel aktif memakai
  `Academic/KelasRombel::ActiveClassGroupRosterReader`.
- Penghuni asrama/kamar aktif memakai
  `Pesantrian/Asrama::ActiveDormitoryResidentReader`.
- Izin santri approved/berjalan/selesai per tanggal memakai
  `Pesantrian/PerizinanSantri::ApprovedStudentPermitReader`.

Contract ini bersifat read-only dan hanya mengembalikan DTO sederhana yang
dibutuhkan untuk membuat sesi/entry presensi. Mutation tetap menjadi tanggung
jawab module pemilik datanya masing-masing.

## Konteks Presensi

Baseline mendukung tiga konteks data:

- `class_group`: presensi kelas/rombel.
- `dormitory`: presensi asrama atau kamar.
- `activity`: presensi kegiatan umum pesantren.

Konteks `activity` boleh berjalan tanpa module jadwal khusus pada baseline,
dengan nama kegiatan dicatat sebagai snapshot teks.

## Lifecycle

```text
Draft sesi presensi
    -> Ambil daftar santri kandidat
    -> Isi status kehadiran
    -> Submit presensi
    -> Revisi terbatas bila ada koreksi
    -> Rekap/read-only untuk laporan
```

Status sesi minimum:

- `draft`: sesi dibuat, belum final.
- `submitted`: presensi sudah disubmit.
- `revised`: pernah direvisi setelah submit.
- `void`: dibatalkan/diarsipkan karena salah buat.

Status kehadiran minimum:

- `present`: hadir.
- `late`: terlambat.
- `excused`: izin.
- `sick`: sakit.
- `absent`: alfa/tanpa keterangan.

## Public Boundary Candidate

Public boundary keluar dari PresensiSantri ditunda sampai consumer nyata ada.
Candidate yang mungkin dibutuhkan nanti:

- ringkasan presensi santri untuk dashboard wali/santri;
- rekap alfa/terlambat untuk KedisiplinanSantri;
- rekap sakit untuk KesehatanSantri;
- data kehadiran kegiatan untuk Reporting.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/PresensiSantri/`.

Struktur komponen wajib menjaga page tetap tipis:

```text
resources/js/pages/Pesantrian/PresensiSantri/
|-- pages/
|   |-- Index.tsx
|   `-- Show.tsx
`-- components/
    |-- PresensiSantriDashboard.tsx
    |-- PresensiSantriFilters.tsx
    |-- PresensiSantriTable.tsx
    |-- PresensiSantriDetailPanel.tsx
    |-- PresensiSantriMutationDialogs.tsx
    |-- PresensiSantriFormFields.tsx
    |-- PresensiSantriStatusBadge.tsx
    |-- PresensiSantriSummaryCards.tsx
    `-- PresensiSantriPagination.tsx
```

Menu sidebar berada di namespace Pesantrian dengan nama tampil
`Presensi Santri`.

## Status Implementasi

Presensi Santri sudah memiliki baseline operasional:

- data foundation session, entry, dan revision;
- API read/list/detail dan mutation;
- lifecycle draft -> submitted -> revised -> void;
- demo seeder untuk presensi kelas, asrama, kegiatan umum, dan void;
- UI Inertia list, detail, create/update sesi, editor entry, submit, revisi,
  dan void;
- QA browser desktop dan mobile/responsive untuk flow utama.

Integrasi read-only awal ke `PerizinanSantri` sudah tersedia untuk membaca
izin yang relevan pada tanggal presensi. Integrasi ini belum otomatis mengubah
entry presensi menjadi `izin`; operator tetap bisa mengisi status manual.
Relasi otomatis ke `Kesehatan/Klinik` dan `Pelanggaran/Kedisiplinan` belum
dibuat karena module terkait belum tersedia.

## Dokumentasi Terkait

- [`specification.md`](specification.md)
- [`plan.md`](plan.md)
- [`tasks.md`](tasks.md)
- [`../Santri/`](../Santri/)
- [`../../Academic/KelasRombel/`](../../Academic/KelasRombel/)
- [`../Asrama/`](../Asrama/)

## Verifikasi Saat Implementasi

```bash
php artisan module:make Pesantrian PresensiSantri --dry-run --json --no-ansi
php artisan module:make Pesantrian PresensiSantri --force --yes --no-ansi
php artisan module:validate
php artisan test --filter=PresensiSantri
npm run types:check
npm run lint:check
npm run build
npx playwright test tests/Browser/presensi-santri.spec.ts
```
