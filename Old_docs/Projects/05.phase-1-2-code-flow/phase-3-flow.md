# Alur Code Phase 3

## 1. Tujuan

Phase 3 membuat generator `module:make` untuk menghasilkan module baru sesuai
struktur canonical. Generator memakai contract dari Phase 2 dan tidak boleh
menimpa module yang sudah ada.

Alur utama:

```text
ModuleGenerationRequest
    -> DefaultModuleProfile
    -> ModuleGenerationPlan
    -> ModuleGenerationPreviewer
    -> ModuleConflictDetector
    -> ModulePromotionService
    -> module:make
```

## 2. Input Contract

File: `packages/StarterKit/src/Generator/Contracts/ModuleGenerationRequest.php`.

`ModuleGenerationRequest` adalah readonly DTO untuk menjaga input generator
tetap typed dan tervalidasi.

Input yang digunakan:

- `module`: wajib PascalCase, contoh `AccessControl`.
- `domain`: wajib PascalCase, contoh `System`.
- `profile`: kebab-case, default `default-v1`.
- `dry_run`: preview tanpa menulis file.
- `force`: wajib untuk operasi mutasi.
- `yes`: hanya boleh digunakan bersama `force`.

Input invalid menghasilkan `InvalidArgumentException` sebelum filesystem
diubah.

## 3. Profile dan Plan

File utama:

- `packages/StarterKit/src/Generator/Profiles/DefaultModuleProfile.php`.
- `packages/StarterKit/src/Generator/Contracts/ModuleGenerationPlan.php`.

`DefaultModuleProfile` mengubah request menjadi plan deterministic. Plan berisi:

- profile yang dipakai;
- target `app/Modules/{Domain}/{Module}`;
- daftar directory DDD-lite;
- isi `module.json`, `module.php`, `permissions.php`, provider, README, dan
  route entry point.

Profile hanya membuat data plan. Pada tahap ini belum ada filesystem mutation.
Request dengan profile yang belum didukung ditolak.

## 4. Preview dan Conflict Detection

File utama:

- `ModuleGenerationPreviewer.php`.
- `ModuleConflictDetector.php`.
- `ModuleGenerationPreview.php`.

Sebelum menulis file, generator:

1. Membuat plan dari profile.
2. Memeriksa target sudah ada atau belum.
3. Memakai `ModuleRegistry` untuk membaca module existing.
4. Memeriksa duplicate name, path, namespace, dan provider.
5. Memeriksa target tetap berada di bawah `app/Modules`.

Jika ada diagnostic, command berhenti sebelum membuat staging atau target.
Mode `--dry-run` menampilkan plan yang valid tanpa mutation.

## 5. Staging dan Atomic Promotion

File: `packages/StarterKit/src/Generator/ModulePromotionService.php`.

Alur promotion:

```text
buat staging ULID
    -> buat directory dan file pada staging
    -> validasi relative path
    -> buat parent target
    -> rename staging ke target
    -> hapus staging saat terjadi exception
```

Target existing selalu ditolak. `--force` hanya menyatakan bahwa caller
mengizinkan mutasi; `--force` tidak membuka mode overwrite. Strategi ini menjaga
proses aman pada environment Windows dan mencegah module setengah jadi.

## 6. Console Command

File: `packages/StarterKit/src/Console/Commands/ModuleMakeCommand.php`.

Contoh penggunaan:

```bash
php artisan module:make AccessControl --domain=System --dry-run --json
php artisan module:make AccessControl --domain=System --force --yes --json
```

Output memiliki code stabil:

- `MODULE_PREVIEWED`: plan valid dan tidak ada file ditulis.
- `MODULE_CREATED`: module berhasil dipromosikan.
- `MODULE_GENERATION_FAILED`: conflict atau diagnostic ditemukan.
- `MODULE_GENERATION_INVALID`: input atau proses tidak valid.

Output JSON tidak boleh berisi secret, credential, token, atau sensitive
payload.

## 7. Test dan Verifikasi

Test utama:

- `tests/Unit/ModuleGenerationRequestTest.php` untuk input valid/invalid.
- `tests/Unit/DefaultModuleProfileTest.php` untuk plan deterministic.
- `tests/Unit/ModuleGenerationPreviewTest.php` untuk preview dan conflict.
- `tests/Unit/ModulePromotionServiceTest.php` untuk promotion dan cleanup.
- `tests/Feature/ModuleMakeCommandTest.php` untuk CLI, JSON, dry-run, force,
  invalid input, dan conflict.

Perintah verifikasi:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan test
```

Evidence terakhir Phase 3: focused generator 20 test/65 assertion dan full
backend 85 test/256 assertion lulus. Mode extension dan overwrite masih di luar
scope.

## 8. Alasan Urutan

- Input harus valid sebelum profile membuat plan.
- Plan harus deterministic sebelum conflict diperiksa.
- Conflict harus bersih sebelum filesystem disentuh.
- Staging harus selesai sebelum atomic promotion.
- Command menjadi adapter terakhir agar seluruh behavior dapat diuji terpisah.
