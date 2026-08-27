# Arsitektur Baseline v0.1
## Laboratory Compliance & Evidence Platform
### SNI ISO/IEC 17025 + Future SNI ISO/IEC 17065

**Status:** Draft Baseline — Revised Module Structure
**Target Teknologi:** Laravel 13 + Inertia React Starter Kit
**Arsitektur:** DDD-lite Modular Monolith + Hexagonal Arsitektur
**Database Utama:** MySQL
**Ruang Lingkup Referensi Awal:** Laboratorium Pengujian Mainan Anak
**Ruang Lingkup Dinamis Tambahan:** Keramik, Elektrik, Lampu, dan ruang lingkup pengujian lain

---

# Riwayat Revisi

## v0.1-r5 — SystemSetting & Penghapusan Laravel Boost

Perubahan utama:

```text
SystemSetting                  -> ditambahkan sebagai module ke-15
Global/Dynamic Application Setting -> disiapkan sebagai capability dasar
Laravel Boost                 -> dihapus dari stack proyek
laravel/boost                  -> tidak boleh dipasang kembali
Boost-specific tooling/config -> tidak boleh menjadi dependency workflow Codex
```

---


## v0.1-r4 — Adapter & Integrasi Opsional

Perubahan:

```text
Infrastructure/Adapters/       -> optional/on-demand
Infrastructure/Integrations/   -> optional/on-demand
Module Generator default       -> tidak membuat folder kosong tersebut
Generator child commands       -> membuat folder saat diperlukan
Codex guardrail                -> jangan membuat abstraction/integration ceremony tanpa kebutuhan
```

---


## v0.1-r3 — Module Generator Ditambahkan

```text
Module Generator added
Custom module stubs added
CQRS generator support added
Generator validation and testing added
Phase 0 updated
```

---


## v0.1-r2 — Koreksi Struktur Namespace

Perubahan utama:

```text
app/Modules/<namespace>/...
```

dipertahankan sebagai:

```text
app/Modules/namespace/<Module>/...
```

Setiap module sekarang secara eksplisit memiliki:

```text
Database/
Routes/
```

Database artifacts dan route definitions menjadi milik module, bukan concern global aplikasi.

---


---

# 1. Tujuan Dokumen

Dokumen ini menjadi baseline arsitektur teknis untuk pengembangan aplikasi laboratorium berbasis SNI ISO/IEC 17025 yang:

1. mendukung banyak ruang lingkup pengujian secara dinamis;
2. tidak mengikat sistem hanya pada pengujian mainan anak;
3. mengelola metode, parameter, alat, kalibrasi, kompetensi, sampel, hasil uji, laporan, dan bukti objektif;
4. menggunakan DDD-lite Modular Monolith;
5. menerapkan prinsip Hexagonal Arsitektur;
6. memiliki Shared Kernel yang terkontrol;
7. mendukung CQRS secara pragmatis;
8. menggunakan domain events dan event listeners untuk komunikasi antarmodul;
9. dapat berkembang menjadi Evidence Readiness Platform;
10. dapat berkembang lebih lanjut menjadi Calibration Intelligence, CAPA Intelligence, Risk Intelligence, dan Compliance Intelligence;
11. dapat menjadi fondasi integrasi proses SNI ISO/IEC 17065 di masa depan.

Aplikasi ini **bukan aplikasi checklist ISO**.

Aplikasi dirancang sebagai:

> **Laboratory Operations + Compliance Evidence Platform**

yang menghubungkan aktivitas operasional laboratorium dengan persyaratan, kompetensi, alat, bukti objektif, dan readiness.

---

# 2. Prinsip Produk

## 2.1 Multi Testing Scope

Aplikasi tidak di-hard-code sebagai:

```text
Toy Testing Application
```

tetapi sebagai:

```text
Laboratory Platform
    └── Testing Scope
            ├── Mainan Anak
            ├── Keramik
            ├── Elektrik
            ├── Lampu
            └── Scope lainnya
```

Ruang lingkup baru harus dapat ditambahkan tanpa membuat modul Laravel baru hanya karena produk atau jenis pengujian bertambah.

## 2.2 Scope adalah Data, bukan Source Code

Yang bersifat configurable:

- testing scope;
- product category;
- standard/regulation;
- test method;
- method revision;
- test parameter;
- acceptance criteria;
- equipment requirement;
- environmental requirement;
- competence requirement;
- authorization requirement;
- evidence requirement;
- result schema/template.

Contoh:

```text
Scope: Mainan Anak
Method: Tension Test
Required Equipment:
- Force Gauge
- Test Fixture

Scope: Keramik
Method: Water Absorption
Required Equipment:
- Balance
- Oven
- Water Bath
```

Engine aplikasi tetap sama.

---

# 3. Baseline Teknologi

## Backend

```text
PHP
Laravel 13
Laravel Fortify
MySQL
Laravel Queue
Laravel Events
Laravel Notifikasi
```

## Frontend

```text
React
TypeScript
Inertia
Tailwind CSS
shadcn/ui
Framer Motion
Ziggy
Vite
```

## Packages

```text
spatie/laravel-permission
spatie/laravel-medialibrary
tightenco/ziggy
```

## Explicit Arsitektur Decisions

```text
Laravel Wayfinder      : DIHAPUS
Routing frontend       : Ziggy konvensional
ziggy:generate         : TIDAK digunakan sebagai workflow utama
Auth                   : Laravel Starter Kit + Fortify
RBAC                   : Spatie Permission
Media / Attachment     : Spatie Media Library
Frontend Components    : shadcn/ui
Animation              : Framer Motion
```

---


## Kebijakan Laravel Boost

Laravel Boost **bukan bagian dari stack proyek**.

```text
Package : laravel/boost
Status  : REMOVE / FORBIDDEN
```

Jika package ditemukan pada project:

```bash
composer remove laravel/boost
```

Setelah penghapusan:

```text
composer.json tidak lagi memuat laravel/boost
composer.lock diperbarui oleh Composer
artefak/config/instruksi khusus Boost dibersihkan jika memang ada
workflow Codex tidak bergantung pada Boost
```

Codex tidak boleh:

```text
menginstal ulang laravel/boost
menjalankan boost:install
mengandalkan Laravel Boost MCP
mengandalkan Boost-generated guidance sebagai source of truth proyek
```

Gunakan:

```text
native Laravel / Artisan
Composer
repository-defined scripts
tests
AGENTS.md
docs/architecture/
docs/work/
installed Codex skills yang relevan
```

Penghapusan Laravel Boost merupakan keputusan tooling proyek dan tidak mengubah business architecture aplikasi.


# 4. High-Level Arsitektur

```text
┌─────────────────────────────────────────────────────────────┐
│                     INERTIA REACT UI                        │
│ React + TypeScript + shadcn/ui + Framer Motion + Ziggy     │
└─────────────────────────────┬───────────────────────────────┘
                              │
                     Presentation Layer
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                       APPLICATION                           │
│ Actions │ Commands │ Queries │ DTO │ Services │ Handlers   │
└─────────────────────────────┬───────────────────────────────┘
                              │
                         Domain Ports
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                          DOMAIN                             │
│ Entities │ Value Objects │ Contracts │ Events │ Services   │
│ Policies │ Rules                                               │
└─────────────────────────────┬───────────────────────────────┘
                              │
                         Adapters
                              │
┌─────────────────────────────▼───────────────────────────────┐
│                     INFRASTRUCTURE                          │
│ Eloquent │ Repositories │ Media │ Queue │ Event Listeners  │
└─────────────────────────────────────────────────────────────┘
```

