# Implementation Plan: {Nama Project}

## Architecture

{Gambarkan alur utama, misalnya FormRequest -> DTO -> Service -> Transaction.}

## Preflight dan Generator

1. Baca `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, dan baseline module.
2. Catat project intake dan jalankan inventory module existing.
3. Jalankan dry-run sebelum generator mutasi.

Prompt resmi:

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module existing dengan module:discover, module:validate, dan
module:list. Buat module {Module} pada domain {Domain} dengan profile
default-v1. Tampilkan planned output pada dry-run dan jangan menulis file
sebelum hasilnya ditinjau.
```

Expected output:

- dry-run: `MODULE_PREVIEWED`, target dan planned structure benar, tidak ada
  perubahan filesystem;
- actual: `MODULE_CREATED`, manifest/provider/runtime config/permission source,
  README, dan structure canonical tersedia;
- module existing tidak tertimpa;
- business logic belum dianggap selesai pada tahap skeleton.

## Urutan

1. Contract dan dokumentasi.
2. Test fokus dalam kondisi RED.
3. Implementasi capability inti.
4. Authorization, audit, dan security guard.
5. Frontend vertical slice: page, route Ziggy, state UI, permission visibility,
   responsive layout, dan browser/accessibility test.
6. API atau integrasi terkait.
7. Quality gate dan review scope.
8. Tinjau checklist sebelum increment dan setelah increment; jangan lanjut jika
   evidence increment sebelumnya belum lengkap.

## Risiko

| Risiko | Mitigasi |
|---|---|
| {Risiko teknis atau operasional} | {Cara mencegah atau mendeteksi} |

## Rollback

{Jelaskan cara membatalkan perubahan. Sertakan catatan migration, data
production, dan batasan rollback bila ada.}
