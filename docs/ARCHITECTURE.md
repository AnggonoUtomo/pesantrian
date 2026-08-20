# Arsitektur

## Gaya arsitektur

Starter13 memakai **DDD-lite Modular Monolith dengan Hexagonal Architecture**.
Setiap module adalah satu boundary bisnis dan satu hexagon. DDD-lite menjaga
model tetap pragmatis: abstraction dibuat karena ada behavior atau dependency
nyata, bukan untuk melengkapi pola secara teoritis.

- Framework reusable: `packages/StarterKit`.
- Module aplikasi: `app/Modules/{Domain}/{Module}`.
- Frontend module: `resources/js/pages/{Domain}/{Module}`.
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
Database / Laravel / package / layanan eksternal
```

`ServiceProvider.php` menjadi composition root. File ini boleh mengetahui port
Application dan adapter Infrastructure untuk melakukan binding. Pengecualian
tersebut tidak mengizinkan use case mengakses adapter konkret secara langsung.

## Tanggung jawab layer

| Layer          | Tanggung jawab                                                                                | Tidak boleh                                                                          |
| -------------- | --------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| Domain         | Entity, value object, domain service, domain event, dan invariant                             | Bergantung pada Application, Infrastructure, Presentation, controller, atau Eloquent |
| Application    | Action/use case, command, query, DTO, port, orchestration, dan application event              | Mengimpor implementasi dalam Infrastructure atau menulis detail HTTP/UI              |
| Infrastructure | Repository Eloquent, adapter package/framework, persistence, runtime, dan integrasi eksternal | Menjadi pemilik business rule atau public API lintas module                          |
| Presentation   | Controller, FormRequest, API resource, policy, middleware, dan console command                | Menulis query persistence atau business mutation langsung                            |

Domain boleh tidak ada pada capability CRUD sederhana yang belum memiliki
aturan bisnis murni. Jika Domain digunakan, class-nya harus bebas dari detail
Laravel dan persistence.

## Arah dependency

```text
Presentation ------> Application ------> Domain
Infrastructure ----> Application ------> Domain
```

Aturan wajib:

- Domain tidak mengimpor layer lain.
- Application boleh memakai Domain, tetapi tidak mengimpor Infrastructure.
- Presentation memanggil Application dan tidak mengakses persistence langsung.
- Infrastructure mengimplementasikan port dari Application atau Domain.
- Binding port ke adapter dilakukan di `ServiceProvider.php`.
- Route hanya mengarah ke Presentation.

## Port dan adapter

- Inbound port direpresentasikan oleh Action, Command, Query, atau public
  capability yang menjalankan use case.
- Outbound port didefinisikan di `Application/Contracts` ketika Application
  memerlukan persistence, runtime setting, publisher, session, atau layanan
  eksternal.
- Outbound adapter ditempatkan di `Infrastructure` dan mengimplementasikan port.
- `Domain/Contracts` hanya digunakan untuk abstraction yang benar-benar menjadi
  bahasa domain dan bebas framework.
- Port tidak dibuat tanpa consumer nyata. Satu implementasi sederhana boleh
  tetap langsung selama tidak melanggar arah dependency.

## Komunikasi lintas module

Public boundary lintas module hanya:

- `Application/Contracts`;
- `Application/DTO`;
- `Application/Events`.

Import model, repository, policy, controller, service Infrastructure, atau
Domain privat milik module lain dilarang. Dependency nyata harus dicantumkan
pada `module.json`.

Untuk runtime setting, consumer mendefinisikan port yang dibutuhkannya. Adapter
Infrastructure membaca nilai dari provider yang sesuai. Dengan pola ini,
consumer tidak bergantung pada implementasi konkret SystemSetting.

## Data, authorization, dan frontend

- Migration dan seeder dimiliki module pada `Database`.
- Model Eloquent dan repository berada di `Infrastructure/Persistence`.
- Primary key dan foreign key menggunakan ULID.
- Backend adalah security authority; state permission frontend hanya untuk UX.
- Frontend mengikuti boundary module, tetapi tidak menjadi bagian dari Domain.
- Wayfinder dan Laravel Boost dilarang; route frontend memakai Ziggy.

## Aturan kesederhanaan

- Jangan membuat folder kosong atau placeholder agar struktur terlihat lengkap.
- Jangan membuat Command, Event, Repository, Service, atau Adapter tanpa behavior
  dan owner nyata.
- Gunakan lokasi canonical ketika concern tersebut sudah diperlukan.
- Perubahan arah dependency atau lokasi canonical memerlukan ADR sebelum coding.

## Gap conformance yang diketahui

`AuditLog/Application/Actions/RecordAuditEntry.php` masih mengimpor
`Infrastructure/Context/AuditSecurityContext`. Ini melanggar arah dependency
Application ke Infrastructure dan tidak boleh dijadikan contoh. Perbaikannya
memerlukan work item terpisah karena mengubah contract dan wiring runtime.
