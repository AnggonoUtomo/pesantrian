import { Head } from '@inertiajs/react';
import { LockKeyhole } from 'lucide-react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function PerizinanSantriAccessDenied() {
    return (
        <>
            <Head title="Akses Perizinan Santri ditolak" />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Akses Perizinan Santri ditolak"
                description="Akun ini belum memiliki permission untuk melihat data perizinan santri."
            >
                <section
                    role="alert"
                    className="dashboard-card rounded-2xl border p-6 text-center"
                >
                    <LockKeyhole
                        className="mx-auto size-10 text-foreground/35"
                        aria-hidden="true"
                    />
                    <h2 className="mt-3 font-semibold">
                        Permission belum tersedia
                    </h2>
                    <p className="mx-auto mt-1 max-w-xl text-sm text-foreground/65">
                        Minta admin memberi akses `perizinan_santri.view`
                        melalui menu Access Control.
                    </p>
                </section>
            </SystemDashboardLayout>
        </>
    );
}
