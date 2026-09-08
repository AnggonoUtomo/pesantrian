# API Publik

API publik penuh belum menjadi scope release awal SakaSantri. Baseline routing
frontend memakai Laravel named routes melalui Ziggy untuk Inertia React.

## Konvensi Saat Ini

- Frontend routing: Ziggy conventional named routes.
- Authentication: Laravel Starter Kit + Fortify.
- Authorization: backend policy/permission milik module.
- Identifier resource aplikasi: ULID.
- Security authority: backend.

## Endpoint API v1 Aktif

| Method | Endpoint | Route name | Authorization | Idempotency | Response |
| --- | --- | --- | --- | --- | --- |
| GET | `/api/v1/audit-logs` | `api.v1.audit-logs.index` | Audit log view policy | Tidak | Envelope sukses dengan daftar audit dan pagination meta |
| GET | `/api/v1/audit-logs/{auditLog}` | `api.v1.audit-logs.show` | Audit log view policy | Tidak | Envelope sukses dengan detail audit |
| GET | `/api/v1/academic/class-groups` | `api.v1.academic.class-groups.index` | `kelas_rombel.view` | Tidak | Envelope sukses dengan daftar kelas/rombel dan pagination meta |
| POST | `/api/v1/academic/class-groups` | `api.v1.academic.class-groups.store` | `kelas_rombel.manage` | Ya | Envelope sukses `201` dengan rombel yang dibuat |
| POST | `/api/v1/academic/class-groups/curricula` | `api.v1.academic.class-groups.curricula.store` | `kelas_rombel.manage` | Ya | Envelope sukses `201` dengan kurikulum yang dibuat |
| PATCH | `/api/v1/academic/class-groups/curricula/{curriculum}` | `api.v1.academic.class-groups.curricula.update` | `kelas_rombel.manage` | Ya | Envelope sukses dengan kurikulum yang diperbarui |
| POST | `/api/v1/academic/class-groups/levels` | `api.v1.academic.class-groups.levels.store` | `kelas_rombel.manage` | Ya | Envelope sukses `201` dengan level kelas yang dibuat |
| PATCH | `/api/v1/academic/class-groups/levels/{level}` | `api.v1.academic.class-groups.levels.update` | `kelas_rombel.manage` | Ya | Envelope sukses dengan level kelas yang diperbarui |
| PATCH | `/api/v1/academic/class-groups/{classGroup}` | `api.v1.academic.class-groups.update` | `kelas_rombel.manage` | Ya | Envelope sukses dengan rombel yang diperbarui |
| GET | `/api/v1/academic/class-groups/{classGroup}` | `api.v1.academic.class-groups.show` | `kelas_rombel.view` | Tidak | Envelope sukses dengan detail rombel, siswa, dan wali kelas |
| PATCH | `/api/v1/academic/class-groups/{classGroup}/archive` | `api.v1.academic.class-groups.archive` | `kelas_rombel.archive` | Ya | Envelope sukses dengan rombel yang diarsipkan |
| POST | `/api/v1/academic/class-groups/{classGroup}/homerooms` | `api.v1.academic.class-groups.homerooms.store` | `kelas_rombel.manage` | Ya | Envelope sukses `201` dengan penugasan wali kelas |
| PATCH | `/api/v1/academic/class-groups/{classGroup}/homerooms/{homeroom}/end` | `api.v1.academic.class-groups.homerooms.end` | `kelas_rombel.manage` | Ya | Envelope sukses dengan penugasan wali kelas yang diakhiri |
| PATCH | `/api/v1/academic/class-groups/{classGroup}/restore` | `api.v1.academic.class-groups.restore` | `kelas_rombel.archive` | Ya | Envelope sukses dengan rombel yang dipulihkan |
| POST | `/api/v1/academic/class-groups/{classGroup}/students` | `api.v1.academic.class-groups.students.store` | `kelas_rombel.placement` | Ya | Envelope sukses `201` dengan penempatan santri ke rombel |
| PATCH | `/api/v1/academic/class-groups/{classGroup}/students/{placement}/remove` | `api.v1.academic.class-groups.students.remove` | `kelas_rombel.placement` | Ya | Envelope sukses dengan penempatan santri yang diakhiri |
| PATCH | `/api/v1/academic/class-groups/{classGroup}/students/{placement}/transfer` | `api.v1.academic.class-groups.students.transfer` | `kelas_rombel.placement` | Ya | Envelope sukses dengan perpindahan santri antar rombel |
| GET | `/api/v1/academic/periods/terms` | `api.v1.academic.periods.terms.index` | `academic_period.view` | Tidak | Envelope sukses dengan daftar term akademik dan pagination meta |
| GET | `/api/v1/academic/periods/terms/current` | `api.v1.academic.periods.terms.current` | `academic_period.view` | Tidak | Envelope sukses dengan term akademik aktif global atau `null` |
| POST | `/api/v1/academic/periods/terms` | `api.v1.academic.periods.terms.store` | `academic_period.manage` | Ya | Envelope sukses `201` dengan term akademik yang dibuat |
| PATCH | `/api/v1/academic/periods/terms/{term}/activate` | `api.v1.academic.periods.terms.activate` | `academic_period.manage` | Ya | Envelope sukses dengan term akademik aktif; menonaktifkan active term sebelumnya |
| PATCH | `/api/v1/academic/periods/terms/{term}/close` | `api.v1.academic.periods.terms.close` | `academic_period.manage` | Ya | Envelope sukses dengan term akademik closed dan `is_active=false` |
| PATCH | `/api/v1/academic/periods/terms/{term}` | `api.v1.academic.periods.terms.update` | `academic_period.manage` | Ya | Envelope sukses dengan term akademik yang diperbarui |
| GET | `/api/v1/academic/periods/years` | `api.v1.academic.periods.years.index` | `academic_period.view` | Tidak | Envelope sukses dengan daftar tahun akademik dan pagination meta |
| POST | `/api/v1/academic/periods/years` | `api.v1.academic.periods.years.store` | `academic_period.manage` | Ya | Envelope sukses `201` dengan tahun akademik yang dibuat |
| PATCH | `/api/v1/academic/periods/years/{year}` | `api.v1.academic.periods.years.update` | `academic_period.manage` | Ya | Envelope sukses dengan tahun akademik yang diperbarui |
| GET | `/api/v1/human-resource/employees` | `api.v1.human-resource.employees.index` | `human_resource.view` | Tidak | Envelope sukses dengan daftar employee dan pagination meta |
| POST | `/api/v1/human-resource/employees` | `api.v1.human-resource.employees.store` | `human_resource.manage` | Ya | Envelope sukses `201` dengan employee yang dibuat |
| PATCH | `/api/v1/human-resource/employees/{employee}` | `api.v1.human-resource.employees.update` | `human_resource.manage` | Ya | Envelope sukses dengan employee yang diperbarui |
| PATCH | `/api/v1/human-resource/employees/{employee}/activate` | `api.v1.human-resource.employees.activate` | `human_resource.manage` | Ya | Envelope sukses dengan employee aktif dan `left_on=null` |
| PATCH | `/api/v1/human-resource/employees/{employee}/deactivate` | `api.v1.human-resource.employees.deactivate` | `human_resource.manage` | Ya | Envelope sukses dengan employee inactive; ditolak bila assignment aktif masih terbuka |
| POST | `/api/v1/human-resource/employees/{employee}/unit-assignments` | `api.v1.human-resource.employees.unit-assignments.store` | `human_resource.manage` | Ya | Envelope sukses `201` dengan assignment unit employee yang dibuat |
| DELETE | `/api/v1/impersonation` | `api.v1.impersonation.destroy` | Authenticated user | Ya | Envelope sukses tanpa data |
| GET | `/api/v1/organization/units` | `api.v1.organization.units.index` | `organization.view` | Tidak | Envelope sukses dengan daftar unit dan pagination meta |
| POST | `/api/v1/organization/units` | `api.v1.organization.units.store` | `organization.manage` | Ya | Envelope sukses `201` dengan unit yang dibuat |
| PATCH | `/api/v1/organization/units/{unit}` | `api.v1.organization.units.update` | `organization.manage` | Ya | Envelope sukses dengan unit yang diperbarui |
| GET | `/api/v1/pesantrian/admissions` | `api.v1.pesantrian.admissions.index` | `penerimaan_santri.view` | Tidak | Envelope sukses dengan daftar pendaftar dan pagination meta |
| POST | `/api/v1/pesantrian/admissions` | `api.v1.pesantrian.admissions.store` | `penerimaan_santri.manage` | Ya | Envelope sukses `201` dengan pendaftar yang dibuat |
| PATCH | `/api/v1/pesantrian/admissions/{admission}` | `api.v1.pesantrian.admissions.update` | `penerimaan_santri.manage` | Ya | Envelope sukses dengan pendaftar yang diperbarui |
| PATCH | `/api/v1/pesantrian/admissions/{admission}/accept` | `api.v1.pesantrian.admissions.accept` | `penerimaan_santri.decide` | Ya | Envelope sukses dengan pendaftar diterima |
| PATCH | `/api/v1/pesantrian/admissions/{admission}/cancel` | `api.v1.pesantrian.admissions.cancel` | `penerimaan_santri.manage` | Ya | Envelope sukses dengan pendaftar dibatalkan |
| PATCH | `/api/v1/pesantrian/admissions/{admission}/reject` | `api.v1.pesantrian.admissions.reject` | `penerimaan_santri.decide` | Ya | Envelope sukses dengan pendaftar ditolak |
| PATCH | `/api/v1/pesantrian/admissions/{admission}/verify` | `api.v1.pesantrian.admissions.verify` | `penerimaan_santri.manage` | Ya | Envelope sukses dengan pendaftar terverifikasi |
| GET | `/api/v1/pesantrian/asrama` | `api.v1.pesantrian.asrama.index` | `asrama.view` | Tidak | Envelope sukses dengan daftar asrama dan pagination meta |
| POST | `/api/v1/pesantrian/asrama` | `api.v1.pesantrian.asrama.store` | `asrama.manage` | Ya | Envelope sukses `201` dengan asrama yang dibuat |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}` | `api.v1.pesantrian.asrama.update` | `asrama.manage` | Ya | Envelope sukses dengan asrama yang diperbarui |
| GET | `/api/v1/pesantrian/asrama/{dormitory}` | `api.v1.pesantrian.asrama.show` | `asrama.view` | Tidak | Envelope sukses dengan detail asrama, kamar, penghuni, dan pembina |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/archive` | `api.v1.pesantrian.asrama.archive` | `asrama.archive` | Ya | Envelope sukses dengan asrama yang diarsipkan |
| POST | `/api/v1/pesantrian/asrama/{dormitory}/placements` | `api.v1.pesantrian.asrama.placements.store` | `asrama.placement` | Ya | Envelope sukses `201` dengan penempatan santri ke kamar |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/remove` | `api.v1.pesantrian.asrama.placements.remove` | `asrama.placement` | Ya | Envelope sukses dengan penempatan kamar yang diakhiri |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/transfer` | `api.v1.pesantrian.asrama.placements.transfer` | `asrama.placement` | Ya | Envelope sukses dengan perpindahan kamar santri |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/restore` | `api.v1.pesantrian.asrama.restore` | `asrama.archive` | Ya | Envelope sukses dengan asrama yang dipulihkan |
| POST | `/api/v1/pesantrian/asrama/{dormitory}/rooms` | `api.v1.pesantrian.asrama.rooms.store` | `asrama.manage` | Ya | Envelope sukses `201` dengan kamar yang dibuat |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/rooms/{room}` | `api.v1.pesantrian.asrama.rooms.update` | `asrama.manage` | Ya | Envelope sukses dengan kamar yang diperbarui |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/archive` | `api.v1.pesantrian.asrama.rooms.archive` | `asrama.archive` | Ya | Envelope sukses dengan kamar yang diarsipkan |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/restore` | `api.v1.pesantrian.asrama.rooms.restore` | `asrama.archive` | Ya | Envelope sukses dengan kamar yang dipulihkan |
| POST | `/api/v1/pesantrian/asrama/{dormitory}/supervisors` | `api.v1.pesantrian.asrama.supervisors.store` | `asrama.supervisor` | Ya | Envelope sukses `201` dengan pembina asrama yang ditugaskan |
| PATCH | `/api/v1/pesantrian/asrama/{dormitory}/supervisors/{assignment}/end` | `api.v1.pesantrian.asrama.supervisors.end` | `asrama.supervisor` | Ya | Envelope sukses dengan penugasan pembina yang diakhiri |
| GET | `/api/v1/pesantrian/student-attendances` | `api.v1.pesantrian.student-attendances.index` | `presensi_santri.view` | Tidak | Envelope sukses dengan daftar sesi presensi dan pagination meta |
| GET | `/api/v1/pesantrian/student-attendances/{attendance}` | `api.v1.pesantrian.student-attendances.show` | `presensi_santri.view` | Tidak | Envelope sukses dengan detail sesi, summary, dan entries |
| POST | `/api/v1/pesantrian/student-attendances` | `api.v1.pesantrian.student-attendances.store` | `presensi_santri.manage` | Ya | Envelope sukses `201` dengan sesi draft presensi yang dibuat |
| PATCH | `/api/v1/pesantrian/student-attendances/{attendance}` | `api.v1.pesantrian.student-attendances.update` | `presensi_santri.manage` | Ya | Envelope sukses dengan sesi draft presensi yang diperbarui |
| PATCH | `/api/v1/pesantrian/student-attendances/{attendance}/entries` | `api.v1.pesantrian.student-attendances.entries.update` | `presensi_santri.manage` | Ya | Envelope sukses dengan entry presensi yang di-upsert |
| PATCH | `/api/v1/pesantrian/student-attendances/{attendance}/submit` | `api.v1.pesantrian.student-attendances.submit` | `presensi_santri.submit` | Ya | Envelope sukses dengan sesi presensi berstatus submitted |
| PATCH | `/api/v1/pesantrian/student-attendances/{attendance}/revise` | `api.v1.pesantrian.student-attendances.revise` | `presensi_santri.revise` | Ya | Envelope sukses dengan sesi presensi dibuka untuk revisi |
| PATCH | `/api/v1/pesantrian/student-attendances/{attendance}/void` | `api.v1.pesantrian.student-attendances.void` | `presensi_santri.archive` | Ya | Envelope sukses dengan sesi presensi berstatus void tanpa menghapus entry |
| GET | `/api/v1/pesantrian/student-permits` | `api.v1.pesantrian.student-permits.index` | `perizinan_santri.view` | Tidak | Envelope sukses dengan daftar izin santri dan pagination meta |
| POST | `/api/v1/pesantrian/student-permits` | `api.v1.pesantrian.student-permits.store` | `perizinan_santri.manage` | Ya | Envelope sukses `201` dengan permohonan izin draft yang dibuat |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}` | `api.v1.pesantrian.student-permits.update` | `perizinan_santri.manage` | Ya | Envelope sukses dengan permohonan izin draft yang diperbarui dan revision tercatat |
| GET | `/api/v1/pesantrian/student-permits/{permit}` | `api.v1.pesantrian.student-permits.show` | `perizinan_santri.view` | Tidak | Envelope sukses dengan detail izin, snapshot santri/wali, lifecycle, summary, dan revisions |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/approve` | `api.v1.pesantrian.student-permits.approve` | `perizinan_santri.approve` | Ya | Envelope sukses dengan permohonan izin berstatus approved dan decision tercatat |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/checkout` | `api.v1.pesantrian.student-permits.checkout` | `perizinan_santri.checkout` | Ya | Envelope sukses dengan izin berstatus checked_out, actor check-out, revision, dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/reject` | `api.v1.pesantrian.student-permits.reject` | `perizinan_santri.approve` | Ya | Envelope sukses dengan permohonan izin berstatus rejected dan alasan tercatat |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/return` | `api.v1.pesantrian.student-permits.return` | `perizinan_santri.return` | Ya | Envelope sukses dengan izin berstatus returned, catatan kembali, summary keterlambatan, revision, dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/submit` | `api.v1.pesantrian.student-permits.submit` | `perizinan_santri.manage` | Ya | Envelope sukses dengan permohonan izin berstatus submitted bila tidak overlap izin aktif |
| PATCH | `/api/v1/pesantrian/student-permits/{permit}/void` | `api.v1.pesantrian.student-permits.void` | `perizinan_santri.archive` | Ya | Envelope sukses dengan izin berstatus void tanpa menghapus data, alasan pembatalan, revision, dan audit tercatat |
| GET | `/api/v1/pesantrian/student-discipline-categories` | `api.v1.pesantrian.student-discipline-categories.index` | `kedisiplinan_santri.view` | Tidak | Envelope sukses dengan daftar kategori kedisiplinan santri |
| POST | `/api/v1/pesantrian/student-discipline-categories` | `api.v1.pesantrian.student-discipline-categories.store` | `kedisiplinan_santri.manage` | Ya | Envelope sukses `201` dengan kategori kedisiplinan yang dibuat dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-discipline-categories/{category}` | `api.v1.pesantrian.student-discipline-categories.update` | `kedisiplinan_santri.manage` | Ya | Envelope sukses dengan kategori kedisiplinan yang diperbarui dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-discipline-categories/{category}/archive` | `api.v1.pesantrian.student-discipline-categories.archive` | `kedisiplinan_santri.archive` | Ya | Envelope sukses dengan kategori berstatus archived tanpa menghapus histori kasus |
| GET | `/api/v1/pesantrian/student-discipline-cases` | `api.v1.pesantrian.student-discipline-cases.index` | `kedisiplinan_santri.view` | Tidak | Envelope sukses dengan daftar kasus kedisiplinan, filter, dan pagination meta |
| POST | `/api/v1/pesantrian/student-discipline-cases` | `api.v1.pesantrian.student-discipline-cases.store` | `kedisiplinan_santri.manage` | Ya | Envelope sukses `201` dengan draft kasus, snapshot santri/kategori/pembina, revision, dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-discipline-cases/{case}` | `api.v1.pesantrian.student-discipline-cases.update` | `kedisiplinan_santri.manage` | Ya | Envelope sukses dengan draft kasus yang diperbarui, revision, dan audit tercatat |
| GET | `/api/v1/pesantrian/student-discipline-cases/{case}` | `api.v1.pesantrian.student-discipline-cases.show` | `kedisiplinan_santri.view` | Tidak | Envelope sukses dengan detail kasus, snapshot santri, kategori, lifecycle, action, resolution, dan revision history |
| PATCH | `/api/v1/pesantrian/student-discipline-cases/{case}/assign-action` | `api.v1.pesantrian.student-discipline-cases.assign-action` | `kedisiplinan_santri.review` | Ya | Envelope sukses dengan tindakan pembinaan, pembina opsional, waktu assignment, revision, dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-discipline-cases/{case}/review` | `api.v1.pesantrian.student-discipline-cases.review` | `kedisiplinan_santri.review` | Ya | Envelope sukses dengan kasus berstatus in_review, catatan review, actor review, revision, dan audit tercatat |
| PATCH | `/api/v1/pesantrian/student-discipline-cases/{case}/submit` | `api.v1.pesantrian.student-discipline-cases.submit` | `kedisiplinan_santri.manage` | Ya | Envelope sukses dengan kasus berstatus submitted bila masih draft, santri aktif, dan kategori aktif |
| GET | `/api/v1/pesantrian/students` | `api.v1.pesantrian.students.index` | `santri.view` | Tidak | Envelope sukses dengan daftar data induk santri dan pagination meta |
| POST | `/api/v1/pesantrian/students` | `api.v1.pesantrian.students.store` | `santri.manage` | Ya | Envelope sukses `201` dengan data induk santri yang dibuat |
| POST | `/api/v1/pesantrian/students/from-admission/{admission}` | `api.v1.pesantrian.students.from-admission` | `santri.manage` | Ya | Envelope sukses `201` dengan santri hasil konversi PPDB |
| GET | `/api/v1/pesantrian/students/{student}` | `api.v1.pesantrian.students.show` | `santri.view` | Tidak | Envelope sukses dengan detail data induk santri dan wali |
| PATCH | `/api/v1/pesantrian/students/{student}` | `api.v1.pesantrian.students.update` | `santri.manage` | Ya | Envelope sukses dengan data induk santri yang diperbarui |
| PATCH | `/api/v1/pesantrian/students/{student}/archive` | `api.v1.pesantrian.students.archive` | `santri.archive` | Ya | Envelope sukses dengan data santri yang diarsipkan |
| PATCH | `/api/v1/pesantrian/students/{student}/lifecycle` | `api.v1.pesantrian.students.lifecycle` | `santri.lifecycle` | Ya | Envelope sukses dengan status lifecycle santri yang diperbarui |
| PATCH | `/api/v1/pesantrian/students/{student}/restore` | `api.v1.pesantrian.students.restore` | `santri.archive` | Ya | Envelope sukses dengan data santri yang dipulihkan |
| GET | `/api/v1/pesantrian/tahfidz` | `api.v1.pesantrian.tahfidz.index` | `tahfidz.view` | Tidak | Envelope sukses dengan daftar setoran tahfidz dan pagination meta |
| POST | `/api/v1/pesantrian/tahfidz/programs` | `api.v1.pesantrian.tahfidz.programs.store` | `tahfidz.manage` | Ya | Envelope sukses `201` dengan program tahfidz yang dibuat |
| PATCH | `/api/v1/pesantrian/tahfidz/programs/{program}` | `api.v1.pesantrian.tahfidz.programs.update` | `tahfidz.manage` | Ya | Envelope sukses dengan program tahfidz yang diperbarui |
| POST | `/api/v1/pesantrian/tahfidz/targets` | `api.v1.pesantrian.tahfidz.targets.store` | `tahfidz.manage` | Ya | Envelope sukses `201` dengan target hafalan berisi snapshot santri dan periode |
| PATCH | `/api/v1/pesantrian/tahfidz/targets/{target}` | `api.v1.pesantrian.tahfidz.targets.update` | `tahfidz.manage` | Ya | Envelope sukses dengan target hafalan yang diperbarui |
| POST | `/api/v1/pesantrian/tahfidz/submissions` | `api.v1.pesantrian.tahfidz.submissions.store` | `tahfidz.record` | Ya | Envelope sukses `201` dengan setoran tahfidz/murojaah berisi snapshot santri dan pembimbing |
| PATCH | `/api/v1/pesantrian/tahfidz/submissions/{submission}` | `api.v1.pesantrian.tahfidz.submissions.update` | `tahfidz.record` | Ya | Envelope sukses dengan setoran tahfidz/murojaah draft atau submitted yang diperbarui |
| PATCH | `/api/v1/pesantrian/tahfidz/submissions/{submission}/review` | `api.v1.pesantrian.tahfidz.submissions.review` | `tahfidz.review` | Ya | Envelope sukses dengan setoran berstatus accepted atau needs_revision dan revision history |
| PATCH | `/api/v1/pesantrian/tahfidz/submissions/{submission}/void` | `api.v1.pesantrian.tahfidz.submissions.void` | `tahfidz.archive` | Ya | Envelope sukses dengan setoran berstatus void tanpa menghapus data |
| GET | `/api/v1/pesantrian/tahfidz/{submission}` | `api.v1.pesantrian.tahfidz.show` | `tahfidz.view` | Tidak | Envelope sukses dengan detail setoran, target, pembimbing, summary, dan histori revisi |
| GET | `/api/v1/permissions` | `api.v1.permissions.index` | Access control view policy | Tidak | Envelope sukses dengan daftar permission dan pagination meta |
| GET | `/api/v1/roles` | `api.v1.roles.index` | Access control view policy | Tidak | Envelope sukses dengan daftar role dan pagination meta |
| POST | `/api/v1/roles` | `api.v1.roles.store` | Access control create policy | Ya | Envelope sukses `201` dengan role yang dibuat |
| GET | `/api/v1/roles/{role}` | `api.v1.roles.show` | Access control view policy | Tidak | Envelope sukses dengan detail role |
| PATCH | `/api/v1/roles/{role}` | `api.v1.roles.update` | Access control view policy dan mutation rule | Ya | Envelope sukses dengan role yang diperbarui |
| DELETE | `/api/v1/roles/{role}` | `api.v1.roles.destroy` | Access control view policy dan mutation rule | Ya | Envelope sukses tanpa data |
| GET | `/api/v1/system-settings` | `api.v1.system-settings.index` | System setting view policy | Tidak | Envelope sukses dengan daftar setting |
| PATCH | `/api/v1/system-settings/{key}` | `api.v1.system-settings.update` | System setting update policy | Ya | Envelope sukses dengan setting yang diperbarui |
| GET | `/api/v1/users` | `api.v1.users.index` | User view policy | Tidak | Envelope sukses dengan daftar user dan pagination meta |
| POST | `/api/v1/users` | `api.v1.users.store` | User create policy | Ya | Envelope sukses `201` dengan user yang dibuat |
| GET | `/api/v1/users/{user}` | `api.v1.users.show` | User view policy | Tidak | Envelope sukses dengan detail user |
| PATCH | `/api/v1/users/{user}` | `api.v1.users.update` | User mutate policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}` | `api.v1.users.destroy` | User delete policy | Ya | Envelope sukses tanpa data |
| POST | `/api/v1/users/{user}/impersonation` | `api.v1.users.impersonation.store` | User impersonation policy | Ya | Envelope sukses dengan state impersonation |
| POST | `/api/v1/users/{user}/permissions` | `api.v1.users.permissions.store` | User direct permission policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}/permissions/{permission}` | `api.v1.users.permissions.destroy` | User direct permission policy | Ya | Envelope sukses dengan user yang diperbarui |
| POST | `/api/v1/users/{user}/roles` | `api.v1.users.roles.store` | User role assignment policy | Ya | Envelope sukses dengan user yang diperbarui |
| DELETE | `/api/v1/users/{user}/roles/{role}` | `api.v1.users.roles.destroy` | User role assignment policy | Ya | Envelope sukses dengan user yang diperbarui |

Contoh named route frontend:

```ts
router.visit(route('students.show', student.id))
```

## Jika API Publik Ditambahkan

Sebelum menambah endpoint publik, buat atau perbarui specification/work item dan
catat contract berikut:

| Contract | Wajib dicatat |
| --- | --- |
| Base path | Prefix endpoint dan versioning bila ada |
| Authentication | Guard, token/session mechanism, dan CSRF/CORS bila relevan |
| Authorization | Permission/policy/resource rule backend |
| Request | DTO/schema, validasi, idempotency untuk mutation |
| Response | DTO/resource, pagination, error format |
| Audit | Event atau audit entry yang dibuat |
| Test | Focused API/feature test |

Endpoint tidak boleh ditambahkan hanya karena UI membutuhkan data internal.
Untuk Inertia, gunakan route dan controller Presentation module sesuai baseline.

## Candidate Internal Lookup Contract

`HumanResource/HumanResource` memiliki documented candidate contract untuk
employee lookup aktif, tetapi belum memiliki runtime contract publik sampai
consumer pertama disetujui. Consumer seperti Academic, Dormitory, Communication,
atau Reporting harus memakai contract `Application/Contracts`/`Application/DTO`
ketika tersedia, bukan model Infrastructure module HR.
