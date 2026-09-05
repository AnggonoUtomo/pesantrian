import { ShieldAlert } from 'lucide-react';

export function AsramaAccessDenied() {
    return (
        <section className="dashboard-card rounded-2xl border p-5">
            <div className="flex items-start gap-3">
                <ShieldAlert className="mt-0.5 size-5 text-destructive" />
                <div>
                    <h2 className="font-semibold">Akses asrama dibatasi</h2>
                    <p className="mt-1 text-sm text-foreground/65">
                        Akun ini belum memiliki permission{' '}
                        <code>asrama.view</code>. Hubungi admin jika menu Asrama
                        perlu dibuka.
                    </p>
                </div>
            </div>
        </section>
    );
}
