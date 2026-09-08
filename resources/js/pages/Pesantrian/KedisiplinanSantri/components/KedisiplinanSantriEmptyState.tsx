import { FileSearch } from 'lucide-react';

export function KedisiplinanSantriEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed bg-background p-8 text-center"
        >
            <FileSearch
                className="mx-auto size-10 text-foreground/35"
                aria-hidden="true"
            />
            <h3 className="mt-3 font-medium">
                Belum ada kasus kedisiplinan yang cocok
            </h3>
            <p className="mx-auto mt-2 max-w-xl text-sm text-foreground/60">
                Coba ubah kata kunci, rentang tanggal, status, tingkat, atau
                kategori pelanggaran.
            </p>
        </div>
    );
}