Dependency rule:

```text
Presentation
      ↓
Application
      ↓
Domain

Infrastructure → implements Domain/Application contracts
```

Domain tidak boleh bergantung pada:

- Controller;
- Inertia;
- React;
- Eloquent Model;
- Spatie Media Library;
- Spatie Permission;
- HTTP Request;
- Laravel-specific UI concern.

---

# 5. Modular Monolith

Struktur root:

```text
app/
├── Modules/
│   └── Platform/
│       ├── AccessControl/
│       ├── Organization/
│       ├── SystemSetting/
│       ├── LaboratoryScope/
│       ├── Standards/
│       ├── PersonnelCompetence/
│       ├── Equipment/
│       ├── Calibration/
│       ├── SampleManagement/
│       ├── TestMethod/
│       ├── TestExecution/
│       ├── TestResult/
│       ├── TestReport/
│       ├── Evidence/
│       └── AuditTrail/
│
└── Shared/
    └── Kernel/
```

`namespace` tetap menjadi segment/root namespace modular aplikasi sesuai struktur proyek.

Contoh namespace PHP:

```text
App\Modules\namespace\AccessControl
App\Modules\namespace\Equipment
App\Modules\namespace\Calibration
App\Modules\namespace\TestExecution
```

Struktur ini mempertahankan `namespace` sebagai segment eksplisit pada path fisik module, sesuai convention proyek.

Nilai aktual `namespace` dapat ditetapkan sesuai bounded area/domain proyek, sementara pola folder tetap `app/Modules/namespace/<Module>`.

---

# 6. Struktur Standar Modul

Baseline resmi:

```text
app/Modules/namespace/AccessControl/
├── module.json
├── module.php
├── permissions.php
├── ServiceProvider.php
├── README.md
│
├── Application/
│   ├── Actions/
│   ├── DTO/
│   └── Services/
│
├── Domain/
│   ├── Contracts/
│   ├── Entities/
│   ├── Events/
│   ├── Services/
│   └── ValueObjects/
│
├── Infrastructure/
│   ├── Models/
│   ├── Repositories/
│   ├── Observer/
│   ├── Providers/
│   ├── Adapters/          # optional/on-demand
│   └── Integrations/      # optional/on-demand
│
├── Presentation/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   ├── Factories/
│   └── Providers/
│
└── Routes/
    ├── web.php
    ├── api.php
    ├── console.php
    └── channels.php
```

Catatan normalisasi penamaan:

```text
factorys     -> Factories
channel.php  -> channels.php
```

Penamaan tersebut mengikuti bentuk jamak dan konvensi Laravel agar konsisten dengan `database/factories` dan `routes/channels.php`.

Jika proyek secara eksplisit ingin mempertahankan nama literal:

```text
factorys/
channel.php
```

hal tersebut tetap memungkinkan, tetapi baseline arsitektur merekomendasikan:

```text
Factories/
channels.php
```

## 6.1 Application

Berisi orchestration use case aplikasi:

```text
Actions
DTO
Services
```

CQRS tidak mengharuskan folder `Commands/` dan `Queries/` selalu ada pada seluruh module.

Untuk module yang membutuhkannya, struktur dapat diperluas menjadi:

```text
Application/
├── Actions/
├── Commands/
│   └── Handlers/
├── Queries/
│   └── Handlers/
├── DTO/
└── Services/
```

Jadi struktur dasar tetap ringan, sementara CQRS digunakan secara pragmatis pada use case yang memang membutuhkannya.

## 6.2 Domain

Berisi business model murni:

```text
Contracts
Entities
Events
Services
ValueObjects
```

Folder tambahan seperti:

```text
Exceptions
Policies
Specifications
```

dapat ditambahkan per module bila diperlukan, tetapi tidak menjadi folder wajib.

## 6.3 Infrastructure

Berisi implementasi/adaptor terhadap framework dan teknologi:

```text
Models
Repositories
Observer
Providers
Adapters        # optional
Integrations    # optional
```

`Adapters/` dan `Integrations/` **bukan folder wajib**.

Gunakan `Adapters/` ketika module benar-benar mengimplementasikan port/contract ke teknologi tertentu.

Contoh:

```text
Domain/Contracts/CalibrationCertificateStorage.php
        ↓
Infrastructure/Adapters/SpatieCalibrationCertificateStorage.php
```

Gunakan `Integrations/` ketika module benar-benar terhubung ke sistem eksternal atau boundary/context lain yang membutuhkan adapter khusus.

Contoh:

```text
Infrastructure/Integrations/ExternalCalibrationProvider/
Infrastructure/Integrations/LaboratoryTesting/
```

Repository sendiri secara konseptual juga merupakan adapter. Karena proyek menggunakan DDD-lite, repository tetap boleh berada di `Infrastructure/Repositories/` dan tidak perlu dipindahkan hanya demi kemurnian struktur.

Jangan membuat:

```text
Adapters/
Integrations/
```

hanya agar struktur module terlihat lengkap.

Contoh isi Infrastructure:

```text
Eloquent model
repository implementation
Spatie adapter
external API client
integration mapper
infrastructure service provider
```

## 6.4 Presentation

Berisi interface HTTP/API:

```text
Controllers
Requests
Resources
```

Presentation tidak memiliki folder route internal karena routing dikelola sebagai concern module-level melalui root `Routes/`.

## 6.5 Database

Setiap module memiliki ownership terhadap artefak databasenya sendiri:

```text
Database/
├── Migrations/
├── Seeders/
├── Factories/
└── Providers/
```

Dengan demikian migration untuk `Equipment` berada di:

```text
app/Modules/namespace/Equipment/Database/Migrations/
```

bukan tercampur dengan seluruh migration aplikasi.

Seeder dan factory juga mengikuti ownership module.

## 6.6 Routes

Setiap module memiliki route sendiri:

```text
Routes/
├── web.php
├── api.php
├── console.php
└── channels.php
```

Tanggung jawab:

```text
web.php       -> Inertia/web routes
api.php       -> API routes jika diperlukan
console.php   -> module console route / scheduled command binding bila diperlukan
channels.php  -> broadcasting authorization channels
```

Tidak semua file harus berisi route.

File dapat tetap ada sebagai standar module agar struktur mudah dikenali.

---

# 7. Metadata Modul

## module.json

Digunakan untuk metadata deklaratif.

Contoh:

```json
{
    "name": "Equipment",
    "namespace": "App\\Modules\\Platform\\Equipment",
    "enabled": true,
    "version": "0.1.0",
    "description": "Equipment lifecycle and suitability management",
    "dependencies": [
        "Organization"
    ]
}
```

## module.php

Digunakan untuk konfigurasi runtime modul.

Contoh concern:

```text
route prefix
feature flag
navigation
configuration key
event registration
```

## permissions.php

Permission dimiliki oleh modul yang menggunakan capability tersebut.

Contoh:

```php
return [
    'equipment.view',
    'equipment.create',
    'equipment.update',
    'equipment.retire',
    'equipment.assign',
    'equipment.verify',
];
```

AccessControl mengorkestrasi role dan permission, tetapi domain pemilik permission tetap modul asalnya.

---

# 8. Usulan Bounded Context

## 8.1 AccessControl

Tanggung jawab:

- authentication integration;
- users;
- roles;
- permissions;
- access policies;
- account status.

Teknologi:

```text
Laravel Fortify
Spatie Permission
Laravel Gate / Policies
```

