# Project Agent Instructions

Template ini digunakan sebagai `AGENTS.md` pada root project Laravel yang
memakai documentation baseline pada folder `docs/`.

## Gaya Penulisan Dokumentasi

Setiap dokumen baru atau dokumen yang diubah wajib ditulis dalam Bahasa
Indonesia yang sederhana, jelas, dan mudah dipahami. Hindari kalimat yang
terlalu panjang serta jargon yang tidak perlu. Nama class, command, namespace,
package, API field, dan code identifier tetap menggunakan istilah teknis resmi.

## Penggunaan Skill Agent

Sebelum melakukan setiap langkah kerja yang memakai skill, agent wajib
menginformasikan nama skill dan alasan penggunaannya kepada user. Jika tidak ada
skill yang relevan, agent harus menyatakannya.

Skill yang tersedia berasal dari environment Codex, bukan dari folder skill di
project. Gunakan skill sesuai kebutuhan berikut:

| Skill                               | Kapan Digunakan                                                                                   |
| ----------------------------------- | ------------------------------------------------------------------------------------------------- |
| `documentation-and-adrs`            | Menulis atau mengubah dokumentasi, keputusan arsitektur, dan ADR.                                 |
| `planning-and-task-breakdown`       | Memecah pekerjaan yang memiliki beberapa langkah.                                                 |
| `spec-driven-development`           | Menyusun spesifikasi sebelum membuat capability baru.                                             |
| `incremental-implementation`        | Mengerjakan perubahan multi-file secara bertahap.                                                 |
| `test-driven-development`           | Mengubah behavior, memperbaiki bug, atau menambah logic.                                          |
| `code-review-and-quality`           | Mengevaluasi kode dari sisi correctness, readability, architecture, security, dan performance.    |
| `ci-cd-and-automation`              | Menyiapkan atau mengubah pipeline CI/CD dan quality gate otomatis.                                |
| `security-and-hardening`            | Menangani authentication, authorization, input tidak tepercaya, secret, atau integrasi eksternal. |
| `frontend-ui-engineering`           | Membuat atau mengubah UI frontend yang responsif dan accessible.                                  |
| `browser-testing-with-devtools`     | Menguji atau mendiagnosis behavior aplikasi di browser nyata.                                     |
| `debugging-and-error-recovery`      | Menyelidiki error, test failure, atau behavior yang tidak sesuai.                                 |
| `source-driven-development`         | Memerlukan keputusan implementasi berdasarkan dokumentasi resmi.                                  |
| `git-workflow-and-versioning`       | Melakukan perubahan kode yang berkaitan dengan workflow Git atau versioning.                      |
| `api-and-interface-design`          | Mendesain API, contract, dan batas interface antar bagian aplikasi.                               |
| `code-simplification`               | Menyederhanakan kode tanpa mengubah behavior.                                                     |
| `context-engineering`               | Menyiapkan context kerja agar tetap relevan dan ringkas.                                          |
| `deprecation-and-migration`         | Menghapus atau memindahkan sistem lama secara terencana.                                          |
| `doubt-driven-development`          | Melakukan pemeriksaan adversarial sebelum keputusan penting.                                      |
| `idea-refine`                       | Memperjelas ide dan menguji pilihan sebelum implementasi.                                         |
| `interview-me`                      | Menggali requirement saat intent user belum cukup jelas.                                          |
| `observability-and-instrumentation` | Menambah logging, metrics, tracing, atau alerting.                                                |
| `performance-optimization`          | Mengoptimalkan performa frontend, backend, query, atau database.                                  |
| `shipping-and-launch`               | Menyiapkan checklist dan strategi peluncuran production.                                          |
| `web-performance-addyosmani`        | Mengikuti praktik web performance untuk UI dan asset frontend.                                    |
| `using-agent-skills`                | Menemukan dan memilih skill yang sesuai untuk task.                                               |

Agent tidak boleh menganggap semua skill harus digunakan pada setiap task. Pilih
skill minimal yang benar-benar relevan dengan pekerjaan.

## Disiplin Checklist Kerja

Setiap checklist kerja wajib ditinjau sebelum pekerjaan dimulai untuk memastikan
scope, urutan, dependency, acceptance criteria, dan verifikasinya jelas. Setelah
pekerjaan selesai, checklist wajib ditinjau kembali dan diperbarui sesuai hasil
nyata. Jangan membiarkan task yang sudah selesai tetap bertanda belum selesai,
dan jangan menandai task selesai tanpa bukti verifikasi.
## Authoritative Documentation

Instruksi arsitektur dan workflow authoritative berada di:

- [`docs/AGENTS.md`](docs/AGENTS.md)
- [`docs/README.md`](docs/README.md)
- [`docs/AI-PROMPT-GUIDE.md`](docs/AI-PROMPT-GUIDE.md)

Agent wajib membaca file di atas sebelum melakukan perubahan. Jika instruksi
project ini berbeda dengan `docs/AGENTS.md`, laporkan konflik dan ikuti aturan
yang lebih authoritative; jangan memilih berdasarkan tebakan.

