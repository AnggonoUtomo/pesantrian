# User Manual Lifecycle SakaSantri

Dokumen ini dibuat untuk uji coba manual aplikasi oleh user pemula. Ikuti dari
atas ke bawah agar relasi antar module terasa utuh.

> Catatan aman: data demo hanya untuk environment development/local. Jangan
> jalankan demo seeder pada database production.

## 1. Menyiapkan Data Demo

Jalankan dari root project:

```bash
php artisan migrate
php artisan db:seed
```

Seeder bersifat idempotent, artinya aman dijalankan berulang untuk melengkapi
data demo tanpa membuat data demo dobel berdasarkan kode unik seperti
`DEMO-*`, `PPDB-DEMO-*`, `NIS-DEMO-*`, `PEG-DEMO-*`, `DEMO-ASR-*`,
`DEMO-KMR-*`, kode presensi `DEMO-*`, dan kode Tahfidz `DEMO-THF-*`.

Password akun demo tidak ditulis di source code. Jika ingin semua akun demo
punya password lokal yang sama, isi `.env` lokal:

```env
ACCESS_CONTROL_DUMMY_PASSWORD=password-lokal-kamu
```

Lalu jalankan ulang:

```bash
php artisan db:seed
```

## 2. Akun dan Role Demo

Role `SuperSystem` dan `SecurityAdmin` memiliki semua permission module aktif.
Role operator dibuat agar uji coba terasa seperti pekerjaan harian.

| Role | Akun demo | Cocok untuk uji |
| --- | --- | --- |
| SuperSystem | `super-system@example.test` | Semua fitur dan pemeriksaan permission. |
| SecurityAdmin | `security-admin@example.test` | Role, permission, user, dan audit awal. |
| OperatorPPDB | `operator-ppdb@example.test` | Pendaftaran santri baru sampai keputusan. |
| OperatorSantri | `operator-santri@example.test` | Data induk santri, wali, lifecycle santri, asrama, presensi, dan Tahfidz/Hafalan. |
| OperatorAkademik | `operator-akademik@example.test` | Tahun ajaran, semester, kelas, rombel, placement. |
| OperatorSDM | `operator-sdm@example.test` | Data pegawai, guru, ustaz, staff, dan unit tugas. |
| Auditor | `auditor@example.test` | Audit log dan data baca lintas module. |
| Viewer | `viewer@example.test` | Mode baca data operasional. |

Jika password belum diatur melalui `.env`, akun demo tetap dibuat tetapi
password-nya acak dan tidak ditampilkan.

## 3. Urutan Uji Manual yang Disarankan

### Langkah A: Kontrol Akses

Menu: **System -> Kontrol Akses**

Tujuan:

- Melihat role yang tersedia.
- Memastikan role operator memiliki permission sesuai tugasnya.
- Mencoba assign role ke user demo lain bila diperlukan.

Yang perlu diperhatikan:

- Role `SuperSystem` adalah role terlindungi.
- Backend tetap menjadi penjaga izin. Menu yang hilang bukan satu-satunya
  pengaman.

### Langkah B: Pengguna

Menu: **System -> Pengguna**

Tujuan:

- Melihat daftar user demo.
- Mengubah status user jika ingin mencoba lifecycle akun.
- Mencoba assign role operator ke user tertentu.

Relasi:

- User dipakai sebagai actor audit dan pemilik aksi.
- User belum otomatis menjadi pegawai. Data pegawai ada di module SDM.

### Langkah C: Pengaturan Sistem

Menu: **System -> Pengaturan Sistem**

Tujuan:

- Melihat setting runtime seperti format nomor, keamanan password, dan mail.
- Mengubah setting bila ingin mencoba konfigurasi lokal.

Catatan:

- Setting sensitif tidak boleh dicatat sembarangan di dokumen atau log.
- Format nomor PPDB/Santri sudah disiapkan sebagai basis, namun detail generator
  tiap module mengikuti increment masing-masing.

### Langkah D: Audit Trail

Menu: **System -> Audit Trail**

Tujuan:

- Melihat jejak aktivitas demo.
- Memastikan perubahan penting meninggalkan audit tanpa password/token.

