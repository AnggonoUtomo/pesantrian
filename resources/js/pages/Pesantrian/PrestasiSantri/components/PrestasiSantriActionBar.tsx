import { FolderPlus, PlusCircle, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    canRecord: boolean;
    canVerify: boolean;
    canArchive: boolean;
    onCreate: () => void;
    onCreateCategory: () => void;
};

export function PrestasiSantriActionBar({
    canManage,
    canRecord,
    canVerify,
    canArchive,
    onCreate,
    onCreateCategory,
}: Props) {
    return (
        <section className="dashboard-card dashboard-card--yellow flex flex-col gap-4 rounded-2xl border p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <p className="text-xs font-semibold uppercase tracking-wide text-yellow-700 dark:text-yellow-300">
                    Lifecycle Prestasi Santri
                </p>
                <h2 className="mt-1 font-semibold">Ruang kerja prestasi</h2>
                <p className="mt-1 text-sm text-foreground/65">
                    Form tambah, ubah, submit, verifikasi, revisi, dan batal
                    disiapkan pada Increment 11.
                </p>
            </div>
            <div className="flex flex-wrap items-center gap-2">
                <PermissionBadge active={canManage} label="Kelola" />
                <PermissionBadge active={canRecord} label="Catat" />
                <PermissionBadge active={canVerify} label="Verifikasi" />
                <PermissionBadge active={canArchive} label="Arsip" />
                {canManage ? (
                    <Button
                        type="button"
                        variant="outline"
                        className="gap-2"
                        onClick={onCreateCategory}
                    >
                        <FolderPlus className="size-4" aria-hidden="true" />
                        Buat kategori
                    </Button>
                ) : null}
                {canRecord ? (
                    <Button type="button" className="gap-2" onClick={onCreate}>
                        <PlusCircle className="size-4" aria-hidden="true" />
                        Tambah prestasi
                    </Button>
                ) : null}
            </div>
        </section>
    );
}

function PermissionBadge({ active, label }: { active: boolean; label: string }) {
    return (
        <Badge variant="outline" className="gap-1.5">
            <ShieldCheck
                className={
                    active
                        ? 'size-3.5 text-emerald-600'
                        : 'size-3.5 text-foreground/35'
                }
                aria-hidden="true"
            />
            {label}
        </Badge>
    );
}
