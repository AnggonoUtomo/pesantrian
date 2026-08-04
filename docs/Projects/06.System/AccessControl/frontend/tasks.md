# Tasks Frontend AccessControl

## Task 01 — Contract dan inventory frontend

**Tujuan:** memastikan struktur frontend mengikuti baseline dan contoh.

**Files:** `resources/js/pages/System/AccessControl/`, Ziggy route, shared
types, `FrontendContoh/access-control/`, dan dokumen frontend.

**Acceptance criteria:**

- [ ] Folder dan ownership page ditetapkan.
- [ ] Props, role, permission group, dan ULID string typed.
- [ ] Route page memiliki nama Ziggy yang disepakati.

## Task 02 — Page dan workspace role/permission

**Tujuan:** membuat halaman utama dan komponen role/permission.

**Acceptance criteria:**

- [ ] Header, shortcut, role card, permission panel, dan summary tersedia.
- [ ] Role dapat dipilih dan permission dikelompokkan berdasarkan module.
- [ ] `SuperSystem` protected dari delete/update yang dilarang.

## Task 03 — Form, state, dan authorization visibility

**Tujuan:** memastikan UX mutation dan state aman.

**Acceptance criteria:**

- [ ] Add/delete role memakai dialog dan confirmation.
- [ ] Loading, empty, error, success, dan read-only state tersedia.
- [ ] Permission visibility memakai shared authorization context.
- [ ] Backend tetap menolak request yang tidak berwenang.

## Task 04 — Browser dan accessibility verification

**Tujuan:** membuktikan module dapat ditinjau langsung di browser.

**Acceptance criteria:**

- [ ] Critical flow role dan permission lulus di browser.
- [ ] Keyboard navigation dan focus state lulus.
- [ ] Responsive desktop/mobile ditinjau.
- [ ] Console error dan network error tidak tersisa.
- [ ] Accessibility scan relevan lulus.

## Final quality checkpoint

- [ ] Type check dan frontend build lulus.
- [ ] Positive dan negative browser flow lulus.
- [ ] Permission visibility tidak menjadi security boundary.
- [ ] Dokumentasi dan execution evidence diperbarui.
