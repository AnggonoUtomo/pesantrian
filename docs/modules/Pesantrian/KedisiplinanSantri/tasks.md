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

- [x] Buat module `Pesantrian/KedisiplinanSantri`.
- [x] Buat permission identity.
- [x] Tambahkan permission ke seeder/wiring role.
- [x] Tambahkan README source module.
- [x] Jalankan module validation.

Hasil:

- Module skeleton dibuat dengan generator `module:make` untuk namespace
  `Pesantrian` dan module `KedisiplinanSantri`.
- Permission identity tersedia: view/manage/review/resolve/archive.
- Role demo `OperatorSantri` mendapat permission operasional
  view/manage/review/resolve.
- Role demo `OperatorAkademik`, `Auditor`, dan `Viewer` mendapat permission
  view-only untuk kebutuhan baca.
- Permission `kedisiplinan_santri.archive` tetap sensitif dan tidak diberikan
  ke role operator biasa pada baseline.

Verifikasi:

- [x] `php artisan module:make Pesantrian KedisiplinanSantri --dry-run --json --no-ansi`
- [x] `php artisan module:make Pesantrian KedisiplinanSantri --force --yes --no-ansi`
- [x] `php artisan test tests/Unit/KedisiplinanSantriPermissionIdentityTest.php --no-ansi`
- [x] `php artisan test tests/Unit/KedisiplinanSantriPermissionIdentityTest.php tests/Feature/AccessControlSeederTest.php --no-ansi`
- [x] `php artisan test tests/Feature/BusinessDemoSeederTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `vendor\bin\pint --dirty --test`

Acceptance:

- [x] Module discover/validate normal.
- [x] Permission `kedisiplinan_santri.view`, `kedisiplinan_santri.manage`,
  `kedisiplinan_santri.review`, `kedisiplinan_santri.resolve`, dan
  `kedisiplinan_santri.archive` tersedia.
- [x] Belum ada table bisnis.

## Increment 3: Contract Readiness

- [x] Audit contract `ActiveStudentReader`.
- [x] Audit contract `ActiveEmployeeReader`.
- [x] Tambahkan test readiness.
- [x] Dokumentasikan batas integrasi Presensi/Perizinan.

Hasil:

- KedisiplinanSantri memakai `ActiveStudentReader` dari module Santri untuk
  kandidat santri aktif.
- KedisiplinanSantri memakai `ActiveEmployeeReader` dari module HumanResource
  untuk kandidat pembina/petugas aktif.
- Query Application `ListDisciplineStudentCandidates` dan
  `ListDisciplineOfficerCandidates` dibuat sebagai adapter internal use case
  KedisiplinanSantri ke public contract dependency.
- Integrasi PresensiSantri dan PerizinanSantri tetap dibatasi sebagai kandidat
  read-only untuk increment berikutnya; belum ada auto-generate kasus
  pelanggaran.
- KedisiplinanSantri belum memiliki import model Infrastructure lintas module.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriContractReadinessTest.php --no-ansi`
- [x] `php -l app/Modules/Pesantrian/KedisiplinanSantri/Application/Queries/ListDisciplineStudentCandidates.php`
- [x] `php -l app/Modules/Pesantrian/KedisiplinanSantri/Application/Queries/ListDisciplineOfficerCandidates.php`
- [x] `php -l tests/Feature/KedisiplinanSantriContractReadinessTest.php`
- [x] Scan import `App\Modules\*\Infrastructure\` pada module KedisiplinanSantri.

Acceptance:

- [x] KedisiplinanSantri bisa membaca santri aktif melalui public contract.
- [x] KedisiplinanSantri bisa membaca pembina/petugas aktif melalui public
  contract.
- [x] KedisiplinanSantri tidak membaca model Infrastructure module lain.

## Increment 4: Data Foundation

- [x] Buat migration `student_discipline_categories`.
- [x] Buat migration `student_discipline_cases`.
- [x] Buat migration `student_discipline_revisions`.
- [x] Buat record model dan factory minimum.
- [x] Tambahkan test data foundation.

Hasil:

- Table `student_discipline_categories` dibuat untuk kategori pelanggaran,
  severity default, poin default, dan status kategori.
- Table `student_discipline_cases` dibuat untuk nomor kasus, snapshot santri,
  unit, kategori, severity, poin, waktu/lokasi kejadian, status lifecycle,
  assignment pembina/petugas, tindakan pembinaan, penyelesaian, void, dan actor
  lifecycle.
- Table `student_discipline_revisions` dibuat untuk histori perubahan kasus.
- Record model Eloquent dibuat untuk kategori, kasus, dan revision dengan
  relasi category -> cases, case -> category, case -> revisions, dan revision
  -> case.
- Factory minimum dibuat untuk kategori, kasus, dan revision.
- `ServiceProvider` KedisiplinanSantri sekarang memuat migration module.
- Unique kode kategori dan nomor kasus dijaga di database.
- Index pendukung filter santri, tanggal, status, severity, kategori, unit, dan
  pembina tersedia dengan nama eksplisit pendek.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriDataFoundationTest.php --no-ansi`
