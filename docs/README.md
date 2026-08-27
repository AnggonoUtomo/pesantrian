# Dokumentasi [Nama Project]

Dokumentasi ini adalah acuan aktif yang ringkas. Isi placeholder dan cocokkan
setiap aturan dengan source code sebelum digunakan.

## Mulai di sini

1. Baca [Project](PROJECT.md) untuk tujuan dan scope produk.
2. Baca [Arsitektur](ARCHITECTURE.md) untuk batas dan arah dependency.
3. Baca [Struktur Folder](FOLDER-STRUCTURE.md) sebelum mengubah struktur.
4. Baca [Daftar Module](MODULES.md) untuk ownership dan dependency.
5. Baca [Workflow](WORKFLOW.md) sebelum memulai pekerjaan.
6. Pilih pemeriksaan dari [Quality](QUALITY.md) sesuai risiko.
7. Baca [Keputusan](DECISIONS.md) saat menyentuh keputusan aktif.
8. Baca [API](API.md) saat mengubah endpoint publik.

Template tersedia di [`templates/`](templates/README.md). Konvensi dokumentasi
module dan work item berada di [`modules/`](modules/README.md) dan
[`work-items/`](work-items/README.md).

## Stack

- Backend: `[Laravel dan versi PHP]`.
- Frontend: `[framework, bundler, dan UI library]`.
- Database/cache/queue: `[teknologi yang digunakan]`.
- CI/deployment: `[pipeline dan target environment]`.

## Prinsip

Dokumentasikan contract publik, perubahan behavior, langkah operasi, serta
keputusan yang sulit dibalik. Jangan menduplikasi kode atau membuat execution
log panjang untuk perubahan kecil.

Semua tautan memakai path relatif. Hapus semua placeholder sebelum baseline
project dinyatakan aktif.
