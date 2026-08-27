# Daftar Modul

Dokumen ini menjadi indeks ownership dan dependency. Detail module dibuat pada
`modules/{Domain}/{Module}/` ketika module memperoleh pekerjaan signifikan baru.

| Domain | Module | Tanggung jawab | Dependency deklaratif | Status |
| --- | --- | --- | --- | --- |
| System | AccessControl | Role, permission, dan authorization capability | StarterKit | Aktif |
| System | UserManagement | Lifecycle user, role assignment, dan impersonation | AccessControl | Aktif |
| System | AuditLog | Rekaman aktivitas dan public audit capability | AccessControl, UserManagement | Aktif |
| System | SystemSetting | Setting aplikasi dinamis | AccessControl, UserManagement, AuditLog | Aktif |
| Organization | Organization | Organisasi, laboratorium, unit, lokasi, dan afiliasi | Belum ditetapkan | Direncanakan |
| Laboratory | LaboratoryScope | Testing scope dan product category | Belum ditetapkan | Direncanakan |
| Laboratory | PersonnelCompetence | Kompetensi dan authorization teknis | Belum ditetapkan | Direncanakan |
| Laboratory | Equipment | Identitas, karakteristik, status, dan lifecycle alat | Belum ditetapkan | Direncanakan |
| Laboratory | Calibration | Kontrol metrologi dan status kalibrasi | Belum ditetapkan | Direncanakan |
| Laboratory | TestMethod | Metode, revision, parameter, dan requirement | Belum ditetapkan | Direncanakan |
| Laboratory | SampleManagement | Registrasi, kondisi, custody, dan disposition | Belum ditetapkan | Direncanakan |
| Laboratory | TestExecution | Validasi dan pelaksanaan pengujian | Belum ditetapkan | Direncanakan |
| Laboratory | TestResult | Raw value, kalkulasi, keputusan, dan revision | Belum ditetapkan | Direncanakan |
| Laboratory | TestReport | Review, approval, finalisasi, dan amendment | Belum ditetapkan | Direncanakan |
| Compliance | Standards | Standard, clause, requirement, dan revision | Tidak ada | Direncanakan |
| Compliance | Evidence | Evidence, validity, link, dan readiness | Public API module terkait | Direncanakan |
| Compliance | AuditTrail | Traceability compliance | Belum ditetapkan | Kandidat |

Framework reusable berada pada `packages/StarterKit` dan bukan module bisnis.
Urutan dependency baseline adalah AccessControl, UserManagement, AuditLog, lalu
SystemSetting.

Dependency untuk module direncanakan sengaja belum ditetapkan. Nama contract dan
dependency final ditentukan dari consumer nyata dalam specification module.
`AuditTrail` belum boleh dibuat sampai hubungannya dengan `System/AuditLog`
diputuskan.

Lihat [Product Baseline](PRODUCT-BASELINE.md) dan
[ADR-007](decisions/ADR-007-PRODUCT-DOMAIN-OWNERSHIP.md) untuk pembagian domain.

Saat menambah module, perbarui tabel ini dengan owner tanggung jawab, dependency
nyata, dan statusnya. Jangan menambahkan dependency untuk kebutuhan hipotetis.
