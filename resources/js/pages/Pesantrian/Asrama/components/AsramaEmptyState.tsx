import { BedDouble } from 'lucide-react';

export function AsramaEmptyState() {
    return (
        <section className="rounded-xl border border-dashed p-8 text-center">
            <BedDouble
                className="mx-auto size-8 text-foreground/40"
                aria-hidden="true"
            />
            <h2 className="mt-3 font-semibold">Belum ada asrama yang cocok</h2>
            <p className="mx-auto mt-1 max-w-md text-sm text-foreground/65">
                Coba ubah kata kunci atau filter. Data asrama baru akan bisa
                dibuat dari UI pada increment mutation berikutnya.
            </p>
        </section>
    );
}
