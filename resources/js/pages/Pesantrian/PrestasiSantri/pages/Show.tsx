import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { PrestasiSantriAccessDenied } from '../components/PrestasiSantriAccessDenied';
import { PrestasiSantriDetailPanel } from '../components/PrestasiSantriDetailPanel';
import type { StudentAchievementShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        achievement,
        options,
        canManage,
        canRecord,
        canVerify,
        canArchive,
    } =
        usePage<StudentAchievementShowPageProps>().props;

    if (!canAccess(auth, 'prestasi_santri.view')) {
        return <PrestasiSantriAccessDenied />;
    }

    return (
        <>
            <Head title={`Prestasi ${achievement.achievement_no}`} />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title={achievement.title}
                description={`Detail prestasi ${achievement.student_name} dengan nomor ${achievement.achievement_no}.`}
            >
                <PrestasiSantriDetailPanel
                    achievement={achievement}
                    options={options}
                    canManage={canManage}
                    canRecord={canRecord}
                    canVerify={canVerify}
                    canArchive={canArchive}
                />
            </SystemDashboardLayout>
        </>
    );
}
