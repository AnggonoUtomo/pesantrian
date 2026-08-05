# Alur Code Phase 2

## 1. Tujuan

Phase 2 membuat contract framework untuk module. Code ditempatkan pada
`packages/StarterKit` agar dapat digunakan kembali oleh aplikasi dan generator.

Urutan implementasi:

```text
Composer package -> ServiceProvider -> ModuleManifest
  -> PermissionIdentity -> ModuleRegistry -> command module:*
```

## 2. Composer Package

`packages/StarterKit/composer.json` mendefinisikan package
`starterkit/framework`.

Bagian pentingnya:

- PHP `^8.4`.
- Dependency Illuminate yang dibutuhkan package.
- PSR-4 `StarterKit\\` ke folder `src/`.
- Laravel provider discovery melalui `StarterKitServiceProvider`.

Root `composer.json` menambahkan path repository ke `packages/StarterKit`.
Composer dapat melakukan autoload package lokal tanpa menyalin source ke `app/`.

## 3. Service Provider

File `packages/StarterKit/src/StarterKitServiceProvider.php` menjadi pintu
masuk package ke Laravel.

Alurnya:

1. `register()` mendaftarkan `ModuleRegistry` sebagai singleton.
2. Saat berjalan di console, provider mendaftarkan tiga command module.
3. Laravel dapat melakukan dependency injection `ModuleRegistry` ke command.

Provider hanya mengatur wiring framework. Ia tidak membaca manifest dan tidak
menjalankan business rule module.

## 4. ModuleManifest

File: `packages/StarterKit/src/Modules/Contracts/ModuleManifest.php`.

`ModuleManifest` adalah readonly value object untuk identity deklaratif module.
Data dibaca dari `module.json`, lalu divalidasi melalui `fromArray()`.

Field yang divalidasi:

- `name` dan `domain` harus PascalCase.
- `namespace` harus memiliki segment PascalCase.
- `version` harus semver sederhana.
- `schema_version` harus bilangan positif.
- `status` harus `enabled` atau `disabled`.
- `path` harus mengikuti `app/Modules/{Domain}/{Module}`.
- `provider` harus berakhir dengan `ServiceProvider`.
- `dependencies` harus berupa array string.
- `permission_source` dan `config_source` harus nama file PHP.

Data invalid melempar `InvalidArgumentException`. Test ada di
`tests/Unit/ModuleManifestTest.php`.

## 5. PermissionIdentity

File: `packages/StarterKit/src/Modules/Contracts/PermissionIdentity.php`.

Contract ini memvalidasi metadata permission milik module:

- `key`: dot notation, contoh `access_control.role.manage`.
- `description`: penjelasan permission.
- `module`: owner permission dalam PascalCase.
- `sensitive`: boolean untuk permission sensitif.

Test ada di `tests/Unit/PermissionIdentityTest.php`. Duplicate key lintas module
ditangani registry karena membutuhkan konteks banyak module.

## 6. ModuleRegistry

File: `packages/StarterKit/src/Modules/ModuleRegistry.php`.

Registry melakukan discovery read-only. Ia tidak membuat, mengubah, atau
menghapus file module.

Alur satu manifest:

```text
glob module.json -> baca JSON -> ModuleManifest::fromArray()
  -> cek duplicate name/path/namespace/provider
  -> cek permission_source dan config_source tersedia
  -> require permissions.php -> PermissionIdentity::fromArray()
  -> cek duplicate permission key -> masukkan module valid
```

Jika satu module gagal, exception ditangkap dan dimasukkan ke `diagnostics`.
Module lain tetap diproses. Hasil `discover()` memiliki `modules` valid dan
`diagnostics` yang berisi `path` serta `message`.

`module.php` hanya diverifikasi keberadaannya. Isinya tidak dieksekusi karena
discovery harus tetap read-only.

## 7. Console Command

| Command | Peran | Code sukses | Code gagal |
|---|---|---|---|
| `module:discover` | Menemukan module dan diagnostic | `MODULE_DISCOVERED` | `MODULE_DISCOVERY_FAILED` |
| `module:validate` | Melaporkan module valid dan diagnostic | `MODULE_VALID` | `MODULE_INVALID` |
| `module:list` | Menampilkan module valid | `MODULE_LISTED` | `MODULE_LIST_FAILED` |

Semua command mendukung `--json` dan exit code `0` saat sukses serta `1` saat
ada diagnostic. Test ada di `tests/Feature/ModuleCommandTest.php`.

## 8. Alasan Urutan

- Package harus terdaftar sebelum class dapat di-autoload.
- Provider harus tersedia sebelum command dan singleton dipakai Laravel.
- Manifest harus valid sebelum registry mempercayai identity module.
- Permission identity harus valid sebelum registry memeriksa duplicate global.
- Registry harus stabil sebelum command menjadi interface developer dan CI.
