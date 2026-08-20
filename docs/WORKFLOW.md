# Workflow Kerja

## Sebelum mengubah

1. Tentukan file dan perilaku yang terdampak.
2. Tetapkan acceptance criteria dan verifikasi yang paling spesifik.
3. Tanyakan user jika ada keputusan yang mengubah arah atau scope.

Untuk module, generator, atau struktur baru, periksa modul terkait terlebih
dahulu dengan command yang tersedia, misalnya:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:inspect {Domain}/{Module} --json
```

## Saat mengubah

- Kerjakan satu increment kecil pada satu waktu.
- Jalankan focused test setelah increment tersebut.
- Tambahkan test bila behavior berubah atau bug diperbaiki.
- Untuk alur pengguna, verifikasi UI, permission, loading/error state, dan
  browser bila perubahan menyentuh frontend.

## Setelah mengubah

- Jalankan pemeriksaan relevan, misalnya test terfokus, lint, typecheck, build,
  atau validasi modul.
- Perbarui dokumentasi bila contract, operasi, atau keputusan berubah.
- Laporkan risiko yang masih terbuka. Jangan otomatis mengerjakannya.
- Commit dan push hanya jika user memintanya.

## Catatan bukti

Untuk perubahan penting, catat singkat: apa yang berubah, alasan, command yang
dijalankan, hasil, dan risiko. Tidak perlu membuat execution log panjang untuk
perubahan kecil yang sudah tercakup oleh test dan Git history.
