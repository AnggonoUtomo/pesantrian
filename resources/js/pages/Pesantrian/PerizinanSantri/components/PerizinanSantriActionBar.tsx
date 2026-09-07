import { ClipboardPlus } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    onCreate: () => void;
};

export function PerizinanSantriActionBar({ canManage, onCreate }: Props) {
    return (
        <div className="flex flex-col gap-3 rounded-2xl border bg-background/70 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 className="font-semibold">Aksi Perizinan</h2>
                <p className="text-sm text-foreground/65">
                    Buat draft izin dari data santri aktif. Aksi submit,
                    approve, check-out, return, dan void tersedia dari detail.
                </p>
            </div>
            {canManage ? (
                <Button type="button" onClick={onCreate}>
                    <ClipboardPlus className="size-4" aria-hidden="true" />
                    Buat izin
                </Button>
            ) : null}
        </div>
    );
}
