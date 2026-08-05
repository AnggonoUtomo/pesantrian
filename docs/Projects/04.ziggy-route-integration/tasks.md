# Task Integrasi Route Ziggy

| ID       | Tahap   | Task                      | Acceptance                                             | Verifikasi       | Status  |
| -------- | ------- | ------------------------- | ------------------------------------------------------ | ---------------- | ------- |
| TASK-001 | INC-001 | Putuskan kebijakan route  | Allowlist dan `route().current()` terdokumentasi       | Review spec/ADR  | Selesai |
| TASK-002 | INC-002 | Perkuat typed shared prop | Boundary Ziggy bebas `any`                             | TypeScript, lint | Selesai |
| TASK-003 | INC-002 | Bersihkan adapter route   | Satu implementasi aktif                                | Lint dan build   | Selesai |
| TASK-004 | INC-003 | Verifikasi route positif  | Route UI menghasilkan URL valid                        | Pest dan build   | Selesai |
| TASK-005 | INC-003 | Verifikasi route negatif  | Route internal tidak diekspos; backend tetap authority | Pest terarah     | Selesai |

## Detail Task

### TASK-001 — Kebijakan route

- [x] Scope task selesai.
    - Kondisi awal: Ziggy belum memakai allowlist dan `route().current()` belum
      memiliki keputusan.
    - File ditinjau: route list, `HandleInertiaRequests`, `config/ziggy.php`,
      adapter route, specification, dan ADR.
    - Perubahan: menetapkan allowlist route UI dan menolak kebutuhan
      `route().current()` untuk contract awal.
    - Alasan: membatasi metadata route yang dikirim ke browser.
    - Evidence: specification/ADR menyatakan allowlist sebagai keputusan.

### TASK-002 — Typed shared prop

- [x] Scope task selesai.
    - Kondisi awal: boundary Ziggy memakai `any` dan konfigurasi belum typed.
    - File diubah: `resources/js/lib/ziggy.ts`,
      `resources/js/types/global.d.ts`, `resources/js/app.tsx`.
    - Perubahan: menambah `ZiggyConfig`, typed `page.props.ziggy`, dan
      `setZiggy()` dari `withApp()` standar Inertia.
    - Alasan: menghindari runtime/type mismatch dan menjaga lifecycle bawaan.
    - Evidence: `npm run types:check`, lint, build, dan test lulus.

### TASK-003 — Adapter route canonical

- [x] Scope task selesai.
    - Kondisi awal: `resources/js/lib/route.ts` memiliki dead code dan lebih dari
      satu pola implementasi.
    - File diubah: `resources/js/lib/route.ts`.
    - Perubahan: menyisakan wrapper typed tunggal yang meneruskan config Ziggy.
    - Alasan: consumer frontend harus memiliki satu sumber perilaku route.
    - Evidence: ESLint, TypeScript, dan CSR/SSR build lulus.

### TASK-004 — Route positif

- [x] Scope task selesai.
    - Kondisi awal: belum ada regression test untuk route UI utama.
    - File dibuat/diubah: `tests/Feature/ZiggyRouteTest.php`.
    - Perubahan: menguji named route UI ada pada konfigurasi Ziggy.
    - Alasan: memastikan perubahan allowlist tidak memutus navigasi frontend.
    - Evidence: test Ziggy lulus; browser `/` dan `/login` menampilkan link valid.

### TASK-005 — Route negatif dan security boundary

- [x] Scope task selesai.
    - Kondisi awal: belum ada bukti route internal tidak ikut dibagikan.
    - File: `tests/Feature/ZiggyRouteTest.php`, specification, ADR, execution log.
    - Perubahan: menguji route unnamed, storage, dan up tidak tersedia pada config
      Ziggy; mendokumentasikan bahwa Ziggy bukan authorization boundary.
    - Alasan: URL generation tidak boleh dianggap sebagai bypass authorization.
    - Evidence: test negatif lulus; middleware/backend tetap menjadi security
      authority.

## Definisi Selesai

- [x] Scope task selesai.
    - Semua task memiliki kondisi awal, file, perubahan, alasan, dan evidence.
- [x] Test positif dan negatif tersedia.
    - Positif: route UI ada dan menghasilkan URL.
    - Negatif: route internal tidak masuk allowlist.
- [x] Dampak authorization ditinjau.
    - Ziggy hanya untuk URL/UX; authorization tetap di backend.
- [x] Bukti verifikasi tersimpan.
    - Pest, lint, type check, build, SSR, dan browser dicatat.
- [x] Dokumentasi dan log diperbarui.
    - Specification, ADR, plan, task, roadmap, dan execution log konsisten.
- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
    - Tidak ada task ditandai selesai tanpa evidence.
