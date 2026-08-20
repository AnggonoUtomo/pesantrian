import type { Auth } from '@/types/auth';

type AuthorizationContext = Pick<Auth, 'permissions' | 'superSystem'>;

export function canAccess(
    auth: AuthorizationContext,
    permission: string,
): boolean {
    return auth.superSystem === true || auth.permissions?.[permission] === true;
}
