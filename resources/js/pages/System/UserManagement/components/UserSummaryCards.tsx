import { ShieldCheck, UserCheck, UsersRound } from 'lucide-react';
import type { UserManagementUser } from '../types';

type Props = { users: UserManagementUser[] };

export function UserSummaryCards({ users }: Props) {
    const active = users.filter((user) => user.status === 'active').length;
    const protectedUsers = users.filter((user) => user.isProtected).length;
    const items = [
        {
            label: 'Total user',
            value: users.length,
            icon: UsersRound,
            cardTone: 'dashboard-card--blue',
            tone: 'dashboard-accent--blue',
        },
        {
            label: 'User aktif',
            value: active,
            icon: UserCheck,
            cardTone: 'dashboard-card--emerald',
            tone: 'dashboard-accent--emerald',
        },
        {
            label: 'User terlindungi',
            value: protectedUsers,
            icon: ShieldCheck,
            cardTone: 'dashboard-card--violet',
            tone: 'dashboard-accent--violet',
        },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-3">
            {items.map((item) => (
                <section
                    key={item.label}
                    className={`dashboard-card ${item.cardTone} rounded-2xl border p-4`}
                >
                    <div className="flex items-center gap-3">
                        <span
                            className={`dashboard-icon ${item.tone} flex size-10 items-center justify-center rounded-lg`}
                        >
                            <item.icon aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">
                                {item.label}
                            </p>
                            <p className="mt-1 text-2xl font-semibold">
                                {item.value}
                            </p>
                        </div>
                    </div>
                </section>
            ))}
        </div>
    );
}
