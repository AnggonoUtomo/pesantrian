# Product Baseline: Laboratory Compliance & Evidence Platform

## Status

Disetujui sebagai baseline produk awal pada 2026-08-20. Baseline ini menjadi
acuan tujuan produk dan batas Release 1. Detail tiap module tetap harus dibuat
melalui specification dan work item sebelum coding.

Draft sumber `v0.1-r5-ID` disimpan di `../Old_docs/` sebagai referensi historis.
Jika draft tersebut berbeda dengan dokumentasi aktif, dokumentasi aktif pada
folder `docs/` yang berlaku.

## Identitas produk

Laboratory Compliance & Evidence Platform adalah aplikasi operasi laboratorium
multi-scope yang menghubungkan pelaksanaan pengujian dengan kompetensi
personel, kelayakan alat, metode, hasil, laporan, dan bukti objektif.

Produk ini bukan aplikasi checklist ISO. Kepatuhan dibuktikan dari rekaman
operasional yang dapat ditelusuri.

## Sasaran kepatuhan

- Fokus awal: laboratorium pengujian berbasis SNI ISO/IEC 17025.
- Reference implementation pertama: pengujian mainan anak.
- Validasi multi-scope berikutnya: keramik.
- Ekspansi berikutnya: elektrik, lampu, dan scope lain.
- Arah masa depan: integrasi proses SNI ISO/IEC 17065.

Dokumen ini tidak menggantikan interpretasi standar, regulasi, atau penilaian
oleh personel kompeten. Clause, requirement, dan evidence expectation harus
divalidasi oleh pemilik proses laboratorium sebelum menjadi konfigurasi aktif.

## Pengguna utama

- Administrator aplikasi mengelola user, role, permission, dan setting.
- Manajer laboratorium mengelola organisasi, scope, resource, dan readiness.
- Manajer teknis menetapkan metode, persyaratan, kompetensi, dan review teknis.
- Petugas laboratorium menerima sampel dan menjaga custody.
- Analis atau teknisi menjalankan pengujian sesuai authorization teknis.
- Quality manager memeriksa evidence, traceability, dan audit readiness.
- Approver meninjau serta mengesahkan laporan sesuai kewenangannya.
- Auditor atau reviewer membaca bukti yang diizinkan tanpa mengubah rekaman.

## Prinsip produk

### Scope adalah data

Penambahan jenis produk tidak otomatis menghasilkan module baru. Hal berikut
dimodelkan sebagai data berversi:

- testing scope dan product category;
- standard, clause, dan regulatory reference;
- test method dan method revision;
- parameter dan acceptance criteria;
- equipment, environment, competence, dan authorization requirement;
- evidence requirement dan result schema.

Module baru hanya dibuat ketika terdapat ownership, lifecycle, atau business
capability yang berbeda.

### Evidence adalah bagian dari proses

Evidence bukan lampiran terakhir. Bukti dikaitkan dengan subject, requirement,
owner, validity, dan verification state sejak aktivitas berlangsung. File bukti
harus melalui authorization backend dan tidak diasumsikan memiliki URL publik.

### Histori tidak ikut berubah bersama master data

Saat pengujian dimulai, eksekusi harus mengunci revision atau snapshot yang
relevan, minimal:

- method revision dan acceptance criteria revision;
- equipment serta rekaman metrologi yang digunakan;
- personnel authorization;
- environmental record dan evidence requirement yang berlaku.

Perubahan master data setelahnya tidak boleh mengubah makna histori pengujian.
Raw data, hasil resmi, dan laporan final menggunakan revision atau amendment,
bukan overwrite tanpa jejak.

### Permission aplikasi berbeda dari authorization teknis

Permission menentukan apakah user boleh mengakses capability aplikasi.
Authorization teknis menentukan apakah personel kompeten melakukan metode
tertentu. Test execution yang dilindungi harus memenuhi keduanya.

### Readiness dimulai secara deterministik

Release awal menghitung readiness dari requirement yang eksplisit, misalnya
kelengkapan identitas sampel, method revision, authorization, eligibility alat,
raw data, review, approval, dan evidence wajib. AI atau prediksi bukan bagian
Release 1.

## Domain dan ownership module

