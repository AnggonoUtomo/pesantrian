# Temuan Evaluasi: AccessControl

## Evidence Review

- `php artisan module:inspect System/AccessControl --json` menghasilkan module valid tanpa diagnostic.
- `php artisan test --filter=AccessControl` lulus: 37 test dan 158 assertion.
- Browser `http://starter13.test/system/access-control` pada desktop light memuat role, permission group, dialog role, shortcut, dan sidebar tanpa console error/warning.
- Controller tetap tipis; query, mutation, policy, dan audit berada pada boundary yang sesuai.

## Required 01 — Semantik permission mutation belum selaras

- Kondisi awal: `permissions.php` mendeklarasikan `access_control.permission.manage` dan `access_control.permission.assign` sebagai permission sensitif.
- Temuan: route sync permission memakai policy `AccessControlPolicy`; policy dan `AuthorizeRoleMutation` hanya memeriksa `access_control.role.manage`. Pencarian source tidak menemukan consumer runtime untuk dua permission tersebut.
- Dampak: actor dengan `role.manage` dapat memasang semua permission ke role, sedangkan actor yang hanya diberi `permission.assign` tidak dapat melakukannya. Nama permission dan security boundary menjadi menyesatkan.
- Rekomendasi: putuskan vocabulary authorization melalui ADR. Rekomendasi awal adalah `role.manage` untuk membuat/menghapus role, `permission.assign` untuk sync permission pada role, lalu menghapus atau mendeprekasi `permission.manage` sampai ada owner registry permission yang nyata.
- Dependency: AccessControl sebagai owner; AuditLog harus mencatat perubahan permission role dengan reason bila mutation sensitif dinaikkan.
- Acceptance implementasi nanti: policy, Action, UI visibility, seeder, test positif/negatif, dan dokumentasi memakai vocabulary yang sama.

## Required 02 — Application Action belum memvalidasi input secara mandiri

- Kondisi awal: `StoreRoleRequest` memvalidasi format, panjang, dan uniqueness nama role.
- Temuan: `CreateRole::execute()` hanya melakukan `trim()` sebelum `Role::query()->create()`. `SyncRolePermissions::execute()` juga mempercayai daftar permission dari FormRequest.
- Dampak: pemanggil internal baru, command, job, atau capability masa depan dapat melewati validasi HTTP dan menghasilkan exception database atau guard mismatch, bukan error application yang jelas.
- Rekomendasi: pindahkan aturan inti nama role, guard `web`, permission valid, dan duplicate conflict ke DTO/value object atau validator Application. FormRequest tetap menjadi validasi UX pertama.
- Acceptance implementasi nanti: direct Action test untuk nama kosong, format salah, duplicate, permission guard salah, dan daftar duplikat; HTTP tetap mengembalikan 422 yang konsisten.

## Optional 01 — Penghapusan role belum menunjukkan dampak user

- Kondisi awal: `DeleteRole` dapat menghapus role reguler dan mencatat audit role.
- Temuan: dashboard/dialog tidak menampilkan jumlah user yang memiliki role tersebut; Action juga tidak memiliki precondition atau metadata jumlah assignment terdampak.
- Dampak: operator dapat menghapus role yang dipakai banyak user tanpa memahami perubahan akses yang terjadi.
- Rekomendasi: tambahkan role usage summary, confirmation eksplisit, dan audit metadata `affected_user_count`. Aturan blokir atau forced revoke perlu keputusan produk.
- Owner: AccessControl. UserManagement hanya menjadi consumer UI bila membutuhkan detail user.

## Optional 02 — Dashboard role belum siap untuk catalog besar

- Kondisi awal: `BuildAccessControlDashboard` memuat semua role beserta permission dan seluruh permission group dalam satu response Inertia.
- Dampak: saat role/permission bertambah, page dapat menjadi berat dan pencarian hanya bekerja setelah seluruh data dikirim ke browser.
- Rekomendasi: ketika jumlah role nyata mulai besar, pisahkan read contract role catalog menjadi search/pagination server-side. Jangan dioptimalkan sebelum ada evidence volume atau latency.
- Owner: AccessControl.

## FYI — Baseline UI dan audit transaction baik

- `RoleController` hanya mengorkestrasi request/response.
- `LaravelAccessControlActivityPublisher` menjalankan mutation dan dispatch integration event dalam transaction. Failure consumer AuditLog dapat menggagalkan mutation sehingga audit tidak tertinggal.
- UI AccessControl memenuhi baseline visual dan console browser bersih pada review ini.

## Status Rekomendasi

Required 01 ditutup pada increment `01.security-hardening` melalui ADR-0001:
`role.manage` mengelola role dan `permission.assign` menyinkronkan permission.
Required 02 ditutup: `CreateRole` kini memvalidasi nama, guard `web`, dan
duplikasi; `SyncRolePermissions` menolak tipe, key, guard, serta daftar yang
tidak valid sebelum mutation. Focused test Application membuktikan caller
internal tidak dapat melewati aturan HTTP.
