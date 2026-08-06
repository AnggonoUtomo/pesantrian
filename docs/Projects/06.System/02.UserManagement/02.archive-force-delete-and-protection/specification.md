# Specification: Archive, Force Delete, dan Perlindungan User

## Status dan Owner

- Status: `Implemented — quality checkpoint lulus`.
- Owner: `System/UserManagement`.
- Keputusan sumber: [ADR-0003](../decisions/ADR-0003-USER-ARCHIVE-AND-PROTECTION-LIFECYCLE.md).

## Kondisi Awal

- `deleted_at` menandai user diarsipkan dan tidak mengubah `status`.
- Soft delete, perubahan status, dan impersonation sudah menolak target
  `SuperSystem`.
- `UpdateUser` dan `AssignUserRole` belum memiliki guard target `SuperSystem`
  di Application boundary.
- Daftar arsip masih berisiko menawarkan aksi `Arsipkan` kembali.
- Restore dan force delete belum tersedia pada kondisi awal; implementasi
  selesai pada Task 03 dan Task 04.

## Keputusan yang Diterapkan

1. Arsip dan `status` adalah state berbeda. Soft delete tidak mengubah status.
2. Target dengan role `SuperSystem` adalah protected. Semua mutation
   administratif harus ditolak server-side.
3. User terarsip tidak boleh menerima soft delete kedua.
4. Restore dan force delete adalah capability terpisah; keduanya tidak boleh
   ditambahkan tanpa permission, policy, action, audit, dialog, dan test.
5. `user.restore` dan `user.force.delete` adalah permission sensitif terpisah.

## Standar Operasi Frontend

- Backend mutation mengirim payload `Inertia::flash('toast', ...)` dengan type
  dan pesan sukses. `<Toaster />` global menampilkan Sonner; dialog tidak boleh
  menambah toast sukses kedua secara lokal.
- Tombol submit mutation memakai `LoadingButton` atau pola setara: disabled
  saat request berjalan, menampilkan spinner, dan memakai label proses yang
  jelas.
- Aturan ini berlaku untuk create, update, status, role, arsip, restore, force
  delete, dan impersonation UserManagement. Aturan yang sama menjadi baseline
  bagi module berikutnya.

## Task 01: Guard Backend SuperSystem

Task pertama hanya memperbaiki kesenjangan keamanan yang telah ditemukan.

- `UpdateUser::execute()` menolak target protected.
- `AssignUserRole::execute()` menolak target protected.
- Policy dan controller tetap menjadi coarse-grained authorization; Application
  Action menjadi pertahanan akhir untuk invariant target.
- Error authorization tidak membocorkan data sensitif.
- Test positif memastikan mutation terhadap user biasa tetap berfungsi.
- Test negatif memastikan actor berizin sekalipun tidak dapat mengubah profil
  atau role target `SuperSystem` melalui route maupun action terkait.

## Acceptance Criteria Task 01

- [x] Update target `SuperSystem` ditolak backend.
- [x] Assignment role target `SuperSystem` ditolak backend.
- [x] Mutation user biasa tetap tersedia bagi actor yang berizin.
- [x] Test positif dan negatif lulus.
- [x] Tidak ada import concrete private dari AccessControl.
- [x] `module:validate System/UserManagement --json` lulus.

## Non-scope Increment

- Tidak menambah tabel, migration, atau package.
- Tidak mengubah status ketika soft delete.
- Tidak mengerjakan pagination, role efektif, dan toolbar ADR-0004.

## Verifikasi

```bash
php artisan test --filter=UserManagement
php artisan module:validate System/UserManagement --json
npm run lint:check
npm run types:check
```

Browser memverifikasi state arsip, dialog restore/force delete, dan console
bersih. Test presentation memverifikasi session flash toast untuk create user.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menetapkan scope pertama ADR-0003 sebagai guard backend SuperSystem. |
| 1.1 | 2026-08-06 | Menandai acceptance Task 01 berdasarkan focused test dan quality check. |
| 1.2 | 2026-08-06 | Menyelesaikan restore dan force delete dengan permission, policy, audit, dan UI. |
| 1.3 | 2026-08-06 | Mencatat Ziggy, toast Sonner, LoadingButton, dan evidence quality checkpoint akhir. |
