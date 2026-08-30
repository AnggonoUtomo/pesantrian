import { ShieldAlert } from 'lucide-react';

export function AdmissionAccessDenied() {
    return (
        <div
            role="alert"
            className="mx-auto mt-10 max-w-xl rounded-2xl border border-destructive/30 bg-destructive/5 p-6 text-center"
        >
            <ShieldAlert className="mx-auto size-10 text-destructive" />
            <h1 className="mt-3 text-lg font-semibold">
                Akses PPDB dibatasi
            </h1>
            <p className="mt-2 text-sm text-foreground/70">
                Anda belum memiliki izin untuk melihat data Penerimaan Santri
                Baru.
            </p>
        </div>
    );
}