## First Read dan Project Intake

Sebelum coding, generator, migration, atau perubahan konfigurasi apa pun, agent
wajib:

1. Membaca file ini.
2. Membaca `docs/AGENTS.md` dan `docs/README.md`.
3. Menentukan mode project:
    - `greenfield`;
    - `existing starter kit`;
    - `module extension`.
4. Mengidentifikasi Laravel version, PHP version, starter kit, package, database,
   cache, queue, dan frontend stack.
5. Menginventarisasi module existing, manifest, provider, route, permission,
   event, migration, test, dan README.
6. Menentukan dokumen authoritative dan downstream yang terdampak.
7. Menyusun acceptance criteria, focused test, verification command, dan risk.

Jika project sudah memiliki module, agent tidak boleh menganggap project kosong.
Jika project dinyatakan `greenfield`, status tersebut harus didukung evidence.

## Existing Module Verification

Sebelum menjalankan `module:make` atau mengubah module existing, jalankan command
berikut jika tersedia:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
```

Untuk module target:

```bash
php artisan module:inspect {Domain}/{Module} --json
php artisan module:validate {Domain}/{Module} --json
```

Jika command belum tersedia, lakukan read-only scan dan laporkan keterbatasannya.
Jangan membuat module dengan name, path, namespace, provider, atau permission key
yang sudah dimiliki module valid.

## Baseline Module Order

Baseline implementasi mengikuti urutan:

```text
Framework prerequisite
  -> AccessControl
  -> UserManagement
  -> AuditLog
  -> SystemSetting
```

`AccessControl` harus menyediakan public authorization capability sebelum flow
UserManagement yang membutuhkan role, permission, policy, atau authorization
diaktifkan. UserManagement tidak boleh mengimpor private model, repository,
policy, atau service dari AccessControl.

## Architecture Rules

- Gunakan DDD-lite Modular Monolith.
- Reusable framework berada di `packages/StarterKit`.
- Module berada di `app/Modules/{Domain}/{SubModule}`.
- Cross-module concrete dependency dilarang.
- Komunikasi lintas module menggunakan public contract, DTO, public event,
  capability, atau shared value object yang disetujui.
- Permission identity dimiliki module owner melalui `permissions.php`.
- Backend adalah security authority.
- Frontend authorization context hanya untuk visibility/UX.
- Wayfinder dan Laravel Boost dilarang total.
- Route frontend menggunakan Ziggy.
- Primary key dan foreign key menggunakan ULID.
- Secret, token, password, credential, dan sensitive payload tidak boleh masuk
  ke source, log, output, diagnostic, atau generated artifact.

## Standard Authorization Pattern

Semua module mengikuti pola:

```text
Authentication
  -> Controller/Route Middleware
  -> Policy/Gate
  -> AccessControl Public Capability
  -> Spatie Permission Adapter
  -> Resource/State Rule
  -> Audit bila mutation atau event sensitif
```

Controller middleware digunakan untuk coarse-grained authorization. Policy module
owner menangani ownership, scope, state, dan resource-specific rule. Inertia
`auth.authorization` hanya boleh dipakai untuk frontend UX dan tidak boleh
menjadi security boundary.

## Incremental Implementation

Untuk perubahan multi-file atau module baru, agent wajib bekerja incremental:

1. Inventory dan dry-run.
2. Manifest dan runtime identity.
3. Permission identity dan public contract.
4. Domain boundary.
5. Application boundary.
6. Infrastructure dan persistence.
7. Presentation dan routes.
8. Tests, README, dan documentation evidence.

Setiap increment wajib memiliki:

- scope kecil;
- acceptance criteria;
- focused positive test;
- focused negative test;
- verification command;
- execution evidence;
- documentation update bila contract berubah.

Jangan memulai increment berikutnya sebelum increment sebelumnya diverifikasi.

## Change Protocol

Sebelum perubahan:

- identifikasi authoritative document dan downstream document;
- gunakan specification sebelum capability baru;
- gunakan planning untuk pekerjaan multi-step;
- pertahankan perubahan user yang tidak terkait;
- jangan commit, membuat branch, atau menginstal dependency tanpa permintaan
  eksplisit;
- jangan mengubah Open Decision berdasarkan asumsi.

## Definition of Done

Perubahan dianggap selesai hanya jika:

- acceptance criteria terpenuhi;
- positive dan negative test tersedia;
- verification command dijalankan dan evidence dicatat;
- authorization/security impact diperiksa;
- module discovery/validation lulus bila module terkait;
- generated structure/manifest test lulus bila generator terkait;
- documentation authoritative dan downstream diperbarui;
- tidak ada broken link atau forbidden dependency;
- unresolved risk dilaporkan.

## Agent Handoff Format

Setiap selesai bekerja, laporkan secara ringkas:

```text
CHANGES MADE:
- path/to/file: perubahan

VERIFICATION:
- command: hasil

NOT TOUCHED:
- path/to/file: alasan tidak disentuh

OPEN RISKS:
- risiko atau keputusan yang belum selesai
```
