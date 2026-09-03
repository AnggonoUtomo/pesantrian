# Demo Seeders Lintas Module

Work item ini menambahkan data demo development untuk module bisnis yang sudah
memiliki lifecycle operasional.

## Scope

- `Organization/Organization`: unit yayasan, pesantren, pendidikan, asrama, dan
  satu unit nonaktif.
- `Academic/AcademicPeriod`: tahun ajaran dan semester dengan status
  `draft`, `active`, dan `closed`.
- `HumanResource/HumanResource`: pegawai aktif/nonaktif beserta penugasan unit.
- `Pesantrian/PenerimaanSantri`: data PPDB dengan status
  `draft`, `submitted`, `verified`, `accepted`, `rejected`, dan `cancelled`.
- `Pesantrian/Santri`: data induk santri aktif, nonaktif, pindah, lulus,
  terarsip, dan satu santri hasil PPDB accepted.
- `Academic/KelasRombel`: kurikulum, tingkat kelas, rombel, placement santri,
  wali kelas aktif, wali kelas historis, dan rombel arsip.
- `System/AccessControl`: role operator demo dan user demo untuk uji manual.
- Dokumentasi user manual lifecycle lintas module.

## Non-scope

- Tidak membuat data demo untuk module yang belum diimplementasikan.
- Tidak menjalankan `migrate:fresh` atau menghapus data lokal existing.
- Tidak membuat data demo di environment `production`.
- Tidak menyimpan password pengujian personal ke source code.

## Keputusan

Setiap module baru yang memiliki table atau data operasional wajib menyertakan
seeder demo idempotent di `Database/Seeders` dan dipanggil dari
`database/seeders/DatabaseSeeder.php` sesuai urutan dependency.

Password akun demo dibaca dari `ACCESS_CONTROL_DUMMY_PASSWORD` pada `.env`
lokal bila ingin password seragam untuk uji manual. Jika env kosong, password
demo dibuat acak dan tidak ditampilkan.
