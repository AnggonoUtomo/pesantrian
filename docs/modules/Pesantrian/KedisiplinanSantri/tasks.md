# Tasks: Pesantrian/KedisiplinanSantri

Dokumen ini menjadi checklist incremental. Setiap increment harus bisa
diverifikasi dan di-commit terpisah.

## Increment 1: Documentation Baseline

- [x] Buat folder dokumentasi `docs/modules/Pesantrian/KedisiplinanSantri/`.
- [x] Buat README module.
- [x] Buat specification.
- [x] Buat implementation plan.
- [x] Buat task breakdown.
- [x] Update `docs/MODULES.md`.

Hasil:

- Boundary KedisiplinanSantri ditetapkan sebagai catatan pelanggaran, kategori,
  poin/tingkat, tindakan pembinaan, status penyelesaian, revision history, dan
  audit.
- Nama tampil ditetapkan `Pelanggaran / Kedisiplinan`.
- Source module belum dibuat pada increment ini.
- Integrasi otomatis dari PresensiSantri dan PerizinanSantri ditunda. Keduanya
  hanya akan menjadi kandidat read-only setelah ada keputusan eksplisit.
- KedisiplinanSantri tidak boleh menjadi catatan bebas tanpa struktur.

Verifikasi:

- [x] `git diff --check`
- [x] Pemeriksaan tautan Markdown relatif.

Acceptance:

- [x] Scope dan non-scope tercatat.
- [x] Dependency awal tercatat.
- [x] Permission candidate tercatat.
- [x] Data model candidate tercatat.
- [x] Increment implementasi tersedia.

## Increment 2: Module Skeleton dan Permission

- [ ] Buat module `Pesantrian/KedisiplinanSantri`.
- [ ] Buat permission identity.
- [ ] Tambahkan permission ke seeder/wiring role.
- [ ] Tambahkan README source module.
- [ ] Jalankan module validation.

Acceptance:

- Module discover/validate normal.
- Permission `kedisiplinan_santri.view`, `kedisiplinan_santri.manage`,
  `kedisiplinan_santri.review`, `kedisiplinan_santri.resolve`, dan
  `kedisiplinan_santri.archive` tersedia.
- Belum ada table bisnis.

## Increment 3: Contract Readiness

- [ ] Audit contract `ActiveStudentReader`.
- [ ] Audit contract `ActiveEmployeeReader`.
- [ ] Tambahkan test readiness.
- [ ] Dokumentasikan batas integrasi Presensi/Perizinan.

Acceptance:

- KedisiplinanSantri bisa membaca santri aktif melalui public contract.
- KedisiplinanSantri bisa membaca pembina/petugas aktif melalui public
  contract.
- KedisiplinanSantri tidak membaca model Infrastructure module lain.

## Increment 4: Data Foundation

- [ ] Buat migration `student_discipline_categories`.
- [ ] Buat migration `student_discipline_cases`.
- [ ] Buat migration `student_discipline_revisions`.
- [ ] Buat record model dan factory minimum.
- [ ] Tambahkan test data foundation.

Acceptance:

- Table memakai ULID.
- Kode kategori dan nomor kasus unique.
- Index/filter tanggal, status, severity, kategori, santri, dan pembina
  tersedia.
- Migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

- [ ] Buat DTO/read model kategori dan kasus.
- [ ] Buat repository/query list kategori.
- [ ] Buat repository/query list kasus.
- [ ] Buat query detail kasus.
- [ ] Buat API read routes/resources.
- [ ] Tambahkan test API read/list/detail.

Acceptance:

- List kasus mendukung search, filter tanggal, status, severity, kategori,
  santri, pembina, dan pagination.
- Detail kasus menampilkan lifecycle, snapshot santri, kategori, action,
  resolution, dan revision history.
- Actor tanpa `kedisiplinan_santri.view` ditolak oleh middleware backend.

## Increment 6: Category Management

- [ ] Buat action create/update/archive kategori.
- [ ] Buat request validation kategori.
- [ ] Buat API mutation kategori.
- [ ] Tambahkan audit kategori.
- [ ] Tambahkan test mutation kategori.

Acceptance:

- Kategori bisa dibuat dan diubah.
- Kategori archived tidak muncul untuk kasus baru.
- Archive kategori tidak menghapus histori kasus lama.
- Kode kategori tetap unique.

