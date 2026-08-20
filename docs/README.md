# Laravel Engineering Starter Kit Documentation

> Baseline dokumentasi reusable untuk Laravel 13, developer, QA, DevOps, dan AI coding assistant.

## Tujuan

Repository ini menjadi single source of truth untuk membangun reusable Laravel
Engineering Starter Kit. Dokumentasi harus cukup jelas agar implementasi dapat
dilakukan tanpa instruksi lisan dan dapat diverifikasi end-to-end.

Starter kit menggunakan Laravel 13, runtime PHP 8.4+, Laravel React starter kit, Inertia,
React, TypeScript, Vite, Tailwind, shadcn/ui, MySQL, Redis, Ziggy, dan
package Spatie yang disetujui.

Runtime lokal terakhir terverifikasi pada PHP 8.4.16. `composer.json` memakai
constraint PHP `^8.4` sesuai baseline runtime project.

## Entry Point

### First Read: Project Intake and Existing Module Verification

Sebelum membaca detail baseline atau menjalankan generator, agent wajib:

1. Membaca `AGENTS.md` dan bagian ini.
2. Menentukan mode project: greenfield, existing starter kit, atau module
   extension.
3. Menginventarisasi module yang sudah ada dari `app/Modules`, manifest,
   provider, route, permission, event, migration, test, dan README module.
4. Menjalankan discovery dan validation module yang tersedia, atau mencatat
   alasan jika command belum tersedia.
5. Membuat evidence inventory dan menyatakan module mana yang akan dibuat,
   dipertahankan, diisolasi, atau tidak disentuh.

Generator dilarang membuat module sebelum langkah ini selesai. Untuk project
yang sudah memiliki module, output generator wajib dibandingkan dengan inventory
agar tidak membuat duplicate module atau mengambil alih ownership module lama.

Baca dokumen dengan urutan:

1. AGENTS.md — aturan context dan behavior AI.
2. README.md — navigasi dan governance dokumentasi.
3. 00-PROJECT — context, vision, scope, glossary, baseline direction.
4. 01-REQUIREMENTS — requirement dan acceptance criteria.
5. 02-DESIGN — API, database, security, system, dan UI/UX design.
6. 03-IMPLEMENTATION — coding, console, workflow, structure, generator,
   modules, technical spec, test plan, dan task plan.
7. 04-DEPLOYMENT — CI/CD, environment, operations, dan release.
8. 05-DECISIONS — changelog, known issues, dan ADR.
9. 06-FRAMEWORK — baseline package/generator path lalu supporting detail.
10. 07-KERNEL — baseline runtime path lalu supporting detail.

Jangan membaca 06-FRAMEWORK atau 07-KERNEL sebagai dasar implementasi sebelum
requirement, design, dan implementation contract yang relevan dipahami.

Semua link dokumentasi menggunakan relative Markdown link terhadap file ini.
Saat repository ditempatkan sebagai folder `docs/` di project Laravel, link tetap
valid dan tidak bergantung pada nama workspace lokal.

## Navigation Index

- [Project Context](00-PROJECT/00.05-DOCUMENTATION-CONTEXT.md)
- [Baseline Direction](00-PROJECT/00.06-BASELINE-DIRECTION.md)
- [Requirements](01-REQUIREMENTS/01.05-BASELINE-SPECIFICATION.md)
- [Design](02-DESIGN/02.04-SYSTEM-DESIGN.md)
- [Implementation](03-IMPLEMENTATION/03.11-BASELINE-TASK-PLAN.md)
- [Module Communication and Execution](03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Deployment](04-DEPLOYMENT/04.04-RELEASE.md)
- [Decisions](05-DECISIONS/ADR/05.04-ADR-0002-DOCUMENTATION-STANDARD.md)
- [ADR-0003 Module Communication](05-DECISIONS/ADR/05.05-ADR-0003-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Project Templates](Projects/README.md)

## Baseline Implementation Path

### Framework

