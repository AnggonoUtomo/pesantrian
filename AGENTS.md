# Aturan Kerja Proyek

## Mulai dari konteks yang cukup

Sebelum mengubah kode, baca `docs/README.md`, lalu hanya dokumen yang relevan
dengan pekerjaan. Jangan membaca seluruh dokumentasi bila tugas kecil dan
mandiri.

Sebelum membuat atau mengubah module, contract, port, adapter, generator, atau
struktur folder, wajib membaca `docs/ARCHITECTURE.md` dan
`docs/FOLDER-STRUCTURE.md`. Jika kode, generator, dan dokumen tersebut tidak
selaras, hentikan perubahan struktural dan laporkan konfliknya.

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

- Aplikasi memakai DDD-lite Modular Monolith dengan Hexagonal Architecture.
- Framework reusable berada di `packages/StarterKit`; modul aplikasi berada di
  `app/Modules/{Domain}/{SubModule}`.
- Arah dependency internal adalah `Presentation -> Application -> Domain` dan
  `Infrastructure -> Application -> Domain`. Domain tidak bergantung pada layer
  luar; Application tidak mengimpor implementasi Infrastructure.
- `Presentation` adalah inbound adapter. `Application` berisi use case dan port.
  `Infrastructure` berisi outbound adapter. `ServiceProvider` adalah composition
  root yang menghubungkan port dengan adapter.
- Dependensi konkret lintas modul dilarang. Gunakan public contract, DTO, atau
  event pada `Application` yang memang dibutuhkan.
- Ikuti lokasi canonical dalam `docs/FOLDER-STRUCTURE.md`. Jangan membuat folder,
  port, event, service, atau adapter tanpa concern dan consumer nyata.
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

## Memulai pekerjaan baru

- Modul baru wajib memiliki folder `docs/modules/{Domain}/{Module}/` sebelum
  implementasi. Mulai dari template module di `docs/templates/`.
- Capability atau bagian modul yang signifikan memakai
  `docs/modules/{Domain}/{Module}/work-items/{nama-pekerjaan}/`.
- Pekerjaan signifikan yang melintasi beberapa modul memakai
  `docs/work-items/{nama-pekerjaan}/`.
- Gunakan nama folder `kebab-case`. Nama Domain dan Module mengikuti nama pada
  kode, misalnya `System/UserManagement`.
- Work item cukup memiliki `README.md`, `plan.md`, dan `tasks.md`. Tambahkan PRD
  bila kebutuhan produk baru atau belum jelas. Tambahkan ADR hanya bila ada
  keputusan yang mahal atau sulit dibalik.
- Bug kecil, typo, perubahan dokumentasi sederhana, dan perubahan satu file
  tidak wajib memiliki folder kerja baru.
- Setelah scope yang disetujui selesai, perbarui hasil pada `tasks.md`, pindahkan
  informasi permanen ke specification/API/ADR, laporkan risiko lain, lalu
  berhenti. Jangan otomatis memulai work item berikutnya.

## Referensi lama

Dokumentasi dan instruksi sebelumnya disimpan apa adanya di `Old_docs/` dan
`Old_AGENTS.md`. Keduanya adalah arsip, bukan aturan kerja aktif.
