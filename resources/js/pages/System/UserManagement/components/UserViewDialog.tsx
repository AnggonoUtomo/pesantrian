import { Eye, Pencil, ShieldCheck, UserRound } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    canEdit: boolean;
    canImpersonate: boolean;
    onOpenChange: (open: boolean) => void;
    onEdit: () => void;
    onImpersonate: () => void;
};

export function UserViewDialog({
    open,
    user,
    canEdit,
    canImpersonate,
    onOpenChange,
    onEdit,
    onImpersonate,
}: Props) {
    if (!user) {
        return null;
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <Eye className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>Detail user</DialogTitle>
                            <DialogDescription className="mt-1">
                                Tinjau identity dan status akun.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <div className="dashboard-subcard space-y-4 rounded-xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-11 items-center justify-center rounded-lg">
                            <UserRound className="size-5" />
                        </span>
                        <div className="min-w-0">
                            <p className="truncate font-semibold">
                                {user.name}
                            </p>
                            <p className="truncate text-sm text-muted-foreground">
                                {user.email}
                            </p>
                        </div>
                        <Badge className="ml-auto">{user.status}</Badge>
                    </div>
                    <dl className="grid gap-3 border-t pt-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt className="text-xs text-muted-foreground">
                                Identifier
                            </dt>
                            <dd className="mt-1 font-mono break-all">
                                {user.id}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs text-muted-foreground">
                                Protected
                            </dt>
                            <dd className="mt-1 flex items-center gap-1">
                                {user.isProtected ? (
                                    <>
                                        <ShieldCheck className="size-4 text-emerald-500" />{' '}
                                        Ya
                                    </>
                                ) : (
                                    'Tidak'
                                )}
                            </dd>
                        </div>
                    </dl>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Tutup
                    </Button>
                    {canImpersonate ? (
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={onImpersonate}
                        >
                            Impersonate
                        </Button>
                    ) : null}
                    {canEdit && !user.isProtected ? (
                        <Button type="button" onClick={onEdit}>
                            <Pencil className="size-4" /> Edit
                        </Button>
                    ) : null}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
