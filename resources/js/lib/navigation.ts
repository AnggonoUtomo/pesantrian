import {
    BedDouble,
    Building2,
    CalendarRange,
    GraduationCap,
    LayoutGrid,
    NotebookTabs,
    ScrollText,
    Settings2,
    School,
    ShieldCheck,
    UsersRound,
} from 'lucide-react';
import route from '@/lib/route';
import { routeOr } from '@/lib/route';
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
            title: 'HumanResource',
            items: buildHumanResourceNavigation(auth),
        },
        {
            title: 'Academic',
            items: buildAcademicNavigation(auth),
        },
        {
            title: 'Pesantrian',
            items: buildPesantrianNavigation(auth),
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
    if (!hasAnyPermission(auth, ['organization.view', 'organization.manage'])) {
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
            'kelas_rombel.view',
            'kelas_rombel.manage',
            'kelas_rombel.placement',
            'kelas_rombel.archive',
        ])
    ) {
        return [];
    }

    const items: NavItem[] = [];

    if (
        hasAnyPermission(auth, [
            'academic_period.view',
            'academic_period.manage',
        ])
    ) {
        items.push({
            title: 'Periode Akademik',
            href: route('academic.periods.index'),
            icon: CalendarRange,
            iconClassName: 'text-rose-600 dark:text-rose-300',
        });
    }

    if (
        hasAnyPermission(auth, [
            'kelas_rombel.view',
            'kelas_rombel.manage',
            'kelas_rombel.placement',
            'kelas_rombel.archive',
        ])
    ) {
        items.push({
            title: 'Kelas / Rombel / Kurikulum',
            href: routeOr(
                '/academic/class-groups',
                'academic.class-groups.index',
            ),
            icon: School,
            iconClassName: 'text-blue-600 dark:text-blue-300',
        });
    }

    return items;
}

function buildHumanResourceNavigation(auth: Auth): NavItem[] {
    if (
        !hasAnyPermission(auth, [
            'human_resource.view',
            'human_resource.manage',
        ])
    ) {
        return [];
    }

    return [
        {
            title: 'SDM Pesantren',
            href: route('human-resource.employees.index'),
            icon: UsersRound,
            iconClassName: 'text-orange-600 dark:text-orange-300',
        },
    ];
}

function buildPesantrianNavigation(auth: Auth): NavItem[] {
    if (
        !hasAnyPermission(auth, [
            'penerimaan_santri.view',
            'penerimaan_santri.manage',
            'penerimaan_santri.decide',
            'santri.view',
            'santri.manage',
            'santri.lifecycle',
            'santri.archive',
            'asrama.view',
            'asrama.manage',
            'asrama.placement',
            'asrama.supervisor',
            'asrama.archive',
        ])
    ) {
        return [];
    }

    const items: NavItem[] = [];

    if (
        hasAnyPermission(auth, [
            'penerimaan_santri.view',
            'penerimaan_santri.manage',
            'penerimaan_santri.decide',
        ])
    ) {
        items.push({
            title: 'PPDB / Penerimaan Santri',
            href: routeOr(
                '/pesantrian/admissions',
                'pesantrian.admissions.index',
            ),
            icon: NotebookTabs,
            iconClassName: 'text-teal-600 dark:text-teal-300',
        });
    }

    if (
        hasAnyPermission(auth, [
            'santri.view',
            'santri.manage',
            'santri.lifecycle',
            'santri.archive',
        ])
    ) {
        items.push({
            title: 'Data Induk Santri',
            href: routeOr('/pesantrian/students', 'pesantrian.students.index'),
            icon: GraduationCap,
            iconClassName: 'text-blue-600 dark:text-blue-300',
        });
    }

    if (
        hasAnyPermission(auth, [
            'asrama.view',
            'asrama.manage',
            'asrama.placement',
            'asrama.supervisor',
            'asrama.archive',
        ])
    ) {
        items.push({
            title: 'Asrama',
            href: routeOr('/pesantrian/asrama', 'pesantrian.asrama.index'),
            icon: BedDouble,
            iconClassName: 'text-indigo-600 dark:text-indigo-300',
        });
    }

    return items;
}

function hasAnyPermission(auth: Auth, permissions: string[]): boolean {
    return (
        auth.superSystem === true ||
        permissions.some(
            (permission) => auth.permissions?.[permission] === true,
        )
    );
}

function hasPermission(auth: Auth, permission: string): boolean {
    return hasAnyPermission(auth, [permission]);
}
