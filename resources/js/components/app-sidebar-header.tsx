import { usePage } from '@inertiajs/react';
import { Bell, Languages, Moon, Sun } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Toggle } from '@/components/ui/toggle';
import { UserInfo } from '@/components/user-info';
import { UserMenuContent } from '@/components/user-menu-content';
import { useAppearance } from '@/hooks/use-appearance';
import type { Auth } from '@/types';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDarkMode = resolvedAppearance === 'dark';

    return (
        <header className="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-sidebar-border/50 bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/80 sm:px-6">
            <div className="flex min-w-0 items-center gap-3">
                <SidebarTrigger className="shrink-0" />
                <Separator
                    orientation="vertical"
                    className="hidden h-5 sm:block"
                />
                <div className="min-w-0">
                    <p className="hidden text-[10px] font-semibold tracking-[0.2em] text-primary uppercase sm:block">
                        System workspace
                    </p>
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                    {breadcrumbs.length === 0 ? (
                        <p className="truncate text-sm font-medium">
                            System Dashboard
                        </p>
                    ) : null}
                </div>
            </div>
            <div className="flex shrink-0 items-center gap-1">
                <Toggle
                    pressed={isDarkMode}
                    onPressedChange={(pressed) =>
                        updateAppearance(pressed ? 'dark' : 'light')
                    }
                    variant="outline"
                    size="sm"
                    aria-label={
                        isDarkMode
                            ? 'Aktifkan mode terang'
                            : 'Aktifkan mode gelap'
                    }
                    title={isDarkMode ? 'Mode terang' : 'Mode gelap'}
                    data-test="theme-toggle"
                >
                    {isDarkMode ? <Sun /> : <Moon />}
                </Toggle>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label="Pilih bahasa"
                        >
                            <Languages />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuRadioGroup value="id">
                            <DropdownMenuRadioItem value="id">
                                Bahasa Indonesia
                            </DropdownMenuRadioItem>
                            <DropdownMenuRadioItem value="en">
                                English
                            </DropdownMenuRadioItem>
                        </DropdownMenuRadioGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
                <Button variant="ghost" size="icon" aria-label="Notifikasi">
                    <Bell />
                </Button>
                {auth.user ? (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                className="ml-1 h-10 gap-2 px-2"
                                aria-label={`Buka menu profil ${auth.user.name}`}
                                data-test="top-nav-profile-trigger"
                            >
                                <UserInfo user={auth.user} />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-64">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                ) : null}
            </div>
        </header>
    );
}
