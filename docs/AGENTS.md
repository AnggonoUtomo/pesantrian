# Context Project: Laravel Engineering Starter Kit

## Bahasa Dokumentasi

- Bahasa utama dokumentasi, requirement, acceptance criteria, task, dan komunikasi adalah Bahasa Indonesia.
- Istilah teknis, nama class, command, namespace, package, API field, dan code identifier tetap menggunakan bentuk resmi aslinya.
- Kutipan error, output command, dan nama konfigurasi tidak diterjemahkan bila dapat mengurangi ketepatan teknis.
- AI assistant wajib menjawab user dalam Bahasa Indonesia kecuali user meminta bahasa lain.

## Misi

Membangun starter kit Laravel 13 yang reusable sehingga developer, QA, DevOps engineer, atau AI coding assistant dapat mengimplementasikan dan memverifikasinya tanpa instruksi lisan tambahan.

## Fixed Stack

- Laravel 13 dan PHP 8.4+.
- Laravel React starter kit bawaan, Inertia, React, TypeScript, dan Vite.
- Tailwind CSS dan shadcn/ui.
- PostgreSQL dan Redis.
- Ziggy untuk route frontend.
- Spatie Permission dan package Spatie yang disetujui, termasuk Media Library saat modul membutuhkan media.
- Laragon pada Windows untuk lingkungan baseline lokal pertama.
- Pilihan deployment: DigitalOcean Ubuntu via SSH atau cPanel, sesuai dengan kemampuan target environment.

## Arsitektur

Gunakan DDD-lite Modular Monolith dengan batas-batas berbasis domain (*domain-driven boundaries*), public contracts, DTO bertipe (*typed DTOs*), dan events. Pisahkan modul bisnis dari orkestrasi kernel, layanan framework package, dan detail infrastruktur Laravel.

- Kapabilitas framework yang reusable berada pada `packages/StarterKit`.
- Modul aplikasi berada pada `app/Modules/{Domain}/{SubModule}`.
- Dependensi konkret lintas modul (*cross-module concrete dependency*) dilarang keras.
- Komunikasi antar-modul wajib menggunakan public contract, DTO, public event, atau shared value object yang disetujui.
- Setiap modul memiliki communication layer, permission identity, schema setting, test suite, dan `README.md` tersendiri.
- Modul yang tidak valid diisolasi; modul valid lainnya tetap melanjutkan bootstrap.

## Modul Baseline dan Urutan Implementasi

Sebelum implementasi atau perubahan apa pun, lakukan Project Intake dan Module
Inventory untuk mengidentifikasi starter kit, module, manifest, provider, route,
permission, event, migration, dan capability yang sudah ada. Hasil inventory
wajib diverifikasi melalui `module:discover`, `module:validate`, atau pemeriksaan
setara sebelum generator membuat atau mengubah module. Dilarang menjalankan
`module:make` sebelum inventory selesai, kecuali project dinyatakan greenfield
berdasarkan evidence.

Setelah framework prerequisite generator/console tersedia, business vertical
slice pertama diimplementasikan dalam urutan berikut:

1. AccessControl sebagai module baseline pertama dan pemilik capability
   authorization.
2. UserManagement sebagai module business pertama setelah AccessControl contract
   tersedia.
3. AuditLog.
4. SystemSetting.

Urutan ini memastikan UserManagement tidak perlu membuat authorization sendiri
atau bergantung pada private implementation AccessControl.

Authentication, notifikasi, profil, dan setting default dari Laravel React starter kit digunakan kembali dan diintegrasikan, bukan dibangun ulang, kecuali ada spesifikasi yang mengubahnya secara eksplisit.

## Aturan Identifier dan Keamanan

- Setiap primary key dan foreign key tabel menggunakan ULID.
- Boundary identifier, correlation ID, actor ID, job ID, fixture, dan resource ID pada API juga menggunakan ULID.
- Baseline otorisasi menggunakan aturan *allow permissions* dan *policy rules*; tidak ada model *explicit deny*.
- `SuperSystem` adalah peran baseline istimewa yang dibuat melalui `AccessControl`, bukan hardcoded project role. Kapabilitas istimewa tersembunyi dari pengguna lain.
- Impersonation membutuhkan izin dan alasan eksplisit; penargetan `SuperSystem` selalu dilarang, termasuk oleh `SuperSystem` itu sendiri.
- AuditLog bersifat *append-only*, ter-scoped, data sensitif ter-redaksi, dan disimpan minimal selama satu tahun.
- Perubahan SystemSetting dibatasi hanya untuk `SuperSystem`, divalidasi, langsung aktif setelah validasi, dan diaudit.
- Operational setting baseline mencakup target RTO 4 jam dan RPO 24 jam melalui SystemSetting.
- Dilarang menyimpan atau mencatat secret, token, password, credential, atau sensitive payload sebagai data polos (*plain data*).

