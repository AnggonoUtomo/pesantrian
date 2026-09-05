import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { AsramaAccessDenied } from '../components/AsramaAccessDenied';
import { AsramaDetailPanel } from '../components/AsramaDetailPanel';
import type { DormitoryShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        dormitory,
        canManage,
        canPlacement,
        canSupervisor,
        canArchive,
    } = usePage<DormitoryShowPageProps>().props;
    const canView = canAccess(auth, 'asrama.view');

    return (
        <>
            <Head title={`Asrama · ${dormitory.name}`} />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Asrama"
                description="Detail asrama, daftar kamar, penempatan santri aktif, dan musyrif."
            >
                {canView ? (
                    <AsramaDetailPanel
                        dormitory={dormitory}
                        canManage={canManage}
                        canPlacement={canPlacement}
                        canSupervisor={canSupervisor}
                        canArchive={canArchive}
                    />
                ) : (
                    <AsramaAccessDenied />
                )}
            </SystemDashboardLayout>
        </>
    );
}
