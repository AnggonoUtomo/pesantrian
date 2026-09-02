# Daftar Module

Dokumen ini menjadi indeks ownership, nama tampil, dan dependency release awal.
Detail module berada pada `docs/modules/<Namespace>/<Module>/` setelah module
mulai dikerjakan.

Nama tampil memakai Bahasa Indonesia agar familiar untuk operator pesantren.
Namespace/module teknis memakai PascalCase ASCII dan tidak di-rename tanpa work
item migrasi.

## Status Source Existing

| Source existing | Nama tampil | Status | Catatan |
| --- | --- | --- | --- |
| `System/AccessControl` | Kontrol Akses | Active | Mengelola role, permission, dan authorization. |
| `System/UserManagement` | Pengguna | Active | Lifecycle akun user, invite, status, avatar, dan impersonation. |
| `System/SystemSetting` | Pengaturan Sistem | Active | Runtime setting aplikasi. |
| `System/AuditLog` | Audit Trail | Active | Implementation bridge untuk audit trail. |
| `Organization/Organization` | Organisasi | Active | Unit, hierarchy, archive/restore, dan audit struktur organisasi. |
| `Academic/AcademicPeriod` | Tahun Ajaran & Semester | Active | Tahun ajaran, term/semester, active/close lifecycle. |
| `HumanResource/HumanResource` | SDM Pesantren | Active | Employee, guru, ustadz, staff, assignment unit, lifecycle, audit, dan read page. |
| `Pesantrian/PenerimaanSantri` | PPDB / Penerimaan Santri Baru | Active | Calon santri, status pendaftaran, checklist dokumen, biaya pendaftaran, dan contract accepted admission. |
| `Pesantrian/Santri` | Data Induk Santri dan Wali | Active | Master santri, snapshot wali minimum, konversi PPDB accepted, lifecycle, archive/restore, dan UI. |
| `Academic/KelasRombel` | Kelas / Rombel / Kurikulum | Active | Contract readiness untuk kelas, rombel, kurikulum minimum, penempatan santri, dan wali kelas. |

## Target Module Baseline

