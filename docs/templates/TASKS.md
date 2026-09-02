# Tasks: {Nama Pekerjaan}

## Sebelum Mulai

- [ ] Scope dan non-scope disetujui.
- [ ] Dependency dan keputusan terbuka diketahui.
- [ ] Focused test atau cara verifikasi ditentukan.
- [ ] Dokumen baseline yang relevan sudah dibaca.

## Pekerjaan

- [ ] {Task kecil dengan satu hasil yang jelas.}
  - Acceptance: {kondisi lulus}.
  - Verification: `{command}`.
- [ ] Jika module memiliki table/data operasional, tambahkan seeder demo
  idempotent dan verifikasi `php artisan db:seed --no-ansi`.

## Hasil

- [ ] Scope selesai.
  - Perubahan: {ringkasan file/behavior}.
  - Verification: `{command}` -> {hasil}.
  - Risiko terbuka: {risiko tersisa atau tidak ada}.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
