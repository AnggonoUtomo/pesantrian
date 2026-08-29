import {
    Building2,
    CalendarRange,
    LayoutGrid,
    ScrollText,
    Settings2,
    ShieldCheck,
    UsersRound,
} from 'lucide-react';
import route from '@/lib/route';
import type { Auth, NavGroup, NavItem } from '@/types';

export function buildNamespaceNavigation(auth: Auth): NavGroup[] {
    return [
        {
            title: 'System',
            items: buildSystemNavigation(auth),
        },
        {
            title: 'Organization',
            items: buildOrganizationNavigation(auth),
        },
        {
            title: 'Academic',
            items: buildAcademicNavigation(auth),
        },
    ].filter((group) => group.items.length > 0);
}

function buildSystemNavigation(auth: Auth): NavItem[] {
    const items: NavItem[] = [
        {
            title: 'System Dashboard',
            href: route('dashboard'),
            icon: LayoutGrid,
            iconClassName: 'text-cyan-600 dark:text-cyan-300',
        },
    ];

    if (hasPermission(auth, 'access_control.role.manage')) {
        items.push({
            title: 'Access Control',
            href: route('access-control.index'),
            icon: ShieldCheck,
            iconClassName: 'text-violet-600 dark:text-violet-300',
        });
    }

    if (hasPermission(auth, 'user.view')) {
        items.push({
            title: 'User Management',
            href: route('system.users.index'),
            icon: UsersRound,
            iconClassName: 'text-emerald-600 dark:text-emerald-300',
        });
    }

    if (hasPermission(auth, 'audit_log.view')) {
        items.push({
            title: 'Audit Log',
            href: route('system.audit-logs.index'),
            icon: ScrollText,
            iconClassName: 'text-amber-600 dark:text-amber-300',
        });
    }

    if (auth.superSystem === true) {
        items.push({
            title: 'SystemSetting',
            href: route('system.system-settings.index'),
            icon: Settings2,
            iconClassName: 'text-sky-600 dark:text-sky-300',
        });
    }

    return items;
}

function buildOrganizationNavigation(auth: Auth): NavItem[] {
    if (
        !hasAnyPermission(auth, [
            'organization.view',
            'organization.manage',
        ])
    ) {
        return [];
    }

    return [
        {
            title: 'Unit Organisasi',
            href: route('organization.units.index'),
            icon: Building2,
            iconClassName: 'text-lime-600 dark:text-lime-300',
        },
    ];
}

function buildAcademicNavigation(auth: Auth): NavItem[] {
    if (
        !hasAnyPermission(auth, [
            'academic_period.view',
            'academic_period.manage',
        ])
    ) {
        return [];
    }

    return [
        {
            title: 'Periode Akademik',
            href: route('academic.periods.index'),
            icon: CalendarRange,
            iconClassName: 'text-rose-600 dark:text-rose-300',
        },
    ];
}

function hasAnyPermission(auth: Auth, permissions: string[]): boolean {
    return (
        auth.superSystem === true ||
        permissions.some((permission) => auth.permissions?.[permission] === true)
    );
}

function hasPermission(auth: Auth, permission: string): boolean {
    return hasAnyPermission(auth, [permission]);
}
