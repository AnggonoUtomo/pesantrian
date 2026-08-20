import { Head, usePage } from '@inertiajs/react';
import { Keyboard, Search, Settings2, ShieldX } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { categoryFromKey } from '../categories';
import { EditSystemSettingCategoryDialog } from '../components/EditSystemSettingCategoryDialog';
import { SystemSettingMenu } from '../components/SystemSettingMenu';
import { SystemSettingSummary } from '../components/SystemSettingSummary';
import { SystemSettingWorkspace } from '../components/SystemSettingWorkspace';
import type { SettingCategory, SystemSettingPageProps } from '../types';

export default function Index() {
    const { auth, settings } = usePage<SystemSettingPageProps>().props;
    const [activeCategory, setActiveCategory] =
        useState<SettingCategory>('api');
    const [query, setQuery] = useState('');
    const [editingCategory, setEditingCategory] =
        useState<SettingCategory | null>(null);
    const searchRef = useRef<HTMLInputElement>(null);

    const filteredSettings = useMemo(() => {
        const normalized = query.trim().toLowerCase();

        return settings.filter(
            (setting) =>
                categoryFromKey(setting.key) === activeCategory &&
                (normalized === '' ||
                    `${setting.key} ${setting.description}`
                        .toLowerCase()
                        .includes(normalized)),
        );
    }, [activeCategory, query, settings]);

    const categorySettings = useMemo(
        () =>
            settings.filter(
                (setting) => categoryFromKey(setting.key) === activeCategory,
            ),
        [activeCategory, settings],
    );

    useEffect(() => {
        const handleShortcut = (event: KeyboardEvent) => {
            const target = event.target;
            const isTyping =
                target instanceof HTMLElement &&
                (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) ||
                    target.isContentEditable);

            if (!isTyping && event.key === '/') {
                event.preventDefault();
                searchRef.current?.focus();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, []);

    if (auth.superSystem !== true) {
        return (
            <>
                <Head title="SystemSetting" />
                <SystemDashboardLayout
                    title="SystemSetting"
                    description="Kelola konfigurasi runtime global aplikasi."
                >
                    <section className="dashboard-card dashboard-card--rose rounded-2xl border p-8 text-center">
                        <ShieldX className="mx-auto size-10 text-rose-500" />
                        <h2 className="mt-3 text-lg font-semibold">
                            Akses terbatas
                        </h2>
                        <p className="mt-2 text-sm text-foreground/65">
                            Hanya SuperSystem yang dapat membuka workspace ini.
                        </p>
                    </section>
                </SystemDashboardLayout>
            </>
        );
    }

    return (
        <>
            <Head title="SystemSetting" />
            <SystemDashboardLayout
                eyebrow="System operations"
                title="SystemSetting"
                description="Kelola konfigurasi runtime global yang tervalidasi, langsung aktif, dan tercatat pada AuditLog."
                actions={
                    <Badge
                        variant="outline"
                        className="dashboard-badge dashboard-badge--blue gap-2 rounded-full px-3 py-1.5"
                    >
                        <Settings2 aria-hidden="true" />
                        SuperSystem only
                    </Badge>
                }
            >
                <div className="space-y-5">
                    <SystemSettingSummary settings={settings} />

                    <div className="dashboard-shortcut-bar flex flex-col gap-3 rounded-xl border px-3 py-3 text-xs text-foreground/75 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <span className="flex items-center gap-2 font-medium">
                                <Keyboard
                                    className="size-4"
                                    aria-hidden="true"
                                />
                                Shortcut
                            </span>
                            <span>
                                <kbd>/</kbd> fokus pencarian
                            </span>
                            <span>
                                <kbd>Esc</kbd> tutup modal
                            </span>
                        </div>
                        <div className="relative w-full sm:max-w-xs">
                            <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-foreground/50" />
                            <Input
                                id="system-setting-search"
                                name="system-setting-search"
                                ref={searchRef}
                                value={query}
                                onChange={(event) =>
                                    setQuery(event.target.value)
                                }
                                placeholder="Cari key atau deskripsi..."
                                aria-label="Cari SystemSetting"
                                className="pl-9 placeholder:text-foreground/70"
                            />
                        </div>
                    </div>

                    <div className="grid items-start gap-5 xl:grid-cols-[300px_minmax(0,1fr)]">
                        <div className="xl:order-2">
                            <SystemSettingWorkspace
                                category={activeCategory}
                                settings={filteredSettings}
                                onEditCategory={() =>
                                    setEditingCategory(activeCategory)
                                }
                            />
                        </div>
                        <div className="xl:order-1">
                            <SystemSettingMenu
                                activeCategory={activeCategory}
                                settings={settings}
                                onCategoryChange={setActiveCategory}
                            />
                        </div>
                    </div>
                </div>

                {editingCategory ? (
                    <EditSystemSettingCategoryDialog
                        key={editingCategory}
                        category={editingCategory}
                        settings={categorySettings}
                        onClose={() => setEditingCategory(null)}
                    />
                ) : null}
            </SystemDashboardLayout>
        </>
    );
}
