# Alur Backend AccessControl

## 1. Bootstrap Module

Laravel mendaftarkan `AccessControl\ServiceProvider` melalui
`bootstrap/providers.php`.

Alurnya:

```text
bootstrap/providers.php
    -> AccessControl\ServiceProvider
    -> register singleton AuthorizationCapability
    -> daftarkan access-control:seed
    -> boot migration module dan policy Role
```

`ServiceProvider` hanya melakukan wiring framework. Query, mutation, dan aturan
business tidak diletakkan di provider.

## 2. Permission Identity

File: `app/Modules/System/AccessControl/permissions.php`.

File ini menjadi sumber permission module. Setiap permission memiliki `key`,
`description`, `module`, dan `sensitive`.

Permission dibaca oleh registry atau seeder. Module lain tidak boleh mengambil
permission dari private implementation AccessControl.

## 3. Request Membuka Halaman

Route utama berada pada `Routes/web.php`:

```text
GET /system/dashboard
    -> RoleController@systemDashboard

GET /system/access-control
    -> RoleController@index
```

Sebelum controller berjalan, route menggunakan middleware coarse-grained untuk
memastikan actor memiliki permission yang diperlukan. Policy tetap menjadi
pemeriksaan resource dan state.

Alur lengkap:

```text
HTTP request
    -> authentication
    -> route middleware
    -> RoleController
    -> BuildAccessControlDashboard::execute()
    -> AccessControlDashboardData::toArray()
    -> Inertia response
```

`RoleController` tidak mengambil query Eloquent untuk membangun halaman. Ia
hanya menerima dependency, memanggil query, dan mengembalikan response.

## 4. Authorization Capability

Public contract: `Application/Contracts/AuthorizationCapability.php`.

Hasil typed: `Application/DTO/AuthorizationDecision.php`.

Implementasi: `Infrastructure/Services/SpatieAuthorizationAdapter.php`.

Alurnya:

```text
Application atau policy
    -> AuthorizationCapability
    -> SpatieAuthorizationAdapter
    -> User/Role/Permission Spatie
    -> AuthorizationDecision
```

Adapter menyembunyikan detail Spatie dari pemanggil public contract. Module
lain hanya boleh menggunakan capability publik.

## 5. Membuat Role

File yang terlibat:

- `Presentation/Requests/StoreRoleRequest.php`;
- `Presentation/Controllers/RoleController.php`;
- `Application/Actions/CreateRole.php`;
- `Application/Services/AuthorizeRoleMutation.php`;
- `Infrastructure/Persistence/Models/Role.php`.

Alurnya:

```text
POST access-control.roles.store
    -> StoreRoleRequest memvalidasi name
    -> RoleController menerima request
    -> CreateRole::execute(actor, name)
    -> AuthorizeRoleMutation::ensureAllowed(actor)
    -> buat model Role konkret
    -> simpan role dengan ULID
    -> redirect dengan flash notification
```

Authorization diperiksa kembali di application layer. Pemeriksaan frontend
tidak cukup untuk mengizinkan mutation.

## 6. Mengubah Permission Role

Alurnya:

```text
PUT access-control.roles.permissions.update/{role}
    -> SyncRolePermissionsRequest memvalidasi permission list
    -> route binding mengambil Role
    -> AccessControlPolicy memeriksa role dan state
    -> SyncRolePermissions::execute(actor, role, permissions)
    -> AuthorizeRoleMutation menolak role SuperSystem
    -> Spatie menyimpan permission role
    -> redirect dengan flash notification
```

Role `SuperSystem` dilindungi agar permission istimewanya tidak diubah melalui
halaman biasa.

## 7. Menghapus Role

```text
DELETE access-control.roles.destroy/{role}
    -> policy memeriksa actor dan target role
    -> DeleteRole::execute(actor, role)
    -> AuthorizeRoleMutation memeriksa ulang
    -> role protected ditolak
    -> role biasa dihapus
    -> redirect dengan flash notification
```

## 8. Shared Authorization Context

File: `app/Http/Middleware/HandleInertiaRequests.php`.

Props global yang dibagikan:

- `auth.user`;
- `auth.roles` sebagai associative boolean object;
- `auth.permissions` sebagai associative boolean object;
- `auth.superSystem` sebagai boolean.

Data ini membantu frontend menentukan menu dan tampilan. Data ini bukan
security boundary karena backend tetap memeriksa middleware, policy, dan
application action.

## 9. Seeder Module

Seeder tetap berada pada owner module, tetapi dipanggil dari `DatabaseSeeder`
global sesuai dependency order.

```text
php artisan migrate:fresh --seed
    -> DatabaseSeeder
    -> AccessControlSeeder
    -> ModuleRegistry membaca permissions.php setiap module valid
    -> buat seluruh permission identity
    -> buat SuperSystem dan SecurityAdmin
    -> buat user demo saat bukan production
```

Seeder bersifat idempotent. Password demo dibaca melalui
`config/access-control.php`, bukan langsung menggunakan `env()` di seeder.

## 10. Database

Migration module berada di:

`Database/Migrations/2026_08_05_000000_create_permission_tables.php`.

Tabel permission, role, dan pivot memakai ULID. Model module berikut memakai
model Spatie dengan konfigurasi ULID:

- `Infrastructure/Persistence/Models/Role.php`;
- `Infrastructure/Persistence/Models/Permission.php`.

## Verification Backend

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan test
composer ci:check
```

Focused test penting:

- `tests/Feature/AccessControlAuthorizationCapabilityTest.php`;
- `tests/Feature/AccessControlPageTest.php`;
- `tests/Feature/AccessControlPolicyAndContextTest.php`;
- `tests/Feature/AccessControlSchemaTest.php`;
- `tests/Feature/AccessControlSeederTest.php`;
- `tests/Feature/AccessControlArchitectureTest.php`.