Struktur source mengikuti `app/Modules/{Domain}/{Module}`. Keputusan domain
dicatat pada ADR-007.

### System

| Module | Status | Ownership |
| --- | --- | --- |
| AccessControl | Aktif | Role, permission, dan authorization capability aplikasi |
| UserManagement | Aktif | Lifecycle user, role assignment, dan impersonation |
| AuditLog | Aktif | Rekaman aktivitas aplikasi dan public audit capability |
| SystemSetting | Aktif | Setting aplikasi dinamis dan module-owned registration |

### Organization

| Module | Status | Ownership |
| --- | --- | --- |
| Organization | Direncanakan | Organisasi, laboratorium, unit, lokasi, dan afiliasi personel |

### Laboratory

| Module | Status | Ownership |
| --- | --- | --- |
| LaboratoryScope | Direncanakan | Testing scope, product category, aktivasi, dan scope capability |
| PersonnelCompetence | Direncanakan | Kompetensi, pelatihan, qualification, dan authorization teknis |
| Equipment | Direncanakan | Identitas, karakteristik, lokasi, status, dan lifecycle alat |
| Calibration | Direncanakan | Kalibrasi, verifikasi, intermediate check, dan status metrologi |
| TestMethod | Direncanakan | Metode, revision, parameter, requirement, dan acceptance criteria |
| SampleManagement | Direncanakan | Registrasi, kondisi, custody, penyimpanan, dan disposition sampel |
| TestExecution | Direncanakan | Assignment, pre-test validation, eksekusi, dan raw data |
| TestResult | Direncanakan | Nilai, kalkulasi, uncertainty, keputusan, dan revision hasil |
| TestReport | Direncanakan | Draft, review, approval, finalisasi, dan amendment laporan |

### Compliance

| Module | Status | Ownership |
| --- | --- | --- |
| Standards | Direncanakan | Standard, clause, requirement revision, dan regulatory reference |
| Evidence | Direncanakan | Evidence record, attachment, validity, link, dan verification state |
| AuditTrail | Kandidat | Traceability compliance dan tampilan audit yang belum ditetapkan |

Evidence Readiness adalah capability yang menggunakan requirement dan evidence.
Owner finalnya ditetapkan saat specification `Compliance/Evidence` dibuat;
Readiness tidak otomatis menjadi module tersendiri.

`System/AuditLog` dan kandidat `Compliance/AuditTrail` tidak boleh menyimpan dua
rekaman sumber untuk fakta yang sama. Sebelum `AuditTrail` dibuat, specification
wajib menetapkan apakah ia menjadi read model, facade, atau capability dalam
module yang sudah ada.

## Alur bisnis Release 1

```text
Organization dan Laboratory Scope
    -> Standard dan Test Method Revision
    -> Personnel Authorization dan Equipment Eligibility
    -> Sample Registration dan Custody
    -> Pre-test Validation
    -> Test Execution dan Raw Data
    -> Test Result dan Decision
    -> Technical Review dan Approval
    -> Test Report, Evidence, dan Audit Trace
```

## Capability inti Release 1

- Mengelola organisasi, laboratorium, unit, lokasi, dan afiliasi personel.
- Mengelola scope pengujian sebagai data yang dapat diaktifkan.
- Mengelola standard, requirement, metode, dan revision.
- Mengelola kompetensi serta authorization teknis yang memiliki masa berlaku.
- Mengelola equipment dan kontrol metrologi kontekstual.
- Mendaftarkan sampel dengan nomor referensi yang immutable setelah resmi.
- Memvalidasi kesiapan personel, alat, metode, lingkungan, dan evidence.
- Mencatat eksekusi, raw data, kalkulasi, hasil, review, dan approval.
- Menghasilkan laporan yang berversi dan terlindungi.
- Menyediakan traceability serta evidence readiness yang dapat dijelaskan.

## Di luar Release 1

- Workflow sertifikasi SNI ISO/IEC 17065 secara penuh.
- Calibration, CAPA, risk, atau compliance intelligence.
- AI assistant dan keputusan kepatuhan otomatis.
- Integrasi eksternal tanpa consumer, contract, dan owner yang disetujui.
- Microservice, database per module, dan event streaming terdistribusi.

