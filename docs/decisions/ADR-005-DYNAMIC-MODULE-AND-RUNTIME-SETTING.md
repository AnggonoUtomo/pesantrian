# ADR-005: Dynamic Module dan Runtime Setting

## Status

Accepted

## Tanggal

2026-08-20

## Konteks

Module perlu di-bootstrap dari registry aktif tanpa daftar provider bisnis yang
hardcoded. Consumer juga perlu membaca runtime setting tanpa bergantung pada
implementasi konkret SystemSetting.

## Keputusan

- Gunakan dynamic module bootstrap dari registry module yang valid.
- Consumer mendefinisikan port runtime setting yang dibutuhkannya pada
  `Application/Contracts`.
- Adapter Infrastructure menghubungkan port tersebut dengan provider setting.
- `system-setting:set {key} {value}` boleh menerima argumen posisi untuk nilai
  biasa. Setting sensitif menolak nilai posisi dan memakai input tersembunyi
  interaktif atau `--value-stdin` untuk otomasi.

## Konsekuensi

- Bootstrap tidak perlu mengenal setiap provider bisnis secara hardcoded.
- Consumer tetap mengontrol contract yang dibutuhkan.
- Adapter dan binding bertambah ketika ada consumer nyata.
- Input sensitif tidak terekspos melalui riwayat command line.

## Verifikasi

- Module discovery dan validation lulus.
- Application consumer tidak mengimpor implementasi SystemSetting.
- Focused test membuktikan setting sensitif menolak argumen posisi.
