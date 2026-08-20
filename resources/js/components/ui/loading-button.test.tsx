import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { LoadingButton } from './loading-button';

describe('LoadingButton', () => {
    it('menonaktifkan mutation dan menampilkan indikator saat loading', () => {
        render(<LoadingButton loading>Menyimpan...</LoadingButton>);

        expect(screen.getByRole('button', { name: 'Menyimpan...' })).toBeDisabled();
        expect(screen.getByRole('status', { hidden: true })).toBeInTheDocument();
    });

    it('mempertahankan disabled eksplisit tanpa indikator loading', () => {
        render(<LoadingButton disabled>Simpan</LoadingButton>);

        expect(screen.getByRole('button', { name: 'Simpan' })).toBeDisabled();
        expect(screen.queryByRole('status', { hidden: true })).not.toBeInTheDocument();
    });
});
