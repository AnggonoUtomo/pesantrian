# Specification: Pesantrian/Asrama

## Status

Active - create/update asrama dan kamar. Source module awal, permission
identity, contract minimum lintas module, migration, record model, factory
minimum, route API read-only, query list/detail, serta mutation create/update
asrama dan kamar sudah tersedia. Placement, seeder demo, dan UI belum dibuat
pada increment ini.

## Objective

Membangun module `Pesantrian/Asrama` sebagai pengelola asrama, kamar, kapasitas,
musyrif, dan penempatan santri agar operasional tinggal santri dapat dilacak
dengan jelas.

## Scope

- Mencatat data asrama minimum.
- Mencatat kamar dalam asrama.
- Mengatur kapasitas kamar.
- Menempatkan santri aktif ke kamar.
- Memindahkan santri antar kamar dengan histori.
- Mengeluarkan santri dari kamar dengan alasan.
- Menugaskan musyrif/pembina ke asrama atau kamar.
- Menyediakan read/list/detail Asrama untuk operator.
- Menambahkan demo seeder untuk lifecycle asrama.
- Menulis audit untuk mutasi signifikan.

## Non-Scope

- Pengelolaan lifecycle utama santri.
- Presensi harian asrama.
- Perizinan keluar/pulang/sakit.
- Pelanggaran dan pembinaan detail.
- Kesehatan/klinik.
- Inventaris barang kamar.
- Tagihan asrama atau biaya tambahan.
- Portal wali/santri.

## Actor

- SuperSystem.
- Operator Pesantrian.
- Musyrif atau pembina asrama yang diberi permission.
- Kepala unit/pengelola asrama yang diberi permission baca.

## Use Case Baseline

### List dan Search Asrama

Operator melihat daftar asrama, mencari berdasarkan kode/nama, dan memfilter
status serta unit/lokasi organisasi.

### Detail Asrama

Operator membuka detail asrama berisi identitas, daftar kamar, kapasitas, jumlah
terisi, dan musyrif aktif.

### Kelola Kamar

Operator membuat dan memperbarui kamar. Kamar memiliki kapasitas dan status.
Kamar yang tidak aktif tidak menerima penempatan baru.

### Penempatan Santri

Operator menempatkan santri aktif ke kamar aktif bila kapasitas tersedia dan
aturan jenis santri terpenuhi.

### Transfer Kamar

Operator memindahkan santri dari kamar lama ke kamar baru. Placement lama
ditutup dan placement baru aktif.

### Keluar Kamar

Operator menutup placement aktif bila santri keluar dari asrama atau tidak lagi
tinggal di kamar tersebut. Alasan wajib dicatat.

### Penugasan Musyrif

Operator menugaskan pegawai aktif sebagai musyrif/pembina asrama atau kamar.
Penugasan lama dapat ditutup untuk menjaga histori.

### Archive dan Restore

Asrama atau kamar dapat diarsipkan secara aman bila tidak lagi dipakai pada
operasional aktif. Penghapusan permanen tidak menjadi baseline.

## Candidate Data Model

### `dormitories`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `unit_id` | Rujukan unit/lokasi Organization |
| `code` | Kode asrama, unique |
| `name` | Nama asrama |
| `gender_policy` | putra/putri/campuran/unspecified |
| `description` | Nullable |
| `status` | active/inactive |
| `archived_at` | Nullable |
| `archived_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `dormitory_rooms`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `dormitory_id` | FK ke `dormitories` |
| `code` | Kode kamar, unique per asrama |
| `name` | Nama kamar |
| `capacity` | Kapasitas maksimum |
| `status` | active/inactive |
| `archived_at` | Nullable |
| `archived_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `student_room_placements`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `student_id` | Rujukan santri dari public contract |
| `dormitory_room_id` | FK ke kamar |
| `started_at` | Tanggal mulai tinggal |
| `ended_at` | Nullable, tanggal keluar/pindah |
| `status` | active/transferred/ended |
| `reason` | Nullable untuk alasan pindah/keluar |
| `active_student_key` | Nullable unique untuk menjaga satu placement aktif per santri |
| `created_by`, `ended_by` | Nullable actor id |
| `created_at`, `updated_at` | Timestamp standar |

### `dormitory_supervisor_assignments`

| Field | Catatan |
| --- | --- |
| `id` | ULID primary key |
| `employee_id` | Rujukan pegawai dari public contract |
| `dormitory_id` | Nullable jika assignment khusus kamar |
| `dormitory_room_id` | Nullable jika assignment level asrama |
| `employee_name` | Snapshot nama pegawai saat assignment dibuat |
| `role` | musyrif/pembina/koordinator |
| `started_at` | Tanggal mulai |
| `ended_at` | Nullable |
| `status` | active/ended |
| `reason` | Nullable untuk alasan penugasan ditutup |
| `created_at`, `updated_at` | Timestamp standar |

