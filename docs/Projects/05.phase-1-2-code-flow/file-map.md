# Peta File Phase 1 dan Phase 2

## Phase 1

| File | Tanggung jawab | Test/evidence |
|---|---|---|
| `composer.json` | Dependency PHP dan script quality gate | Composer install |
| `package.json` | Dependency frontend dan script npm | ESLint, TypeScript, build |
| `.env.example` | Contoh konfigurasi PostgreSQL, Redis, dan aplikasi | `starter:verify` |
| `app/Console/Commands/VerifyStarterFoundation.php` | Check environment dan runtime | `StarterFoundationVerificationTest.php` |
| `config/database.php` | Wiring database Laravel | Database health check |
| `config/cache.php` | Driver cache | Cache round-trip |
| `config/queue.php` | Driver queue | Inventory Phase 1 |

## Phase 2 Package

| File | Tanggung jawab | Test/evidence |
|---|---|---|
| `packages/StarterKit/composer.json` | Metadata dan autoload package | Package boot test |
| `StarterKitServiceProvider.php` | Register singleton dan command | Laravel boot |
| `ModuleManifest.php` | Validasi identity `module.json` | `ModuleManifestTest.php` |
| `PermissionIdentity.php` | Validasi metadata permission | `PermissionIdentityTest.php` |
| `ModuleRegistry.php` | Discovery, isolation, dan duplicate check | `ModuleRegistryTest.php` |
| `ModuleDiscoverCommand.php` | Discovery untuk developer/CI | `ModuleCommandTest.php` |
| `ModuleValidateCommand.php` | Validasi module dan exit code | `ModuleCommandTest.php` |
| `ModuleListCommand.php` | Daftar module valid | `ModuleCommandTest.php` |

## Fixture Test

| Fixture | Tujuan |
|---|---|
| `Basic` | Satu module valid dan satu invalid |
| `Duplicate` | Duplicate name/provider/path identity |
| `PermissionDuplicate` | Duplicate permission key antar module |
| `MissingSource` | `config_source` tidak tersedia |

## Jalur Data Singkat

```text
module.json -> ModuleManifest -> ModuleRegistry
  -> module:discover / module:validate / module:list
  -> human-readable atau JSON + exit code
```