Tidak menangani kompetensi teknis laboratorium.

Contoh:

```text
Role:
Laboratory Manager

Permission:
test-report.approve
```

berbeda dengan:

```text
Technical Authorization:
Authorized to perform Tension Test
```

Authorization teknis adalah domain `PersonnelCompetence`.

---

## 8.2 Organization

Tanggung jawab:

- organisasi;
- laboratorium;
- unit;
- lokasi;
- organizational structure;
- person affiliation.

Contoh:

```text
Organization
└── Laboratory
    ├── Mechanical Lab
    ├── Electrical Lab
    └── Chemical Lab
```

---


## SystemSetting

Tanggung jawab:

```text
global application settings
dynamic application options
setting definitions
setting groups
setting values
module-owned setting registration
application preferences
```

Tujuan awal module ini adalah menyediakan fondasi pengaturan aplikasi yang dapat berkembang seiring implementasi module lain.

Model konseptual:

```text
SettingDefinition
├── key
├── group
├── label
├── description
├── data_type
├── default_value
├── validation_rules
├── visibility
├── editability
└── owning_module

SettingValue
├── setting_definition
├── value
└── updated_by
```

Prinsip penting:

```text
.env / config
= konfigurasi teknis/deployment

SystemSetting
= konfigurasi bisnis/aplikasi yang dapat diubah secara dinamis

Master Data
= data domain seperti Testing Scope, Equipment Category, Test Method, dan lainnya
```

Contoh yang cocok sebagai `SystemSetting`:

```text
application display name
timezone aplikasi
date format
sample number prefix
report number prefix
calibration warning days
authorization warning days
```

Contoh yang bukan `SystemSetting`:

```text
DB_HOST
APP_KEY
QUEUE_CONNECTION
Testing Scope
Test Method
Equipment
Personnel
```

Module lain boleh mendaftarkan setting yang dimilikinya, tetapi ownership business rule tetap berada di module asal.

`SystemSetting` tidak boleh berubah menjadi generic key/value dumping ground untuk seluruh master data.


## 8.3 LaboratoryScope

Tanggung jawab:

- testing scopes;
- product categories;
- scope activation;
- scope-method mapping;
- scope capability.

Contoh data:

```text
TOY
CERAMIC
ELECTRICAL
LAMP
```

Tidak menyimpan detail prosedur pengujian.

---

## 8.4 Standards

Tanggung jawab:

```text
Standards
Clauses
Requirements
Requirement revisions
Regulatory references
Evidence expectations
```

Struktur konseptual:

```text
Standard
    ↓
Clause
    ↓
Requirement
    ↓
Expected Evidence
```

Kelak modul ini menjadi salah satu fondasi Evidence Readiness.

---

## 8.5 PersonnelCompetence

Tanggung jawab:

- personnel profile;
- competence;
- training;
- qualification;
- authorization;
- authorization expiry;
- method-specific authorization.

Contoh:

```text
ARYA
├── Tension Test            AUTHORIZED
├── Drop Test               AUTHORIZED
└── Flammability Test       NOT AUTHORIZED
```

---

## 8.6 Equipment

Tanggung jawab:

- equipment master;
- equipment identification;
- category;
- manufacturer/model;
- serial number;
- location;
- measurement characteristics;
- operational status;
- lifecycle;
- suitability status.

Kategori konseptual:

```text
Measuring Equipment
Testing Equipment
Supporting Equipment
Reference Standard
```

Equipment tidak menentukan sendiri aturan kalibrasi.

---

## 8.7 Calibration

Tanggung jawab:

- calibration requirement;
- calibration schedule;
- calibration history;
- certificate;
- correction;
- uncertainty information;
- metrological traceability;
- verification;
- intermediate checks;
- performance checks;
- calibration status.

Relasi:

```text
Equipment
    ↓
Metrological Control
    ├── Calibration
    ├── Verification
    ├── Intermediate Check
    └── Performance Check
```

Equipment dapat:

```text
Calibration = VALID
Intermediate Check = FAILED
```

dan tetap memiliki:

```text
Suitability = NOT SUITABLE
```

---

# 9. Test Method Domain

## TestMethod

Tanggung jawab:

- method definition;
- method revision;
- method parameters;
- reference standard;
- environmental requirements;
- equipment requirements;
- competence requirements;
- result schema;
- evidence requirements.

Model konseptual:

```text
TestMethod
├── Scope
├── Revision
├── Parameters
├── AcceptanceCriteria
├── EquipmentRequirements
├── EnvironmentalRequirements
├── CompetenceRequirements
└── EvidenceRequirements
```

Contoh:

```text
Method:
Tension Test

Scope:
Mainan Anak

Equipment Requirement:
Force Gauge

Minimum Range:
200 N

Maximum Resolution:
1 N

Calibration Required:
YES
```

---

# 10. Sample Management

Tanggung jawab:

```text
sample registration
sample identity
customer reference
sample condition
sample photos
sample quantity
sample receipt
sample storage
sample custody
sample disposition
```

Sample harus memiliki immutable reference number setelah registrasi resmi.

Contoh:

```text
SMP-2026-000001
```

---

# 11. Test Execution

TestExecution adalah domain operasional utama.

Workflow:

```text
Test Request
      ↓
Sample
      ↓
Method Selection
      ↓
Method Revision Lock
      ↓
Personnel Assignment
      ↓
Equipment Assignment
      ↓
Pre-test Validation
      ↓
Test Execution
      ↓
Raw Data
      ↓
Calculation
      ↓
Result
```

Pre-test validation harus dapat memeriksa:

```text
Method active?
Method revision valid?
Personnel authorized?
Equipment suitable?
Calibration valid?
Intermediate check valid?
Environment acceptable?
Required evidence configuration available?
```

---

# 12. Test Result

Tanggung jawab:

- raw values;
- calculated values;
- unit;
- uncertainty when applicable;
- acceptance decision;
- pass/fail;
- observations;
- calculation metadata;
- result revision.

Raw data tidak boleh sekadar ditimpa.

Perubahan setelah penyimpanan resmi harus memiliki:

```text
revision
reason
actor
timestamp
audit record
```

---

# 13. Test Report

Tanggung jawab:

- report drafting;
- result aggregation;
- technical review;
- approval;
- report revision;
- finalization;
- report media/PDF;
- amendment.

Lifecycle contoh:

```text
DRAFT
   ↓
TECHNICAL_REVIEW
   ↓
APPROVAL
   ↓
FINAL
```

Final report tidak boleh terbit bila mandatory evidence belum memenuhi readiness rule yang ditetapkan.

---

# 14. Evidence Module

Evidence adalah salah satu domain strategis.

Evidence dapat berasal dari:

```text
Document
Calibration Certificate
Raw Test Record
Photo
Training Record
Authorization
Environmental Record
Review
Approval
External Certificate
Test Report
```

Model konseptual:

```text
Evidence
├── subject
├── type
├── source
├── owner
├── validity
├── media
├── requirement links
└── verification state
```

Evidence dapat direlasikan dengan banyak subject.

Contoh:

```text
Calibration Certificate
        │
        ├── Equipment FG-001
        ├── Tension Test Execution #883
        └── ISO Requirement X
```

---

# 15. Evidence Readiness

Release awal tidak membutuhkan AI.

Readiness dihitung dari rule deterministik.

Contoh:

