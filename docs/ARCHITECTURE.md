# Arsitektur

## Gaya arsitektur

`[Nama Project]` memakai **DDD-lite Modular Monolith dengan Hexagonal
Architecture**. Setiap module adalah satu boundary dan satu hexagon. Abstraction
dibuat karena ada behavior atau dependency nyata, bukan untuk melengkapi pola.

- Framework reusable: `[path atau tidak ada]`.
- Module aplikasi: `app/Modules/{Domain}/{Module}`.
- Frontend module: `[path frontend module]`.
- Struktur canonical: [FOLDER-STRUCTURE.md](FOLDER-STRUCTURE.md).

## Hexagon pada setiap module

```text
HTTP / Console / Queue / UI
            |
            v
Presentation (inbound adapter)
            |
            v
Application (use case dan port)
            |
            v
Domain (aturan bisnis)
            ^
            |
Infrastructure (outbound adapter)
            |
Database / framework / package / layanan eksternal
```

`ServiceProvider.php` atau composition root project menghubungkan port
Application dengan adapter Infrastructure. Use case tidak mengakses adapter
konkret secara langsung.

## Tanggung jawab layer

| Layer          | Tanggung jawab                                                        | Tidak boleh                                          |
| -------------- | --------------------------------------------------------------------- | ---------------------------------------------------- |
| Domain         | Entity, value object, domain service, event, dan invariant            | Bergantung pada layer luar atau framework            |
| Application    | Use case, command, query, DTO, port, dan orchestration                | Mengimpor adapter Infrastructure atau detail HTTP/UI |
| Infrastructure | Persistence dan adapter framework/package/layanan eksternal           | Menjadi pemilik business rule                        |
| Presentation   | Controller, request, resource, policy, middleware, dan command UI/CLI | Menulis persistence atau business mutation langsung  |

Domain boleh tidak ada pada capability sederhana yang belum memiliki business
rule murni. Jika digunakan, Domain harus bebas dari detail framework dan
persistence.

## Arah dependency

```text
Presentation ------> Application ------> Domain
Infrastructure ----> Application ------> Domain
```

- Domain tidak mengimpor layer lain.
- Application tidak mengimpor Infrastructure.
- Presentation memanggil Application.
- Infrastructure mengimplementasikan port milik Application atau Domain.
- Binding port-adapter dilakukan oleh composition root.
- Route mengarah ke Presentation.

## Port dan adapter

- Action, Command, atau Query menjadi inbound port/use case.
- Outbound port berada di `Application/Contracts` ketika Application memerlukan
  persistence, runtime setting, publisher, session, atau integrasi eksternal.
- Adapter berada di `Infrastructure` dan mengimplementasikan outbound port.
- `Domain/Contracts` hanya untuk abstraction yang menjadi bahasa domain.
- Port tidak dibuat tanpa consumer nyata.

## Komunikasi lintas module

Public boundary yang disarankan:

- `Application/Contracts`;
- `Application/DTO`;
- `Application/Events`.

Import model, repository, controller, policy, adapter, atau Domain privat module
lain dilarang. Dependency nyata dicatat pada manifest atau konfigurasi module.

## Aturan kesederhanaan

- Jangan membuat folder atau placeholder agar struktur terlihat lengkap.
- Jangan membuat port, event, repository, service, atau adapter tanpa kebutuhan.
- Gunakan lokasi canonical ketika concern benar-benar diperlukan.
- Perubahan arah dependency atau struktur canonical memerlukan ADR.

## Gap conformance

- [Catat penyimpangan source code terhadap arsitektur dan work item perbaikannya.]
