# Workflow Kerja

## Tentukan Jenis Pekerjaan

| Jenis pekerjaan | Dokumentasi minimum |
| --- | --- |
| Modul baru | Folder module, README, specification, plan, dan tasks |
| Capability signifikan | Folder work item, README, plan, dan tasks |
| Pekerjaan lintas module | Folder global work item, README, plan, dan tasks |
| Bug kecil, typo, atau satu file | Tidak perlu folder baru |

Gunakan PRD bila pekerjaan memperkenalkan kebutuhan produk baru atau requirement
belum jelas. Gunakan ADR hanya untuk keputusan yang mahal atau sulit dibalik.

## Sebelum Mengubah

1. Tentukan scope, non-scope, acceptance criteria, dan verifikasi.
2. Baca `AGENTS.md`, `docs/README.md`, lalu dokumen yang relevan.
3. Untuk module, generator, contract, adapter, atau struktur folder, baca
   `ARCHITECTURE.md` dan `FOLDER-STRUCTURE.md`.
4. Inventarisasi source/generator terkait sebelum mengubah struktur.
5. Tanyakan user bila ada keputusan yang mengubah arah, boundary, stack,
   identifier, atau scope.

Command baseline yang dapat dipakai ketika relevan:

```bash
php artisan module:validate --no-ansi
php artisan starter:verify --no-ansi
php artisan about --no-ansi
```

Jika command belum tersedia atau gagal karena gap project, lakukan inventory
read-only, catat keterbatasannya, dan jangan mengarang hasil validasi.

## Saat Mengubah

- Kerjakan satu increment kecil pada satu waktu.
- Gunakan module generator project untuk module baru bila tersedia.
- Ikuti `app/Modules/<Namespace>/<Module>/`.
- Jangan membuat folder optional tanpa isi dan concern nyata.
- Module baru yang memiliki table/data operasional wajib menyertakan seeder demo
  idempotent di `Database/Seeders` dan dipanggil dari `DatabaseSeeder` sesuai
  urutan dependency.
- Jangan melakukan direct mutation lintas module.
- Tambahkan test bila behavior berubah atau bug diperbaiki.
- Untuk alur pengguna, verifikasi permission backend, loading/error state,
  Inertia response, dan browser bila perubahan menyentuh frontend.

## Setelah Mengubah

- Jalankan pemeriksaan relevan: test terfokus, lint/typecheck/build,
  `module:validate`, atau command runtime.
- Jalankan `git diff --check` untuk hygiene diff.
- Perbarui dokumentasi bila contract, operasi, atau keputusan berubah.
- Laporkan perubahan, verifikasi, area yang tidak disentuh, dan risiko terbuka.
- Berhenti setelah scope terpenuhi. Jangan otomatis memulai work item berikutnya.
- Commit dan push hanya jika user memintanya.

## Catatan Bukti

Untuk perubahan penting, catat singkat: apa yang berubah, alasan, command yang
dijalankan, hasil, dan risiko. Tidak perlu membuat execution log panjang untuk
perubahan kecil yang sudah tercakup oleh test dan Git history.
