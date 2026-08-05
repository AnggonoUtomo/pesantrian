# Project Agent Instructions

Template ini digunakan sebagai `AGENTS.md` pada root project Laravel yang
memakai documentation baseline pada folder `docs/`.

## Gaya Penulisan Dokumentasi

Setiap dokumen baru atau dokumen yang diubah wajib ditulis dalam Bahasa
Indonesia yang sederhana, jelas, dan mudah dipahami. Hindari kalimat yang
terlalu panjang serta jargon yang tidak perlu. Nama class, command, namespace,
package, API field, dan code identifier tetap menggunakan istilah teknis resmi.

## Bahasa Commit

Commit message wajib menggunakan Bahasa Indonesia yang sederhana dan
menjelaskan tujuan perubahan. Contoh: `tutup quality gate phase 2`,
`perbaiki validasi registry`, atau `tambah test generator`. Nama class,
command, package, dan code identifier tetap memakai istilah teknis resminya.

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

## Format Detail Task dan Evidence

Implementation plan, task plan, dan execution log tidak boleh hanya berisi
judul atau kalimat umum seperti "scope task selesai". Setiap task wajib
menjelaskan kondisi awal, file/path yang dibaca atau diubah, perubahan kode
atau konfigurasi, alasan teknis, acceptance criteria, command/test, hasil
penting, serta risiko atau batasan.

Checklist selesai wajib memiliki sub-item detail. Gunakan pola berikut:

```markdown
- [x] Scope task selesai.
  - Kondisi awal: `path/file` memiliki ...
  - Perubahan: `path/file` diubah menjadi ...
  - Alasan: perubahan diperlukan karena ...
  - Evidence: `command` menghasilkan ...
```

Execution log harus dapat dipahami tanpa membaca percakapan agent. Hindari
catatan seperti "implementasi selesai" tanpa menyebut file, perubahan, alasan,
command, hasil, dan risiko.
## Authoritative Documentation

Instruksi arsitektur dan workflow authoritative berada di:

- [`docs/AGENTS.md`](docs/AGENTS.md)
- [`docs/README.md`](docs/README.md)
- [`docs/AI-PROMPT-GUIDE.md`](docs/AI-PROMPT-GUIDE.md)

Agent wajib membaca file di atas sebelum melakukan perubahan. Jika instruksi
project ini berbeda dengan `docs/AGENTS.md`, laporkan konflik dan ikuti aturan
yang lebih authoritative; jangan memilih berdasarkan tebakan.

Sebelum membuat atau mengubah specification, design, generator, module,
contract, atau struktur folder, agent wajib membaca dokumen baseline `docs/`
yang langsung terkait. Untuk generator/module minimal mencakup:

- `docs/03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md`;
- `docs/03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md`;
- `docs/06-FRAMEWORK/06.05-GENERATOR-ENGINE.md`;
- `docs/06-FRAMEWORK/06.06-STUB-ENGINE.md`; dan
- `docs/06-FRAMEWORK/06.07-CONSOLE.md`.

Ringkasan tidak boleh menggantikan struktur canonical. Jika dokumen baseline
menentukan golden structure, output generator dan specification wajib mengikuti
struktur tersebut atau mencatat ADR perubahan terlebih dahulu.

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

## Preflight Baseline dan Traceability

Sebelum membuat atau mengubah code, specification, generator, module, contract,
atau struktur folder, agent wajib membuat pemeriksaan singkat yang menjawab:

| Item | Wajib dicatat |
|---|---|
| Authoritative source | Dokumen baseline yang menjadi acuan utama |
| Downstream docs | Specification, plan, task, ADR, README, dan log yang terdampak |
| Existing code | File/class/command yang sudah memiliki behavior terkait |
| Golden structure | Struktur folder, field, contract, dan naming yang wajib diikuti |
| Dependency | Increment, package, module, atau keputusan yang menjadi prasyarat |
| Acceptance | Behavior yang harus terbukti setelah perubahan |
| Rollback trace | Commit/file yang dapat ditelusuri jika perubahan dibatalkan |

Agent tidak boleh mulai coding jika authoritative source dan golden structure
belum dicocokkan dengan specification. Jika ditemukan konflik, hentikan coding,
laporkan konflik, dan buat keputusan melalui ADR atau minta arahan user.

Setiap perubahan contract atau structure wajib memperbarui semua downstream
document terkait dalam increment yang sama. Execution log wajib menyebut source
yang dibaca, file yang berubah, alasan, evidence, dan risiko. Tujuannya agar
tim dapat melakukan review, rollback, dan audit tanpa bergantung pada riwayat
percakapan agent.

## Konfirmasi Langsung Saat Blocker

Jika agent menemukan kesulitan teknis, konflik antar dokumen, requirement yang
belum jelas, atau keputusan yang dapat mengubah arah pekerjaan, agent wajib
langsung mengonfirmasi kepada user pada saat masalah ditemukan. Jangan menunda
pertanyaan, mengisi keputusan dengan asumsi, atau melanjutkan coding melewati
blocker. Sambil menunggu jawaban, agent boleh melanjutkan pemeriksaan read-only
yang tidak mengubah arah implementasi.

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
php artisan module:validate {Domain}/{Module} --json
```

`module:inspect {Domain}/{Module}` sekarang menjadi command wajib untuk membaca
detail target module sebelum generator atau perubahan module dijalankan.
Gunakan bersama `module:discover --json`, `module:validate --json`, dan
`module:list --json`. Read-only scan tambahan hanya digunakan bila command
mengembalikan diagnostic yang membutuhkan pemeriksaan lebih dalam.
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

Controller wajib menjadi orchestration layer yang tipis. Controller boleh
menangani middleware, menerima FormRequest, memanggil Application Query/Action,
menyiapkan flash notification, dan mengembalikan response. Query Eloquent,
aturan validasi, persistence mutation, dan business rule tidak boleh ditulis
langsung di controller. Logic tersebut harus berada pada boundary yang
memilikinya dan memiliki focused test.

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

Untuk module yang memiliki alur pengguna, setiap implementasi juga wajib
memiliki vertical slice frontend yang dapat dibuka dan diuji. Minimal mencakup
page/component, route Ziggy, loading/empty/error state, permission visibility,
responsive layout, dan focused browser test. Backend tanpa UI yang dapat diuji
tidak boleh dinyatakan sebagai module selesai.

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
- frontend UI/UX tersedia untuk alur module yang memiliki pengguna;
- frontend build/type check dan browser/accessibility test yang relevan lulus;
- hasil UI dapat ditinjau langsung pada browser, bukan hanya melalui dokumen
  atau test backend.

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
