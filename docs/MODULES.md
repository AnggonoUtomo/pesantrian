# Daftar Modul

Dokumen ini menjadi indeks ownership dan dependency. Detail module dibuat pada
`modules/{Domain}/{Module}/` ketika module memperoleh pekerjaan signifikan baru.

| Domain | Module         | Tanggung jawab                                     | Dependency utama | Status |
| ------ | -------------- | -------------------------------------------------- | ---------------- | ------ |
| System | AccessControl  | Role, permission, dan capability authorization     | StarterKit       | Aktif  |
| System | UserManagement | Lifecycle user, role assignment, dan impersonation | AccessControl    | Aktif  |
| System | AuditLog       | Pencatatan dan pembacaan aktivitas                 | AccessControl    | Aktif  |
| System | SystemSetting  | Setting aplikasi dan runtime                       | AccessControl    | Aktif  |

Framework reusable berada pada `packages/StarterKit` dan bukan module bisnis.
Urutan dependency baseline adalah AccessControl, UserManagement, AuditLog, lalu
SystemSetting.

Saat menambah module, perbarui tabel ini dengan owner tanggung jawab, dependency
nyata, dan statusnya. Jangan menambahkan dependency untuk kebutuhan hipotetis.
