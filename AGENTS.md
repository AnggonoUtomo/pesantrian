# Aturan Kerja Project

## Konfigurasi project

Sebelum memakai file ini, isi dan verifikasi:

- Nama project: `[Nama Project]`.
- Stack utama: `[Laravel/PHP/frontend/database/queue/cache]`.
- Lokasi module: `app/Modules/{Domain}/{Module}` atau `[lokasi lain]`.
- Lokasi framework reusable: `[path atau tidak ada]`.
- Strategi identifier: `[ULID/UUID/integer]`.
- Mekanisme route frontend: `[mekanisme yang digunakan]`.
- Command discovery/validation module: `[command atau tidak tersedia]`.

Hapus pilihan dan placeholder yang tidak berlaku setelah project intake.

## Mulai dari konteks yang cukup

Sebelum mengubah kode, baca `docs/README.md`, lalu hanya dokumen yang relevan
dengan pekerjaan. Jangan membaca seluruh dokumentasi untuk tugas kecil dan
mandiri.

Sebelum membuat atau mengubah module, contract, port, adapter, generator, atau
struktur folder, wajib membaca `docs/ARCHITECTURE.md` dan
`docs/FOLDER-STRUCTURE.md`. Jika kode, generator, dan dokumen tidak selaras,
hentikan perubahan struktural dan laporkan konfliknya.

Tentukan scope, acceptance criteria, dan cara verifikasi secara singkat. Jika
requirement atau keputusan penting belum jelas, tanyakan langsung kepada user.

## Cara bekerja

- Utamakan perubahan kecil dan terfokus.
- Jangan memperluas scope atau menutup risiko lain tanpa persetujuan user.
- Pecah perubahan multi-file menjadi increment yang dapat diverifikasi.
- Jalankan test atau pemeriksaan yang proporsional dengan risiko perubahan.
- Pertahankan perubahan user yang tidak terkait.
- Jangan membuat branch, commit, push, atau memasang dependency tanpa permintaan
  eksplisit user.

## Arsitektur dan keamanan

- Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture.
- Arah dependency internal adalah `Presentation -> Application -> Domain` dan
  `Infrastructure -> Application -> Domain`.
- Domain tidak bergantung pada layer luar. Application tidak mengimpor adapter
  konkret Infrastructure.
- `Presentation` adalah inbound adapter, `Application` berisi use case dan port,
  `Infrastructure` berisi outbound adapter, dan `ServiceProvider` menjadi
  composition root.
- Dependensi konkret lintas module dilarang. Gunakan public contract, DTO, atau
  event yang memang memiliki consumer nyata.
- Ikuti `docs/FOLDER-STRUCTURE.md`. Jangan membuat folder, port, event, service,
  atau adapter sebagai placeholder.
- Backend adalah security authority. Frontend permission hanya untuk UX.
- Jangan menyimpan atau menampilkan secret, token, password, credential, atau
  payload sensitif dalam source, log, test output, maupun dokumentasi.

## Dokumentasi pekerjaan

- Modul baru memakai `docs/modules/{Domain}/{Module}/`.
- Bagian module yang signifikan memakai
  `docs/modules/{Domain}/{Module}/work-items/{nama-pekerjaan}/`.
- Pekerjaan lintas module memakai `docs/work-items/{nama-pekerjaan}/`.
- Gunakan nama folder `kebab-case`; Domain dan Module mengikuti source code.
- Work item cukup memiliki `README.md`, `plan.md`, dan `tasks.md`.
- Tambahkan PRD untuk kebutuhan produk baru atau requirement yang belum jelas.
- Tambahkan ADR hanya untuk keputusan yang mahal atau sulit dibalik.
- Bug kecil, typo, dokumentasi sederhana, dan perubahan satu file tidak wajib
  memiliki folder kerja baru.

Setelah scope selesai, perbarui hasil, laporkan risiko lain, lalu berhenti.
Jangan otomatis memulai work item berikutnya.

## Bahasa dan handoff

- Gunakan bahasa project yang disepakati: `[Bahasa dokumentasi]`.
- Commit message mengikuti konvensi project: `[Konvensi commit]`.
- Pada handoff, laporkan perubahan, verifikasi, yang tidak disentuh, dan risiko
  terbuka secara ringkas.
