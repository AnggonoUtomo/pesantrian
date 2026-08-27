# PRD: Laboratory Compliance & Evidence Platform

## Status

Disetujui

## Masalah

Aktivitas laboratorium, kompetensi, alat, metode, hasil, laporan, dan evidence
sering tersimpan sebagai rekaman terpisah. Kondisi tersebut menyulitkan
traceability, validasi kesiapan sebelum pengujian, dan penyusunan bukti objektif
saat review atau audit.

## Tujuan

Membangun platform laboratorium multi-scope yang menghasilkan evidence
compliance dari proses operasional dan menjaga histori resmi tetap dapat
ditelusuri.

## Pengguna

- Administrator aplikasi.
- Manajer laboratorium dan manajer teknis.
- Petugas penerimaan sampel.
- Analis atau teknisi laboratorium.
- Quality manager, reviewer, approver, dan auditor yang diizinkan.

## Scope Release 1

- Organization dan laboratory scope.
- Standard, requirement, test method, dan revision.
- Personnel competence dan technical authorization.
- Equipment serta kontrol metrologi.
- Sample custody, test execution, result, dan report.
- Evidence, readiness deterministik, dan audit trace.

## Di luar scope

- AI atau intelligence.
- Workflow sertifikasi ISO/IEC 17065 penuh.
- Integrasi eksternal tanpa requirement tersendiri.
- Otomasi keputusan kepatuhan tanpa review personel berwenang.

## Requirement

- Scope baru dapat ditambahkan sebagai data tanpa module khusus produk.
- Test execution mengunci revision dan referensi historis yang digunakan.
- Permission aplikasi dan authorization teknis diperiksa secara terpisah.
- Evidence memiliki subject, owner, validity, dan verification state.
- Perubahan rekaman resmi menggunakan revision atau amendment.
- Readiness menjelaskan requirement yang terpenuhi dan yang gagal.
- Backend selalu menjadi security authority.

## Acceptance criteria

- [ ] Vertical slice mainan anak dapat berjalan dari sample registration hingga
  final report dengan traceability lengkap.
- [ ] Scope keramik dapat ditambahkan melalui konfigurasi tanpa module baru.
- [ ] Perubahan method revision tidak mengubah histori test execution lama.
- [ ] Personel atau alat yang tidak eligible ditolak dengan alasan yang jelas.
- [ ] Evidence yang tidak lengkap mencegah transisi yang memang mensyaratkannya.
- [ ] Audit trace menunjukkan actor, action, subject, waktu, dan perubahan yang
  relevan tanpa mengekspos data sensitif.

## Pertanyaan terbuka

- Lihat bagian `Keputusan yang masih terbuka` pada `../../PRODUCT-BASELINE.md`.
- Setiap pertanyaan wajib ditutup dalam work item module pemilik sebelum coding
  yang bergantung pada jawaban tersebut.