Relasi:

- Module seperti PPDB, Santri, SDM, Academic Period, Kelas/Rombel, dan Asrama
  menulis audit lewat contract audit yang tersedia.

### Langkah E: Organisasi

Menu: **Organisasi -> Organisasi**

Data demo penting:

- `DEMO-YAYASAN` - Yayasan Saka Santri.
- `DEMO-PESANTREN` - Pesantren Saka Santri.
- `DEMO-MTS` - MTs Saka Santri.
- `DEMO-MA` - MA Saka Santri.
- `DEMO-ASRAMA-PUTRA` dan `DEMO-ASRAMA-PUTRI`.
- `DEMO-ARSIP` - unit nonaktif untuk uji status.

Tujuan:

- Memahami hierarchy yayasan -> pesantren -> unit.
- Menguji create, update, archive, dan restore unit.

Relasi:

- Unit dipakai oleh PPDB, Santri, SDM, Academic/KelasRombel, dan Asrama.
- `DEMO-ASRAMA-PUTRA` dan `DEMO-ASRAMA-PUTRI` dipakai sebagai unit induk data
  Asrama.

### Langkah F: Tahun Ajaran dan Semester

Menu: **Academic -> Tahun Ajaran & Semester**

Data demo penting:

- `2025-2026` status closed.
- `2026-2027` status active.
- `2027-2028` status draft.
- `2026-2027-GANJIL` sebagai semester aktif.

Tujuan:

- Membuat tahun ajaran.
- Membuat semester.
- Mengaktifkan semester.
- Menutup semester.

Relasi:

- Kelas/rombel membutuhkan tahun ajaran dan semester.
- Tagihan, presensi, tahfidz, dan module akademik lain nanti akan memakai periode
  ini setelah module-nya dibuat.

### Langkah G: SDM Pesantren

Menu: **Human Resource -> SDM Pesantren**

Data demo penting:

- `PEG-DEMO-001` pengasuh pesantren.
- `PEG-DEMO-003` guru aktif MTs.
- `PEG-DEMO-004` guru aktif MA.
- `PEG-DEMO-005` dan `PEG-DEMO-006` nonaktif.

Tujuan:

- Melihat pegawai aktif/nonaktif.
- Menguji data guru/staff dan unit utama.

Relasi:

- Guru aktif dipakai sebagai wali kelas di module Kelas/Rombel.
- Musyrif/pembina asrama dipakai oleh module Asrama untuk penugasan pembina
  aktif dan historis.
- Payroll belum dibuat dan memang tidak dicampur ke module SDM awal.

### Langkah H: PPDB / Penerimaan Santri Baru

Menu: **Pesantrian -> PPDB / Penerimaan Santri Baru**

Data demo penting:

- `PPDB-DEMO-DRAFT`
- `PPDB-DEMO-SUBMITTED`
- `PPDB-DEMO-VERIFIED`
- `PPDB-DEMO-ACCEPTED`
- `PPDB-DEMO-REJECTED`
- `PPDB-DEMO-CANCELLED`

Tujuan:

- Membuat pendaftaran baru.
- Mengisi data calon santri dan wali snapshot.
- Memeriksa biaya pendaftaran.
- Memeriksa checklist dokumen.
- Mengubah status sampai accepted/rejected/cancelled.

Relasi:

- PPDB memakai target unit dari Organisasi.
- Pendaftaran accepted bisa dikonversi menjadi Data Induk Santri.
- Module Dokumen belum dibuat, jadi checklist dokumen masih tersimpan sebagai
  checklist sederhana di PPDB, bukan file/lampiran dokumen terpusat.
- Module Keuangan belum dibuat, jadi biaya pendaftaran masih status sederhana,
  belum menjadi invoice/payment ledger.

### Langkah I: Data Induk Santri dan Wali

Menu: **Pesantrian -> Data Induk Santri dan Wali**

Data demo penting:

- `NIS-DEMO-AKTIF` santri aktif.
- `NIS-DEMO-NONAKTIF` santri nonaktif.
- `NIS-DEMO-PINDAH` santri pindah.
- `NIS-DEMO-LULUS` santri lulus.
- `NIS-DEMO-ARSIP` santri terarsip.
- `NIS-DEMO-PPDB` santri hasil PPDB accepted.

