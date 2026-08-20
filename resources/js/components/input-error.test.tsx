import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import InputError from './input-error';

describe('InputError', () => {
    it('merender pesan error dekat input dengan atribut yang diteruskan', () => {
        render(
            <InputError
                id="email-error"
                role="alert"
                message="Email tidak valid."
            />,
        );

        expect(screen.getByRole('alert')).toHaveTextContent(
            'Email tidak valid.',
        );
        expect(screen.getByRole('alert')).toHaveAttribute('id', 'email-error');
    });

    it('tidak merender node saat pesan tidak tersedia', () => {
        const { container } = render(<InputError />);

        expect(container).toBeEmptyDOMElement();
    });
});
