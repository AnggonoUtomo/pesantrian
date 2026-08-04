# Specification Frontend AccessControl

## Objective

Menyediakan halaman role dan permission yang dapat digunakan langsung melalui
browser dengan tampilan responsif, state yang jelas, dan permission visibility
yang aman.

## Acuan contoh

Pola dari `FrontendContoh/access-control` dipakai sebagai referensi struktur:

- header dengan icon dan ringkasan halaman;
- shortcut panel untuk action penting;
- role control card untuk memilih role dan menampilkan ringkasan;
- permission module panel dengan accordion dan checkbox;
- dialog tambah role dan hapus role;
- empty workspace saat belum ada role;
- summary box untuk jumlah permission.

Contoh tidak disalin mentah. Identifier, route, type, permission key, dan data
harus disesuaikan dengan contract AccessControl saat ini.

## Route dan data

Route frontend memakai Ziggy. Target route page:

```text
GET /system/access-control
```

Props Inertia minimum:

```ts
interface AccessControlPageProps {
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
    selectedRoleId: string | null;
    auth: SharedData['auth'];
}
```

Semua identifier role dan permission menggunakan ULID string. Permission
visibility memakai `auth.permissions`, `auth.roles`, dan `auth.superSystem`.
Frontend hanya mengatur visibility/UX; backend tetap memeriksa authorization.

## Acceptance criteria

- User berwenang dapat membuka halaman AccessControl.
- User tanpa permission menerima response server `403`.
- Role tampil dalam role control card dan dapat dipilih.
- Permission dikelompokkan berdasarkan module.
- Role protected `SuperSystem` tidak memiliki action destructive.
- User tanpa permission update hanya dapat melihat permission.
- Loading, empty, error, dan success feedback tersedia.
- Layout dapat dipakai pada desktop dan viewport mobile.
- Critical flow dapat dijalankan dengan keyboard.
- Route frontend menggunakan Ziggy dan tidak memakai Wayfinder.

## Boundaries

- Always: backend authority, ULID string, typed props, Ziggy, accessibility.
- Ask first: perubahan struktur halaman, permission visibility, dan action role.
- Never: hardcode bypass authorization, import private repository module lain,
  secret di props, atau URL route string tersebar.