Tujuan:

- Membuat data santri manual.
- Mengonversi PPDB accepted menjadi santri.
- Mengisi wali snapshot.
- Mengubah status santri.
- Archive dan restore data santri.

Relasi:

- Santri memakai unit utama dari Organisasi.
- Santri hasil PPDB menyimpan referensi pendaftaran asal.
- Module WaliSantri master belum dibuat, jadi wali masih snapshot minimum di
  data santri.
- Module Alumni belum dibuat, jadi status lulus baru tersimpan sebagai lifecycle
  santri.

### Langkah J: Kelas / Rombel / Kurikulum

Menu: **Academic -> Kelas / Rombel / Kurikulum**

Data demo penting:

- Kurikulum `KUR-DEMO-MERDEKA`, `KUR-DEMO-DINIYAH`, dan `KUR-DEMO-ARSIP`.
- Tingkat kelas `DEMO-VII`, `DEMO-VIII`, `DEMO-X`, dan `DEMO-XI`.
- Rombel `DEMO-MTS-VII-A`, `DEMO-MTS-VII-B`, `DEMO-MA-X-A`,
  `DEMO-MA-XI-A`, dan `DEMO-ARSIP`.

Tujuan:

- Membuat kurikulum.
- Membuat tingkat kelas.
- Membuat rombel.
- Melihat detail rombel.
- Menempatkan santri aktif ke rombel.
- Menetapkan wali kelas dari guru aktif.
- Archive dan restore rombel.

Relasi:

- Rombel membutuhkan Organisasi, Tahun Ajaran/Semester, Santri, dan SDM.
- Santri hanya boleh aktif pada satu rombel dalam semester yang sama.
- Wali kelas harus pegawai aktif bertipe guru pada unit rombel.
- Mapel/detail kurikulum belum dibuat; kurikulum saat ini masih label/struktur
  minimum.

### Langkah K: Asrama

Menu: **Pesantrian -> Asrama**

Data demo penting:

- `DEMO-ASR-PUTRA` asrama putra aktif.
- `DEMO-ASR-PUTRI` asrama putri aktif.
- `DEMO-ASR-RENOVASI` asrama nonaktif/arsip untuk uji restore.
- Kamar `DEMO-KMR-PUTRA-A01`, `DEMO-KMR-PUTRA-A02`,
  `DEMO-KMR-PUTRI-A01`, `DEMO-KMR-PUTRI-A02`, dan
  `DEMO-KMR-RENOVASI-A01`.
- Placement aktif untuk `NIS-DEMO-AKTIF` dan `NIS-DEMO-PPDB`.
- Riwayat pindah kamar untuk `NIS-DEMO-PPDB`.
- Riwayat keluar kamar untuk `NIS-DEMO-NONAKTIF`.
- Musyrif aktif dari `PEG-DEMO-002` dan riwayat pembina selesai dari
  `PEG-DEMO-006`.

Tujuan:

- Melihat daftar dan detail asrama.
- Memeriksa kapasitas dan keterisian kamar.
- Menempatkan santri aktif ke kamar.
- Memindahkan santri ke kamar lain.
- Mengeluarkan santri dari kamar dengan alasan.
- Menugaskan dan mengakhiri tugas musyrif/pembina.
- Mengarsipkan dan memulihkan asrama atau kamar.

Relasi:

- Asrama memakai unit dari Organisasi.
- Penempatan kamar memakai santri aktif dari Data Induk Santri.
- Musyrif/pembina memakai pegawai aktif dari SDM Pesantren.
- Satu santri hanya boleh memiliki satu kamar aktif pada satu waktu.
- Asrama/kamar yang diarsipkan tidak menerima placement baru.

### Langkah L: Presensi Santri

Menu: **Pesantrian -> Presensi Santri**

Data demo penting:

