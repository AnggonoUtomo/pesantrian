import {
    Activity,
    ArrowUpRight,
    BarChart3,
    CircleDollarSign,
    Clock3,
    EllipsisVertical,
    LockKeyhole,
    ShieldCheck,
    Users,
} from 'lucide-react';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import type { AccessControlPermissionGroup, AccessControlRole } from '../types';

interface DashboardWidgetsProps {
    roles: AccessControlRole[];
    permissionGroups: AccessControlPermissionGroup[];
}

export function SystemDashboardWidgets({
    roles,
    permissionGroups,
}: DashboardWidgetsProps) {
    const permissionCount = permissionGroups.reduce(
        (total, group) => total + group.permissions.length,
        0,
    );
    const assignedCount = roles.reduce(
        (total, role) => total + role.permissions.length,
        0,
    );
    const averageCoverage = roles.length
        ? Math.round(
              (assignedCount / (roles.length * Math.max(permissionCount, 1))) *
                  100,
          )
        : 0;

    return (
        <div className="space-y-6">
            <SystemStatisticsGrid
                roles={roles}
                permissionCount={permissionCount}
            />
            <div className="grid gap-6 xl:grid-cols-2">
                <SystemInsightCard
                    roles={roles}
                    permissionCount={permissionCount}
                />
                <SystemCoverageCard
                    roles={roles}
                    permissionCount={permissionCount}
                />
            </div>
            <SystemMetricsCard
                roleCount={roles.length}
                permissionCount={permissionCount}
                assignedCount={assignedCount}
                averageCoverage={averageCoverage}
            />
            <SystemActivityTable roles={roles} />
        </div>
    );
}

