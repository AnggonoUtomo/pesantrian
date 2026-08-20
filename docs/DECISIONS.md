# Keputusan Aktif

## Arsitektur modular

Starter13 memakai DDD-lite Modular Monolith. Batas modul dan public contract
dipertahankan agar perubahan satu modul tidak mengikat implementasi modul lain.

## Runtime setting

Dynamic module bootstrap dan port runtime setting yang dimiliki consumer
berstatus **Accepted** (ADR-005). Implementasi harus tetap menjaga ownership
setting pada consumer dan tidak membuat dependency konkret lintas modul.

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