```text
TEST-2026-0188

Sample Identification       ✓
Method Revision             ✓
Operator Authorization      ✓
Equipment Record            ✓
Calibration Certificate     ✓
Intermediate Check          ✓
Raw Data                    ✓
Calculation                 ✓
Photo Evidence              ✓
Technical Review            ✕
Approval                    ✕

Readiness:
9 / 11
81.8%
```

Status:

```text
NOT_READY
READY_FOR_REVIEW
READY_FOR_APPROVAL
READY_FOR_RELEASE
```

Intelligence layer baru ditambahkan setelah volume dan kualitas data memadai.

---

# 16. Audit Trail

AuditTrail adalah cross-cutting compliance capability.

Minimal menyimpan:

```text
actor
action
subject_type
subject_id
old_values
new_values
reason
occurred_at
request_id
ip/device metadata when appropriate
```

Audit trail berbeda dari application log.

```text
Application Log
= debugging / technical operations

Audit Trail
= traceability / compliance evidence
```

---

# 17. Shared Kernel

Lokasi:

```text
app/Shared/Kernel/
├── Application/
├── Domain/
│   ├── Contracts/
│   ├── Events/
│   ├── Exceptions/
│   ├── ValueObjects/
│   └── Support/
└── Infrastructure/
```

Shared Kernel harus sangat kecil.

Boleh berisi:

```text
AggregateRoot
DomainEvent
DomainException
EntityId / ULID abstraction
Money
DateRange
Measurement
UnitOfMeasure
Pagination contracts
Clock contract
CurrentUser contract
Transactional contract
```

Tidak boleh menjadi:

```text
Helpers/
Utils/
Common/
Misc/
EverythingReusable/
```

Rule:

> Jika konsep memiliki makna domain yang kuat hanya pada satu module, tetap simpan di module tersebut.

---

# 18. CQRS Strategy

CQRS diterapkan secara pragmatis.

Tidak menggunakan dua database terpisah pada Release 1.

```text
Write Model
     │
     └── MySQL

Read Model
     │
     └── MySQL
```

CQRS di level Application Arsitektur:

```text
Application/
├── Commands/
│   ├── RegisterEquipment.php
│   └── Handlers/
│       └── RegisterEquipmentHandler.php
│
└── Queries/
    ├── GetEquipmentDetail.php
    └── Handlers/
        └── GetEquipmentDetailHandler.php
```

## Command

Mengubah state.

```text
RegisterEquipment
AssignEquipment
RecordCalibration
AuthorizePersonnel
RegisterSample
StartTest
RecordTestResult
ApproveTestReport
```

## Query

Tidak mengubah state.

```text
GetEquipmentDetail
FindEligibleEquipment
GetCalibrationDueList
GetTestReadiness
GetAuthorizedPersonnel
GetSampleTimeline
```

Rule:

```text
Command -> may write
Query   -> read only
```

Queries diperbolehkan menggunakan optimized Eloquent/read queries tanpa memaksa seluruh read path melewati aggregate domain.

---

# 19. Application Actions

`Actions/` digunakan untuk use case orchestration yang:

- melibatkan beberapa command/domain service;
- membutuhkan transaction boundary;
- bukan sekadar single CRUD operation.

Contoh:

```text
FinalizeTestReportAction
```

dapat melakukan:

```text
Check readiness
Validate technical review
Lock final result
Create report revision
Attach generated document
Dispatch TestReportFinalized
```

---

# 20. Domain Events

Domain event menyatakan fakta bisnis yang sudah terjadi.

Contoh:

```text
EquipmentRegistered
CalibrationRecorded
CalibrationExpired
IntermediateCheckFailed
PersonnelAuthorized
AuthorizationExpired
SampleRegistered
TestStarted
TestCompleted
TestResultApproved
TestReportFinalized
EvidenceAttached
```

Naming rule:

```text
Past tense
```

Bukan:

```text
CalibrateEquipment
```

tetapi:

```text
EquipmentCalibrated
```

---

# 21. Event Listeners

Listener digunakan untuk side effects.

Contoh:

```text
CalibrationRecorded
        │
        ├── RecalculateEquipmentSuitability
        ├── UpdateCalibrationSchedule
        ├── RefreshEvidenceReadiness
        └── WriteAuditTrail
```

Cross-module rule:

```text
Module A
   │
   └── emits event
           │
           ▼
       Module B listener
```

Hindari:

```text
EquipmentService
    -> new CalibrationService
    -> new EvidenceService
    -> new AuditService
```

yang menciptakan tight coupling.

---

# 22. Event Sinkron vs Asinkron

Default Release 1:

```text
Domain invariant       : synchronous
Critical status update : synchronous
Audit record           : synchronous
Email notification     : queue
Heavy document process : queue
Analytics projection   : queue
```

Jangan menjadikan semua listener asynchronous.

Status yang memengaruhi validitas pengujian harus konsisten pada transaction yang sesuai.

---

# 23. Pola Repository

Repository hanya digunakan ketika memberikan boundary domain yang nyata.

Contoh:

```php
interface EquipmentRepository
{
    public function save(Equipment $equipment): void;

    public function get(EquipmentId $id): Equipment;
}
```

Infrastructure:

```text
Infrastructure/
└── Repositories/
    └── EloquentEquipmentRepository.php
```

Tidak perlu membuat generic repository seperti:

```text
BaseRepository<T>
GenericRepository
AbstractCrudRepository
```

hanya demi abstraksi.

---

# 24. Eloquent Model Rule

Eloquent Model berada di:

```text
Infrastructure/Models
```

bukan:

```text
Domain/Entities
```

Domain entity dan Eloquent model boleh berbeda.

Namun karena pendekatan DDD-lite, tidak semua modul harus memaksakan rich aggregate bila domainnya sederhana.

Gunakan tingkat pemisahan sesuai kompleksitas.

---

# 25. Batas Transaksi

Transaction dikelola di Application Layer.

Contoh:

```text
RecordCalibrationAction
    ↓
begin transaction
    ↓
persist calibration
update equipment state
record evidence
record audit trail
    ↓
commit
    ↓
dispatch post-commit events
```

Domain object tidak mengetahui database transaction.

---

# 26. Authorization Arsitektur

Ada dua jenis authorization.

## System Authorization

Dikelola:

```text
AccessControl
Spatie Permission
Laravel Gate/Policy
```

Contoh:

```text
equipment.update
calibration.record
test-report.approve
```

## Technical Authorization

Dikelola:

```text
PersonnelCompetence
```

Contoh:

```text
User may access Test Execution page
```

belum berarti:

```text
User technically authorized to perform Tension Test
```

Keduanya harus lolos.

---

# 27. Media & File Arsitektur

Spatie Media Library digunakan sebagai file adapter.

Use case:

```text
Calibration Certificate
Sample Photo
Raw Data Attachment
Training Certificate
Test Evidence
Final Test Report
```

Domain tidak bergantung langsung pada:

```php
Spatie\MediaLibrary\HasMedia
```

untuk business rule kritis.

Domain menggunakan konsep:

```text
EvidenceAttachment
DocumentReference
MediaReference
```

Infrastructure mengadaptasikannya ke Media Library.

---

# 28. Frontend Arsitektur

Baseline:

```text
resources/js/
├── app.tsx
├── components/
│   ├── ui/
│   └── shared/
├── layouts/
├── hooks/
├── lib/
├── types/
└── modules/
    ├── access-control/
    ├── organization/
    ├── laboratory-scope/
    ├── equipment/
    ├── calibration/
    ├── personnel/
    ├── samples/
    ├── test-methods/
    ├── test-execution/
    ├── test-results/
    ├── test-reports/
    └── evidence/
```

