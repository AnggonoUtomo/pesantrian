# Keputusan Aktif

## Arsitektur modular

Starter13 memakai DDD-lite Modular Monolith. Batas modul dan public contract
dipertahankan agar perubahan satu modul tidak mengikat implementasi modul lain.
Hexagonal Architecture menetapkan arah dependency dan pemisahan port-adapter.
Lihat [ADR-006](decisions/ADR-006-DDD-LITE-MODULAR-MONOLITH-HEXAGONAL.md).

## Domain produk

Module fondasi tetap berada di domain `System`. Module bisnis Laboratory
Compliance Platform ditempatkan pada domain `Organization`, `Laboratory`, dan
`Compliance`; domain generik `Platform` dan placeholder `namespace` tidak
digunakan. Lihat
[ADR-007](decisions/ADR-007-PRODUCT-DOMAIN-OWNERSHIP.md).

## Runtime setting

Dynamic module bootstrap dan port runtime setting yang dimiliki consumer
berstatus **Accepted** (ADR-005). Implementasi harus tetap menjaga ownership
setting pada consumer dan tidak membuat dependency konkret lintas modul.
Lihat [ADR-005](decisions/ADR-005-DYNAMIC-MODULE-AND-RUNTIME-SETTING.md).

## Input setting sensitif

Command `system-setting:set {key} {value}` boleh mempertahankan argumen posisi
untuk nilai biasa. Untuk setting sensitif, nilai posisi ditolak; gunakan input
interaktif tersembunyi atau `--value-stdin` untuk otomasi.

## Migrasi legacy user ID

Migrasi legacy user ID ke ULID berstatus **Accepted**. Detail historis dan
runbook sebelumnya tetap tersedia di `../Old_docs/`.

## Arsip dokumentasi

Baseline dokumentasi lama diarsipkan ke `../Old_docs/` agar riwayat tetap dapat
ditelusuri tanpa membebani workflow aktif. Instruksi root lama disimpan di
`../Old_AGENTS.md`.
