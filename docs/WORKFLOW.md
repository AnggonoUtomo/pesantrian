# Workflow Kerja

## Tentukan jenis pekerjaan

| Jenis pekerjaan          | Dokumentasi minimum                                   |
| ------------------------ | ----------------------------------------------------- |
| Modul baru               | Folder module, README, specification, plan, dan tasks |
| Capability signifikan    | Folder work item, README, plan, dan tasks             |
| Pekerjaan lintas modul   | Folder global work item, README, plan, dan tasks      |
| Bug kecil atau satu file | Tidak perlu folder baru                               |

Gunakan PRD bila pekerjaan memperkenalkan kebutuhan produk baru atau requirement
belum jelas. Gunakan ADR hanya untuk keputusan yang mahal atau sulit dibalik.

## Sebelum mengubah

1. Tentukan file dan perilaku yang terdampak.
2. Tetapkan acceptance criteria dan verifikasi yang paling spesifik.
3. Tanyakan user jika ada keputusan yang mengubah arah atau scope.
4. Untuk pekerjaan signifikan, buat folder dari template yang sesuai:
    - module: `modules/{Domain}/{Module}/`;
    - bagian module: `modules/{Domain}/{Module}/work-items/{nama-pekerjaan}/`;
    - lintas module: `work-items/{nama-pekerjaan}/`.

Untuk module, generator, atau struktur baru, periksa modul terkait terlebih
dahulu dengan command yang tersedia, misalnya:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:inspect {Domain}/{Module} --json
```

Sebelum coding, cocokkan rencana dengan `ARCHITECTURE.md` dan
`FOLDER-STRUCTURE.md`. Jika lokasi, dependency, atau test structure bertentangan
dengan kode/generator, hentikan perubahan struktural dan minta keputusan.

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
- Berhenti setelah scope work item terpenuhi dan tunggu arahan berikutnya.
- Commit dan push hanya jika user memintanya.

## Catatan bukti

Untuk perubahan penting, catat singkat: apa yang berubah, alasan, command yang
dijalankan, hasil, dan risiko. Tidak perlu membuat execution log panjang untuk
perubahan kecil yang sudah tercakup oleh test dan Git history.
