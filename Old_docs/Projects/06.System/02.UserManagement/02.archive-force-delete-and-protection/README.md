# 02. Archive, Force Delete, dan Perlindungan User

## Status

`Selesai — quality checkpoint lulus.`

Increment ini menjalankan keputusan pada
[ADR-0003](../decisions/ADR-0003-USER-ARCHIVE-AND-PROTECTION-LIFECYCLE.md).
Pekerjaan dimulai dari invariant keamanan: semua mutation terhadap target
`SuperSystem` harus ditolak oleh backend. Setelah itu, lifecycle arsip, restore,
dan force delete dikerjakan sebagai task terpisah dan diverifikasi satu per satu.

## Boundary dan Urutan

- Owner: `App\\Modules\\System\\UserManagement`.
- Dependency publik: capability AccessControl untuk authorization dan role.
- Audit mutation: Integration Event `UserManagementActivityOccurred` untuk
  consumer AuditLog.
- Urutan ini menggantikan sementara roadmap ADR-0004 yang sebelumnya
  meletakkan pagination lebih dahulu. Alasannya, guard `SuperSystem` adalah
  koreksi security yang harus tertutup sebelum mutation lifecycle ditambah.

## Urutan Baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)
5. [ADR-0003](../decisions/ADR-0003-USER-ARCHIVE-AND-PROTECTION-LIFECYCLE.md)

## Batas Scope

- Task 01 hanya menutup backend guard `SuperSystem` untuk mutation yang sudah
  tersedia.
- Restore dan force delete telah dibuat pada Task 03 dan Task 04.
- Pagination, role efektif, dan toolbar tetap menjadi scope ADR-0004 setelah
  lifecycle security ini selesai.

## Risiko Terbuka

| Risiko | Status | Tindakan |
| --- | --- | --- |
| Permission force delete | Ditutup | Memakai `user.force.delete` agar memenuhi dot notation manifest. |
| Mutation baru dapat lupa memakai guard | Ditutup | Guard Application/Policy dan matrix protected target telah dipakai oleh mutation lifecycle; setiap increment baru wajib memperluas test matrix yang sama. |

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menyiapkan increment ADR-0003 dan memprioritaskan guard backend SuperSystem. |
| 1.1 | 2026-08-06 | Menyelesaikan restore, force delete, audit, dan UI lifecycle user arsip. |
