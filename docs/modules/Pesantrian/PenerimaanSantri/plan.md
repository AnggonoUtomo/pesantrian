# Implementation Plan: Pesantrian/PenerimaanSantri

## Scope

Pekerjaan ini memulai module `Pesantrian/PenerimaanSantri` sebagai foundation
PPDB / Penerimaan Santri Baru. Fokus awal adalah data calon santri, data wali
minimum saat pendaftaran, status pendaftaran, permission, audit, dan read page.

Tidak ada coding sampai user menyetujui dokumen dan menginstruksikan mulai
coding lagi.

## Increment 1: Dokumentasi module

- Perubahan:
  - `docs/modules/Pesantrian/PenerimaanSantri/README.md`
  - `docs/modules/Pesantrian/PenerimaanSantri/specification.md`
  - `docs/modules/Pesantrian/PenerimaanSantri/plan.md`
  - `docs/modules/Pesantrian/PenerimaanSantri/tasks.md`
- Dependency: baseline operasional pesantren dan module-roadmap disetujui.
- Acceptance: scope, non-scope, acceptance criteria, dependency, risiko, dan
  verifikasi tertulis.
- Verifikasi: `git diff --check`.

## Increment 2: Skeleton module

- Perubahan:
  - generate `app/Modules/Pesantrian/PenerimaanSantri/` dengan generator;
  - review `module.json`, `module.php`, `permissions.php`,
    `ServiceProvider.php`, `README.md`, dan `Routes/*`.
- Dependency: Increment 1 direview.
- Acceptance:
  - target path `app/Modules/Pesantrian/PenerimaanSantri`;
  - tidak ada folder kosong placeholder;
  - manifest valid;
  - permission identity awal tersedia.
- Verifikasi:
  - `php artisan module:make Pesantrian PenerimaanSantri --dry-run --json --no-ansi`
  - `php artisan module:make Pesantrian PenerimaanSantri --force --yes --no-ansi`
  - `php artisan module:validate --no-ansi`
  - `git diff --check`

## Increment 3: Data foundation

- Perubahan:
  - migration pendaftaran/calon santri;
  - model/persistence minimum;
  - validation rule status dan unique registration number.
- Dependency: Increment 2.
- Acceptance:
  - table memakai ULID;
  - `registration_no` unik dan dibuat otomatis oleh sistem;
  - candidate identity, wali snapshot, target unit, status, registered/decided
    timestamp tersedia;
  - status administrasi biaya pendaftaran sederhana tersedia;
  - checklist dokumen pendaftaran minimum tersedia;
  - tidak ada dependency langsung ke model privat Organization.
- Verifikasi:
  - focused migration/model tests;
  - `php artisan module:validate --no-ansi`.

## Increment 4: Backend read/list dan create/update minimum

- Perubahan:
  - query/action minimum;
  - controller/request/resource API;
  - route backend minimum;
  - authorization backend.
- Dependency: Increment 3.
- Acceptance:
  - actor berizin dapat membaca/membuat/memperbarui pendaftaran;
  - actor tanpa izin ditolak;
  - duplicate `registration_no` ditolak;
  - status biaya pendaftaran dan checklist dokumen dapat dicatat tanpa invoice
    Finance atau upload file;
  - response tidak mengekspos field sensitif.
- Verifikasi:
  - focused feature tests PenerimaanSantri;
  - `php artisan module:validate --no-ansi`;
  - `php artisan starter:verify --no-ansi`.

## Increment 5: Lifecycle status pendaftaran

- Perubahan:
  - action verify;
  - action accept;
  - action reject;
  - action cancel;
  - rule transition status.
- Dependency: Increment 4.
- Acceptance:
  - transisi draft/submitted/verified/accepted/rejected/cancelled berjalan;
  - terminal state tidak bisa dimutasi sembarangan;
  - invalid transition menghasilkan validation error yang jelas.
- Verifikasi:
  - focused lifecycle tests PenerimaanSantri.

## Increment 6: Audit mutation

- Perubahan:
  - audit create/update;
  - audit verify/accept/reject/cancel;
  - metadata audit aman.
