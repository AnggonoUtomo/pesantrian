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

- [Dokumentasi module AccessControl](../AccessControl/README.md)
- [Specification AccessControl](../AccessControl/specification.md)
- [Implementation plan AccessControl](../AccessControl/implementation-plan.md)
- [Task AccessControl](../AccessControl/tasks.md)
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