### Generator Output Rules

- `module.json` adalah manifest deklaratif yang divalidasi oleh registry.
- `module.php` adalah runtime configuration source dan bukan manifest kedua.
- Profile generator wajib menghasilkan manifest, runtime configuration, provider,
  permission identity, README, test, dan struktur DDD-lite sesuai golden contract.
- Generator default tidak boleh overwrite file.
- Generator wajib mendukung dry-run, conflict detection, staging, atomic
  promotion, cleanup, dan output JSON.
- Generator wajib menolak duplicate module name, path, namespace, provider, dan
  permission key kecuali mode module extension disetujui secara eksplisit.

## Aturan Dependensi

Wayfinder dan Laravel Boost dilarang total. Dilarang memasukkannya kembali melalui composer, npm, konfigurasi transitif, kode hasil generate, source code, contoh, atau dokumentasi. Generasi route frontend menggunakan Ziggy.

## Aturan Dokumentasi

Baca `README.md` dan `AGENTS.md` sebelum bekerja. Kemudian baca dokumen relevan dalam urutan berikut:

1. 00 context dan baseline direction.
2. 01 requirements dan acceptance criteria.
3. 02 design dan contracts.
4. 03 implementation, tests, dan task plan.
5. 04 environment, CI/CD, operations, dan release.
6. 05 ADR, changelog, dan known issues.
7. 06 baseline framework path sebelum dokumen pendukung framework.
8. 07 baseline kernel path sebelum dokumen pendukung kernel.

Perlakukan `Open Decision` sebagai unresolved (belum terputuskan). Dilarang mengarang jawaban. Ketika sebuah keputusan dikonfirmasi, perbarui dokumen authoritative, referensi downstream, changelog, dan revision history.

Semua referensi dokumentasi wajib menggunakan relative Markdown links terhadap repository. Dilarang menggunakan path workspace lokal absolut atau link yang mengandung nama workspace lokal.
Repository dokumentasi dirancang untuk ditempatkan sebagai folder `docs/` di dalam project Laravel; link harus tetap valid setelah pemindahan tersebut.

## First Agent Verification Gate

