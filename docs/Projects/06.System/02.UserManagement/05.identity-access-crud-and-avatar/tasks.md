# Tasks - Identity, Akses CRUD, dan Avatar

## Checklist

- [x] 01. Inventarisasi contract dan state UI identity.
  - Kondisi awal: `UserData`, resource, dan `Index.tsx` belum membawa avatar, verifikasi email, serta last login.
  - Perubahan: petakan file Application, Infrastructure, Presentation, TypeScript, route, policy, dan consumer AuditLog yang terkena.
  - Alasan: perubahan data lintas layer harus mengikuti owner boundary.
  - Evidence: `module:inspect`, `module:discover`, dan `module:validate` berhasil tanpa diagnostic; detail dicatat pada execution log.

- [x] 02. Implementasikan data identity dan modal read-only terlebih dahulu.
  - Kondisi awal: tabel/modal belum menyediakan fallback avatar dan aktivitas.
  - Perubahan: tambah field read contract, resource, TypeScript, kolom tabel, serta bagian Identity, Access, dan Activity pada detail.
  - Alasan: operator dapat menilai data sebelum mengubah akses user.
  - Acceptance: avatar kosong memakai fallback, `last_login_at = null` terbaca, dan aksi archive tidak regresi.
  - Evidence: test `mengirim identity dan access read model dengan fallback aktivitas aman` lulus; `composer ci:check` lulus dengan 260 test dan 1225 assertion. Browser runtime belum dapat dijalankan karena Chrome DevTools MCP tidak tersedia sebagai tool sesi.

- [ ] 03. Implementasikan role awal dan status awal secara atomik.
  - Kondisi awal: create form hanya mengirim name/email/password.
  - Perubahan: ubah DTO, FormRequest, Action/Command, policy/capability, dan modal tambah tanpa akses concrete lintas module.
  - Alasan: role/status awal harus aman dan tidak memberi privilege escalation.
  - Acceptance: permission backend menolak payload manual tanpa hak, role `SuperSystem` tidak dapat dipilih, dan rollback terjadi saat assignment gagal.
  - Evidence: positive/negative authorization test dan transaction test.

- [ ] 04. Tambahkan Spatie Media Library untuk avatar.
  - Kondisi awal: `composer.json` tidak memiliki Media Library dan User belum mengimplementasikan `HasMedia`.
  - Perubahan: tambah dependency kompatibel, config/migration package, collection `avatar`, conversion, validation, Action, request, dan UI upload.
  - Alasan: media perlu lifecycle file terpisah dari kolom identity user.
  - Acceptance: JPEG/PNG/WebP maksimal 2 MB berhasil; file lain/oversize gagal; collection hanya menyimpan satu avatar.
  - Evidence: filesystem fake, media test, `migrate:fresh --seed`, browser upload/replace/remove.

- [ ] 05. Tambahkan verifikasi email dan aktivitas login.
  - Kondisi awal: `email_verified_at` ada tetapi belum ditampilkan; tidak ada `last_login_at`.
  - Perubahan: tampilkan badge; tambah migration dan listener login sukses; perluas read model/detail.
  - Alasan: status security dan aktivitas terbaca tanpa mengubah flow native Laravel/Fortify.
  - Acceptance: user baru belum verified, login sukses mengubah timestamp, login gagal tidak mengubah timestamp.
  - Evidence: auth event test, migration test, dan browser detail.

- [ ] 06. Quality checkpoint dan dokumentasi akhir.
  - Kondisi awal: seluruh increment implementasi telah diverifikasi terpisah.
  - Perubahan: perbarui specification, implementation plan, task, ADR, README, execution log, serta dokumen downstream relevan.
  - Alasan: perubahan contract dan media harus dapat diaudit.
  - Evidence: `composer ci:check`, module validation, focused browser/accessibility test, dan Open Risk tersisa.

## Definition of Done

- [ ] Tabel serta modal menampilkan identitas, akses, verifikasi, dan aktivitas sesuai policy.
- [ ] Role/status awal aman, atomik, dan memiliki test positif/negatif.
- [ ] Avatar memakai Media Library dengan validasi, fallback, dan test file.
- [ ] `last_login_at` dicatat dari login sukses; verifikasi email tetap native.
- [ ] Dokumentasi dan evidence final diperbarui tanpa checklist palsu.
