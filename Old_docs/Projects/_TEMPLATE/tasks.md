# Tasks: {Nama Project}

Setiap task harus kecil, dapat diverifikasi, dan tidak mencampur capability
yang tidak berkaitan.

## Aturan sebelum mulai

- [ ] Parent boundary, code path, namespace, owner, dependency, dan non-scope
  sudah ditulis pada specification.
- [ ] Project intake dan inventory module existing sudah dicatat.
- [ ] Prompt generator, dry-run, expected output, dan acceptance criteria sudah
  ditinjau.
- [ ] Dependency task dan checkpoint sudah jelas.

## Task 01 — {Nama capability}

**Tujuan:** {Hasil yang ingin dicapai.}

**Files:** {File atau folder yang dibaca, dibuat, atau diubah.}

**Acceptance criteria:**

- [ ] {Perilaku berhasil.}
- [ ] {Perilaku ditolak atau failure case.}
- [ ] Frontend page/component dan state UI dapat dibuka bila task memiliki alur
  pengguna.
- [ ] Browser/accessibility test tersedia bila task memiliki alur pengguna.
- [ ] Positive test dan negative test memiliki command serta hasil yang dapat
  dicatat.
- [ ] Checklist task ditinjau sebelum dan sesudah pekerjaan.

**Hasil implementasi:** {Isi setelah task selesai, termasuk tanggal.}

**Test:** `{command test}`

**Evidence:**

- Kondisi awal: `{path atau behavior sebelum perubahan}`.
- Perubahan: `{file dan perubahan yang dibuat}`.
- Alasan: `{alasan teknis}`.
- Hasil command: `{output penting}`.
- Risiko/batasan: `{risiko yang masih terbuka}`.

## Execution log

Catat setiap increment dengan format berikut. Bagian ini tidak boleh hanya
berisi kalimat "selesai".

### {YYYY-MM-DD} — {Increment atau task}

- Skill yang digunakan: `{nama skill dan alasan}`.
- Source yang dibaca: `{AGENTS, baseline, dan file existing}`.
- File yang berubah: `{path dan ringkasan perubahan}`.
- Alasan teknis: `{mengapa perubahan diperlukan}`.
- Verification: `{command, hasil penting, dan browser check bila ada}`.
- Risiko/open decision: `{status atau batasan}`.

## Task 02 — {Nama capability berikutnya}

**Tujuan:** {Hasil yang ingin dicapai.}

**Files:** {File atau folder yang terdampak.}

**Acceptance criteria:**

- [ ] {Perilaku berhasil.}
- [ ] {Perilaku ditolak atau failure case.}

**Hasil implementasi:** {Isi setelah task selesai.}

**Test:** `{command test}`

## Final quality checkpoint

- [ ] Test fokus lulus.
- [ ] Test negatif dan security impact ditinjau.
- [ ] Quality gate lulus.
- [ ] Documentation dan execution evidence diperbarui.
- [ ] Frontend build/type check dan browser/accessibility test lulus untuk task
  yang memiliki alur pengguna.
- [ ] `module:discover`, `module:validate`, dan `module:list` lulus.
- [ ] README, execution log, revision history, ADR/Open Decision, dan open risk
  sudah diperbarui.
- [ ] Code review correctness, readability, architecture, security, performance,
  dan dependency sudah dilakukan.
