# Remediasi Kode dan Dokumentasi System

## Status

Status: `Berjalan - security, bootstrap, boundary, dan fondasi API selesai;
vertical slice API berikutnya`.

Dokumen ini menjadi pintu masuk rencana perbaikan hasil evaluasi kode terhadap
baseline pada folder `docs/`. Open risk koneksi browser, performa SSR lokal,
rehearsal MySQL, dan exact constraint Composer sudah dikerjakan. Remediasi
Security SystemSetting dan dynamic module bootstrap sudah selesai. Remediasi
boundary serta API dilanjutkan sesuai dependency dan checkpoint task.

## Tujuan

- menutup risiko kebocoran nilai sensitif pada SystemSetting;
- memastikan record terenkripsi yang rusak memakai default aman;
- mengaktifkan isolasi module invalid atau disabled saat bootstrap;
- menghapus dependency module tersembunyi dan siklus dependency;
- mengembalikan arah dependency Application AccessControl ke contract/port;
- melengkapi contract API baseline;
- melengkapi quality gate CI, migration rehearsal, dan browser verification;
- menyelaraskan dokumentasi yang tertinggal dari implementasi.

## Dokumen Kerja

- [Implementation plan](implementation-plan.md)
- [Task checklist](tasks.md)
- [Execution log](planning/execution-log.md)
- [API implementation specification](api-implementation-specification.md)

## Batasan

- Rencana ini tidak memberi izin untuk commit, membuat branch, menginstal
  dependency, atau mengubah versi package. Penyesuaian constraint pada increment
  ini tidak mengubah versi package yang terkunci.
- `migrate:fresh --seed` tidak boleh dijalankan pada database default atau
  database yang belum dipastikan khusus untuk pengujian.
- Nilai secret, token, password, credential, ciphertext, dan session data tidak
  boleh masuk ke test output, log, screenshot, atau dokumen evidence.
- Perubahan public API dan bootstrap module harus didahului specification serta
  ADR yang disetujui.

## Status Open Risk Operasional

- Koneksi MCP `chrome-devtools` sudah berhasil. Halaman publik, login, dashboard,
  dan empat module sudah diperiksa pada desktop/mobile serta dua role demo.
- Konfigurasi SSR Inertia sekarang opt-in. Initial page tidak lagi menunggu
  endpoint SSR yang tidak berjalan; trace login menghasilkan LCP 1,053 detik,
  TTFB 575 ms, dan CLS 0.
- Rehearsal MySQL pada database sementara lulus untuk fresh/seed, idempotency,
  rollback, dan migrate/seed ulang. Database sementara sudah dihapus.
- Exact constraint Composer sudah diubah menjadi caret range tanpa mengubah
  versi package pada lock file. Strict validation serta audit lulus.
- Dynamic module bootstrap sudah aktif. Empat module production boot melalui
  manifest dalam urutan dependency canonical; provider statis sudah dihapus
  dari `bootstrap/providers.php`; cache config/rute dan full quality gate lulus.
- Boundary Application AccessControl, runtime-setting port, canonical API
  envelope, serta capability idempotency framework sudah selesai. Persistence
  idempotency dan policy retention/rate tetap dimiliki adapter SystemSetting.
- Open risk tersisa: upgrade migration dari snapshot release lama, automation
  Playwright/axe/CodeQL, flow browser mutation/empty/error/focus lengkap, dan
  static analysis package menyeluruh serta vertical slice API pada
  implementation plan.

## Keputusan Arsitektur

- [ADR-0001: Dynamic Module Runtime Bootstrap](decisions/ADR-0001-DYNAMIC-MODULE-RUNTIME-BOOTSTRAP.md) — `Accepted`.
- [ADR-0002: Consumer-owned Runtime-setting Port](decisions/ADR-0002-CONSUMER-OWNED-RUNTIME-SETTING-PORT.md) — `Accepted`.
- [ADR-0003: Framework Idempotency Capability](decisions/ADR-0003-FRAMEWORK-IDEMPOTENCY-CAPABILITY.md) — `Accepted`.
