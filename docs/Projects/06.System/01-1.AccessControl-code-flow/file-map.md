# Peta File Code AccessControl

## Backend Module

| Path | Tanggung jawab |
| --- | --- |
| `module.json` | Identity deklaratif module |
| `module.php` | Runtime configuration module |
| `ServiceProvider.php` | Register command, migration, dan policy |
| `permissions.php` | Sumber permission identity module |
| `Application/Contracts/AuthorizationCapability.php` | Contract authorization publik |
| `Application/DTO/AuthorizationDecision.php` | Hasil authorization typed |
| `Application/DTO/AccessControlDashboardData.php` | DTO data halaman |
| `Application/Queries/BuildAccessControlDashboard.php` | Query dashboard dan role/permission data |
| `Application/Actions/CreateRole.php` | Use case membuat role |
| `Application/Actions/SyncRolePermissions.php` | Use case sinkronisasi permission |
| `Application/Actions/DeleteRole.php` | Use case menghapus role |
| `Application/Services/AuthorizeRoleMutation.php` | Re-check authorization mutation |
| `Infrastructure/Persistence/Models/Role.php` | Model Spatie Role dengan ULID |
| `Infrastructure/Persistence/Models/Permission.php` | Model Spatie Permission dengan ULID |
| `Infrastructure/Services/SpatieAuthorizationAdapter.php` | Adapter Spatie ke public capability |
| `Presentation/Controllers/RoleController.php` | Orchestration request dan response |
| `Presentation/Policies/AccessControlPolicy.php` | Rule resource dan protected role |
| `Presentation/Requests/StoreRoleRequest.php` | Validasi pembuatan role |
| `Presentation/Requests/SyncRolePermissionsRequest.php` | Validasi permission mutation |
| `Routes/web.php` | Route halaman dan mutation web |
| `Database/Migrations/*` | Schema role, permission, dan pivot |
| `Database/Seeders/AccessControlSeeder.php` | Data demo idempotent |
| `Presentation/Console/Commands/SeedAccessControlCommand.php` | Adapter command seeder |

## Runtime dan Shared Context

| Path | Tanggung jawab |
| --- | --- |
| `bootstrap/providers.php` | Mendaftarkan provider module |
| `app/Http/Middleware/HandleInertiaRequests.php` | Shared user, role, permission, dan SuperSystem context |
| `config/access-control.php` | Konfigurasi password dummy seeder |
| `config/permission.php` | Mapping model dan tabel Spatie |
| `resources/js/components/app-sidebar.tsx` | Visibility menu sidebar |
| `resources/js/components/command-palette.tsx` | Visibility command palette |

## Frontend Module

| Path | Tanggung jawab |
| --- | --- |
| `pages/System/AccessControl/pages/Index.tsx` | Page role dan permission |
| `pages/System/AccessControl/layouts/system-dashboard-layout.tsx` | Shell layout module |
| `pages/System/AccessControl/components/RoleControlCard.tsx` | Search dan pemilihan role |
| `pages/System/AccessControl/components/PermissionModulePanel.tsx` | Permission group dan checkbox |
| `pages/System/AccessControl/components/AddRoleDialog.tsx` | Dialog tambah role |
| `pages/System/AccessControl/components/DeleteRoleDialog.tsx` | Dialog hapus role |
| `pages/System/AccessControl/components/AccessControlHeader.tsx` | Header page |
| `pages/System/AccessControl/components/SystemDashboardWidgets.tsx` | Widget dashboard System |
| `pages/System/AccessControl/types.ts` | Contract props TypeScript |
| `resources/js/components/ui/sonner.tsx` | Toast global dan tombol close |

## Boundary komunikasi

| Konsep | Status pada AccessControl |
| --- | --- |
| Public Module API | `AuthorizationCapability` dan `RoleAssignmentCapability` |
| Mutation | Application Action synchronous |
| Read | Application Query dan DTO |
| Domain/Application/Integration Event | Belum dipakai pada runtime module |
| Command Bus dan Queue/Job | Belum menjadi dependency module |
| Facade dan Shared Kernel domain | Tidak digunakan; `packages/StarterKit` bukan Shared Kernel |
| Pola keseluruhan | CQRS-lite |

## Test

| Path | Fokus |
| --- | --- |
| `tests/Feature/AccessControlArchitectureTest.php` | Boundary controller dan application |
| `tests/Feature/AccessControlAuthorizationCapabilityTest.php` | Capability publik dan action role |
| `tests/Feature/AccessControlPageTest.php` | Page, route, mutation, dan denial |
| `tests/Feature/AccessControlPolicyAndContextTest.php` | Policy dan shared Inertia context |
| `tests/Feature/AccessControlSchemaTest.php` | ULID dan schema Spatie |
| `tests/Feature/AccessControlSeederTest.php` | Seeder, command, dan idempotency |
| `tests/Unit/AccessControlPermissionIdentityTest.php` | Permission identity module |
