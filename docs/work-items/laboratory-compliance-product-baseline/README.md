# Work Item: Normalisasi Product Baseline Laboratory Compliance

## Status

Done

## Owner dan lokasi

- Owner module: lintas module.
- Target: dokumentasi produk dan arsitektur pada `docs/`.

## Kondisi awal

Draft `v0.1-r5-ID` memuat visi produk yang kuat, tetapi mencampur requirement
produk, detail generator, struktur lama, dan contoh yang tidak kompatibel
dengan starter13. Draft juga bergantian memakai domain `Platform` dan
placeholder `namespace`.

## Scope

- Menjadikan draft sebagai product baseline ringkas dan authoritative.
- Mempertahankan module fondasi pada domain `System`.
- Menetapkan domain bisnis `Organization`, `Laboratory`, dan `Compliance`.
- Menyelaraskan project index, module map, dan keputusan arsitektur.
- Mempertahankan draft asli sebagai arsip historis.

## Tidak dikerjakan

- Membuat atau mengubah source module.
- Menetapkan aggregate, schema database, event, API, permission, atau UI.
- Menutup open decision milik specification module berikutnya.

## Acceptance criteria

- [x] Product baseline menjelaskan visi, pengguna, scope, invariant, module,
  phase, kriteria keberhasilan, dan open decision.
- [x] Semua path, identifier, manifest, dan dependency rule mengikuti starter13.
- [x] Keputusan domain dicatat sebagai ADR Accepted.
- [x] Draft sumber tetap dapat ditelusuri di `Old_docs/`.

## Dependency dan keputusan

- `AGENTS.md` aktif menjadi aturan authoritative.
- User menyetujui domain `System`, `Organization`, `Laboratory`, dan
  `Compliance` pada 2026-08-20.
- ADR-005, ADR-006, dan ADR-007 berlaku.

## Handoff

- Perubahan: draft dinormalisasi menjadi baseline produk yang kompatibel dengan
  starter13 dan indeks dokumentasi diperbarui.
- Verifikasi: lihat `tasks.md`.
- Risiko terbuka: boundary `AuditLog`/`AuditTrail`, data standard, amendment,
  evidence retention, uncertainty, dan detail model organisasi.
