# Tasks: AccessControl Module

Setiap task harus kecil, dapat diverifikasi, dan tidak mencampur capability
UserManagement, AuditLog, atau SystemSetting.

## Task 01 — Namespace dan boundary module

**Tujuan:** menetapkan `System/AccessControl` sebagai module authorization
baseline dengan namespace yang konsisten.

**Files:** folder `docs/Projects/06.System/AccessControl/`, ADR namespace,
manifest module, dan README module.

**Acceptance criteria:**

- [ ] Namespace `App\Modules\System\AccessControl` disepakati.
- [ ] Path `app/Modules/System/AccessControl` tidak bentrok dengan module valid.
- [ ] Ownership capability authorization dan permission tercatat.

**Hasil implementasi:** Belum dikerjakan.

**Test:** `php artisan module:discover --json`

## Task 02 — Module skeleton dan manifest

**Tujuan:** membuat struktur module dari profile generator dan memastikan
manifest tervalidasi registry.

**Files:** `app/Modules/System/AccessControl/`, module manifest, provider,
runtime config, permission identity, dan README module.

**Acceptance criteria:**

- [ ] Struktur golden module tersedia.
- [ ] `module.json` dan `module.php` valid.
- [ ] Module tidak menimpa module existing.

**Hasil implementasi:** Belum dikerjakan.

**Test:** `php artisan module:make AccessControl --domain=System --dry-run --json`

## Task 03 — Permission identity

**Tujuan:** menetapkan dan memvalidasi permission identity yang dimiliki
`AccessControl`.

**Files:** `permissions.php`, permission contract, validator, dan test.

**Acceptance criteria:**

- [ ] Permission key mengikuti format yang disepakati.
- [ ] Owner module adalah `AccessControl`.
- [ ] Duplicate permission ditolak.
- [ ] Permission sensitif diberi metadata yang tepat.

**Hasil implementasi:** Belum dikerjakan.

**Test:** focused permission contract test.

## Task 04 — Public authorization capability

**Tujuan:** menyediakan contract typed untuk pemeriksaan authorization oleh
module lain.

**Files:** public contract, DTO/result, adapter Spatie internal, service, dan
contract test.

**Acceptance criteria:**

- [ ] Actor berizin dapat melewati pemeriksaan.
- [ ] Actor tanpa izin ditolak.
- [ ] Module pemanggil tidak mengimpor private model atau repository.
- [ ] Hasil capability typed dan tidak memuat data sensitif.

**Hasil implementasi:** Belum dikerjakan.

**Test:** positive dan negative authorization contract test.

## Task 05 — Integration, security, dan quality gate

**Tujuan:** memastikan module dapat dipakai dalam flow Laravel dan aman sebagai
security authority.

**Files:** middleware/policy integration, authorization context, tests,
README, dan execution evidence.

**Acceptance criteria:**

- [ ] Server-side denial terbukti.
- [ ] Frontend context hanya digunakan untuk UX.
- [ ] Discovery, validation, list, dan test lulus.
- [ ] Forbidden dependency dan sensitive output scan bersih.

**Hasil implementasi:** Belum dikerjakan.

**Test:** full relevant quality gate.

## Final quality checkpoint

- [ ] Inventory sebelum perubahan tersedia.
- [ ] Positive dan negative test tersedia.
- [ ] Authorization, security, audit, dan dependency impact ditinjau.
- [ ] Module discovery/validation/list lulus.
- [ ] Documentation dan execution evidence diperbarui.
- [ ] Open risk dilaporkan atau ditutup.