Per frontend module:

```text
equipment/
├── pages/
├── components/
├── hooks/
├── types/
└── schemas/
```

shadcn/ui berada pada:

```text
components/ui
```

Business-specific reusable component tetap berada di module terkait atau `components/shared` bila benar-benar lintas domain.

---

# 29. Ziggy Routing

Wayfinder tidak digunakan.

Frontend menggunakan named Laravel routes melalui Ziggy.

Contoh konseptual:

```ts
router.visit(route('equipment.show', equipment.id))
```

Bukan generated Wayfinder action API.

Route names harus stabil.

Convention:

```text
equipment.index
equipment.show
equipment.create
equipment.store
equipment.edit
equipment.update

calibrations.index
calibrations.store

tests.execution.show
tests.execution.start
```

Hindari route name berdasarkan implementasi controller.

---

# 30. Routing Modul

Setiap module memiliki root folder routing sendiri.

Contoh:

```text
app/Modules/namespace/Equipment/
└── Routes/
    ├── web.php
    ├── api.php
    ├── console.php
    └── channels.php
```

`ServiceProvider` module bertanggung jawab memuat file route yang relevan.

Dengan pendekatan ini:

```text
routes/web.php
routes/api.php
```

pada root aplikasi tidak menjadi tempat seluruh route domain dikumpulkan.

Root routes hanya digunakan untuk concern global yang benar-benar bukan milik module tertentu bila diperlukan.

Untuk frontend, route tetap dipanggil melalui Ziggy berdasarkan named route Laravel.

---

# 31. Service Provider

Setiap module memiliki:

```text
ServiceProvider.php
```

Tanggung jawab:

```text
bind contracts
register repositories
register event listeners
load module routes
load module migrations
register module seed/factory support when required
merge module configuration
register policies
register infrastructure providers
```

Contoh sumber yang dimuat:

```text
Database/Migrations
Routes/web.php
Routes/api.php
Routes/console.php
Routes/channels.php
permissions.php
module.php
```

`ServiceProvider` tidak mengandung business logic.

Provider khusus infrastruktur dapat ditempatkan pada:

```text
Infrastructure/Providers/
```

sedangkan provider khusus database dapat ditempatkan pada:

```text
Database/Providers/
```

`ServiceProvider.php` pada root module bertindak sebagai composition root module.

---

# 32. Model Ruang Lingkup Dinamis

Core relationship:

```text
TestingScope
    ↓
TestMethod
    ↓
MethodRevision
    ├── Parameters
    ├── EquipmentRequirements
    ├── CompetenceRequirements
    ├── EnvironmentRequirements
    ├── EvidenceRequirements
    └── AcceptanceCriteria
```

Dengan struktur ini penambahan:

```text
Textile
Food Contact Material
Furniture
Cable
Battery
```

tidak membutuhkan perubahan engine inti selama proses pengujiannya dapat direpresentasikan melalui model metode yang ada.

---

# 33. Engine Kelayakan Alat

Saat sebuah pengujian akan dilakukan:

```text
Method Revision
       ↓
Equipment Requirement
       ↓
Candidate Equipment
       ↓
Eligibility Check
```

Rule contoh:

```text
equipment active
equipment category compatible
range sufficient
resolution sufficient
calibration valid
verification valid
intermediate check valid
not under maintenance
not quarantined
location allowed
```

Hasil:

```text
FG-001  ELIGIBLE
FG-002  ELIGIBLE
FG-003  NOT_ELIGIBLE
         reason: calibration expired
```

---

# 34. Engine Kelayakan Personel

```text
Method
   ↓
Competence Requirement
   ↓
Personnel Authorization
```

Check:

```text
authorization active
authorization not expired
required competence fulfilled
required training valid
scope allowed
method revision covered
```

---

# 35. Engine Evidence Readiness

Readiness Rule:

```text
Subject
   ↓
Expected Evidence
   ↓
Actual Evidence
   ↓
Validation
   ↓
Readiness State
```

Possible subjects:

```text
Equipment
Calibration
Personnel
Sample
Test Execution
Test Result
Test Report
Future Certification Case
```

Ini menjadi fondasi `Evidence Readiness Platform`.

---

# 36. Evolusi Audit Readiness

Tahap awal:

```text
missing evidence
expired evidence
invalid calibration
authorization expiry
overdue review
```

Tahap lanjut:

```text
Requirement
   ↕
Process
   ↕
Record
   ↕
Evidence
   ↕
Responsible Person
```

Sistem kemudian dapat memberikan:

```text
Requirement Coverage
Evidence Completeness
Evidence Validity
Audit Readiness Score
```

---

# 37. Calibration Intelligence Masa Depan

Data dasar:

```text
Equipment
Calibration History
Error
Correction
Uncertainty
Intermediate Checks
Maintenance
Failure History
Usage
Environment
```

Future capability:

```text
drift analysis
calibration interval evaluation
recurring failure detection
risk-based calibration recommendation
equipment reliability trend
```

Calibration Intelligence dibangun dari operational data, bukan menjadi modul AI terpisah sejak awal.

---

# 38. Integrasi SNI ISO/IEC 17065 di Masa Depan

Arsitektur target:

```text
Certification Case
        ↓
Product
        ↓
Required Evaluation
        ↓
Testing Requirement
        ↓
17025 Test Request
        ↓
Sample
        ↓
Test Execution
        ↓
Test Report
        ↓
Certification Review
        ↓
Certification Decision
```

Shared capabilities:

```text
Organization
People
Access Control
Standards
Evidence
Documents
Audit Trail
Notifikasi
Workflow
```

Domain khusus 17065 tetap menjadi module terpisah.

---

# 39. Aturan Dependensi Modul

Contoh dependency direction:

```text
AccessControl
Organization

LaboratoryScope
    → Standards

PersonnelCompetence
    → Organization
    → LaboratoryScope
    → TestMethod contracts where necessary

Equipment
    → Organization

Calibration
    → Equipment

TestMethod
    → LaboratoryScope
    → Standards

SampleManagement
    → Organization

TestExecution
    → SampleManagement
    → TestMethod
    → Equipment contracts
    → PersonnelCompetence contracts

TestResult
    → TestExecution

TestReport
    → TestResult
    → Evidence readiness contract

Evidence
    → generic subject references

AuditTrail
    → listens to events
```

Circular module dependencies dilarang.

Jika A membutuhkan B dan B membutuhkan A:

1. evaluasi ulang boundary;
2. gunakan domain event;
3. ekstrak contract kecil;
4. pindahkan konsep yang benar-benar shared ke Shared Kernel hanya bila memang universal.

---

# 40. Kontrak Antar-Modul

Module tidak mengambil Eloquent model module lain lalu mengubahnya secara langsung.

Hindari:

```php
use App\Modules\Lab\Equipment\Infrastructure\Models\EquipmentModel;
```

di domain lain untuk business operation.

Gunakan:

```text
Application Contract
Domain Contract
Query
Command
Domain Event
```

sesuai kebutuhan.

---

# 41. Strategi Database

Release awal:

```text
MySQL
single database
module-owned tables
module-owned migrations
module-owned seeders
module-owned factories
foreign keys where appropriate
transactional consistency
```

Tidak menggunakan database per module.

Ownership fisik artefak database mengikuti module:

```text
app/Modules/namespace/<Module>/Database/
```

Naming contoh:

