# Implementation Plan: Normalisasi Product Baseline

## Scope

Mengubah draft arsitektur panjang menjadi product baseline aktif yang ringkas,
dapat ditelusuri, dan kompatibel dengan starter13 tanpa menyentuh kode.

## Increment 1: Baseline produk

- Kondisi awal: draft menggunakan struktur serta convention lama.
- Perubahan: arsipkan draft dan buat `docs/PRODUCT-BASELINE.md`.
- Dependency: keputusan user mengenai pembagian domain.
- Acceptance: visi, scope, invariant, module, phase, dan open decision jelas.
- Verifikasi: pencarian istilah lama dan pemeriksaan tautan Markdown.

## Increment 2: Traceability keputusan

- Kondisi awal: keputusan pembagian domain hanya ada di percakapan.
- Perubahan: buat ADR-007 berstatus Accepted dan perbarui indeks keputusan.
- Dependency: Increment 1.
- Acceptance: alasan, alternatif, konsekuensi, dan verifikasi tercatat.
- Verifikasi: periksa penomoran ADR dan tautan relatif.

## Increment 3: Downstream documentation

- Kondisi awal: `PROJECT.md` dan `MODULES.md` masih menggambarkan starter kit
  generik.
- Perubahan: selaraskan project identity, indeks module, dan README dokumentasi.
- Dependency: Increment 1 dan 2.
- Acceptance: pembaca menemukan satu baseline produk dan status module nyata.
- Verifikasi: periksa konsistensi nama domain dan status module.

## Batas berhenti

Berhenti setelah dokumentasi konsisten. Implementasi module, schema, API, dan UI
menjadi work item berikutnya setelah mendapat persetujuan user.

## Rollback

Seluruh perubahan terbatas pada dokumen. Draft sumber tetap tersedia pada
`Old_docs/Laboratory_Compliance_Platform_Architecture_Baseline_v0.1-r5-ID.md`.