## Aturan kompatibilitas Starter13

Baseline produk ini memakai fondasi teknis yang sudah ditetapkan:

- DDD-lite Modular Monolith dengan Hexagonal Architecture.
- Domain source `System`, `Organization`, `Laboratory`, dan `Compliance`.
- Struktur canonical pada `FOLDER-STRUCTURE.md`; folder dibuat saat diperlukan.
- Manifest mengikuti schema module loader aktual, bukan contoh draft lama.
- Primary key dan foreign key menggunakan ULID.
- Public boundary lintas module memakai Application contract, DTO, atau event.
- Persistence berada di `Infrastructure/Persistence`.
- Migration dan seeder dimiliki module; global seeder menjadi entry point.
- Frontend mengikuti `resources/js/pages/{Domain}/{Module}` dan memakai Ziggy.
- Backend menjadi security authority; state frontend hanya membantu UX.
- Wayfinder dan Laravel Boost dilarang.
- Queue hanya untuk side effect atau proses berat yang tidak menentukan
  validitas transaksi utama secara langsung.

Command generator, metadata, dan struktur test harus mengikuti implementasi
starter13 yang sedang berlaku. Contoh command pada draft historis tidak
menjadi contract.

## Urutan pengembangan

### Phase 0: kesiapan fondasi

- Tutup gap conformance arsitektur dan test discovery yang sudah diketahui.
- Verifikasi module loader, generator, quality gate, media, queue, dan UI shell.
- Tetapkan domain map, dependency matrix, serta pola audit compliance.

### Phase 1: organisasi dan governance

- `Organization/Organization`.
- `Laboratory/LaboratoryScope`.
- `Compliance/Standards`.
- `Laboratory/PersonnelCompetence`.

### Phase 2: metrological core

- `Laboratory/Equipment`.
- `Laboratory/Calibration`.
- Equipment eligibility sebagai vertical slice lintas capability publik.

### Phase 3: testing core

- `Laboratory/TestMethod`.
- `Laboratory/SampleManagement`.
- `Laboratory/TestExecution`.
- `Laboratory/TestResult`.

### Phase 4: reporting dan evidence

- `Laboratory/TestReport`.
- `Compliance/Evidence` dan readiness.
- Keputusan serta implementasi audit trail compliance.

### Phase 5: validasi multi-scope

Tambahkan scope keramik melalui konfigurasi data. Phase ini harus membuktikan
bahwa engine tidak bergantung pada aturan mainan anak yang di-hard-code.

### Phase berikutnya

Ekspansi scope elektrik dan lampu dilakukan setelah validasi multi-scope.
Intelligence dan ISO/IEC 17065 memerlukan PRD serta ADR terpisah.

## Kriteria keberhasilan produk

- Satu pengujian dapat ditelusuri ke sampel, revision metode, personel, alat,
  kontrol metrologi, raw data, hasil, review, laporan, dan evidence.
- Pengguna tanpa permission atau authorization teknis yang sesuai tidak dapat
  menjalankan tindakan yang dilindungi.
- Histori resmi tetap konsisten setelah master data berubah.
- Readiness menunjukkan item yang lengkap, kurang, kedaluwarsa, atau tidak
  valid beserta alasannya.
- Scope keramik dapat ditambahkan tanpa module bisnis baru dan tanpa perubahan
  engine inti yang khusus untuk keramik.
- Setiap module lulus discovery, validation, focused test, quality gate, dan
  browser verification yang relevan sebelum dinyatakan selesai.

## Keputusan yang masih terbuka

- Model hubungan organisasi, laboratorium, unit, lokasi, dan personel.
- Sumber authoritative konten standard serta proses persetujuan revision.
- Boundary final `AuditLog`, `AuditTrail`, dan Evidence Readiness.
- Model amendment untuk raw data, result, dan report yang sudah resmi.
- Storage, retention, hash/integrity, dan access policy file evidence.
- Definisi uncertainty dan decision rule per keluarga metode.

Open decision tersebut diselesaikan pada PRD, specification, atau ADR module
terkait. Keberadaannya tidak memberi izin untuk mengisi keputusan melalui
asumsi saat coding.
