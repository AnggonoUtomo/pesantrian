import SystemDashboardLayout from '@/layouts/system-dashboard-layout';

export function EmployeeAccessDenied() {
    return (
        <SystemDashboardLayout
            eyebrow="HumanResource"
            title="Akses SDM ditolak"
            description="Akun ini belum memiliki permission untuk melihat data SDM pesantren."
        >
            <div
                role="alert"
                className="dashboard-card dashboard-card--red rounded-2xl border p-5 text-sm text-foreground/75"
            >
                Hubungi administrator untuk permission{' '}
                <code>human_resource.view</code>.
            </div>
        </SystemDashboardLayout>
    );
}
