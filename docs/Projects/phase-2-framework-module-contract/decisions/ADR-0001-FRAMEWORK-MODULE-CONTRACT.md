# ADR-0001: Framework dan Module Contract

## Status

Diterima.

## Konteks

Phase 1 sudah memverifikasi starter kit, tetapi `packages/StarterKit`, manifest
module, registry, dan command discovery belum ada. Phase 3 membutuhkan contract
stabil agar generator tidak membuat struktur yang berbeda-beda.

## Keputusan

1. Reusable framework ditempatkan pada `packages/StarterKit`.
2. `module.json` menjadi satu-satunya manifest deklaratif module.
3. `module.php` menjadi sumber konfigurasi runtime, bukan manifest kedua.
4. `permissions.php` menjadi sumber permission identity milik module owner.
5. Registry Phase 2 bersifat read-only: discovery, validation, dan listing tidak
   membuat atau menimpa file module.
6. Module invalid diisolasi; module valid tetap dapat ditemukan dan ditampilkan.
7. Duplicate name, path, namespace, provider, dan permission key ditolak.
8. Semua command registry menyediakan output human-readable dan `--json`.
9. Phase 2 tidak membuat `module:make`; generator dimulai pada Phase 3 setelah
   contract dan registry lulus test.

## Alasan

Keputusan ini memisahkan identity deklaratif, konfigurasi runtime, dan permission
ownership. Read-only registry mengurangi risiko overwrite sebelum generator
memiliki staging dan atomic promotion.

## Dampak

- Phase 2 membutuhkan package, schema validator, registry, command, dan test.
- Phase 3 dapat memakai contract yang sama tanpa menebak struktur module.
- Developer belum dapat membuat module melalui command sampai Phase 3.

## Alternatif yang Ditolak

- Menjadikan `module.php` sebagai manifest utama: ditolak karena manifest perlu
  dibaca tanpa mengeksekusi konfigurasi runtime.
- Membuat module manual sekarang: ditolak karena generator adalah jalur resmi.
- Menghentikan seluruh discovery saat satu module invalid: ditolak karena module
  valid harus tetap dapat diproses.

## Verifikasi

- Schema test manifest dan permission.
- Discovery test untuk module valid, invalid, dan duplicate.
- Command test untuk output biasa, JSON, dan exit code.
- Security test untuk secret dan sensitive payload pada diagnostic.
