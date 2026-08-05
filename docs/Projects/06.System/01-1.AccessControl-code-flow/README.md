# Alur Code System/AccessControl

## Tujuan

Dokumen ini menjelaskan alur pembuatan dan penggunaan code pada module
`System/AccessControl`. Gunakan dokumen ini untuk memahami hubungan antara
route, controller, application layer, policy, database, dan frontend.

## Dokumen di Folder Ini

- [Alur backend](backend-flow.md): alur request, authorization, query, dan mutation.
- [Alur frontend](frontend-flow.md): alur page, layout, permission visibility, Ziggy, dan toast.
- [Peta file](file-map.md): daftar file penting dan tanggung jawabnya.

## Sumber Utama

- [Dokumentasi module AccessControl](../01.AccessControl/README.md)
- [Specification AccessControl](../01.AccessControl/specification.md)
- [Implementation plan AccessControl](../01.AccessControl/implementation-plan.md)
- [Task AccessControl](../01.AccessControl/tasks.md)
- [Aturan module dan authorization](../../../AGENTS.md)

## Ringkasan Alur

```text
User login
    -> HandleInertiaRequests membagikan authorization context
    -> sidebar dan command palette menyaring menu untuk UX
    -> route Ziggy membuka halaman module
    -> middleware dan policy memeriksa akses di backend
    -> controller memanggil Application Query/Action
    -> DTO dikirim ke Inertia page
    -> frontend mengirim mutation melalui Ziggy
    -> backend memvalidasi ulang authorization
    -> Action mengubah data dan frontend menampilkan toast
```

Pola eksekusi ini adalah CQRS-lite: Application Action menangani mutation dan
Application Query menangani read. `AuthorizationCapability` dan
`RoleAssignmentCapability` adalah public Module API. AccessControl belum
memakai Application Event, Integration Event, Command Bus, Queue/Job, Facade,
atau Shared Kernel domain. Domain Event juga belum menjadi bagian runtime
AccessControl; event impersonation berada pada UserManagement.

## Checklist Fondasi Enterprise

Code-flow AccessControl harus selalu dipetakan terhadap sembilan fondasi:

| Fondasi | Status saat ini |
| --- | --- |
| Contract/Interface | `implemented` melalui public capability dan contract internal |
| Domain Event | `planned` untuk fakta role/permission yang memiliki consumer |
| Application Event | `not applicable` pada flow saat ini |
| Integration Event | `planned` setelah AuditLog memiliki consumer nyata |
| Command | `planned`; Action tetap menjadi command-like use case saat ini |
| Query/Read Contract | `implemented` melalui Query dan DTO dashboard |
| Shared Kernel | `not applicable`; StarterKit bukan domain shared kernel |
| Facade/Module API | `implemented` melalui public capability |
| Queue/Job | `not applicable` untuk request synchronous saat ini |

Setiap perubahan status wajib disertai pembaruan code-flow, task, test, dan
ADR atau decision yang relevan.

## Batas Penting

- Frontend hanya mengatur visibility dan UX.
- Backend tetap menjadi security authority.
- `RoleController` hanya menjadi orchestration layer.
- Permission dimiliki module melalui `permissions.php`.
- Model Spatie berada pada Infrastructure, bukan pada Domain contract.
- Route frontend memakai Ziggy. Wayfinder dan Laravel Boost tidak digunakan.

## Status

Code-flow AccessControl selesai untuk scope role, permission, dashboard System,
frontend page, seeder demo, authorization, dan quality gate.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan alur code AccessControl |
| 1.1 | 2026-08-06 | Menambahkan batas Contract, Action/Query, event, dan eksekusi module |