| Prioritas | Namespace/Module teknis | Nama tampil | Tanggung jawab | Dependency utama | Status |
| --- | --- | --- | --- | --- | --- |
| 01 | `System/AccessControl` | Kontrol Akses | Role, permission, policy, dan otorisasi backend | Spatie Permission, Fortify | Active |
| 02 | `System/UserManagement` | Pengguna | Akun user, invite, status, avatar, impersonation, profile access | AccessControl | Active |
| 03 | `System/SystemSetting` | Pengaturan Sistem | Dynamic application setting, setting definition, group/value, module-owned setting registration | Module pemilik setting | Active |
| 04 | `System/AuditLog` | Audit Trail | Audit entry, actor/action/resource trace, governance trail | Event atau audit contract module lain | Active |
| 05 | `Organization/Organization` | Organisasi | Yayasan, pesantren, unit, lokasi, struktur organisasi, hierarchy, affiliation | Tidak ada pada baseline awal | Active |
| 06 | `Academic/AcademicPeriod` | Tahun Ajaran & Semester | Academic year, semester/term, calendar, active period, opening/closing | Organization | Active |
| 07 | `HumanResource/HumanResource` | SDM Pesantren | Employee, guru, ustadz, musyrif, staff, position, employment status, work assignment | Organization | Active |
| 08 | `Pesantrian/PenerimaanSantri` | PPDB / Penerimaan Santri Baru | Calon santri, formulir, verifikasi, status pendaftaran, konversi menjadi santri | Organization, WaliSantri, Document bila diperlukan | Active |
| 09 | `Pesantrian/WaliSantri` | Wali Santri | Guardian identity, relasi ke santri, kontak, billing contact, emergency contact | Santri contract bila diperlukan | Planned setelah Santri baseline |
| 10 | `Pesantrian/Santri` | Data Induk Santri dan Wali | Student master, lifecycle, status, registration link, transfer, graduation, snapshot wali minimum | Organization, PenerimaanSantri, WaliSantri bila dipromosikan | Active - baseline complete sampai QA UI desktop/mobile |
| 11 | `Academic/KelasRombel` | Kelas / Rombel / Kurikulum | Kelas, rombel, kurikulum minimum, penempatan santri, dan wali kelas | Organization, AcademicPeriod, Santri contract, HumanResource contract | Active - contract readiness |
| 12 | `Pesantrian/Asrama` | Asrama | Dormitory, room, occupancy, placement, musyrif relation, placement history | Organization, Santri contract, HumanResource contract | Planned |
| 13 | `Pesantrian/Tahfidz` | Tahfidz / Hafalan | Target hafalan, setoran, murojaah, capaian, pembimbing tahfidz | Santri contract, HumanResource contract, AcademicPeriod bila diperlukan | Planned |
| 14 | `Pesantrian/PresensiSantri` | Presensi Santri | Presensi kegiatan santri di luar/bersama akademik formal | Santri contract, Academic/Asrama bila diperlukan | Planned |
| 15 | `Pesantrian/PerizinanSantri` | Perizinan Santri | Izin keluar/pulang/sakit, approval ringan, status kembali, riwayat izin | Santri contract, WaliSantri contract, HumanResource contract bila diperlukan | Planned |
| 16 | `Pesantrian/KedisiplinanSantri` | Pelanggaran / Kedisiplinan | Catatan pelanggaran, kategori, poin/tingkat, tindakan pembinaan, riwayat penyelesaian | Santri contract, WaliSantri contract, HumanResource contract bila diperlukan | Planned |
| 17 | `Pesantrian/PrestasiSantri` | Prestasi | Catatan prestasi, kategori, tingkat, tanggal/periode, lampiran bukti | Santri contract, Document bila diperlukan | Planned |
| 18 | `Pesantrian/KesehatanSantri` | Kesehatan / Klinik | Kunjungan klinik, keluhan, tindakan awal, rujukan, catatan kesehatan operasional | Santri contract, HumanResource contract bila diperlukan | Planned |
| 19 | `Pesantrian/PembinaanSantri` | Konseling / Pembinaan | Catatan pembinaan/konseling, rencana tindak lanjut, status pendampingan | Santri contract, HumanResource contract bila diperlukan | Planned |
| 20 | `Pesantrian/Alumni` | Alumni | Profil alumni, tahun lulus, kontak, relasi historis ke santri | Santri contract | Planned |
| 21 | `Finance/StudentFinance` | Tagihan / Pembayaran / Tunggakan | Fee definition, invoice, payment, allocation, outstanding balance | Organization, AcademicPeriod, Santri contract, WaliSantri query/contract | Planned |
| 22 | `Support/Document` | Dokumen | Document metadata, attachment reference, requirement, controlled download, media adapter | Spatie Media Library adapter | Planned |
| 23 | `Communication/Announcement` | Pengumuman | Announcement publishing, audience, attachment, publication lifecycle | Organization, audience contracts, Document, Notification | Planned |
| 24 | `Support/Notification` | Notifikasi | Database/email notification, future channel adapter | Event atau notification contract | Planned |
| 25 | `Support/Reporting` | Laporan | Dashboard, export, read model/projection, management view | Read/query contract atau projection | Planned |
| 26 | `Finance/DonationWaqf` | Donasi / Wakaf | Donatur, jenis donasi/wakaf, penerimaan, alokasi, bukti, akuntabilitas | Organization, Document bila diperlukan | Planned |
| 27 | `Support/Asset` | Inventaris / Aset | Data aset, kode inventaris, unit/lokasi, kondisi, mutasi, pemeliharaan minimum | Organization | Planned |

## Ditunda Setelah Baseline Running

| Capability | Catatan |
| --- | --- |
| Koperasi / POS | Ditambahkan setelah operasional inti dan keuangan santri stabil. |
| Perpustakaan | Ditambahkan setelah baseline aplikasi berjalan dan kebutuhan sirkulasi/katalog jelas. |
| Payroll | Terpisah dari SDM; jangan dicampur ke HumanResource awal. |
| Procurement | Masuk setelah kebutuhan pembelian/inventaris lebih jelas. |
| Laundry | Ditunda sampai kebutuhan operasional asrama terbukti. |
| Payment Gateway / VA / QRIS | Integrasi setelah flow invoice/payment manual stabil. |
| Public API penuh | Ditunda sampai contract internal matang. |
| BI kompleks / AI Assistant | Ditunda sampai volume dan kualitas data memadai. |

Status yang digunakan: `Planned`, `Active`, `Deprecated`, atau `Disabled`.

Saat menambah module, catat tanggung jawab tunggal, dependency nyata, dan alasan
boundary. Jangan menambahkan dependency untuk kebutuhan hipotetis atau membuat
module hanya karena ada menu UI baru.
