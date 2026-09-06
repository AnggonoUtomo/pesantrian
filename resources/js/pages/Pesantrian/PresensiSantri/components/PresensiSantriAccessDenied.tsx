import { ShieldAlert } from 'lucide-react';

export function PresensiSantriAccessDenied() {
    return (
        <section className="dashboard-card dashboard-card--amber rounded-2xl border p-5">
            <div className="flex items-start gap-3">
                <ShieldAlert
                    className="mt-0.5 size-5 text-amber-600"
                    aria-hidden="true"
                />
                <div>
                    <h2 className="font-semibold">
                        Akses Presensi Santri dibatasi
                    </h2>
                    <p className="mt-1 text-sm text-foreground/65">
                        Akun ini belum memiliki permission{' '}
                        <code>presensi_santri.view</code>. Hubungi admin sistem
                        jika operator perlu melihat data presensi.
                    </p>
                </div>
            </div>
        </section>
    );
}
