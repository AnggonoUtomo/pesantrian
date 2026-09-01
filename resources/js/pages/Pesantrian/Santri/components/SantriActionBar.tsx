import { GraduationCap, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    onCreate: () => void;
    onConvert: () => void;
};

export function SantriActionBar({ canManage, onCreate, onConvert }: Props) {
    if (!canManage) {
        return null;
    }

    return (
        <div className="dashboard-card dashboard-card--teal flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h2 className="text-base font-semibold">
                    Kelola data induk santri
                </h2>
                <p className="mt-1 text-sm text-foreground/65">
                    Tambah santri manual atau konversi pendaftaran PPDB yang
                    sudah diterima.
                </p>
            </div>
            <div className="flex flex-col gap-2 sm:flex-row">
                <Button type="button" variant="outline" onClick={onConvert}>
                    <GraduationCap className="size-4" aria-hidden="true" />
                    Konversi dari PPDB
                </Button>
                <Button type="button" onClick={onCreate}>
                    <Plus className="size-4" aria-hidden="true" />
                    Tambah santri
                </Button>
            </div>
        </div>
    );
}
