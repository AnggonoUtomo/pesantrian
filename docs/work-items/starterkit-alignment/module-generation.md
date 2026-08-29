# Panduan Pembuatan Module Baru

Panduan ini menjadi pegangan awal saat membuat module baru SakaSantri dengan
generator project. Generator sudah diselaraskan dengan baseline
`app/Modules/<Namespace>/<Module>` dan tidak membuat folder kosong sebagai
placeholder.

## Istilah

- `Namespace` adalah area/kategori bisnis, misalnya `Organization`,
  `Academic`, `StudentLife`, `Finance`, `Platform`, atau `Console`.
- `Module` adalah bounded capability nyata di dalam namespace tersebut,
  misalnya `Organization`, `AcademicPeriod`, `Student`, atau `Document`.
- `--domain` masih tersedia hanya sebagai alias kompatibilitas untuk pola lama.
  Untuk module baru, gunakan argumen `Namespace Module`.

## Alur Aman

1. Pastikan branch kerja benar dan bersih.

   ```bash
   git status --short --branch
   ```

2. Baca dokumen module/roadmap yang relevan.

   - `docs/MODULES.md`
   - `docs/work-items/module-roadmap/plan.md`
   - work item module yang sedang dikerjakan

3. Jalankan dry-run generator.

   ```bash
   php artisan module:make <Namespace> <Module> --dry-run --json --no-ansi
   ```

4. Review target, file plan, dan diagnostics. Jangan lanjut bila target atau
   namespace tidak sesuai baseline.

5. Buat module hanya setelah dry-run valid dan scope disetujui.

   ```bash
   php artisan module:make <Namespace> <Module> --force --yes --no-ansi
   ```

6. Tambahkan isi layer secara incremental. Jangan membuat folder, port,
   adapter, event, repository, service, policy, atau test sebagai placeholder.

7. Jalankan verifikasi sesuai risiko perubahan.

## Contoh Command

Dry-run module foundation pertama:

```bash
php artisan module:make Organization Organization --dry-run --json --no-ansi
```

Hasil yang diharapkan:

- `success: true`
- `target: app/Modules/Organization/Organization`
- `directories: []`
- file awal:
  - `module.json`
  - `module.php`
  - `permissions.php`
  - `ServiceProvider.php`
  - `README.md`
  - `Routes/api.php`
  - `Routes/web.php`
  - `Routes/console.php`
  - `Routes/channels.php`

Create module setelah dry-run valid:

```bash
php artisan module:make Organization Organization --force --yes --no-ansi
```

Contoh legacy yang masih didukung untuk compatibility bridge:

```bash
php artisan module:make Billing --domain=System --dry-run --json --no-ansi
```

Pola legacy di atas tidak menjadi standar module baru.

## Aturan Struktur

- Root module berada di `app/Modules/<Namespace>/<Module>/`.
- Migration module berada di `Database/Migrations`.
- Table aplikasi memakai ULID sebagai primary identifier.
- `ServiceProvider.php` adalah composition root, bukan tempat business logic.
- Route module berada di `Routes/`.
- Frontend mengikuti baseline `resources/js/pages/<Namespace>/<Module>/` saat UI module
  mulai dibuat.
- Folder optional/on-demand hanya dibuat ketika memiliki isi dan concern nyata.

Folder yang tidak dibuat otomatis dan tidak boleh dibuat sebagai placeholder:

- `Application/Commands`
- `Application/Queries`
- `Application/Contracts`
- `Domain/Exceptions`
- `Domain/Policies`
- `Domain/Specifications`
- `Infrastructure/Adapters`
- `Infrastructure/Integrations`
- folder test tanpa test nyata

## Checklist Sebelum Membuat Module

- [ ] Namespace dan module sudah ada di roadmap atau disetujui user.
- [ ] Boundary module jelas: ownership, data, rule, dan lifecycle.
- [ ] Dependency lintas module nyata, bukan prediksi.
- [ ] Tidak ada direct dependency ke concrete module lain.
- [ ] Nama table, permission key, route name, dan Inertia path tidak bertabrakan.
- [ ] Dry-run generator valid.

## Checklist Setelah Membuat Module

- [ ] `module.json` sesuai target namespace/module.
- [ ] `permissions.php` hanya berisi permission nyata; boleh kosong jika belum
  ada permission.
- [ ] `ServiceProvider.php` tidak berisi business logic.
- [ ] Route file kosong tidak dipaksa berisi route placeholder.
- [ ] Folder layer hanya ditambahkan saat ada class/file nyata.
- [ ] Migration memakai ULID untuk table aplikasi.
- [ ] Dokumentasi module dibuat di `docs/modules/<Namespace>/<Module>/` saat
  pekerjaan module dimulai.

## Verifikasi Minimum

Untuk module baru tanpa behavior:

```bash
php artisan module:validate --no-ansi
git diff --check
```

Untuk module dengan backend behavior:

```bash
php artisan module:validate --no-ansi
php artisan test --filter=<NamaTestTerkait>
php artisan starter:verify --no-ansi
git diff --check
```

Untuk module dengan frontend:

```bash
npm run types:check
npm run build
```

Gunakan command frontend yang tersedia di project bila nama script berubah.

## Catatan Bridge `System` ke `Console`

Module starterkit existing masih berada di `app/Modules/System/*` dan consumer
lama masih memakai route `system/*`, route name `system.*` atau
`access-control.*`, serta Inertia path `System/*`. Jangan rename ke `Console`
tanpa work item migrasi dan compatibility plan.