## Increment 7: Create/Update Draft dan Submit Case

- [ ] Buat action create draft.
- [ ] Buat action update draft.
- [ ] Buat action submit.
- [ ] Tambahkan request validation.
- [ ] Tambahkan revision/audit create/update/submit.
- [ ] Tambahkan test mutation case awal.

Acceptance:

- Hanya santri aktif yang bisa dibuatkan kasus.
- Kategori aktif wajib dipilih.
- Severity valid dan poin tidak negatif.
- Submit hanya dari draft.

## Increment 8: Review dan Assign Action

- [ ] Buat action review.
- [ ] Buat action assign action.
- [ ] Tambahkan request validation review/action.
- [ ] Tambahkan revision/audit review/action.
- [ ] Tambahkan test lifecycle review/action.

Acceptance:

- Review hanya dari submitted.
- Tindakan pembinaan wajib memiliki catatan.
- Actor dan waktu lifecycle tersimpan.

## Increment 9: Resolve dan Void

- [ ] Buat action resolve.
- [ ] Buat action void.
- [ ] Tambahkan request validation resolve/void.
- [ ] Tambahkan revision/audit resolve/void.
- [ ] Tambahkan test lifecycle resolve/void.

Acceptance:

- Resolve wajib catatan penyelesaian.
- Void wajib alasan dan tidak menghapus data.
- Resolved/void menjadi status final baseline.

## Increment 10: Demo Seeder

- [ ] Buat `KedisiplinanSantriDemoSeeder`.
- [ ] Update `DatabaseSeeder`.
- [ ] Tambahkan test idempotent seeder.

Acceptance:

- Demo mencakup kategori dan kasus draft/submitted/in_review/action_assigned/
  resolved/void.
- Demo memakai santri dan petugas demo yang sudah ada.
- Seeder aman diulang dan tidak berjalan di production.

## Increment 11: UI/Inertia List dan Detail

- [ ] Buat page index/detail.
- [ ] Buat komponen list/detail di folder `components`.
- [ ] Tambahkan filter tanggal/status/severity/kategori/search.
- [ ] Tambahkan summary cards dan pagination.
- [ ] Tambahkan sidebar menu namespace Pesantrian.
- [ ] Tambahkan whitelist route Ziggy.

Acceptance:

- UI berada di `resources/js/pages/Pesantrian/KedisiplinanSantri/`.
- Index tetap minimal.
- Komponen business-specific berada di folder `components`.
- List/detail tampil di desktop dan mobile.

## Increment 12: UI Mutation

- [ ] Buat dialog create/update.
- [ ] Buat dialog submit/review/assign action/resolve/void.
- [ ] Tambahkan UX permission-aware.
- [ ] Tambahkan toast/error handling.
- [ ] Tambahkan test presentation/mutation web bila tersedia.

Acceptance:

- Semua form mutation berada di folder `components`.
- Action final/destructive memakai confirmation.
- Backend tetap authority permission dan validasi.

## Increment 13: QA Browser dan User Manual

- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [ ] Update user manual lifecycle.
- [ ] Update README/tasks hasil final.

Acceptance:

- Flow list/detail/mutation utama bisa diuji manual.
- Relasi ke module yang belum otomatis terhubung diberi keterangan.
- Console browser bersih dari error.

## Increment 14: Integrasi Read-only dari Presensi dan Perizinan

- [ ] Evaluasi contract PresensiSantri untuk rekap alfa/terlambat.
- [ ] Evaluasi contract PerizinanSantri untuk keterlambatan kembali.
- [ ] Buat query kandidat kasus bila flow disetujui.
- [ ] Tambahkan test consumer readiness.
- [ ] Dokumentasikan batas integrasi.

Acceptance:

- KedisiplinanSantri tidak membaca model Infrastructure Presensi/Perizinan.
- Kandidat kasus tidak otomatis menjadi pelanggaran tanpa review manusia.
- Pencatatan manual tetap tersedia.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/KedisiplinanSantri`.
- [x] Nama tampil memakai `Pelanggaran / Kedisiplinan`.
- [x] Kedisiplinan awal memakai admin/internal, bukan public form.
- [x] Poin pelanggaran disiapkan sebagai field opsional.
- [x] Review manusia wajib sebelum data dari Presensi/Perizinan menjadi kasus.
- [x] Konseling mendalam tetap milik module PembinaanSantri nanti.
