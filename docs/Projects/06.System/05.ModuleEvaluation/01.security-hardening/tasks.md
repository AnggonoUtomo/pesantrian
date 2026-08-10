# Tasks: Security Hardening System

## Checklist

- [x] 01. Preflight dan ADR disetujui.
  - Kondisi awal: temuan required ada pada evaluasi tiga module.
  - Perubahan: keputusan lifecycle, permission, dan audit dicatat pada ADR-0001.
  - Evidence: persetujuan user pada 10 Agustus 2026.

- [x] 02. Tutup lifecycle login dan impersonation UserManagement.
  - Kondisi awal: Fortify menerima status user apa pun dan impersonation hanya
    menolak target SuperSystem.
  - Perubahan: `FortifyServiceProvider`, `EnsureActiveUser`, `User`, policy,
    Action impersonation, dan factory diselaraskan dengan status `active`.
  - Alasan: lifecycle akun harus menjadi security boundary backend, bukan hanya
    badge UI.
  - Evidence: focused Auth dan impersonation suite lulus 13 test/63 assertion;
    test baru membuktikan login dan session target nonaktif ditolak.

- [x] 03. Selaraskan permission mutation AccessControl.
  - Kondisi awal: sync permission memakai `role.manage`; actor permission
    assign tidak dapat memakai workflow yang menjadi miliknya.
  - Perubahan: service authorization membedakan view, role mutation, dan
    permission sync; policy serta UI memakai capability yang tepat.
  - Alasan: vocabulary permission harus sama pada manifest, backend, dan UX.
  - Evidence: AccessControl 15 test/66 assertion, ESLint, dan TypeScript lulus.

- [x] 04. Tolak reason audit sensitif dan perkuat append-only code boundary.
  - Kondisi awal: MetadataRedactor hanya membersihkan HTML/control character;
    AuditRecord memakai `$guarded = []`.
  - Perubahan: reason credential memicu exception domain yang diterjemahkan
    menjadi error validasi; AuditRecord memakai allowlist fillable dan
    repository mendapat architecture test tanpa update/delete.
  - Alasan: audit append-only tidak boleh menjadi tempat penyimpanan secret.
  - Evidence: focused AuditLog/UserManagement suite lulus 17 test/71 assertion;
    Pint dan `git diff --check` lulus.

- [x] 05. Quality checkpoint dan traceability.
  - Kondisi awal: generator dapat gagal tidak konsisten ketika full suite
    menjalankan promotion directory pada Windows.
  - Perubahan: promotion atomic mendapat maksimal 20 retry dengan backoff
    terbatas untuk Windows; generator test memakai Artisan in-process dengan
    app/storage path temporary OS; assertion conflict menyimpan output
    diagnostic aman.
  - Evidence: `composer ci:check` lulus dengan 266 test/1159 assertion;
    discovery dan validate empat module lulus; `git diff --check` lulus.
  - Evidence tambahan: Chrome DevTools berhasil membuka `/system/users` setelah
    login sebagai `Security Admin Demo`; snapshot memuat User Management,
    filter, pagination, dan kontrol lifecycle.
  - Risiko: blocker browser tertutup. Hak database runtime minimum tetap
    berada di luar workspace dan menjadi tanggung jawab operasi.

## Definition of Done

- [x] Semua acceptance security terbukti oleh test positif dan negatif.
  - Evidence: lifecycle login/session/impersonation, permission sync, reason
    credential, dan append-only memiliki focused test serta full CI hijau.
- [x] Dokumentasi evaluasi serta temuan terkait diperbarui tanpa checklist palsu.
  - Evidence: ADR, task, execution log, dan tiga temuan module diperbarui pada
    increment yang sama.
- [x] Risiko operasi database production memiliki owner dan batasan jelas.
  - Batasan: hak database minimum privilege, backup, dan rehearsal production
    berada di owner DevOps dan tidak dapat dibuktikan dari workspace.
