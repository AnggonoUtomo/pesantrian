import { Link, usePage } from '@inertiajs/react';
import { Palette, ShieldCheck, UserRound } from 'lucide-react';
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
import { buildNamespaceNavigation } from '@/lib/navigation';
import route from '@/lib/route';
import type { Auth } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { isCurrentUrl } = useCurrentUrl();
    const dashboardUrl = route('dashboard');
    const mainNavGroups = buildNamespaceNavigation(auth);

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
                <NavMain groups={mainNavGroups} />
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
