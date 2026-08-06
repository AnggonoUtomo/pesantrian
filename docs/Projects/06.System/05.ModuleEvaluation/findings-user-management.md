# Temuan Evaluasi: UserManagement

## Evidence Review

- `php artisan module:inspect System/UserManagement --json` menghasilkan module valid tanpa diagnostic.
- `php artisan test --filter=UserManagement` lulus: 33 test dan 189 assertion.
- Browser `http://starter13.test/system/users` pada desktop light memuat 12 user, status lifecycle, action modal, shortcut, dan sidebar tanpa console error/warning.
- Controller telah memanggil Action dan Query; repository serta audit publisher tidak ditulis langsung di controller.

## Required 01 — Status lifecycle belum menjadi security boundary login

- Kondisi awal: UserManagement memiliki status `active`, `inactive`, dan `suspended`. UI menampilkan perbedaan status dan Action `ChangeUserStatus` dapat mengubahnya.
- Temuan: `FortifyServiceProvider` tidak memiliki `Fortify::authenticateUsing()` untuk membatasi status. Fortify memakai alur default; `LaravelImpersonationSession::start()` juga langsung menjalankan `loginUsingId()` tanpa memeriksa status target.
- Dampak: user dengan status `inactive` atau `suspended` tetap dapat login dengan password yang benar. Operator juga dapat melakukan impersonation terhadap target yang tidak aktif atau ditangguhkan.
- Rekomendasi: buat public contract/read model lifecycle yang dapat dipakai Fortify dan session impersonation; hanya `active` dan belum soft-deleted yang boleh terautentikasi. Pesan penolakan harus netral agar tidak membocorkan status akun.
- Owner: UserManagement. AccessControl tetap owner authorization setelah user berhasil diautentikasi; AuditLog mencatat perubahan status dan event impersonation, bukan credential.
- Acceptance implementasi nanti: test negatif login dan impersonation untuk `inactive`, `suspended`, serta soft-deleted; test positif untuk user aktif; session lama juga harus ditolak atau diakhiri saat status berubah sesuai keputusan keamanan.

## Required 02 — Target SuperSystem belum terlindungi pada update dan role assignment

- Kondisi awal: policy dan Action telah melarang perubahan status, soft delete, dan impersonation pada user SuperSystem.
- Temuan: `UserManagementPolicy::update()` hanya memeriksa `user.update`. `UpdateUser` tidak membaca target sebelum update. `AssignUserRole` juga hanya memeriksa permission actor; adapter AccessControl melindungi nama role `SuperSystem`, tetapi tidak melindungi user target SuperSystem dari role reguler.
- Dampak: actor dengan `user.update` berpotensi mengubah identity atau role user SuperSystem melalui endpoint yang sah. Ini tidak sesuai pola protected user yang sudah dipakai pada mutation lain.
- Rekomendasi: buat guard target protection di Application boundary, lalu gunakan policy resource sebagai defense in depth pada controller. Guard harus menolak update dan role assignment target SuperSystem kecuali actor SuperSystem sendiri bila keputusan produk tetap mengizinkan self-administration.
- Dependency: UserManagement owner rule protected target; AccessControl hanya menyediakan capability role assignment publik; AuditLog harus merekam outcome mutation yang diizinkan.
- Acceptance implementasi nanti: focused test langsung Action dan HTTP untuk actor non-SuperSystem terhadap target SuperSystem; test positif SuperSystem mengikuti keputusan policy yang disetujui.

## Required 03 — Action mutation masih menerima user soft-deleted

- Kondisi awal: repository `find`, `update`, dan `changeStatus` memakai `withTrashed()` agar UI dapat memperlihatkan user terarsipkan.
- Temuan: `UpdateUser`, `ChangeUserStatus`, dan `StartImpersonation` hanya memeriksa null/protected. Mereka tidak menolak `deletedAt` sebelum memanggil repository/session. `softDelete()` sendiri memakai query tanpa trashed sehingga pemanggilan ulang dapat menghasilkan exception database.
- Dampak: user yang sudah diarsipkan dapat diubah status atau identitasnya. Impersonation dapat menyimpan session sebelum `loginUsingId()` gagal menemukan model soft-deleted. Perilaku ini tidak konsisten dengan lifecycle archive.
- Rekomendasi: pisahkan read contract katalog yang boleh menyertakan archived user dari mutation target yang hanya menerima user aktif/non-archived. Tambahkan exception application yang dapat diterjemahkan menjadi response konsisten.
- Acceptance implementasi nanti: semua mutation menolak `deletedAt` kecuali feature restore yang memang menjadi backlog; tidak ada session impersonation setengah jadi ketika target archived.

## Required 04 — Middleware update/role assignment belum memakai policy resource

- Kondisi awal: route update memakai `can:user.update`; route role assignment juga memakai permission generik yang sama.
- Temuan: middleware tersebut memeriksa ability tanpa model target. Policy `UserManagementPolicy::update()` tidak dipanggil sebagai defense in depth untuk endpoint tersebut, berbeda dengan route impersonation yang memakai `can:impersonate,user`.
- Dampak: ketika resource-specific rule ditambahkan atau diubah, controller dapat tetap membuka jalur yang hanya memeriksa permission global. Ini berkontribusi pada tidak terlindunginya target SuperSystem.
- Rekomendasi: setelah guard Application tersedia, ubah middleware menjadi policy resource yang eksplisit atau gunakan `authorize()` pada FormRequest dengan target `User`. Application guard tetap wajib karena Action juga dapat dipanggil non-HTTP.
- Acceptance implementasi nanti: route test membuktikan policy target dipanggil, serta Action test membuktikan tidak dapat dilewati oleh caller internal.

## Optional 01 — Katalog user belum menggunakan pagination server-side

- Kondisi awal: `EloquentUserRepository::list()` memuat seluruh user termasuk archived, lalu frontend menerima seluruh katalog untuk tabel.
- Dampak: tabel dan pencarian akan semakin berat jika jumlah user bertambah besar.
- Rekomendasi: siapkan pagination dan filter status server-side bila data nyata menunjukkan volume/latency. Jangan menambah kompleksitas sebelum ada evidence kebutuhan.
- Owner: UserManagement.

## Optional 02 — Scope lanjutan belum siap diaktifkan

- Kondisi awal: README UserManagement telah mencatat restore user, invitation email, dan multi-role management sebagai scope lanjutan.
- Rekomendasi: pertahankan sebagai backlog terpisah. Restore membutuhkan policy serta audit khusus; invitation membutuhkan token, expiry, mail queue, dan revocation; multi-role membutuhkan contract assignment atomic dan tampilan perbandingan akses.
- Dependency: AccessControl public capability dan AuditLog integration event, tanpa import concrete lintas module.

## FYI — UI sudah membedakan user protected

- Dashboard UserManagement hanya menampilkan aksi lihat pada SuperSystem. Pada daftar normal, aksi edit, status, archive, impersonation, dan role tersedia sesuai permission.
- State UI ini baik untuk UX, tetapi backend guard tetap menjadi security authority.

## Status Rekomendasi

Belum ada code yang diubah. Required 01 sampai Required 04 perlu diselesaikan sebagai hardening UserManagement sebelum feature restore, invitation, multi-role, atau feature lintas module baru dimulai.
