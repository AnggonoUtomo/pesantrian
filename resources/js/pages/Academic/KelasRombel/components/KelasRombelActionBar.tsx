import { BookOpen, Layers3, School } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    onCreateCurriculum: () => void;
    onCreateLevel: () => void;
    onCreateClassGroup: () => void;
};

export function KelasRombelActionBar({
    canManage,
    onCreateCurriculum,
    onCreateLevel,
    onCreateClassGroup,
}: Props) {
    if (!canManage) {
        return (
            <section className="dashboard-card rounded-2xl border border-dashed p-4 text-sm text-foreground/65">
                Mode baca aktif. Minta akses kelola kelas/rombel untuk menambah
                kurikulum, tingkat kelas, atau rombel.
            </section>
        );
    }

    return (
        <section className="dashboard-card rounded-2xl border p-4 sm:p-5">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 className="font-semibold">Aksi cepat</h2>
                    <p className="text-sm text-foreground/65">
                        Tambah data akademik dasar dari urutan paling kecil:
                        kurikulum, tingkat kelas, lalu rombel.
                    </p>
                </div>
                <div className="flex flex-col gap-2 sm:flex-row">
                    <Button type="button" variant="outline" onClick={onCreateCurriculum}>
                        <BookOpen className="size-4" aria-hidden="true" />
                        Tambah kurikulum
                    </Button>
                    <Button type="button" variant="outline" onClick={onCreateLevel}>
                        <Layers3 className="size-4" aria-hidden="true" />
                        Tambah kelas
                    </Button>
                    <Button type="button" onClick={onCreateClassGroup}>
                        <School className="size-4" aria-hidden="true" />
                        Tambah rombel
                    </Button>
                </div>
            </div>
        </section>
    );
}