- [06.00-FRAMEWORK-CONTEXT](06-FRAMEWORK/06.00-FRAMEWORK-CONTEXT.md)
- [06.01-PACKAGE-ARCHITECTURE](06-FRAMEWORK/06.01-PACKAGE-ARCHITECTURE.md)
- [06.02-MODULE-CONTRACTS](06-FRAMEWORK/06.02-MODULE-CONTRACTS.md)
- [06.03-MODULE-REGISTRY](06-FRAMEWORK/06.03-MODULE-REGISTRY.md)
- [06.04-MODULE-DISCOVERY](06-FRAMEWORK/06.04-MODULE-DISCOVERY.md)
- [06.05-GENERATOR-ENGINE](06-FRAMEWORK/06.05-GENERATOR-ENGINE.md)
- [06.06-STUB-ENGINE](06-FRAMEWORK/06.06-STUB-ENGINE.md)
- [06.07-CONSOLE](06-FRAMEWORK/06.07-CONSOLE.md)

### Kernel

- [07.00-KERNEL-CONTEXT](07-KERNEL/07.00-KERNEL-CONTEXT.md)
- [07.01-BOOTSTRAP](07-KERNEL/07.01-BOOTSTRAP.md)
- [07.02-SERVICE-CONTAINER](07-KERNEL/07.02-SERVICE-CONTAINER.md)
- [07.03-EVENT-BUS](07-KERNEL/07.03-EVENT-BUS.md)
- [07.04-AUTHORIZATION](07-KERNEL/07.04-AUTHORIZATION.md)
- [07.05-AUDIT](07-KERNEL/07.05-AUDIT.md)
- [07.06-REGISTRY-SERVICE](07-KERNEL/07.06-REGISTRY-SERVICE.md)
- [07.07-SYSTEM-SETTING](07-KERNEL/07.07-SYSTEM-SETTING.md)

Dokumen bernomor 06.13+, 07.08+, dan seterusnya adalah supporting
implementation documents yang dibaca sesuai kebutuhan detail. Dokumen tersebut
tidak menggantikan baseline contract pada 06.00-06.07 dan 07.00-07.07.

## Baseline Vertical Slice

Generator dan module console merupakan prerequisite pada layer framework, bukan
bagian dari urutan business vertical slice. Setelah prerequisite framework
tersedia, urutan implementasi baseline adalah:

1. AccessControl sebagai baseline authorization module pertama.
2. UserManagement sebagai business module pertama setelah public authorization
   contract tersedia.
3. AuditLog.
4. SystemSetting.

Auth, notification, profile, dan settings dasar dari Laravel React starter kit
digunakan kembali, bukan dibangun ulang.

## Global Project Documentation

Repository ini juga digunakan untuk project yang sudah memiliki starter kit atau
hanya memerlukan module tambahan. Jalankan Project Intake sebelum bekerja:

- deteksi starter kit dan module yang sudah terpasang;
- tentukan mode greenfield, existing starter kit, atau module extension;
- buat Projects/{project-slug} dari Projects/_TEMPLATE;
- dokumentasikan hanya scope dan perubahan project tersebut;
- pecah pekerjaan menggunakan incremental implementation.

Struktur project documentation:

Projects/{project-slug}/
|-- README.md
|-- specification.md
|-- implementation-plan.md
|-- tasks.md
|-- decisions/

Folder Projects tidak menggantikan baseline 00–07. Baseline menjadi referensi
global, sedangkan folder project berisi context, keputusan, task, dan evidence.

## Architecture Rules

- DDD-lite Modular Monolith.
- Reusable framework berada di packages/StarterKit.
- Module berada di app/Modules/{Domain}/{SubModule}.
- Cross-module dependency memakai contract, DTO, event, atau shared value object
  yang disepakati.
- Direct concrete dependency lintas module dilarang.
- Module invalid diisolasi; module valid lain tetap berjalan.
- Module yang memiliki alur pengguna wajib memiliki vertical slice frontend
  yang dapat dibuka dan diuji langsung di browser, termasuk state UI,
  permission visibility, responsive layout, dan browser/accessibility test.
- Semua identifier persistence dan boundary menggunakan ULID.
- Permission baseline menggunakan allow permission dan policy rule.
- Wayfinder dan Laravel Boost dilarang total; frontend route memakai Ziggy.

## Environment and Operations

