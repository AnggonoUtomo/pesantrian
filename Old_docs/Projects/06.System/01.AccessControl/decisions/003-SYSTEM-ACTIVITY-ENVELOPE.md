# ADR-003: Envelope Activity Lintas Module System

## Status

`Diterima` pada 10 Agustus 2026.

## Konteks

AccessControl dan UserManagement menerbitkan event activity dengan payload yang sama untuk AuditLog. Dua class dan dua listener terpisah membuat contract lintas module berulang tanpa perbedaan perilaku.

## Keputusan

AccessControl memiliki public contract `SystemActivityOccurred`. Envelope membawa identitas module, nama/version event, correlation ID, actor, subject, reason, dan metadata. AuditLog memakai satu listener yang hanya menerima module dan event name yang diizinkan. Event lama tidak dihapus dalam increment ini, tetapi publisher resmi berpindah ke envelope baru.

## Konsekuensi

- Tidak ada dependency concrete baru atau circular dependency.
- AuditLog tetap fail-closed dan menolak module/event version yang tidak dikenal.
- Contract test wajib membuktikan kedua producer menghasilkan envelope yang sama dan consumer menolak envelope tidak didukung.
