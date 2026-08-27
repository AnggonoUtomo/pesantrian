# Tasks: Normalisasi Product Baseline

## Sebelum mulai

- [x] Scope dan non-scope disetujui.
  - Kondisi awal: user meminta normalisasi tanpa permintaan coding.
  - Keputusan: domain `System`, `Organization`, `Laboratory`, dan `Compliance`
    disetujui pada 2026-08-20.
- [x] Authoritative source ditentukan.
  - Kondisi awal: referensi sesi ke `docs/AGENTS.md` bertentangan dengan
    repository karena file tersebut telah sengaja dihapus.
  - Keputusan: user menetapkan `AGENTS.md` aktif sebagai authoritative.
- [x] Verification ditentukan.
  - Pemeriksaan: status Git, tautan Markdown, forbidden term, whitespace, dan
    konsistensi module/domain.

## Pekerjaan

- [x] Normalisasi baseline produk.
  - Kondisi awal: draft terdiri dari 3.411 baris dan mencampur product scope
    dengan detail tooling lama.
  - Perubahan: buat baseline aktif yang fokus pada visi, requirement,
    invariant, module ownership, phase, dan open decision.
  - Alasan: specification berikutnya membutuhkan sumber produk yang kompatibel
    dengan starter13.
  - Evidence: `docs/PRODUCT-BASELINE.md` tersedia dan draft asli diarsipkan.
- [x] Rekam keputusan pembagian domain.
  - Kondisi awal: keputusan hanya terdapat dalam percakapan.
  - Perubahan: tambah ADR-007 berstatus Accepted.
  - Alasan: pemindahan namespace module mahal untuk dibalik.
  - Evidence: `docs/decisions/ADR-007-PRODUCT-DOMAIN-OWNERSHIP.md` tersedia.
- [x] Selaraskan dokumentasi downstream.
  - Kondisi awal: project dan module index hanya menjelaskan starter kit.
  - Perubahan: perbarui `PROJECT.md`, `MODULES.md`, `DECISIONS.md`, dan
    `README.md`.
  - Alasan: pembaca harus mendapatkan konteks produk dan status implementasi
    yang sama dari semua entry point.
  - Evidence: tautan serta tabel module mengarah ke baseline aktif.

## Hasil

- [x] Scope selesai.
  - Kondisi awal: draft sumber belum menjadi bagian baseline aktif dan dokumen
    project masih menggambarkan starter kit generik.
  - Perubahan: draft 3.411 baris diarsipkan; product baseline, PRD, ADR-007,
    project index, module map, plan, dan handoff diselaraskan.
  - Alasan: pekerjaan module berikutnya membutuhkan satu sumber produk yang
    konsisten dengan arsitektur starter13.
  - Evidence: pemeriksaan tautan lokal menghasilkan `MARKDOWN_LINKS_OK`;
    `git diff --check` selesai tanpa error.
  - Risiko terbuka: boundary `AuditLog`/`AuditTrail`, data standard, amendment,
    evidence retention, uncertainty, dan detail model organisasi dipindahkan ke
    work item module pemilik; tidak ditetapkan melalui asumsi.