```text
lab_scopes
test_methods
test_method_revisions
equipment
calibrations
samples
test_executions
test_results
evidence_records
audit_entries
```

Prefix global per module tidak wajib bila nama table sudah jelas dan tidak ambigu.

---

# 42. Strategi Identifier

Rekomendasi:

```text
Internal PK:
BIGINT

External/Public Identifier:
ULID
```

Contoh:

```text
id            = 2388
public_id     = 01JZ...
equipment_no  = FG-001
```

Business identifier tidak dijadikan primary key.

---

# 43. Soft Delete

Tidak semua table otomatis menggunakan soft delete.

Gunakan berdasarkan makna domain.

Contoh:

```text
Equipment          -> retire/status lebih baik daripada delete
Calibration        -> immutable history
Test Result        -> revision/history
Audit Entry        -> never delete through normal UI
Temporary Draft    -> delete mungkin diperbolehkan
```

---

# 44. Pemodelan Status

Hindari boolean explosion:

```text
is_active
is_valid
is_calibrated
is_verified
is_failed
is_locked
is_approved
```

Gunakan explicit state/value objects bila lifecycle kompleks.

Contoh:

```text
EquipmentStatus:
ACTIVE
OUT_OF_SERVICE
UNDER_MAINTENANCE
QUARANTINED
RETIRED
```

dan:

```text
SuitabilityStatus:
SUITABLE
CONDITIONALLY_SUITABLE
NOT_SUITABLE
```

---

# 45. Penanganan Error

Domain error:

```text
EquipmentNotSuitable
CalibrationExpired
PersonnelNotAuthorized
MethodRevisionInactive
EvidenceIncomplete
TestAlreadyFinalized
```

Presentation layer menerjemahkan error menjadi:

```text
validation message
HTTP response
Inertia error
```

Business exception tidak berupa string random dari controller.

---

# 46. Observer

Eloquent Observer hanya untuk concern persistence-level ringan.

Tidak digunakan untuk business workflow utama.

Hindari:

```text
EquipmentModel::updated
    -> automatically create calibration
    -> notify manager
    -> update evidence
    -> modify test
```

Workflow bisnis eksplisit melalui Action / Command Handler / Domain Event.

---

# 47. Notifikasi

Notification merupakan side effect.

Contoh:

```text
Calibration due
Calibration overdue
Authorization expiring
Equipment unavailable
Technical review requested
Report approval requested
Evidence expired
```

Channel awal:

```text
database
email
```

Channel lain dapat ditambahkan kemudian.

---

# 48. Queue

Queue digunakan untuk:

```text
emails
large exports
document generation
media conversion
read projections
analytics
non-critical notifications
```

Tidak digunakan untuk rule yang menentukan validitas transaksi utama secara immediate.

---

# 49. Baseline Keamanan

Minimal:

```text
Fortify authentication
2FA where enabled
role + permission
technical authorization
CSRF
request validation
rate limiting
secure file access
audit trail
principle of least privilege
```

Media evidence tidak diasumsikan public URL.

File yang merupakan bukti objektif harus memiliki authorization sebelum diakses.

---

# 50. Strategi Pengujian

## Unit Tests

```text
Domain rules
Value objects
Specifications
Eligibility rules
Readiness rules
```

## Feature Tests

```text
Commands
Actions
Authorization
Controllers
Inertia responses
Database effects
Events
```

## Integration Tests

```text
Spatie Permission
Media Library
Queue
Mail
Storage
Cross-module event listeners
```

Folder dapat mengikuti module.

```text
tests/
└── Modules/
    ├── Equipment/
    ├── Calibration/
    └── TestExecution/
```

---

# 51. Ruang Lingkup Release 1

Prioritas implementasi:

```text
01 Access Control
02 Organization
03 System Setting
04 Laboratory Scope
05 Standards / References
06 Personnel & Competence
07 Equipment
08 Calibration / Verification
09 Test Method
10 Sample Management
11 Test Execution
12 Test Result
13 Test Report
14 Evidence
15 Audit Trail
```

Tidak perlu langsung mengimplementasikan:

```text
CAPA Intelligence
Risk Intelligence
AI assistant
Predictive calibration
Complex management review
Full 17065 certification workflow
```

Tetapi arsitektur tidak boleh menghalangi penambahannya.

---

# 52. Urutan Implementasi yang Direkomendasikan

## Phase 0 — Foundation

```text
Laravel 13 starter kit
Fortify
Inertia React
TypeScript
Tailwind
shadcn/ui
Framer Motion
Ziggy
Spatie Permission
Spatie Media Library
remove Laravel Boost
module loader
Module Generator
custom module stubs
Shared Kernel
base event architecture
CQRS conventions
testing conventions
```

## Phase 1 — Organization & Governance

```text
AccessControl
Organization
SystemSetting
LaboratoryScope
Standards
PersonnelCompetence
```

## Phase 2 — Metrological Core

```text
Equipment
Calibration
Verification
Intermediate Check
Equipment Eligibility
```

## Phase 3 — Testing Core

```text
TestMethod
SampleManagement
TestExecution
TestResult
```

## Phase 4 — Evidence & Reporting

```text
TestReport
Evidence
AuditTrail
Readiness
```

## Phase 5 — Additional Scope Validation

Masukkan minimal satu scope kedua, disarankan:

```text
Keramik
```

Tujuan:

> membuktikan bahwa architecture benar-benar multi-scope dan bukan aplikasi mainan anak yang sekadar diberi nama generik.

## Phase 6 — Expansion

```text
Elektrik
Lampu
scope lainnya
```

## Phase 7 — Intelligence

```text
Calibration Intelligence
Evidence Intelligence
Risk
CAPA
Compliance Intelligence
```

---


# 52A. Tooling Arsitektur — Module Generator

Module Generator menjadi bagian resmi dari **Phase 0 — Foundation**.

Tujuannya adalah memastikan setiap module baru mengikuti struktur, namespace, metadata, routing, database ownership, dan convention arsitektur yang sama tanpa pembuatan manual berulang.

Command utama:

```bash
php artisan make:module Equipment
```

Output minimum:

```text
app/Modules/namespace/Equipment/
├── module.json
├── module.php
├── permissions.php
├── ServiceProvider.php
├── README.md
│
├── Application/
│   ├── Actions/
│   ├── DTO/
│   └── Services/
│
├── Domain/
│   ├── Contracts/
│   ├── Entities/
│   ├── Events/
│   ├── Services/
│   └── ValueObjects/
│
├── Infrastructure/
│   ├── Models/
│   ├── Repositories/
│   ├── Observer/
│   └── Providers/
│
├── Presentation/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Database/
│   ├── Migrations/
│   ├── Seeders/
│   ├── Factories/
│   └── Providers/
│
└── Routes/
    ├── web.php
    ├── api.php
    ├── console.php
    └── channels.php
```

## 52A.1 Namespace Convention

Generator wajib menghasilkan namespace sesuai pola:

```text
App\Modules\namespace\<Module>
```

Contoh:

```text
App\Modules\namespace\Equipment
App\Modules\namespace\Calibration
App\Modules\namespace\TestExecution
```

Namespace tidak boleh mengikuti namespace default generator Laravel bila hasilnya keluar dari boundary module.

---

## 52A.2 Generated Metadata

Generator wajib membuat `module.json`.

Contoh:

```json
{
    "name": "Equipment",
    "namespace": "App\\Modules\\namespace\\Equipment",
    "enabled": true,
    "version": "0.1.0",
    "description": "",
    "dependencies": []
}
```

