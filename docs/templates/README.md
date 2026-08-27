# Template Dokumentasi

Salin hanya template yang diperlukan:

- `PRD.md`: kebutuhan produk baru atau requirement yang belum jelas.
- `MODULE-README.md`: tujuan dan cara memakai module.
- `MODULE-SPECIFICATION.md`: boundary dan contract module.
- `WORK-ITEM.md`: scope capability atau perubahan signifikan.
- `IMPLEMENTATION-PLAN.md`: urutan increment.
- `TASKS.md`: checklist dan hasil verifikasi.
- `ADR.md`: keputusan yang mahal atau sulit dibalik.

Template memakai placeholder `{...}` agar jelas bagian mana yang harus diganti.
Hapus bagian yang tidak relevan. Jangan mengisi bagian dengan asumsi hanya agar
dokumen terlihat lengkap.

Untuk SakaSantri, semua template harus mengikuti baseline DDD-lite Modular
Monolith, path `app/Modules/<Namespace>/<Module>/`, ULID primary identifier,
Ziggy named routes, backend authorization sebagai source of truth, dan Bahasa
Indonesia untuk dokumentasi.
