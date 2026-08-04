import { Link, usePage } from '@inertiajs/react';
import { LayoutGrid, ShieldCheck } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import route from '@/lib/route';
import type { Auth, NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const dashboardUrl = route('dashboard');
    const canAccessControl =
        auth.superSystem === true ||
        auth.permissions?.['access_control.role.manage'] === true;

    const mainNavItems: NavItem[] = [
        {
            title: 'System Dashboard',
            href: dashboardUrl,
            icon: LayoutGrid,
        },
    ];

    if (canAccessControl) {
        mainNavItems.push({
            title: 'Access Control',
            href: route('access-control.index'),
            icon: ShieldCheck,
        });
    }

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader>
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

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>
        </Sidebar>
    );
}