function SystemStatisticsGrid({
    roles,
    permissionCount,
}: {
    roles: AccessControlRole[];
    permissionCount: number;
}) {
    const protectedCount = roles.filter((role) => role.is_protected).length;
    const cards = [
        {
            icon: <Users />,
            value: roles.length,
            title: 'Total role',
            change: '+12% dari baseline',
            accent: 'dashboard-card--blue',
        },
        {
            icon: <ShieldCheck />,
            value: permissionCount,
            title: 'Permission tersedia',
            change: '+8% dari baseline',
            accent: 'dashboard-card--cyan',
        },
        {
            icon: <LockKeyhole />,
            value: protectedCount,
            title: 'Role terlindungi',
            change: 'Aturan tetap aktif',
            accent: 'dashboard-card--emerald',
        },
    ];

    return (
        <div className="grid gap-6 sm:grid-cols-3">
            {cards.map((card) => (
                <Card
                    key={card.title}
                    className={`dashboard-card ${card.accent}`}
                >
                    <CardHeader className="flex items-center gap-3">
                        <div className="dashboard-icon flex size-10 items-center justify-center rounded-lg">
                            {card.icon}
                        </div>
                        <span className="text-2xl font-semibold">
                            {card.value}
                        </span>
                    </CardHeader>
                    <CardContent className="space-y-1">
                        <p className="font-semibold">{card.title}</p>
                        <p className="text-sm text-muted-foreground">
                            {card.change}
                        </p>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}

function SystemInsightCard({
    roles,
    permissionCount,
}: {
    roles: AccessControlRole[];
    permissionCount: number;
}) {
    return (
        <Card className="dashboard-card dashboard-card--violet justify-between">
            <CardHeader className="flex flex-row items-start justify-between gap-4">
                <div>
                    <CardTitle className="text-lg">
                        Permission insight
                    </CardTitle>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Ringkasan cakupan permission setiap role.
                    </p>
                </div>
                <div className="dashboard-icon rounded-lg p-2">
                    <BarChart3 className="size-5" />
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                <Separator />
                {roles.slice(0, 3).map((role) => {
                    const percentage = permissionCount
                        ? Math.round(
                              (role.permissions.length / permissionCount) * 100,
                          )
                        : 0;

                    return (
                        <div key={role.id} className="space-y-2">
                            <div className="flex items-center justify-between gap-3 text-sm">
                                <span className="font-medium">{role.name}</span>
                                <span className="text-muted-foreground">
                                    {percentage}%
                                </span>
                            </div>
                            <div className="h-2 rounded-full bg-primary/10">
                                <div
                                    className="dashboard-progress--cyan h-2 rounded-full transition-all"
                                    style={{ width: `${percentage}%` }}
                                />
                            </div>
                        </div>
                    );
                })}
                {roles.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Belum ada role.
                    </p>
                ) : null}
            </CardContent>
        </Card>
    );
}

function SystemCoverageCard({
    roles,
    permissionCount,
}: {
    roles: AccessControlRole[];
    permissionCount: number;
}) {
    const coverage = roles.length
        ? Math.round(
              (roles.reduce(
                  (total, role) => total + role.permissions.length,
                  0,
              ) /
                  (roles.length * Math.max(permissionCount, 1))) *
                  100,
          )
        : 0;

    return (
        <Card className="dashboard-card dashboard-card--blue">
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="text-lg">Permission coverage</CardTitle>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label="Menu permission coverage"
                        >
                            <EllipsisVertical />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuGroup>
                            <DropdownMenuItem>Refresh data</DropdownMenuItem>
                            <DropdownMenuItem>
                                Export ringkasan
                            </DropdownMenuItem>
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </CardHeader>
            <CardContent className="flex flex-col gap-5">
                <div className="flex items-center gap-5">
                    <div
                        className="relative flex size-28 shrink-0 items-center justify-center rounded-full"
                        style={{
                            background: `conic-gradient(var(--dashboard-chart-cyan) ${coverage}%, color-mix(in oklab, var(--dashboard-chart-cyan) 10%, transparent) 0)`,
                        }}
                    >
                        <div className="flex size-20 items-center justify-center rounded-full bg-card text-2xl font-semibold">
                            {coverage}%
                        </div>
                    </div>
                    <div>
                        <p className="text-lg font-semibold">Cakupan role</p>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Rata-rata permission yang terpasang pada role saat
                            ini.
                        </p>
                    </div>
                </div>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div className="rounded-lg border border-border/70 bg-background/20 p-3">
                        <p className="text-sm text-muted-foreground">
                            Role aktif
                        </p>
                        <p className="mt-1 text-xl font-semibold">
                            {roles.length}
                        </p>
                    </div>
                    <div className="rounded-lg border border-border/70 bg-background/20 p-3">
                        <p className="text-sm text-muted-foreground">
                            Permission identity
                        </p>
                        <p className="mt-1 text-xl font-semibold">
                            {permissionCount}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function SystemMetricsCard({
    roleCount,
    permissionCount,
    assignedCount,
    averageCoverage,
}: {
    roleCount: number;
    permissionCount: number;
    assignedCount: number;
    averageCoverage: number;
}) {
    const metrics = [
        {
            icon: <Activity />,
            title: 'Role terkelola',
            value: roleCount.toString(),
            accent: 'dashboard-accent--blue',
        },
        {
            icon: <LockKeyhole />,
            title: 'Permission terpasang',
            value: assignedCount.toString(),
            accent: 'dashboard-accent--violet',
        },
        {
            icon: <CircleDollarSign />,
            title: 'Guard aktif',
            value: 'web',
            accent: 'dashboard-accent--amber',
        },
        {
            icon: <Clock3 />,
            title: 'Status policy',
            value: 'Aktif',
            accent: 'dashboard-accent--emerald',
        },
    ];

    return (
        <Card className="dashboard-card dashboard-card--cyan">
            <CardContent className="grid gap-6 lg:grid-cols-5">
                <div className="flex flex-col justify-between gap-5 lg:col-span-3">
                    <div>
                        <p className="text-lg font-semibold">
                            Authorization metrics
                        </p>
                        <p className="text-sm text-muted-foreground">
                            Ringkasan health check AccessControl.
                        </p>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2">
                        {metrics.map((metric) => (
                            <div
                                key={metric.title}
                                className="dashboard-metric flex items-center gap-3 rounded-lg border border-border/70 bg-background/20 p-3"
                            >
                                <div
                                    className={`dashboard-icon ${metric.accent} flex size-8 shrink-0 items-center justify-center rounded-md [&>svg]:size-4`}
                                >
                                    {metric.icon}
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">
                                        {metric.title}
                                    </p>
                                    <p className="font-medium">
                                        {metric.value}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="dashboard-card dashboard-card--emerald flex flex-col justify-center gap-4 rounded-xl border p-5 lg:col-span-2">
                    <p className="text-lg font-semibold">Coverage goal</p>
                    <div className="flex items-center justify-between">
                        <span className="text-5xl font-semibold">
                            {averageCoverage}%
                        </span>
                        <ArrowUpRight className="size-6 text-primary" />
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Target coverage permission role pada baseline module.
                    </p>
                    <div className="h-2 rounded-full bg-primary/10">
                        <div
                            className="dashboard-progress--emerald h-2 rounded-full"
                            style={{ width: `${averageCoverage}%` }}
                        />
                    </div>
                    <p className="text-xs text-muted-foreground">
                        {permissionCount} permission identity terdaftar
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}

function SystemActivityTable({ roles }: { roles: AccessControlRole[] }) {
    return (
        <Card className="dashboard-card dashboard-card--violet overflow-hidden py-0">
            <CardHeader className="flex flex-row items-center justify-between border-b py-5">
                <div>
                    <CardTitle>Role permission activity</CardTitle>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Daftar role yang tersedia pada module.
                    </p>
                </div>
                <Badge variant="outline">{roles.length} role</Badge>
            </CardHeader>
            <div className="overflow-x-auto">
                <table className="w-full min-w-[620px] text-sm">
                    <thead className="border-b text-left text-muted-foreground">
                        <tr>
                            <th className="px-5 py-4 font-medium">Role</th>
                            <th className="px-5 py-4 font-medium">
                                Permission
                            </th>
                            <th className="px-5 py-4 font-medium">Status</th>
                            <th className="px-5 py-4 font-medium">Guard</th>
                        </tr>
                    </thead>
                    <tbody>
                        {roles.map((role) => (
                            <tr
                                key={role.id}
                                className="border-b last:border-0"
                            >
                                <td className="px-5 py-4">
                                    <div className="flex items-center gap-3">
                                        <Avatar>
                                            <AvatarFallback>
                                                {role.name
                                                    .slice(0, 2)
                                                    .toUpperCase()}
                                            </AvatarFallback>
                                        </Avatar>
                                        <span className="font-medium">
                                            {role.name}
                                        </span>
                                    </div>
                                </td>
                                <td className="px-5 py-4">
                                    {role.permissions.length} permission
                                </td>
                                <td className="px-5 py-4">
                                    <Badge
                                        variant="outline"
                                        className={`dashboard-badge text-[var(--badge-foreground)] ${
                                            role.is_protected
                                                ? 'dashboard-badge--emerald'
                                                : 'dashboard-badge--blue'
                                        }`}
                                    >
                                        {role.is_protected
                                            ? 'Protected'
                                            : 'Editable'}
                                    </Badge>
                                </td>
                                <td className="px-5 py-4 text-muted-foreground">
                                    {role.guard_name}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                {roles.length === 0 ? (
                    <p className="p-8 text-center text-sm text-muted-foreground">
                        Belum ada aktivitas role.
                    </p>
                ) : null}
            </div>
        </Card>
    );
}