- Dependency: Increment 4-5 dan bridge audit existing.
- Acceptance:
  - mutation menghasilkan audit entry/event;
  - metadata audit tidak memuat secret atau payload sensitif berlebihan.
- Verifikasi:
  - focused audit tests PenerimaanSantri.

## Increment 7: Candidate conversion contract ke Santri

- Perubahan:
  - dokumentasikan candidate contract pendaftaran accepted yang siap dikonversi;
  - implementasi source hanya bila `Pesantrian/Santri` menjadi consumer nyata.
- Dependency: Increment 5-6.
- Acceptance:
  - contract tidak mengekspos model Infrastructure;
  - DTO cukup ringkas untuk membuat data induk Santri;
  - tidak ada direct dependency lintas module.
- Verifikasi:
  - focused contract/query tests bila diimplementasikan;
  - `php artisan module:validate --no-ansi`.

Catatan hasil:

- Contract accepted admission didokumentasikan pada
  `docs/modules/Pesantrian/PenerimaanSantri/contracts/accepted-admission.md`.
- Runtime contract belum dibuat karena `app/Modules/Pesantrian/Santri` belum
  tersedia, sehingga belum ada consumer nyata.
- Saat `Pesantrian/Santri` dibuat, implementasi harus memakai public
  `AcceptedAdmissionReader` dan DTO ringkas, bukan membaca
  `StudentAdmissionRecord` secara langsung.

## Increment 8: UI/Inertia read page

- Perubahan:
  - route web Inertia PenerimaanSantri;
  - controller presentation untuk props daftar pendaftaran;
  - frontend canonical di
    `resources/js/pages/Pesantrian/PenerimaanSantri/`;
  - menu sidebar namespace Pesantrian.
- Dependency: Increment 4-7.
- Acceptance:
  - page resolve dari canonical frontend module;
  - `Index.tsx` hanya menjadi komposer layout;
  - komponen business-specific berada di folder `components`;
  - backend tetap authority untuk permission.
- Verifikasi:
  - focused presentation tests PenerimaanSantri;
  - focused sidebar/Ziggy tests;
  - `npm run types:check`;
  - `npm run lint:check`;
  - `npm run build`.

## Checkpoint UI

Increment UI setelah Increment 8 dimulai hanya setelah user menginstruksikan
lanjut. Setiap increment wajib tetap kecil, terverifikasi, dan dilaporkan
sebelum melanjutkan ke increment berikutnya.

## Catatan Hasil Increment 8

- Web route Inertia `pesantrian.admissions.index` tersedia untuk halaman
  internal/admin PPDB.
- Backend permission tetap menjadi authority melalui
  `penerimaan_santri.view`.
- Frontend canonical tersedia di
  `resources/js/pages/Pesantrian/PenerimaanSantri/`.
- `Index.tsx` dijaga sebagai komposer page/layout; komponen business-specific
  ditempatkan di folder `components`.
- Sidebar memiliki namespace `Pesantrian` dengan menu
  `PPDB / Penerimaan Santri` untuk actor berizin view/manage/decide.

## Increment 9: UI mutation create/edit

- Perubahan:
  - dialog create/edit pendaftaran internal/admin;
  - tombol tambah dan edit pada read page;
  - submit JSON ke API create/update dengan `Idempotency-Key`;
  - route API store/update ditambahkan ke whitelist Ziggy;
  - field error backend ditampilkan pada form.
- Dependency: Increment 8 dan API create/update Increment 4.
- Acceptance:
  - actor dengan `penerimaan_santri.manage` melihat aksi mutation;
  - actor tanpa manage hanya membaca daftar;
  - `Index.tsx` tetap menjadi komposer dan form berada di folder `components`;
  - mutation lifecycle accept/reject/cancel tidak masuk scope increment ini.
- Verifikasi:
  - focused presentation/Ziggy tests;
  - `npm run types:check`;
  - `npm run lint:check`;
  - `npm run build`.

## Increment 10: UI lifecycle action

- Perubahan:
  - tombol lifecycle pada daftar pendaftaran;
  - dialog konfirmasi untuk verify/accept/reject/cancel;
  - submit JSON ke API lifecycle dengan `Idempotency-Key`;
  - route API lifecycle ditambahkan ke whitelist Ziggy.
