import { Link, usePage } from '@inertiajs/react';
import { Palette, ShieldCheck, UserRound } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { buildNamespaceNavigation } from '@/lib/navigation';
import route from '@/lib/route';
import type { Auth } from '@/types';

export function AppSidebar() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { state } = useSidebar();
    const { isCurrentUrl } = useCurrentUrl();
    const dashboardUrl = route('dashboard');
    const mainNavGroups = buildNamespaceNavigation(auth);
    const footerNavItems = [
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
    ];
    const hasActiveFooterItem = footerNavItems.some((item) =>
        isCurrentUrl(item.href),
    );

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
                    {state === 'collapsed' ? (
                        <SidebarMenu>
                            <SidebarMenuItem>
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <SidebarMenuButton
                                            isActive={hasActiveFooterItem}
                                            className="justify-center"
                                            tooltip={{
                                                children: 'Akun & Tampilan',
                                            }}
                                            aria-label="Buka menu akun dan tampilan"
                                        >
                                            <UserRound />
                                        </SidebarMenuButton>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        side="right"
                                        align="end"
                                        className="w-48"
                                    >
                                        {footerNavItems.map((item) => (
                                            <DropdownMenuItem
                                                key={item.title}
                                                asChild
                                            >
                                                <Link
                                                    href={item.href}
                                                    prefetch
                                                >
                                                    <item.icon />
                                                    <span>{item.title}</span>
                                                </Link>
                                            </DropdownMenuItem>
                                        ))}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    ) : (
                        <SidebarMenu className="flex-row justify-between">
                            {footerNavItems.map((item) => (
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
                    )}
                </SidebarGroup>
            </SidebarFooter>
        </Sidebar>
    );
}
