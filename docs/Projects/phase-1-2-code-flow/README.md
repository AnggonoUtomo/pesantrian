# Alur Pembuatan Code Phase 1 dan Phase 2

## Tujuan

Dokumen ini menjelaskan bagaimana file code dibuat dari Phase 1 sampai Phase
2. Gunakan dokumen ini sebagai bahan belajar sebelum masuk ke Phase 3.

## Ringkasan Alur

```text
Phase 1: cek starter kit dan runtime
    -> pasang dependency baseline
    -> buat starter:verify
    -> jalankan quality gate
    -> simpan evidence

Phase 2: buat package StarterKit
    -> buat ModuleManifest
    -> buat PermissionIdentity
    -> buat ModuleRegistry
    -> buat command module:*
    -> jalankan quality gate

Phase 3: susun input generator
    -> buat plan dari profile default-v1
    -> cek conflict dan dry-run
    -> tulis ke staging ULID
    -> promote secara atomic
    -> jalankan module:make dan quality gate
```

## Isi Folder

- `phase-1-flow.md`: fondasi runtime dan verification command.
- `phase-2-flow.md`: package dan module contract.
- `phase-3-flow.md`: generator `module:make`, staging, promotion, dan test.
- `file-map.md`: peta file code, tanggung jawab, dan test.

## Cara Belajar

1. Baca Phase 1 untuk memahami kondisi aplikasi sebelum package module dibuat.
2. Ikuti alur `VerifyStarterFoundation` untuk memahami pola check dan exit code.
3. Baca Phase 2 dari `StarterKitServiceProvider` menuju `ModuleRegistry`.
4. Cocokkan setiap class dengan test yang membuktikan behavior-nya.
5. Jalankan command verifikasi setelah membaca setiap bagian.

Phase 1, Phase 2, dan Phase 3 selesai pada scope yang sudah didokumentasikan.
Mode extension dan overwrite generator belum diaktifkan.