- Dependency: Increment 5 lifecycle backend dan Increment 8-9 UI page.
- Acceptance:
  - actor dengan `penerimaan_santri.decide` melihat aksi lifecycle sesuai
    status pendaftaran;
  - status `submitted` dapat diarahkan ke verifikasi;
  - status `verified` dapat diarahkan ke terima/tolak/batal;
  - status terminal tidak menampilkan aksi lifecycle;
  - `Index.tsx` tetap menjadi komposer dan aksi berada di folder
    `components`.
- Verifikasi:
  - focused presentation/Ziggy/API lifecycle tests;
  - `npm run types:check`;
  - `npm run lint:check`;
  - `npm run build`.

## Increment 11: Detail pendaftaran PPDB

- Perubahan:
  - tombol `Lihat detail` pada daftar pendaftaran;
  - dialog detail pendaftaran;
  - tampilan ringkasan status, data calon santri, data wali, administrasi
    biaya, checklist dokumen, dan riwayat keputusan.
- Dependency: Increment 8 read page dan data list yang sudah memuat field
  detail minimum.
- Acceptance:
  - actor dengan `penerimaan_santri.view` dapat membuka detail dari daftar;
  - detail tidak membutuhkan endpoint baru pada baseline awal;
  - detail berada di folder `components`, bukan ditumpuk di `Index.tsx`;
  - field kosong tampil dengan bahasa operator yang jelas.
- Verifikasi:
  - focused presentation/source guard test;
  - `npm run types:check`;
  - `npm run lint:check`;
  - `npm run build`.

## Increment 12: QA UI PPDB

- Perubahan:
  - review flow UI PPDB end-to-end;
  - cek tambah/edit/detail/lifecycle/filter/pagination;
  - catat gap yang perlu diputuskan sebelum module PPDB dianggap baseline
    usable.
- Dependency: Increment 9-11.
- Acceptance:
  - tidak ada console/runtime error yang diketahui pada flow utama;
  - route Ziggy yang dipakai frontend tersedia;
  - command artisan, focused tests, typecheck, lint, dan build lulus.
- Verifikasi:
  - focused feature tests PenerimaanSantri;
  - `php artisan module:validate --no-ansi`;
  - `php artisan optimize:clear --no-ansi`;
  - `php artisan starter:verify --no-ansi`;
  - `npm run types:check`;
  - `npm run lint:check`;
  - `npm run build`;
  - manual/browser QA bila environment frontend aktif.

Catatan hasil:

- Automated gate PPDB lulus melalui focused feature tests, PHPStan, Pint,
  Vitest, typecheck, lint, build, module validation, `optimize:clear`, dan
  `starter:verify`.
- Smoke browser login desktop/mobile lulus.
- Authenticated browser QA khusus PPDB belum dibuat karena membutuhkan
  credential/fixture E2E PPDB yang aman.

## Keputusan Awal

- PPDB awal adalah flow internal/admin.
- Nomor pendaftaran dibuat otomatis oleh sistem memakai konfigurasi nomor di
  `System/SystemSetting`, contoh prefix `SNTR` dengan sequence auto-generate
  untuk bagian `-xxxx`.
- Data wali disimpan sebagai snapshot pendaftaran dulu, lalu dipromosikan
  melalui contract/use case setelah module `Pesantrian/WaliSantri` tersedia.
- Biaya pendaftaran opsional per periode PPDB. PPDB menyimpan status
  administrasi dan nominal sederhana; invoice/payment lifecycle tetap milik
  `Finance/StudentFinance`.
- Dokumen pendaftaran berupa checklist verifikasi; upload/arsip file digital
  menunggu `Support/Document`.

## Rollback

- Revert commit per increment.
- Untuk migration yang sudah dijalankan lokal, gunakan rollback migration
  module sesuai mekanisme Laravel sebelum menghapus source.
- Jangan mengubah atau menghapus data/module `Organization/Organization`,
  `Academic/AcademicPeriod`, atau `HumanResource/HumanResource` sebagai bagian
  dari rollback PenerimaanSantri.