- [x] `php artisan test --filter=KedisiplinanSantri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] PHP lint migration, model, factory, dan test data foundation.

Acceptance:

- [x] Table memakai ULID.
- [x] Kode kategori dan nomor kasus unique.
- [x] Index/filter tanggal, status, severity, kategori, santri, dan pembina
  tersedia.
- [x] Migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

- [x] Buat DTO/read model kategori dan kasus.
- [x] Buat repository/query list kategori.
- [x] Buat repository/query list kasus.
- [x] Buat query detail kasus.
- [x] Buat API read routes/resources.
- [x] Tambahkan test API read/list/detail.

Hasil:

- Read model kategori, kasus, summary, dan revision dibuat di layer
  Application/DTO.
- Contract `StudentDisciplineReadRepository` menjadi port baca untuk kategori,
  list kasus, dan detail kasus.
- Adapter `EloquentStudentDisciplineReadRepository` membaca data foundation
  tanpa mengambil model privat module lain.
- Endpoint API read tersedia untuk kategori, list kasus, dan detail kasus.
- Filter list kasus mendukung search, tanggal, status, severity, kategori,
  santri, pembina, sort, dan pagination.
- Detail kasus mengembalikan snapshot santri/unit, kategori, lifecycle review,
  action plan, resolution/void, summary, dan revision history.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriApiTest.php --no-ansi`

Acceptance:

- [x] List kasus mendukung search, filter tanggal, status, severity, kategori,
  santri, pembina, dan pagination.
- [x] Detail kasus menampilkan lifecycle, snapshot santri, kategori, action,
  resolution, dan revision history.
- [x] Actor tanpa `kedisiplinan_santri.view` ditolak oleh middleware backend.

## Increment 6: Category Management

- [x] Buat action create/update/archive kategori.
- [x] Buat request validation kategori.
- [x] Buat API mutation kategori.
- [x] Tambahkan audit kategori.
- [x] Tambahkan test mutation kategori.

Hasil:

- Mutation kategori tersedia melalui action Application:
  `CreateStudentDisciplineCategory`, `UpdateStudentDisciplineCategory`, dan
  `ArchiveStudentDisciplineCategory`.
- Port `StudentDisciplineCategoryMutationRepository` dan adapter Eloquent
  kategori dibuat untuk create/update/archive.
- Audit kategori dikirim melalui event
  `kedisiplinan-santri.activity.occurred` dan dicatat oleh AuditLog.
- Endpoint kategori mutation memakai idempotency middleware.
- Update kategori tidak mengubah status secara langsung; archive memakai
  endpoint dan permission khusus.
- Kategori archived tidak muncul pada list kategori aktif, tetapi histori kasus
  lama tetap mempertahankan snapshot kategori.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriCategoryMutationApiTest.php --no-ansi`
- [x] `php artisan test --filter=KedisiplinanSantri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] PHP lint file action, contract, DTO, publisher, request, controller, repository, audit listener, dan test mutation.
- [x] `php artisan route:list --name=student-discipline --no-ansi`
- [x] `git diff --check`

Acceptance:

- [x] Kategori bisa dibuat dan diubah.
- [x] Kategori archived tidak muncul untuk kasus baru.
- [x] Archive kategori tidak menghapus histori kasus lama.
- [x] Kode kategori tetap unique.

## Increment 7: Create/Update Draft dan Submit Case

- [x] Buat action create draft.
- [x] Buat action update draft.
- [x] Buat action submit.
- [x] Tambahkan request validation.
- [x] Tambahkan revision/audit create/update/submit.
- [x] Tambahkan test mutation case awal.

Hasil:

- Draft kasus dapat dibuat dari santri aktif dan kategori aktif.
- Snapshot santri memakai contract `ActiveStudentReader`; snapshot pembina
  opsional memakai contract `ActiveEmployeeReader`.
- Nomor kasus dibuat otomatis dengan format awal `DIS-000001`.
- Update draft hanya boleh dilakukan ketika kasus masih berstatus `draft`.
- Submit hanya mengubah kasus dari `draft` menjadi `submitted`.
- Revision dibuat untuk create, update, dan submit.
- Audit dibuat untuk `kedisiplinan_santri.case.created`,
  `kedisiplinan_santri.case.updated`, dan
  `kedisiplinan_santri.case.submitted`.
- Submit tidak menyimpan kolom `submitted_by` karena table foundation belum
  memiliki field tersebut; actor submit tetap tercatat di audit dan revision
  `changed_by`.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriCaseDraftMutationApiTest.php --no-ansi`
- [x] `php artisan test --filter=KedisiplinanSantri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan route:list --name=student-discipline --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] PHP lint file action, contract, DTO, exception, request, controller, repository, dan test mutation.
- [x] `git diff --check`

Acceptance:

- [x] Hanya santri aktif yang bisa dibuatkan kasus.
- [x] Kategori aktif wajib dipilih.
- [x] Severity valid dan poin tidak negatif.
- [x] Submit hanya dari draft.

## Increment 8: Review dan Assign Action

- [x] Buat action review.
- [x] Buat action assign action.
- [x] Tambahkan request validation review/action.
- [x] Tambahkan revision/audit review/action.
- [x] Tambahkan test lifecycle review/action.

Hasil:

- Kasus `submitted` dapat direview menjadi `in_review` dengan catatan review,
  actor reviewer, dan waktu review.
- Tindakan pembinaan dapat ditetapkan dari status `submitted` atau `in_review`
  menjadi `action_assigned`.
- `action_plan` wajib diisi saat menetapkan tindakan pembinaan.
- Pembina/petugas opsional divalidasi melalui contract `ActiveEmployeeReader`;
  pegawai tidak aktif ditolak.
- Revision dibuat untuk review dan assign action.
- Audit dibuat untuk `kedisiplinan_santri.case.reviewed` dan
  `kedisiplinan_santri.case.action_assigned`.

Verifikasi:

- [x] `php artisan test tests/Feature/KedisiplinanSantriReviewActionApiTest.php --no-ansi`
- [x] `php artisan test --filter=KedisiplinanSantri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan route:list --name=student-discipline --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `git diff --check`

Acceptance:

- [x] Review hanya dari submitted.
- [x] Tindakan pembinaan wajib memiliki catatan.
- [x] Actor dan waktu lifecycle tersimpan.

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
