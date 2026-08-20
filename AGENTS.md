# Aturan Kerja Proyek

## Mulai dari konteks yang cukup

Sebelum mengubah kode, baca `docs/README.md`, lalu hanya dokumen yang relevan
dengan pekerjaan. Jangan membaca seluruh dokumentasi bila tugas kecil dan
mandiri.

Tentukan scope, acceptance criteria, dan cara verifikasi secara singkat. Jika
requirement atau keputusan penting belum jelas, tanyakan langsung kepada user.

## Cara bekerja

- Utamakan perubahan kecil dan terfokus.
- Jangan memperluas scope atau menutup risiko lain tanpa persetujuan user.
- Untuk perubahan multi-file, pecah menjadi increment yang dapat diverifikasi.
- Jalankan test atau pemeriksaan yang proporsional dengan perubahan; mulai dari
  pemeriksaan paling spesifik.
- Jangan mengubah, menghapus, atau menimpa pekerjaan user yang tidak terkait.
- Jangan membuat branch, commit, push, atau memasang dependency tanpa permintaan
  eksplisit dari user.

## Arsitektur dan keamanan

- Aplikasi memakai DDD-lite Modular Monolith.
- Framework reusable berada di `packages/StarterKit`; modul aplikasi berada di
  `app/Modules/{Domain}/{SubModule}`.
- Dependensi konkret lintas modul dilarang. Gunakan public contract, DTO, atau
  event yang memang dibutuhkan.
- Backend adalah batas keamanan. Permission frontend hanya untuk UX.
- Gunakan ULID untuk primary key dan foreign key.
- Jangan menyimpan atau menampilkan secret, token, password, credential, atau
  payload sensitif dalam source, log, test output, maupun dokumentasi.
- Wayfinder dan Laravel Boost dilarang. Route frontend memakai Ziggy.

## Dokumentasi dan laporan

- Dokumentasi dan commit message menggunakan Bahasa Indonesia yang jelas.
- Perbarui dokumentasi hanya bila perubahan mengubah perilaku, contract,
  arsitektur, operasi, atau keputusan penting.
- Gunakan ADR singkat untuk keputusan yang mahal atau sulit dibalik.
- Pada handoff, laporkan perubahan, verifikasi, yang sengaja tidak disentuh, dan
  risiko terbuka secara ringkas.

## Referensi lama

Dokumentasi dan instruksi sebelumnya disimpan apa adanya di `Old_docs/` dan
`Old_AGENTS.md`. Keduanya adalah arsip, bukan aturan kerja aktif.