## Rule Baseline

- Satu santri hanya punya satu placement kamar aktif.
- Kamar archived/inactive tidak menerima penempatan baru.
- Kapasitas kamar tidak boleh terlampaui.
- Transfer kamar wajib menutup placement lama.
- Keluar kamar wajib mencatat alasan.
- Gender policy asrama harus kompatibel dengan data santri bila data gender
  tersedia.
- Musyrif harus pegawai aktif menurut public contract SDM.

## Public Boundary Candidate

Public contract dibuat hanya saat consumer nyata tersedia. Candidate awal:

- lookup placement kamar aktif santri;
- ringkasan keterisian kamar untuk dashboard atau presensi;
- query daftar santri per kamar/asrama.

Pada slice awal, Asrama menjadi consumer contract Santri dan HumanResource.
Contract keluar dari Asrama boleh ditunda sampai ada consumer nyata.

Contract readiness yang disiapkan:

| Owner | Contract | Alasan |
| --- | --- | --- |
| `Organization/Organization` | `DormitoryUnitReader` | Asrama perlu selector unit bertipe `dormitory`, sedangkan `EducationUnitReader` sengaja khusus unit pendidikan. |
| `Pesantrian/Santri` | `ActiveStudentReader` + `ActiveStudentOptionData.gender` | Placement Asrama perlu memastikan santri aktif dan rule asrama putra/putri tanpa membaca model Infrastructure Santri. |
| `HumanResource/HumanResource` | `ActiveEmployeeReader` | Penugasan musyrif cukup memakai pegawai aktif dengan employee type/position yang sudah tersedia. |

## Permission Candidate

- `asrama.view`
- `asrama.manage`
- `asrama.placement`
- `asrama.supervisor`
- `asrama.archive`

Backend tetap menjadi authority permission. Frontend hanya menyesuaikan tampilan
aksi untuk UX.

## Audit Candidate

- `asrama.dormitory.created`
- `asrama.dormitory.updated`
- `asrama.dormitory.archived`
- `asrama.dormitory.restored`
- `asrama.room.created`
- `asrama.room.updated`
- `asrama.student.placed`
- `asrama.student.transferred`
- `asrama.student.removed`
- `asrama.supervisor.assigned`
- `asrama.supervisor.ended`

Audit tidak boleh menyimpan payload sensitif berlebihan.

## UI Baseline

UI berada di `resources/js/pages/Pesantrian/Asrama/`.

Acceptance UI awal:

- index tetap minimal dan delegasi ke komponen;
- list mendukung search, filter status/unit/gender policy, dan pagination;
- detail menampilkan kamar, kapasitas, keterisian, santri aktif, dan musyrif;
- mutation form berada di folder `components`;
- destructive/archive action memakai confirmation;
- browser QA desktop/mobile wajib dilakukan.

## Acceptance Criteria

- [x] Module skeleton `Pesantrian/Asrama` valid menurut module registry.
- [x] Permission candidate tersedia.
- [x] Contract readiness lintas module Organization, Santri, dan HumanResource
  tersedia tanpa direct import Infrastructure.
- [x] Schema asrama, kamar, placement, dan musyrif tersedia dengan ULID.
- [x] List/search/filter Asrama tersedia.
- [x] Detail Asrama tersedia.
- [x] Create/update asrama dan kamar tersedia.
- [ ] Penempatan santri menjaga kapasitas dan satu placement aktif.
- [ ] Transfer/keluar kamar menjaga histori.
- [ ] Penugasan musyrif memakai pegawai aktif.
- [ ] Archive/restore aman dan diaudit.
- [ ] Demo seeder Asrama tersedia dan idempotent.
- [ ] UI Inertia list/detail/mutation tersedia di `resources/js/pages`.
- [ ] Browser QA desktop/mobile lulus tanpa console error/warn.

## Keputusan Baseline

- Nama tampil memakai `Asrama`.
- Data asrama putra/putri tetap dimulai sebagai data, bukan module terpisah.
- Inventaris barang kamar tidak dimasukkan ke Asrama baseline; nanti masuk
  `Support/Asset`.
- Presensi kegiatan asrama tidak dimasukkan ke slice awal; nanti masuk
  `Pesantrian/PresensiSantri` atau work item presensi asrama bila kebutuhan
  nyata muncul.
