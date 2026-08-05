import { Head } from '@inertiajs/react';
import SystemDashboardLayout from '@/layouts/system-dashboard-layout';
import { SystemDashboardWidgets } from './AccessControl/components/SystemDashboardWidgets';
import type { AccessControlPageProps } from './AccessControl/types';

export default function Dashboard({
    roles,
    permissionGroups,
}: AccessControlPageProps) {
    return (
        <>
            <Head title="System Dashboard" />
            <SystemDashboardLayout
                eyebrow="System overview"
                title="System Dashboard"
                description="Ringkasan kesehatan role, permission, dan capability system."
            >
                <SystemDashboardWidgets
                    roles={roles}
                    permissionGroups={permissionGroups}
                />
            </SystemDashboardLayout>
        </>
    );
}