Sebelum membuat atau mengubah module, agent wajib menjalankan atau menyediakan
evidence setara untuk:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
```

Inventory minimal mencakup module name, domain, namespace, path, manifest,
runtime configuration, provider, version, status, dependencies, permission
source, routes, events, migrations, tests, README, validation result, dan
ownership. Jika command belum tersedia, agent harus melakukan read-only scan dan
melaporkan keterbatasannya. Target module/path/namespace yang sudah dimiliki
module valid tidak boleh dibuat ulang.

## Path Framework Baseline

Baca sesuai urutan:

- `06.00-FRAMEWORK-CONTEXT.md`
- `06.01-PACKAGE-ARCHITECTURE.md`
- `06.02-MODULE-CONTRACTS.md`
- `06.03-MODULE-REGISTRY.md`
- `06.04-MODULE-DISCOVERY.md`
- `06.05-GENERATOR-ENGINE.md`
- `06.06-STUB-ENGINE.md`
- `06.07-CONSOLE.md`

## Path Kernel Baseline

Baca sesuai urutan:

- `07.00-KERNEL-CONTEXT.md`
- `07.01-BOOTSTRAP.md`
- `07.02-SERVICE-CONTAINER.md`
- `07.03-EVENT-BUS.md`
- `07.04-AUTHORIZATION.md`
- `07.05-AUDIT.md`
- `07.06-REGISTRY-SERVICE.md`
- `07.07-SYSTEM-SETTING.md`

Dokumen pendukung hanya dibaca saat tugas membutuhkan detailnya.

## Protokol Perubahan (*Change Protocol*)

Sebelum melakukan perubahan:

- Identifikasi dokumen authoritative dan dokumen downstream yang terdampak;
- Gunakan `interview-me` saat intent atau requirement belum jelas;
- Gunakan `idea-refine` saat alternatif atau trade-off perlu diuji stress-test;
- Gunakan `spec-driven-development` sebelum mengimplementasikan kapabilitas baru;
- Gunakan `planning-and-task-breakdown` untuk implementasi multi-step;
- Gunakan `context-engineering` untuk menjaga context aktif tetap minimal dan akurat.

Setiap perubahan implementasi membutuhkan:

- Acceptance criteria;
- Positive test dan negative test yang terfokus;
- Pertimbangan keamanan/otorisasi;
- Perintah verifikasi atau bukti (*evidence*);
- Dokumentasi/pembaruan context yang terdampak saat perilaku kode berubah.

## Kualitas dan Verifikasi

Gate kualitas yang relevan mencakup:

- Pint dan PHPStan/Larastan.
- Pest/PHPUnit, Vitest, dan Playwright dengan axe-core.
- ESLint, check TypeScript, dan build frontend.
- CodeQL dan OWASP Dependency-Check.
- Fresh migration, upgrade migration, discovery modul, generator, contract, permission, audit, SystemSetting, dan pengujian browser flow kritis.

Gunakan verifikasi yang paling spesifik terlebih dahulu, baru kemudian gate yang lebih luas. Dilarang mengklaim perubahan telah diverifikasi tanpa bukti (*evidence*).

## Operational Baseline

- Target pengembangan lokal adalah Laragon.
- Shared Development environment bersifat opsional.
- CI menggunakan GitHub Actions.
- Staging/production dapat menggunakan DigitalOcean Ubuntu via SSH atau cPanel.
- Integrasi pemantauan bersifat opsional melalui SystemSetting, sedangkan pemantauan internal terstruktur (*structured logging*) dan diagnostik tetap wajib.
- Target pemulihan default adalah RTO 4 jam dan RPO 24 jam.
- Release wajib mempertahankan artifak yang sama yang telah diuji lintas lingkungan.

## Perilaku Agen AI (*Agent Behavior*)

- Utamakan perubahan kecil yang terfokus.
- Pertahankan perubahan user yang sudah ada dan jangan mereset file yang tidak relevan.
- Dilarang melakukan commit, membuat branch, atau menginstal dependensi kecuali diminta secara eksplisit.
- Dilarang mengubah Open Decision berdasarkan tebakan/asumsi.
- Dilarang menambah logika bisnis ke dalam framework/kernel atau stub generator.
- UserManagement tidak boleh mengimpor private model, repository, policy, atau
  service AccessControl. Interaksi otorisasi wajib melalui public contract,
  capability, DTO, atau public event.
- Laporkan file yang berubah, verifikasi yang dilakukan, dan risiko yang belum terselesaikan secara ringkas.

## Module Definition of Done

Module hanya dianggap selesai jika seluruh kondisi berikut terpenuhi:

- inventory sebelum perubahan tersedia;
- `module.json` dan `module.php` valid sesuai schema;
- module dapat di-discover, di-list, di-inspect, dan di-validate;
- tidak ada duplicate ownership pada name, path, namespace, provider, atau permission key;
- public contract, DTO, event, permission identity, dan boundary tersedia sesuai scope;
- positive test dan negative test terfokus tersedia;
- generated structure snapshot atau structure test lulus;
- README module menjelaskan purpose, boundary, contract, permission, dependency,
  configuration, test, dan operational notes;
- tidak ada Wayfinder, Laravel Boost, secret, credential, atau sensitive payload
  pada output;
- evidence verification dan unresolved risk dilaporkan.

## Project Intake dan Penggunaan Kembali

Dokumentasi ini global dan tidak mengasumsikan project selalu dimulai dari nol. Sebelum bekerja, AI wajib mengidentifikasi source repository, versi Laravel, status starter kit, package, module, migration, route, permission, event, dan kapabilitas yang sudah ada. Tentukan mode greenfield, existing starter kit, atau module extension.

Untuk project turunan, gunakan `Projects/{project-slug}` dari `Projects/_TEMPLATE`. Jangan menyalin seluruh baseline 00–07. Jika starter kit sudah ada, gunakan kapabilitas yang tersedia; jangan membangun ulang tanpa keputusan eksplisit.

## Format Detail Task dan Evidence

Implementation plan, task plan, dan execution log wajib menjelaskan kondisi
awal, file/path, perubahan kode/configuration, alasan teknis, acceptance
criteria, command/test, hasil, dan risiko. Checklist tidak boleh hanya berisi
kalimat umum seperti "scope task selesai".

Gunakan checklist bertingkat:

```markdown
- [x] Scope task selesai.
  - Kondisi awal: `path/file` memiliki ...
  - Perubahan: `path/file` diubah menjadi ...
  - Alasan: perubahan diperlukan karena ...
  - Evidence: `command` menghasilkan ...
```

Execution log harus dapat dipahami tanpa membaca percakapan agent.

## Implementasi Incremental

Gunakan skill `incremental-implementation` untuk perubahan multi-file dan modul baru. Setiap increment wajib memiliki scope kecil, acceptance criteria, focused test, verification evidence, execution log, dan documentation update. Mulai increment berikutnya hanya setelah increment sebelumnya diverifikasi.
