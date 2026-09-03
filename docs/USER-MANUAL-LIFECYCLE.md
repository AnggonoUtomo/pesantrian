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
`DEMO-*`, `PPDB-DEMO-*`, `NIS-DEMO-*`, dan `PEG-DEMO-*`.

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
| OperatorSantri | `operator-santri@example.test` | Data induk santri, wali, lifecycle santri. |
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

- Module seperti PPDB, Santri, SDM, Academic Period, dan Kelas/Rombel menulis
  audit lewat contract audit yang tersedia.

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

- Unit dipakai oleh PPDB, Santri, SDM, Academic/KelasRombel.
- Module Asrama belum dibuat, jadi unit asrama baru menjadi data organisasi
  pendukung.

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
- Musyrif/pembina asrama sudah ada sebagai data demo, tetapi module Asrama belum
  dibuat.
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

## 4. Module yang Belum Dibuat

Jika saat uji manual terasa ada relasi yang belum bisa diklik, itu memang masih
di luar baseline running saat ini.

| Kebutuhan | Status saat ini |
| --- | --- |
| Wali Santri master | Belum dibuat; wali masih snapshot di Santri/PPDB. |
| Asrama | Belum dibuat; unit asrama baru ada di Organisasi. |
| Tahfidz / Hafalan | Belum dibuat. |
| Presensi Santri | Belum dibuat. |
| Perizinan Santri | Belum dibuat. |
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
- Buka Audit Trail setelah beberapa aksi dan cek aktivitas tercatat.

Jika ada error Ziggy/route di console browser, catat nama route yang disebutkan.
Itu biasanya berarti frontend memanggil route yang belum teregistrasi atau build
frontend belum diperbarui.
