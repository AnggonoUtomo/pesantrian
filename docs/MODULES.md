# Daftar Module

Dokumen ini menjadi indeks ownership dan dependency release awal. Detail module
berada pada `docs/modules/<Namespace>/<Module>/` setelah module mulai
dikerjakan.

| Namespace | Module | Tanggung jawab | Dependency utama | Status |
| --- | --- | --- | --- | --- |
| Console | AccessControl | User account, authentication integration, role, permission, policy, 2FA, profile access | Spatie Permission, Fortify | Planned |
| Console | SystemSetting | Dynamic application setting, setting definition, setting group/value, module-owned setting registration | Module pemilik setting | Planned |
| Console | AuditTrail | Audit entry, actor/action/resource trace, governance trail | Event atau audit contract module lain | Planned |
| Organization | Organization | Yayasan, pesantren, unit, lokasi, struktur organisasi, hierarchy, affiliation | Tidak ada pada baseline awal | Active |
| Academic | AcademicPeriod | Academic year, semester, term, calendar, active period, period opening/closing | Organization | Active |
| Academic | Academic | Class, subject, teaching assignment, attendance, academic workflow | Organization, AcademicPeriod, Student contract, HumanResource contract | Planned |
| HumanResource | HumanResource | Employee, teacher, ustadz, staff, position, employment status, work assignment, attendance dasar | Organization | Active |
| StudentLife | Student | Student master, lifecycle, status, registration, transfer, graduation | Organization, Guardian contract bila diperlukan | Planned |
| StudentLife | Guardian | Guardian identity, relation to student, contact, access relation | Student contract bila diperlukan | Planned |
| StudentLife | Dormitory | Dormitory, room, occupancy, placement, musyrif relation | Organization, Student contract, HumanResource contract | Planned |
| Finance | StudentFinance | Fee definition, invoice, payment, student billing, outstanding balance | Organization, Student contract, Guardian query/contract bila diperlukan | Planned |
| Platform | Document | Document metadata, attachment reference, document requirement, media adapter | Spatie Media Library adapter | Planned |
| Communication | Announcement | Announcement publishing, audience, attachment, publication lifecycle | Organization, audience contracts bila diperlukan | Planned |
| Platform | Notification | Database/email notification, future channel adapter | Event atau notification contract | Planned |
| Platform | Reporting | Dashboard, export, read model/projection, management view | Read/query contract atau projection | Planned |

Status yang digunakan: `Planned`, `Active`, `Deprecated`, atau `Disabled`.

Saat menambah module, catat tanggung jawab tunggal, dependency nyata, dan alasan
boundary. Jangan menambahkan dependency untuk kebutuhan hipotetis atau membuat
module hanya karena ada menu UI baru.

Catatan istilah: `StudentLife` adalah namespace teknis untuk area Pesantrian
agar lebih mudah dicerna, tetapi tetap memakai identifier teknis Bahasa
Inggris.
