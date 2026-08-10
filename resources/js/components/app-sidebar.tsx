import { Link, usePage } from '@inertiajs/react';
import {
    LayoutGrid,
    Palette,
    ScrollText,
    Settings2,
    ShieldCheck,
    UserRound,
    UsersRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import route from '@/lib/route';
import type { Auth, NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { isCurrentUrl } = useCurrentUrl();
    const dashboardUrl = route('dashboard');
    const canAccessControl =
        auth.superSystem === true ||
        auth.permissions?.['access_control.role.manage'] === true;
    const canManageUsers =
        auth.superSystem === true || auth.permissions?.['user.view'] === true;
    const canViewAuditLogs =
        auth.superSystem === true ||
        auth.permissions?.['audit_log.view'] === true;
    const canManageSystemSettings = auth.superSystem === true;

    const mainNavItems: NavItem[] = [
        {
            title: 'System Dashboard',
            href: dashboardUrl,
            icon: LayoutGrid,
            iconClassName: 'text-cyan-600 dark:text-cyan-300',
        },
    ];

    if (canAccessControl) {
        mainNavItems.push({
            title: 'Access Control',
            href: route('access-control.index'),
            icon: ShieldCheck,
            iconClassName: 'text-violet-600 dark:text-violet-300',
        });
    }

    if (canManageUsers) {
        mainNavItems.push({
            title: 'User Management',
            href: route('system.users.index'),
            icon: UsersRound,
            iconClassName: 'text-emerald-600 dark:text-emerald-300',
        });
    }

    if (canViewAuditLogs) {
        mainNavItems.push({
            title: 'Audit Log',
            href: route('system.audit-logs.index'),
            icon: ScrollText,
            iconClassName: 'text-amber-600 dark:text-amber-300',
        });
    }

    if (canManageSystemSettings) {
        mainNavItems.push({
            title: 'SystemSetting',
            href: route('system.system-settings.index'),
            icon: Settings2,
            iconClassName: 'text-sky-600 dark:text-sky-300',
        });
    }

    return (
        <Sidebar
            collapsible="icon"
            variant="sidebar"
            className="dashboard-sidebar"
        >
            <SidebarHeader className="dashboard-sidebar-header">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardUrl} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="dashboard-sidebar-content">
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter className="dashboard-sidebar-footer p-2">
                <SidebarGroup className="p-0">
                    <SidebarMenu className="flex-row justify-between">
                        {[
                            {
                                title: 'Profile',
                                href: route('profile.edit'),
                                icon: UserRound,
                            },
                            {
                                title: 'Security',
                                href: route('security.edit'),
                                icon: ShieldCheck,
                            },
                            {
                                title: 'Appearance',
                                href: route('appearance.edit'),
                                icon: Palette,
                            },
                        ].map((item) => (
                            <SidebarMenuItem
                                key={item.title}
                                className="flex-1"
                            >
                                <SidebarMenuButton
                                    asChild
                                    isActive={isCurrentUrl(item.href)}
                                    className="justify-center"
                                    tooltip={{ children: item.title }}
                                >
                                    <Link href={item.href} prefetch>
                                        <item.icon />
                                        <span className="sr-only">
                                            {item.title}
                                        </span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        ))}
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarFooter>
        </Sidebar>
    );
}
