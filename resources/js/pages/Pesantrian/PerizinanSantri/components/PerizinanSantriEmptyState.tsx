import { ClipboardList } from 'lucide-react';

export function PerizinanSantriEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <ClipboardList
                className="mx-auto size-10 text-foreground/35"
                aria-hidden="true"
            />
            <h3 className="mt-3 font-semibold">
                Belum ada izin santri yang cocok
            </h3>
            <p className="mx-auto mt-1 max-w-xl text-sm text-foreground/65">
                Coba ubah kata kunci, rentang tanggal, jenis izin, status, atau
                filter keterlambatan. Pembuatan dan aksi lifecycle izin masuk
                Increment 11.
            </p>
        </div>
    );
}
