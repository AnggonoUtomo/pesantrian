import { Building2, Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    onCreate: () => void;
};

export function OrganizationUnitEmptyState({ canManage, onCreate }: Props) {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <Building2 className="mx-auto size-10 text-foreground/45" />
            <h3 className="mt-3 font-semibold">Belum ada unit ditemukan</h3>
            <p className="mx-auto mt-2 max-w-md text-sm text-foreground/65">
                Ubah filter pencarian atau mulai isi data unit organisasi
                melalui endpoint backend yang sudah tersedia.
            </p>
            {canManage ? (
                <Button type="button" className="mt-4" onClick={onCreate}>
                    <Plus className="size-4" />
                    Tambah unit organisasi
                </Button>
            ) : null}
        </div>
    );
}
