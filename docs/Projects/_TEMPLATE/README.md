# {Nama Project}

{Ringkasan singkat tujuan project atau module.}

## Status

`Discovery`.

{Ringkasan kemampuan yang sudah ada dan kemampuan yang sedang dikerjakan.}

## Boundary Module

- Parent boundary: `{System atau domain bisnis}`.
- Target code path: `app/Modules/{Domain}/{Module}`.
- Namespace: `App\\Modules\\{Domain}\\{Module}`.
- Owner capability: `{kemampuan yang dimiliki module}`.

## Prompt Generator dan Hasil yang Diharapkan

Tulis prompt generator resmi, command dry-run, dan command pembuatan aktual di
`implementation-plan.md` dan `tasks.md`. Dry-run harus menghasilkan
`MODULE_PREVIEWED` tanpa menulis file. Pembuatan aktual harus menghasilkan
`MODULE_CREATED` dan structure yang sesuai baseline.

## Urutan baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. {ADR terkait jika ada}

## Dokumen terkait

- {Dokumen baseline yang menjadi acuan}
- {Dokumen module atau project yang terdampak}

## Cara verifikasi

1. `{command atau langkah verifikasi pertama}`
2. `{command atau langkah verifikasi berikutnya}`
3. `php artisan module:discover --json`
4. `php artisan module:validate --json`
5. `php artisan module:list --json`
6. `php artisan module:inspect {Domain}/{ExistingModule} --json`

## Verifikasi implementasi {YYYY-MM-DD}

- {Command}: {hasil}.