- `DEMO-KBM-PAGI` presensi kelas/rombel yang sudah submitted.
- `DEMO-ASRAMA-MALAM` presensi asrama yang masih draft.
- `DEMO-MUHADHARAH` presensi kegiatan umum yang pernah direvisi.
- `DEMO-VOID` presensi kegiatan umum yang dibatalkan tanpa menghapus entry.

Tujuan:

- Melihat daftar sesi presensi.
- Memfilter berdasarkan pencarian, tanggal, konteks, dan status.
- Membuka detail sesi untuk melihat ringkasan status hadir, terlambat, izin,
  sakit, dan alfa.
- Membuat sesi presensi draft.
- Mengisi atau mengubah entry presensi saat sesi masih draft/revisi.
- Submit presensi agar sesi terkunci.
- Membuka revisi dengan alasan bila ada koreksi.
- Membatalkan/void sesi dengan alasan tanpa menghapus histori data.

Relasi:

- Presensi memakai santri aktif dari Data Induk Santri.
- Presensi kelas/rombel memakai snapshot konteks dari Kelas/Rombel.
- Presensi asrama memakai snapshot konteks dari Asrama.
- Presensi kegiatan umum bisa dipakai tanpa module jadwal khusus.
- Perizinan Santri sudah punya integrasi read-only awal untuk membaca izin yang
  relevan pada tanggal presensi, tetapi layar Presensi belum otomatis mengubah
  entry menjadi izin. Status izin/sakit/alfa saat ini tetap dicatat manual di
  Presensi Santri.
- Kesehatan/Klinik dan Pelanggaran/Kedisiplinan belum otomatis terhubung karena
  module terkait belum dibuat.
- Integrasi perangkat absensi belum dibuat; input awal masih admin/internal.

### Langkah M: Tahfidz / Hafalan

Menu: **Pesantrian -> Tahfidz / Hafalan**

Data demo penting:

- Program aktif `DEMO-THF-REG`.
- Program nonaktif/arsip `DEMO-THF-INT`.
- Target hafalan untuk `NIS-DEMO-AKTIF` dan `NIS-DEMO-PPDB`.
- Setoran dengan status diterima, menunggu review, perlu koreksi, dan
  dibatalkan.
- Contoh murojaah dan riwayat koreksi/pembatalan.

Tujuan:

- Melihat daftar setoran Tahfidz/Hafalan.
- Memfilter setoran berdasarkan pencarian, tanggal, tipe, dan status.
- Membuka detail setoran untuk membaca program, santri, pembimbing, target,
  catatan kualitas, dan riwayat koreksi.
- Membuat atau mengubah program tahfidz.
- Membuat atau mengubah target hafalan santri.
- Mencatat setoran hafalan baru atau murojaah.
- Mereview setoran yang menunggu review menjadi diterima atau perlu koreksi.
- Membatalkan/void setoran dengan alasan tanpa menghapus histori data.

Relasi:

- Tahfidz memakai santri aktif dari Data Induk Santri.
- Tahfidz memakai pembimbing aktif dari SDM Pesantren.
- Target dapat memakai periode aktif dari Tahun Ajaran & Semester.
- Kelas/Rombel dan Asrama saat ini belum menjadi filter khusus di layar
  Tahfidz; data santri tetap bisa ditelusuri dari module asalnya.
- Sertifikat tahfidz, lampiran audio/video, dan portal wali/santri belum
  dibuat. Baseline sekarang fokus pada pencatatan internal/admin dulu.

### Langkah N: Perizinan Santri

Menu: **Pesantrian -> Perizinan Santri**

Data demo penting:

- `IZN-DEMO-DRAFT`: izin pulang yang masih draft.
- `IZN-DEMO-SUBMITTED`: izin kegiatan yang menunggu review.
- `IZN-DEMO-APPROVED`: izin yang sudah disetujui.
- `IZN-DEMO-REJECTED`: izin yang ditolak dengan alasan.
- `IZN-DEMO-CHECKEDOUT`: izin berjalan/santri sudah keluar.
- `IZN-DEMO-RETURNED`: izin selesai dan santri kembali terlambat.
- `IZN-DEMO-VOID`: izin yang dibatalkan tanpa menghapus data.

Tujuan:

