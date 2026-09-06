import { ClipboardPlus } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    onCreate: () => void;
};

export function PresensiSantriActionBar({ canManage, onCreate }: Props) {
    if (!canManage) {
        return null;
    }

    return (
        <div className="dashboard-card dashboard-card--teal flex flex-col gap-3 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <h2 className="text-base font-semibold">
                    Kelola sesi presensi
                </h2>
                <p className="mt-1 text-sm text-foreground/65">
                    Tambah sesi presensi kelas, asrama, atau kegiatan umum.
                    Entry santri bisa langsung diisi dari dialog yang sama.
                </p>
            </div>
            <Button type="button" onClick={onCreate}>
                <ClipboardPlus className="size-4" aria-hidden="true" />
                Tambah sesi presensi
            </Button>
        </div>
    );
}
