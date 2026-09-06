import { ClipboardCheck } from 'lucide-react';

export function PresensiSantriEmptyState() {
    return (
        <div
            role="status"
            className="rounded-xl border border-dashed p-8 text-center"
        >
            <ClipboardCheck
                className="mx-auto size-10 text-foreground/35"
                aria-hidden="true"
            />
            <h3 className="mt-3 font-semibold">
                Belum ada sesi presensi yang cocok
            </h3>
            <p className="mx-auto mt-1 max-w-xl text-sm text-foreground/65">
                Coba ubah kata kunci, rentang tanggal, konteks presensi, atau
                status sesi. Pembuatan dan pengisian presensi masuk Increment
                10.
            </p>
        </div>
    );
}