- Melihat daftar izin santri.
- Memfilter berdasarkan pencarian, tanggal, jenis izin, status, dan
  keterlambatan.
- Membuka detail izin untuk membaca data santri, snapshot wali, lifecycle
  waktu, catatan keputusan, catatan kembali, void, dan histori revisi.
- Membuat draft izin dari santri aktif.
- Mengedit draft sebelum submit.
- Submit draft agar masuk review.
- Approve atau reject izin yang menunggu review.
- Check-out santri untuk izin yang sudah disetujui.
- Return/check-in saat santri kembali, termasuk mencatat keterlambatan.
- Void izin non-final dengan alasan tanpa menghapus histori data.

Relasi:

- Perizinan memakai santri aktif dari Data Induk Santri dan Wali.
- Snapshot wali diambil dari data wali utama santri supaya histori izin tetap
  terbaca walaupun data wali berubah.
- Actor approve, check-out, return, dan void adalah user yang login.
- Presensi Santri sudah bisa membaca izin melalui integrasi read-only internal
  dari PerizinanSantri, tetapi layar Presensi belum otomatis mengisi status
  izin. Operator tetap mencatat status presensi secara manual sampai auto-fill
  diputuskan di increment terpisah.
- Kesehatan/Klinik, Pelanggaran/Kedisiplinan, tagihan/denda, notifikasi wali,
  dan lampiran dokumen izin belum otomatis terhubung karena module terkait
  belum dibuat atau belum diputuskan integrasinya.

## 4. Module yang Belum Dibuat

Jika saat uji manual terasa ada relasi yang belum bisa diklik, itu memang masih
di luar baseline running saat ini.

| Kebutuhan | Status saat ini |
| --- | --- |
| Wali Santri master | Belum dibuat; wali masih snapshot di Santri/PPDB. |
| Pelanggaran / Kedisiplinan | Belum dibuat. |
| Prestasi | Belum dibuat. |
| Kesehatan / Klinik | Belum dibuat. |
| Konseling / Pembinaan | Belum dibuat. |
| Alumni | Belum dibuat; status lulus ada di Santri. |
| Tagihan / Pembayaran / Tunggakan | Belum dibuat; biaya PPDB masih status sederhana. |
| Donasi / Wakaf | Belum dibuat. |
| Inventaris / Aset | Belum dibuat. |
| Dokumen/file requirement | Belum dibuat sebagai module; PPDB masih checklist sederhana. |

## 5. Checklist Uji Manual Cepat

- Login sebagai SuperSystem atau role operator.
- Pastikan menu yang muncul sesuai role.
- Buka Organisasi dan lihat struktur `DEMO-*`.
- Buka Tahun Ajaran & Semester dan pastikan semester aktif ada.
- Buka SDM dan pastikan guru MTs/MA aktif ada.
- Buka PPDB dan coba lifecycle pendaftaran.
- Buka Santri dan cek santri hasil PPDB.
- Buka Kelas/Rombel, buka detail rombel, lalu coba placement santri dan wali
  kelas.
- Buka/akses Asrama, cek data `DEMO-ASR-*`, kamar `DEMO-KMR-*`, placement
  santri, musyrif, archive, dan restore.
- Buka Presensi Santri, cek `DEMO-KBM-PAGI`, `DEMO-ASRAMA-MALAM`,
  `DEMO-MUHADHARAH`, dan `DEMO-VOID`, lalu coba buat draft, isi entry, submit,
  revisi, dan void.
- Buka Tahfidz / Hafalan, cek `DEMO-THF-REG`, target santri, setoran
  accepted/submitted/needs_revision/void, lalu coba tambah program, tambah
  target, tambah setoran, review, dan void.
- Buka Perizinan Santri, cek `IZN-DEMO-*`, lalu coba buat draft, edit, submit,
  approve/reject, check-out, return/check-in, dan void.
- Buka Audit Trail setelah beberapa aksi dan cek aktivitas tercatat.

Jika ada error Ziggy/route di console browser, catat nama route yang disebutkan.
Itu biasanya berarti frontend memanggil route yang belum teregistrasi atau build
frontend belum diperbarui.
