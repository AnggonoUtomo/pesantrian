# Tasks: Evaluasi Ulang Module System

## Task 01 — Discovery dan Inventory

- [x] Memastikan status keempat module System.
  - Kondisi awal: AccessControl, UserManagement, AuditLog, dan SystemSetting telah dinyatakan selesai pada dokumen masing-masing.
  - Perubahan: discovery, validate, dan list dijalankan ulang sebelum review.
  - Evidence: empat module `enabled` dan `valid` tanpa diagnostic.
- [x] Menetapkan urutan review berdasarkan dependency.
  - Perubahan: AccessControl → UserManagement → AuditLog → SystemSetting.
  - Alasan: capability authorization menjadi fondasi module berikutnya.

## Task 02 — Review AccessControl

- [x] Review correctness, architecture, security, performance, test, dan UI.
  - File fokus: manifest, public capability, Action/Query, policy, routes, frontend, test, dan dokumen AccessControl.
  - Perubahan: temuan disimpan pada `findings-access-control.md` tanpa mengubah behavior module.
  - Evidence: `module:inspect` valid, focused suite lulus 37 test/158 assertion, dan browser desktop memuat halaman tanpa console error/warning.
  - Hasil: dua temuan `required`, dua kandidat `optional`, dan satu catatan `FYI` telah memiliki owner serta rekomendasi.

## Task 03 — Review UserManagement

- [x] Review lifecycle user, login, role assignment, impersonation, dan scope lanjutan.
  - Kondisi awal: UserManagement menyediakan status `active`, `inactive`, `suspended`, soft delete, role tunggal, dan impersonation.
  - Perubahan: temuan disimpan pada `findings-user-management.md` tanpa mengubah behavior module.
  - Evidence: `module:inspect` valid, focused suite lulus 33 test/189 assertion, dan browser desktop memuat halaman tanpa console error/warning.
  - Hasil: empat temuan `required`, dua kandidat `optional`, dan satu catatan `FYI` telah memiliki owner serta rekomendasi.

## Task 04 — Review AuditLog

- [x] Review contract audit, ingestion event, redaction, scope, retention, dan UI.
  - Kondisi awal: AuditLog telah dinyatakan selesai, tetapi belum ditinjau ulang
    bersama module System lain.
  - Perubahan: evidence dan temuan dicatat pada `findings-audit-log.md`.
  - Alasan: audit adalah consumer lintas module dan harus diperiksa untuk
    redaction, immutability, serta lifecycle retention.
  - Evidence: `module:inspect System/AuditLog --json` valid; focused suite
    AuditLog lulus 23 test/180 assertion; browser desktop sebelumnya memuat
    filter, pagination, dan detail tanpa console error/warning.
  - Acceptance: consumer lintas module tidak memasukkan payload sensitif atau
    business rule ke AuditLog. Temuan required tetap menjadi backlog hardening.

## Task 05 — Review SystemSetting

- [x] Review registry, runtime activation, command, UI, dan consumer runtime.
  - Kondisi awal: registry belum mencatat konfigurasi mail dan secret masih perlu dibedakan dari setting biasa.
  - Perubahan: registry menambah konfigurasi SMTP/MailHog; tipe `secret` disimpan terenkripsi, dimasking pada UI, dan teredaksi pada audit.
  - Evidence: persistence SystemSetting lulus; browser invitation memakai runtime SMTP dan MailHog menerima pesan.

## Task 06 — Peta Dependency dan Backlog

- [x] Menutup evaluasi dengan rekomendasi urutan implementasi.
  - Perubahan: backlog dipisahkan menjadi capability UserManagement, hardening internal, dan release gate migration shared/production.
  - Evidence: invitation dan multi-role memiliki contract, policy, UI, test, audit redaction, serta trace rollback; migration target tetap berada pada runbook operator.

## Definition of Done Evaluasi

- [x] Keempat module telah direview dengan evidence nyata.
- [x] Temuan required telah dipisahkan dari improvement optional.
- [x] Kandidat lintas module memiliki owner dan arah dependency.
- [x] Open Decision, ADR, dan backlog implementasi telah dicatat.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan checklist evaluasi empat module System |
| 1.1 | 2026-08-06 | Mencatat hasil review UserManagement dan membersihkan karakter rusak |
