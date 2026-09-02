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

## Non-scope

- Tidak membuat data demo untuk module yang belum diimplementasikan.
- Tidak menjalankan `migrate:fresh` atau menghapus data lokal existing.
- Tidak membuat data demo di environment `production`.

## Keputusan

Setiap module baru yang memiliki table atau data operasional wajib menyertakan
seeder demo idempotent di `Database/Seeders` dan dipanggil dari
`database/seeders/DatabaseSeeder.php` sesuai urutan dependency.
