import { router, usePage } from '@inertiajs/react';
import { AnimatePresence, MotionConfig, motion } from 'framer-motion';
import {
    Command,
    LayoutDashboard,
    LockKeyhole,
    Search,
    ScrollText,
    Settings,
    UserRound,
    UsersRound,
} from 'lucide-react';
import { useEffect, useMemo, useState, useSyncExternalStore } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import route from '@/lib/route';
import type { Auth } from '@/types';

type PaletteItem = {
    title: string;
    description: string;
    keywords: string;
    href: string;
    icon: typeof LayoutDashboard;
};

export function CommandPalette() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const shouldReduceMotion = useSyncExternalStore(
        (onStoreChange) => {
            const mediaQuery = window.matchMedia(
                '(prefers-reduced-motion: reduce)',
            );
            const handlePreferenceChange = () => onStoreChange();

            mediaQuery.addEventListener('change', handlePreferenceChange);

            return () =>
                mediaQuery.removeEventListener(
                    'change',
                    handlePreferenceChange,
                );
        },
        () => window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        () => true,
    );

    const items = useMemo<PaletteItem[]>(() => {
        const canViewSystemDashboard = Boolean(
            auth.superSystem || auth.permissions?.['system.dashboard.view'],
        );
        const canManageAccessControl = Boolean(
            auth.superSystem ||
            auth.permissions?.['access_control.role.manage'],
        );
        const canManageUsers = Boolean(
            auth.superSystem || auth.permissions?.['user.view'],
        );
        const canViewAuditLogs = Boolean(
            auth.superSystem || auth.permissions?.['audit_log.view'],
        );

        return [
            ...(canViewSystemDashboard
                ? [
                      {
                          title: 'System Dashboard',
                          description: 'Buka dashboard workspace System.',
                          keywords: 'system dashboard beranda',
                          href: route('system.dashboard'),
                          icon: LayoutDashboard,
                      },
                  ]
                : []),
            ...(canManageAccessControl
                ? [
                      {
                          title: 'Access Control',
                          description: 'Kelola role dan permission.',
                          keywords: 'access control role permission otorisasi',
                          href: route('access-control.index'),
                          icon: LockKeyhole,
                      },
                  ]
                : []),
            ...(canManageUsers
                ? [
                      {
                          title: 'User Management',
                          description: 'Kelola identity dan status user.',
                          keywords: 'user management pengguna identity status',
                          href: route('system.users.index'),
                          icon: UsersRound,
                      },
                  ]
                : []),
            ...(canViewAuditLogs
                ? [
                      {
                          title: 'Audit Log',
                          description: 'Telusuri aktivitas keamanan System.',
                          keywords: 'audit log aktivitas security correlation',
                          href: route('system.audit-logs.index'),
                          icon: ScrollText,
                      },
                  ]
                : []),
            {
                title: 'Profil',
                description: 'Kelola informasi profil akun.',
                keywords: 'profil profile akun user',
                href: route('profile.edit'),
                icon: UserRound,
            },
            {
                title: 'Appearance',
                description: 'Atur mode warna dan palette tema.',
                keywords: 'appearance tampilan tema dark light palette',
                href: route('appearance.edit'),
                icon: Settings,
            },
            {
                title: 'Security',
                description: 'Kelola password dan keamanan akun.',
                keywords: 'security keamanan password passkey two factor',
                href: route('security.edit'),
                icon: LockKeyhole,
            },
        ];
    }, [auth.permissions, auth.superSystem]);

    const filteredItems = useMemo(() => {
        const normalizedQuery = query.trim().toLowerCase();

        if (!normalizedQuery) {
            return items;
        }

        return items.filter((item) =>
            `${item.title} ${item.description} ${item.keywords}`
                .toLowerCase()
                .includes(normalizedQuery),
        );
    }, [items, query]);

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (
                (event.metaKey || event.ctrlKey) &&
                event.key.toLowerCase() === 'k'
            ) {
                event.preventDefault();
                setOpen((current) => !current);
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const navigate = (href: string) => {
        setOpen(false);
        setQuery('');
        router.visit(href);
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => {
                setOpen(nextOpen);

                if (!nextOpen) {
                    setQuery('');
                }
            }}
        >
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    className="hidden h-9 min-w-44 justify-between gap-3 px-3 text-foreground/75 sm:flex"
                >
                    <span className="flex items-center gap-2">
                        <Search className="size-4" />
                        <span>Cari menu...</span>
                    </span>
                    <kbd className="pointer-events-none rounded border bg-muted px-1.5 py-0.5 text-[10px] font-medium text-foreground/75">
                        Ctrl K
                    </kbd>
                </Button>
            </DialogTrigger>
            <DialogContent className="gap-0 overflow-hidden p-0 sm:max-w-xl">
                <DialogHeader className="sr-only">
                    <DialogTitle>Command palette</DialogTitle>
                    <DialogDescription>
                        Cari dan buka halaman yang tersedia untuk akun ini.
                    </DialogDescription>
                </DialogHeader>
                <div className="flex items-center border-b px-4">
                    <Search className="mr-3 size-4 shrink-0 text-foreground/75" />
                    <Input
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                        placeholder="Cari halaman atau fitur..."
                        aria-label="Cari halaman atau fitur"
                        className="h-12 border-0 px-0 shadow-none focus-visible:ring-0"
                        autoFocus
                    />
                    <kbd className="hidden rounded border bg-muted px-1.5 py-0.5 text-[10px] font-medium text-foreground/75 sm:inline-flex">
                        ESC
                    </kbd>
                </div>
                <div className="max-h-80 overflow-y-auto p-2">
                    <MotionConfig
                        reducedMotion={shouldReduceMotion ? 'always' : 'never'}
                    >
                        {filteredItems.length > 0 ? (
                            <div
                                className="space-y-1"
                                role="listbox"
                                aria-label="Hasil pencarian"
                            >
                                <AnimatePresence
                                    initial={false}
                                    mode="popLayout"
                                >
                                    {filteredItems.map((item) => {
                                        const Icon = item.icon;
                                        const content = (
                                            <>
                                                <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
                                                    <Icon className="size-4" />
                                                </span>
                                                <span className="min-w-0">
                                                    <span className="block truncate text-sm font-medium">
                                                        {item.title}
                                                    </span>
                                                    <span className="block truncate text-xs text-foreground/75">
                                                        {item.description}
                                                    </span>
                                                </span>
                                            </>
                                        );
                                        const className =
                                            'flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-left transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:bg-accent focus-visible:text-accent-foreground focus-visible:outline-none';

                                        if (shouldReduceMotion) {
                                            return (
                                                <button
                                                    key={item.title}
                                                    type="button"
                                                    role="option"
                                                    className={className}
                                                    onClick={() =>
                                                        navigate(item.href)
                                                    }
                                                >
                                                    {content}
                                                </button>
                                            );
                                        }

                                        return (
                                            <motion.button
                                                key={item.title}
                                                layout={!shouldReduceMotion}
                                                initial={
                                                    shouldReduceMotion
                                                        ? false
                                                        : { opacity: 0, y: 6 }
                                                }
                                                animate={{ opacity: 1, y: 0 }}
                                                exit={
                                                    shouldReduceMotion
                                                        ? undefined
                                                        : { opacity: 0, y: -4 }
                                                }
                                                transition={
                                                    shouldReduceMotion
                                                        ? { duration: 0 }
                                                        : { duration: 0.16 }
                                                }
                                                type="button"
                                                role="option"
                                                className={className}
                                                onClick={() =>
                                                    navigate(item.href)
                                                }
                                            >
                                                {content}
                                            </motion.button>
                                        );
                                    })}
                                </AnimatePresence>
                            </div>
                        ) : shouldReduceMotion ? (
                            <div className="px-3 py-10 text-center">
                                <Command className="mx-auto size-5 text-foreground/75" />
                                <p className="mt-2 text-sm font-medium">
                                    Menu tidak ditemukan
                                </p>
                                <p className="mt-1 text-xs text-foreground/75">
                                    Coba kata kunci lain.
                                </p>
                            </div>
                        ) : (
                            <motion.div
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                className="px-3 py-10 text-center"
                            >
                                <Command className="mx-auto size-5 text-foreground/75" />
                                <p className="mt-2 text-sm font-medium">
                                    Menu tidak ditemukan
                                </p>
                                <p className="mt-1 text-xs text-foreground/75">
                                    Coba kata kunci lain.
                                </p>
                            </motion.div>
                        )}
                    </MotionConfig>
                </div>
                <div className="border-t px-4 py-2 text-[11px] text-foreground/75">
                    Gunakan{' '}
                    <kbd className="rounded border bg-muted px-1">↑</kbd>{' '}
                    <kbd className="rounded border bg-muted px-1">↓</kbd> untuk
                    meninjau menu dan{' '}
                    <kbd className="rounded border bg-muted px-1">Esc</kbd>{' '}
                    untuk menutup.
                </div>
            </DialogContent>
        </Dialog>
    );
}
