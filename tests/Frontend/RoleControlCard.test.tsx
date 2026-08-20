import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { RoleControlCard } from '@/pages/System/AccessControl/components/RoleControlCard';

const roles = [
    {
        id: '01HROLESECURITY00000000000',
        name: 'SecurityAdmin',
        guard_name: 'web',
        permissions: ['access_control.role.manage'],
        is_protected: false,
    },
    {
        id: '01HROLESUPERSYSTEM0000000',
        name: 'SuperSystem',
        guard_name: 'web',
        permissions: ['*'],
        is_protected: true,
    },
];

describe('RoleControlCard', () => {
    it('memfilter role, memilih hasil, dan menutup listbox', async () => {
        const user = userEvent.setup();
        const onRoleChange = vi.fn();
        render(
            <RoleControlCard
                roles={roles}
                permissionCount={12}
                activeRole={roles[0]}
                onRoleChange={onRoleChange}
            />,
        );

        await user.click(screen.getByRole('combobox', { name: 'Role aktif' }));
        await user.type(
            screen.getByRole('searchbox', { name: 'Cari role' }),
            'super',
        );

        expect(
            screen.queryByRole('option', { name: 'SecurityAdmin' }),
        ).not.toBeInTheDocument();
        await user.click(screen.getByRole('option', { name: 'SuperSystem' }));

        expect(onRoleChange).toHaveBeenCalledWith(roles[1].id);
        expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });

    it('menampilkan empty state pencarian dan status protected', async () => {
        const user = userEvent.setup();
        render(
            <RoleControlCard
                roles={roles}
                permissionCount={12}
                activeRole={roles[1]}
                onRoleChange={vi.fn()}
                actions={<button type="button">Aksi terizin</button>}
            />,
        );

        expect(screen.getByText('Protected')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Aksi terizin' }),
        ).toBeInTheDocument();

        await user.click(screen.getByRole('combobox', { name: 'Role aktif' }));
        await user.type(
            screen.getByRole('searchbox', { name: 'Cari role' }),
            'tidak ada',
        );

        expect(screen.getByText('Role tidak ditemukan.')).toBeInTheDocument();
        await user.keyboard('{Escape}');
        expect(screen.queryByRole('listbox')).not.toBeInTheDocument();
    });

    it('menonaktifkan pemilih ketika role kosong', () => {
        render(
            <RoleControlCard
                roles={[]}
                permissionCount={0}
                activeRole={null}
                onRoleChange={vi.fn()}
            />,
        );

        expect(
            screen.getByRole('combobox', { name: 'Role aktif' }),
        ).toBeDisabled();
        expect(screen.getByText('Belum ada role')).toBeInTheDocument();
    });
});
