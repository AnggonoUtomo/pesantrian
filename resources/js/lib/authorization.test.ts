import { describe, expect, it } from 'vitest';
import { canAccess } from './authorization';

describe('canAccess', () => {
    it('memberi akses kepada SuperSystem tanpa membuka permission map', () => {
        expect(canAccess({ superSystem: true }, 'user.create')).toBe(true);
    });

    it('hanya menerima permission boolean true untuk actor biasa', () => {
        const auth = {
            superSystem: false,
            permissions: {
                'user.view': true,
                'user.create': false,
            },
        };

        expect(canAccess(auth, 'user.view')).toBe(true);
        expect(canAccess(auth, 'user.create')).toBe(false);
        expect(canAccess(auth, 'user.delete')).toBe(false);
    });
});
