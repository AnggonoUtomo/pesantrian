import { Archive, PencilLine } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type {
    StudentAchievementCategory,
    StudentAchievementIndexPageProps,
} from '../types';

type Props = {
    categories: StudentAchievementIndexPageProps['options']['categories'];
    canManage: boolean;
    onEdit: (category: StudentAchievementCategory) => void;
    onArchive: (category: StudentAchievementCategory) => void;
};

export function PrestasiSantriCategoryPanel({
    categories,
    canManage,
    onEdit,
    onArchive,
}: Props) {
    return (
        <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4 sm:p-5">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 className="font-semibold">Kategori prestasi</h2>
                    <p className="text-sm text-foreground/65">
                        Kelola kategori aktif yang dipakai saat mencatat
                        prestasi santri.
                    </p>
                </div>
                <Badge variant="outline">{categories.length} aktif</Badge>
            </div>

            {categories.length > 0 ? (
                <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    {categories.map((category) => {
                        const payload = toCategory(category);

                        return (
                            <article
                                key={category.value}
                                className="rounded-xl border bg-background p-3"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs text-foreground/55">
                                            {payload.code}
                                        </p>
                                        <h3 className="font-medium">
                                            {payload.name}
                                        </h3>
                                    </div>
                                    <Badge variant="outline">Aktif</Badge>
                                </div>
                                <p className="mt-2 line-clamp-2 text-sm text-foreground/65">
                                    {payload.description ??
                                        'Belum ada deskripsi kategori.'}
                                </p>
                                {canManage ? (
                                    <div className="mt-3 flex flex-wrap justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => onEdit(payload)}
                                        >
                                            <PencilLine
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                            Edit kategori
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            onClick={() => onArchive(payload)}
                                        >
                                            <Archive
                                                className="size-4"
                                                aria-hidden="true"
                                            />
                                            Arsipkan
                                        </Button>
                                    </div>
                                ) : null}
                            </article>
                        );
                    })}
                </div>
            ) : (
                <p className="mt-4 rounded-xl border border-dashed p-4 text-sm text-foreground/65">
                    Belum ada kategori aktif. Buat kategori dulu sebelum
                    mencatat prestasi baru.
                </p>
            )}
        </section>
    );
}

function toCategory(
    option: StudentAchievementIndexPageProps['options']['categories'][number],
): StudentAchievementCategory {
    return {
        id: option.id ?? option.value,
        code: option.code ?? option.label,
        name: option.name ?? option.label,
        description: option.description ?? null,
        status: option.status ?? 'active',
        created_at: null,
        updated_at: null,
    };
}
