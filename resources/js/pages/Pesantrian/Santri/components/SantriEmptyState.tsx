import { UsersRound } from 'lucide-react';

export function SantriEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <UsersRound
                className="mx-auto size-10 text-foreground/40"
                aria-hidden="true"
            />
            <h3 className="mt-3 font-medium">Belum ada santri yang cocok</h3>
            <p className="mx-auto mt-1 max-w-md text-sm text-foreground/65">
                Ubah filter pencarian, atau buat data santri dari PPDB accepted
                maupun input manual pada tahap UI berikutnya.
            </p>
        </div>
    );
}
