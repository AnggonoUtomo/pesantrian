import { NotebookTabs } from 'lucide-react';

export function AdmissionEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <NotebookTabs className="mx-auto size-10 text-foreground/45" />
            <h3 className="mt-3 font-semibold">
                Belum ada pendaftaran yang cocok
            </h3>
            <p className="mx-auto mt-2 max-w-md text-sm text-foreground/65">
                Coba ubah pencarian, status pendaftaran, unit tujuan, atau
                status biaya untuk menemukan calon santri.
            </p>
        </div>
    );
}