Generator juga membuat:

```text
module.php
permissions.php
README.md
ServiceProvider.php
```

dengan stub awal yang valid.

---

## 52A.3 Module Registration

Generator harus terintegrasi dengan mekanisme module discovery / module loader.

Target behavior:

```text
create module
      ↓
write metadata
      ↓
create provider
      ↓
module becomes discoverable
      ↓
application boot loads enabled module
```

Sebisa mungkin tidak membutuhkan edit manual pada satu daftar provider global setiap kali module baru dibuat.

Jika Laravel bootstrap tetap membutuhkan registration tertentu, mekanismenya harus dilakukan oleh module loader atau satu registry terpusat, bukan penyebaran registration manual di banyak file.

---

## 52A.4 Module-Specific Generator Commands

Selain `make:module`, architectural tooling dapat menyediakan command turunan.

### Application Layer

```bash
php artisan module:make-action Equipment RegisterEquipment
php artisan module:make-dto Equipment EquipmentData
php artisan module:make-service Equipment EquipmentEligibilityService
```

### Domain Layer

```bash
php artisan module:make-contract Equipment EquipmentRepository
php artisan module:make-entity Equipment Equipment
php artisan module:make-event Equipment EquipmentRegistered
php artisan module:make-service Equipment EquipmentSuitabilityService
php artisan module:make-value-object Equipment EquipmentCode
```

### Infrastructure Layer

```bash
php artisan module:make-model Equipment Equipment
php artisan module:make-repository Equipment EloquentEquipmentRepository
php artisan module:make-observer Equipment EquipmentObserver
php artisan module:make-provider Equipment EquipmentInfrastructureProvider
php artisan module:make-adapter Calibration SpatieCalibrationCertificateStorage
php artisan module:make-integration Calibration ExternalCalibrationProvider
```

`module:make-adapter` dan `module:make-integration` membuat folder terkait **saat dibutuhkan**.

Default `make:module` tidak membuat `Adapters/` atau `Integrations/` kosong.

### Presentation Layer

```bash
php artisan module:make-controller Equipment EquipmentController
php artisan module:make-request Equipment StoreEquipmentRequest
php artisan module:make-resource Equipment EquipmentResource
```

### Database Layer

```bash
php artisan module:make-migration Equipment create_equipment_table
php artisan module:make-seeder Equipment EquipmentSeeder
php artisan module:make-factory Equipment EquipmentFactory
```

---

## 52A.5 CQRS Generator

Karena CQRS digunakan secara pragmatis, generator menyediakan command CQRS tetapi tidak memaksakan folder tersebut pada semua module.

Contoh:

```bash
php artisan module:make-command Equipment RegisterEquipment
php artisan module:make-query Equipment GetEquipmentDetail
```

Output:

```text
Application/
├── Commands/
│   ├── RegisterEquipment.php
│   └── Handlers/
│       └── RegisterEquipmentHandler.php
│
└── Queries/
    ├── GetEquipmentDetail.php
    └── Handlers/
        └── GetEquipmentDetailHandler.php
```

Rule:

> Folder CQRS dibuat saat diperlukan, bukan otomatis memenuhi semua module dengan boilerplate kosong.

---

## 52A.6 Route Generator Integration

Generator Presentation dapat memberikan opsi route registration.

Contoh:

```bash
php artisan module:make-controller Equipment EquipmentController --route
```

yang dapat menambahkan named route pada:

```text
app/Modules/namespace/Equipment/Routes/web.php
```

Tetapi perubahan route harus:

```text
explicit
predictable
idempotent
```

Generator tidak boleh menulis route duplikat.

---

## 52A.7 Permission Generator Integration

Generator dapat mendukung pembuatan permission milik module.

Contoh:

```bash
php artisan module:make-permission Equipment equipment.view
php artisan module:make-permission Equipment equipment.create
```

atau:

```bash
php artisan module:sync-permissions Equipment
```

Source of truth tetap:

```text
app/Modules/namespace/Equipment/permissions.php
```

AccessControl mengonsumsi permission tersebut, tetapi ownership permission tetap berada pada module pemilik capability.

---

## 52A.8 Event and Listener Generator

Domain event:

```bash
php artisan module:make-event Calibration CalibrationRecorded
```

Cross-module listener:

```bash
php artisan module:make-listener Equipment RecalculateEquipmentSuitability \
    --event=Calibration\\CalibrationRecorded
```

Generator harus memisahkan:

```text
Domain event
Infrastructure/Application listener
```

sesuai ownership dan dependency direction.

---

## 52A.9 Stub Strategy

Semua generated artifact menggunakan custom stubs.

Lokasi yang direkomendasikan:

```text
stubs/
└── modules/
    ├── module/
    ├── application/
    ├── domain/
    ├── infrastructure/
    ├── presentation/
    └── database/
```

Tujuan custom stubs:

```text
consistent namespace
consistent imports
consistent constructor style
consistent strict typing
consistent naming
consistent architecture
```

Generated code tidak boleh bergantung pada modifikasi manual berulang setelah dibuat.

---

## 52A.10 Generator Validation

Generator wajib memvalidasi:

```text
module name valid
module does not already exist
target module exists for child generator
class name valid
duplicate artifact prevention
namespace consistency
directory ownership
```

Contoh:

```bash
php artisan make:module Equipment
```

jika module sudah ada:

```text
Module [Equipment] already exists.
```

Generator tidak overwrite file existing secara default.

Overwrite hanya boleh melalui opsi eksplisit, misalnya:

```bash
--force
```

dan tetap harus digunakan dengan hati-hati.

---

## 52A.11 Generator Guardrails

Module Generator bukan sekadar folder scaffolding.

Ia harus menjaga architectural conventions.

Generator tidak boleh:

```text
create Domain class that extends Eloquent Model
place Request inside Domain
place Repository implementation inside Domain
place migration outside owning module
write all routes into root routes/web.php
use Wayfinder
hard-code testing scope into source code
create cross-module model mutation
create empty Adapters/ folders without a real adapter
create empty Integrations/ folders without a real integration
```

Generator harus membantu menghasilkan struktur yang benar sejak awal.

---

## 52A.12 Generator Testing

Architectural tooling harus memiliki automated tests.

Minimal menguji:

```text
module structure generated correctly
namespace generated correctly
metadata generated correctly
route files generated
database folders generated
duplicate module rejected
child generator targets correct module
CQRS folders generated on demand
generated PHP files pass syntax check
```

Generator adalah bagian dari developer infrastructure, sehingga regression pada generator dapat berdampak ke seluruh module baru.

---

## 52A.13 Recommended Internal Location

Implementasi command generator dapat ditempatkan pada concern development/platform tooling terpisah.

Contoh:

```text
app/
└── Console/
    └── Commands/
        └── Modules/
```

atau bila tooling ingin dimodularisasi:

```text
app/
└── Support/
    └── ModuleGenerator/
```

Tooling generator bukan bounded context bisnis dan tidak perlu dipaksa menjadi business module.

---

## 52A.14 Initial Generator Scope

Release pertama generator minimal mendukung:

```text
make:module
module:make-action
module:make-dto
module:make-contract
module:make-entity
module:make-event
module:make-value-object
module:make-model
module:make-repository
module:make-controller
module:make-request
module:make-resource
module:make-migration
module:make-seeder
module:make-factory
module:make-adapter
module:make-integration
```

CQRS command dapat tersedia sejak Phase 0 bila implementasinya ringan:

```text
module:make-command
module:make-query
```

