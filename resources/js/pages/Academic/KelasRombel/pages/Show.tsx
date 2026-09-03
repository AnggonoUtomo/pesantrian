import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { KelasRombelAccessDenied } from '../components/KelasRombelAccessDenied';
import { KelasRombelDetailPanel } from '../components/KelasRombelDetailPanel';
import type { ClassGroupShowPageProps } from '../types';

export default function Show() {
    const { auth, classGroup, options, canManage, canPlacement, canArchive } =
        usePage<ClassGroupShowPageProps>().props;

    if (!canAccess(auth, 'kelas_rombel.view')) {
        return <KelasRombelAccessDenied />;
    }

    return (
        <>
            <Head title={`${classGroup.code} - Kelas / Rombel / Kurikulum`} />
            <SystemDashboardLayout
                eyebrow="Academic"
                title={classGroup.name}
                description="Detail kelas, rombongan belajar, kurikulum, daftar santri, dan riwayat wali kelas."
            >
                <KelasRombelDetailPanel
                    classGroup={classGroup}
                    options={options}
                    canManage={canManage}
                    canPlacement={canPlacement}
                    canArchive={canArchive}
                />
            </SystemDashboardLayout>
        </>
    );
}
