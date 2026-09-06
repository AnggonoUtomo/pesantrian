import { Head, usePage } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { canAccess } from '@/lib/authorization';
import { PresensiSantriAccessDenied } from '../components/PresensiSantriAccessDenied';
import { PresensiSantriDetailPanel } from '../components/PresensiSantriDetailPanel';
import type { StudentAttendanceShowPageProps } from '../types';

export default function Show() {
    const {
        auth,
        attendance,
        options,
        canManage,
        canSubmit,
        canRevise,
        canArchive,
    } = usePage<StudentAttendanceShowPageProps>().props;
    const canView = canAccess(auth, 'presensi_santri.view');

    return (
        <>
            <Head
                title={`${attendance.session_code} - Presensi Santri`}
            />
            <SystemDashboardLayout
                eyebrow="Pesantrian"
                title="Presensi Santri"
                description="Detail sesi presensi, ringkasan status, dan daftar kehadiran santri."
            >
                {canView ? (
                    <PresensiSantriDetailPanel
                        attendance={attendance}
                        options={options}
                        canManage={canManage}
                        canSubmit={canSubmit}
                        canRevise={canRevise}
                        canArchive={canArchive}
                    />
                ) : (
                    <PresensiSantriAccessDenied />
                )}
            </SystemDashboardLayout>
        </>
    );
}