Command tambahan dapat dibuat setelah pola implementasi stabil.

---



# 52B. Adapter & Integrasi Opsional

Hexagonal Arsitektur membutuhkan konsep port dan adapter, tetapi proyek ini tidak memaksakan folder tambahan di setiap module.

Default:

```text
Infrastructure/
├── Models/
├── Repositories/
├── Observer/
└── Providers/
```

Tambahkan saat dibutuhkan:

```text
Infrastructure/
├── Adapters/
└── Integrations/
```

## 52B.1 Adapters

Tujuan:

```text
mengimplementasikan port/contract
mengisolasi ketergantungan teknologi
menghindari Domain bergantung pada package/framework
```

Contoh:

```text
Domain/
└── Contracts/
    └── EvidenceStorage.php

Infrastructure/
└── Adapters/
    └── SpatieMediaEvidenceStorage.php
```

Repository tetap boleh berada di:

```text
Infrastructure/Repositories/
```

karena folder tersebut sudah merupakan adapter khusus persistence.

## 52B.2 Integrations

Digunakan untuk integrasi yang memiliki boundary jelas.

Contoh:

```text
External Calibration Provider
ERP eksternal
Object Storage eksternal
Certification system
Government/reporting system
External identity provider
```

Struktur contoh:

```text
Infrastructure/
└── Integrations/
    └── ExternalCalibrationProvider/
        ├── Client.php
        ├── DTO/
        ├── Mapper.php
        └── Exceptions/
```

Integration internal antar-module tidak otomatis membutuhkan HTTP/API client.

Dalam modular monolith, prioritaskan:

```text
Contract
Application Service
Command
Query
Domain Event
Integration Event
```

sesuai kebutuhan.

Jangan membuat pseudo-microservice di dalam monolith.

## 52B.3 Anti-Corruption Layer

Jika model eksternal memiliki istilah/struktur yang berbeda dengan domain internal, `Integrations/` dapat berfungsi sebagai Anti-Corruption Layer.

```text
External Provider Payload
        ↓
Integration Mapper
        ↓
Internal DTO / Value Object
        ↓
Application / Domain
```

Domain tidak boleh mengetahui payload vendor secara langsung.

## 52B.4 Default Rule

> Adapter dan Integration adalah konsep arsitektur wajib dipahami, tetapi folder fisiknya dibuat hanya jika ada kebutuhan nyata.

---

# 53. Guardrail Arsitektur

1. Business logic tidak diletakkan di Controller.
2. Eloquent Model bukan tempat workflow kompleks.
3. Module tidak mengubah model module lain secara langsung.
4. Hindari circular dependencies.
5. Shared Kernel harus kecil.
6. Scope pengujian adalah data.
7. Test method version/revision harus dapat dikunci saat eksekusi.
8. Historical test data tidak boleh berubah mengikuti update master method.
9. Calibration certificate bukan sekadar attachment; ia memiliki konteks metrologi.
10. Technical authorization berbeda dari application permission.
11. Evidence adalah first-class domain.
12. Audit Trail berbeda dari log aplikasi.
13. CQRS dipakai pragmatis, bukan dogmatis.
14. Event digunakan untuk decoupling, bukan menyembunyikan alur bisnis.
15. Critical consistency tetap synchronous.
16. Intelligence dibangun setelah operational data cukup berkualitas.

---

# 54. Aturan Kritis Immutability Data

Saat test execution dimulai, data penting harus disnapshot atau direferensikan ke immutable revision.

Contoh:

```text
Test Execution
├── Method Revision = 3
├── Acceptance Criteria Revision = 2
├── Equipment Used = FG-001
├── Calibration Record = CAL-2026-0032
└── Personnel Authorization = AUTH-2026-0081
```

Jika master data berubah besok, histori pengujian kemarin tetap merepresentasikan kondisi saat pengujian dilakukan.

Ini merupakan salah satu requirement arsitektur paling penting untuk traceability.

---

# 55. Arah Evolusi

```text
Release 1
Laboratory Operations Platform
        ↓

Evidence-first Laboratory Platform
        ↓

Evidence Readiness Platform
        ↓

Calibration Intelligence
        ↓

CAPA / Risk Intelligence
        ↓

SNI ISO/IEC 17065 Integration
        ↓

Compliance Intelligence Platform
```

---

# 56. Current Arsitektur Decision Summary

```text
Application Type:
Single-organization modular laboratory platform

Core Domain:
Multi-scope laboratory testing

Ruang Lingkup Referensi Awal:
Mainan Anak

Dynamic Scope:
YES

Laravel:
13

Frontend:
Inertia + React + TypeScript

UI:
Tailwind + shadcn/ui

Motion:
Framer Motion

Authentication:
Starter Kit / Fortify

Authorization:
Spatie Permission

Media:
Spatie Media Library

Routing:
Conventional Ziggy

Wayfinder:
Removed

Laravel Boost:
Removed / Forbidden

Arsitektur:
DDD-lite
Modular Monolith
Hexagonal Arsitektur

Module Root:
app/Modules/namespace/<Module>

Module Database:
Database/Migrations
Database/Seeders
Database/Factories
Database/Providers

Module Routes:
Routes/web.php
Routes/api.php
Routes/console.php
Routes/channels.php

Shared Kernel:
YES, strictly limited

CQRS:
YES, pragmatic

Domain Events:
YES

Cross Module Listeners:
YES

Queue:
YES, selective

Database:
MySQL

Developer Tooling:
Module Loader
Module Generator
Custom Module Stubs

Future:
17065 + Evidence Readiness + Calibration/Risk/CAPA/Compliance Intelligence
```

---

# 57. Dokumen Teknis Berikutnya

Setelah baseline ini disepakati, dokumen berikutnya sebaiknya dibuat berurutan:

```text
01 Domain Map & Bounded Context Specification
02 Module Dependency Matrix
03 Domain Model & Aggregate Design
04 Database Design / ERD
05 Event Catalog
06 Command & Query Catalog
07 Permission Matrix
08 Workflow / State Machine Specification
09 API + Inertia Route Specification
10 Frontend Arsitektur Specification
11 Implementation Plan per Phase
12 Strategi Pengujian & Definition of Done
```

Urutan ini menjaga agar database dan source code tidak dibuat sebelum boundary domain, ownership data, workflow, dan dependency antarmodul jelas.

---

# 58. Kesimpulan Baseline

Fondasi aplikasi tidak dibangun sebagai:

```text
Aplikasi ISO 17025 Mainan Anak
```

tetapi sebagai:

```text
Multi-Scope Laboratory Operations
+
Compliance Evidence Platform
```

Mainan anak menjadi reference implementation pertama.

Keramik, elektrik, lampu, dan ruang lingkup lain ditambahkan melalui konfigurasi domain:

```text
Scope
Method
Revision
Parameter
Equipment Requirement
Competence Requirement
Evidence Requirement
Acceptance Criteria
```

Dengan demikian, pertumbuhan fitur tidak mengharuskan pembongkaran architecture utama.

DDD-lite Modular Monolith menjaga domain tetap terpisah tetapi tetap praktis untuk dikerjakan sebagai satu aplikasi Laravel.

Hexagonal Arsitektur menjaga business rules tidak terikat pada Eloquent, Inertia, Spatie, atau detail framework.

Shared Kernel, CQRS, dan Domain Events digunakan secara terkontrol untuk mendukung evolusi sistem tanpa membuat arsitektur terlalu kompleks sejak Release 1.