- Local baseline: Laragon pada Windows.
- Shared Development environment: optional.
- CI: GitHub Actions.
- Deployment: DigitalOcean Ubuntu via SSH atau hosting cPanel.
- Monitoring eksternal: optional melalui SystemSetting.
- Structured logging dan diagnostic internal: wajib.
- Recovery target default: RTO 4 jam dan RPO 24 jam melalui SystemSetting.

## Documentation Rules

- Bahasa utama dokumentasi dan komunikasi adalah Bahasa Indonesia.
- Nama class, command, namespace, package, API field, dan code identifier tetap
  menggunakan bentuk teknis resminya.
- Filename lifecycle memakai format NN.MM.
- Setiap dokumen memiliki status, owner, dependency, acceptance criteria,
  verification, dan revision history sesuai jenisnya.
- Hal yang belum diputuskan ditulis sebagai Open Decision, bukan fakta.
- Satu informasi memiliki satu authoritative source; gunakan reference, bukan
  copy-paste yang berisiko drift.
- Perubahan contract, architecture, numbering, atau dependency wajib memperbarui
  cross-reference dan ADR/changelog yang relevan.

## AI Assistant Workflow

AI assistant wajib:

1. Membaca AGENTS.md, README, dan prerequisite documents.
2. Menggunakan interview-me saat intent atau requirement materially unclear.
3. Menggunakan idea-refine untuk mengevaluasi alternatif dan trade-off.
4. Menggunakan spec-driven-development sebelum implementasi capability baru.
5. Menggunakan planning-and-task-breakdown untuk pekerjaan multi-step.
6. Menggunakan context-engineering untuk menjaga context tetap relevan.
7. Menambahkan acceptance criteria, focused tests, dan verification evidence.
8. Tidak mengisi Open Decision dengan tebakan.
9. Melaporkan file berubah, verification yang dilakukan, dan unresolved risk.

## Definition of Ready

Dokumen atau task siap diimplementasikan bila:

- scope dan owner jelas;
- dependency dan authoritative source tersedia;
- requirement/design/contract terkait terhubung;
- acceptance criteria dan verification method tersedia;
- security, authorization, migration, audit, dan failure impact dipertimbangkan;
- Open Decision yang memblokir sudah resolved atau memiliki owner;
- developer, QA, DevOps, dan AI dapat memahaminya tanpa oral instruction.

## Quality Baseline

Quality gate mencakup Pint, PHPStan/Larastan, Pest/PHPUnit, ESLint,
TypeScript check, Vitest, Playwright, axe-core, frontend build, CodeQL,
OWASP Dependency-Check, migration verification, generator tests, contract tests,
permission tests, audit tests, dan SystemSetting tests.

## Repository Structure

- 00-PROJECT — context dan direction.
- 01-REQUIREMENTS — requirement dan product specification.
- 02-DESIGN — architecture/design contracts.
- 03-IMPLEMENTATION — coding dan execution plan.
- 04-DEPLOYMENT — environment, CI/CD, operations, release.
- 05-DECISIONS — ADR, changelog, known issues.
- 06-FRAMEWORK — reusable package/framework contracts.
- 07-KERNEL — application runtime orchestration contracts.
- AGENTS.md — project context dan agent rules.
- Projects/ — documentation per project atau module extension.

## Status Project

Dokumentasi baseline 00–07 sudah disusun sebagai implementation contract.
Implementasi kode belum dianggap selesai sampai 03.11-BASELINE-TASK-PLAN.md,
test plan, CI gate, deployment verification, dan release acceptance terpenuhi.

## Format Detail Rencana dan Evidence

`implementation-plan.md` dan `tasks.md` wajib menjadi catatan kerja yang dapat
dibaca mandiri. Jika project membutuhkan catatan discovery atau execution log,
file tersebut boleh ditambahkan di folder project. Setiap task harus
menjelaskan kondisi awal, file/path, perubahan kode/configuration, alasan,
acceptance criteria, command/test, hasil, dan risiko.

Checklist selesai wajib memiliki sub-item detail. Execution log tidak boleh
hanya menulis "implementasi selesai" tanpa file dan evidence.

## License

Internal Project Documentation Template.
