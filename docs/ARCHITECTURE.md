# Arsitektur

SakaSantri memakai **DDD-lite Modular Monolith dengan Hexagonal Architecture**.
Arsitektur ini mengikuti baseline lengkap di
[`SakaSantri_Architecture_Baseline_v0.1-r1-ID.md`](SakaSantri_Architecture_Baseline_v0.1-r1-ID.md).

## Baseline Produk

- Model produk: non-SaaS, single yayasan, multi-unit.
- Namespace module adalah area/kategori bisnis, bukan tenant atau pengganti
  boundary.
- Module adalah bounded capability dengan ownership, rule, data, dan lifecycle
  sendiri.
- Unit seperti MI, MTs, MA, tahfidz, asrama putra, dan asrama putri adalah data
  organisasi kecuali ada bounded capability berbeda.
- Shared Kernel harus kecil dan hanya memuat concern yang benar-benar universal.

## Hexagon per Module

```text
HTTP / Console / Queue / UI
            |
            v
Presentation (inbound adapter)
            |
            v
Application (use case, command, query, DTO, port)
            |
            v
Domain (aturan bisnis)
            ^
            |
Infrastructure (outbound adapter)
            |
Database / framework / package / layanan eksternal
```

`ServiceProvider.php` menjadi composition root module. File ini menghubungkan
contract dengan adapter, memuat route/migration, mendaftarkan policy/listener,
dan tidak berisi business logic.

## Tanggung Jawab Layer

| Layer | Tanggung jawab | Tidak boleh |
| --- | --- | --- |
| Domain | Entity, value object, domain service, event, invariant, rule bisnis murni | Bergantung pada framework, HTTP, UI, Eloquent, atau layer luar |
| Application | Use case, action, command, query, DTO, port, orchestration, transaction boundary | Mengimpor adapter Infrastructure konkret atau detail UI/HTTP |
| Infrastructure | Persistence, repository implementation, adapter framework/package, listener side effect | Menjadi pemilik business rule |
| Presentation | Controller, request, resource, route, middleware, console command, Inertia response | Melakukan business mutation atau persistence langsung |

Domain boleh belum ada pada capability yang benar-benar CRUD sederhana dan belum
memiliki rule murni. Jika Domain dibuat, ia harus bebas dari detail framework.

## Arah Dependency

```text
Presentation ------> Application ------> Domain
Infrastructure ----> Application ------> Domain
```

- Domain tidak mengimpor layer lain.
- Application tidak mengimpor Infrastructure.
- Presentation memanggil Application.
- Infrastructure mengimplementasikan port milik Application atau Domain.
- Binding port-adapter dilakukan oleh ServiceProvider module.
- Route mengarah ke Presentation.

## Komunikasi Lintas Module

Public boundary yang disarankan:

- `Application/Contracts`
- `Application/DTO`
- `Application/Events`
- domain event atau integration event yang memang memiliki consumer

Module tidak mengambil Eloquent model, repository, controller, policy, adapter,
atau Domain privat module lain untuk business mutation. Untuk invariant yang
harus sinkron, gunakan published contract/application service yang eksplisit.
Untuk side effect yang dapat dipisahkan, gunakan event/listener.

## CQRS dan Action

CQRS digunakan pragmatis. Command/action dipakai untuk mutation yang memiliki
orchestration, transaction boundary, authorization, event, atau audit. Query
boleh menggunakan read query yang efisien. CRUD sederhana tidak wajib diberi
ceremony CQRS penuh.

## Guardrail Arsitektur

- Jangan membuat folder kosong hanya untuk melengkapi diagram.
- Jangan membuat port, event, service, repository, adapter, atau integration
  tanpa consumer nyata.
- Jangan menggunakan Laravel Boost atau Wayfinder sebagai source of truth.
- Jangan memindahkan migration module ke `database/migrations` global untuk
  table milik module.
- Perubahan bounded context, stack utama, strategi identifier, atau struktur
  canonical memerlukan keputusan eksplisit dan ADR bila sulit dibalik.

## Gap Conformance

Catat penyimpangan source code terhadap baseline pada work item terkait. Jangan
menutup gap di luar scope pekerjaan tanpa persetujuan user.
