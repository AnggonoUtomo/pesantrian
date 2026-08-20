import { render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { InviteUserDialog } from '@/pages/System/UserManagement/components/InviteUserDialog';

vi.mock('@inertiajs/react', () => ({
    useForm: () => ({
        data: { name: '', email: '' },
        errors: {
            email: 'Email undangan tidak dapat dikirim. Coba lagi setelah layanan email tersedia.',
        },
        processing: false,
        post: vi.fn(),
        setData: vi.fn(),
    }),
}));

vi.mock('@/lib/route', () => ({
    default: () => '/system/users/invitations',
}));

describe('InviteUserDialog', () => {
    it('menampilkan kegagalan delivery dekat input email secara aksesibel', () => {
        render(<InviteUserDialog open onOpenChange={vi.fn()} />);

        const email = screen.getByRole('textbox', { name: 'Email' });
        const error = screen.getByRole('alert');

        expect(error).toHaveTextContent(
            'Email undangan tidak dapat dikirim. Coba lagi setelah layanan email tersedia.',
        );
        expect(email).toHaveAttribute('aria-invalid', 'true');
        expect(email).toHaveAttribute('aria-describedby', 'invite-email-error');
        expect(error).toHaveAttribute('id', 'invite-email-error');
    });
});
