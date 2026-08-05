import { ShieldCheck } from 'lucide-react';

export function AccessControlHeader() {
    return (
        <header className="flex items-center gap-4 rounded-xl border bg-card p-5 shadow-xs">
            <div className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600">
                <ShieldCheck aria-hidden="true" className="size-5" />
            </div>
            <div>
                <h1 className="text-xl font-bold tracking-tight">
                    Access Control
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Pilih role, lalu tinjau permission berdasarkan module.
                </p>
            </div>
        </header>
    );
}
