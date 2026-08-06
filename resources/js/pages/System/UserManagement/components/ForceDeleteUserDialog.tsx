import { useForm } from '@inertiajs/react';
import { AlertTriangle, Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LoadingButton } from '@/components/ui/loading-button';
import route from '@/lib/route';
import type { UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    onOpenChange: (open: boolean) => void;
};

export function ForceDeleteUserDialog({ open, user, onOpenChange }: Props) {
    const form = useForm<Record<string, never>>({});

    const submit = () => {
        if (!user || user.isProtected || user.deletedAt === null) {
            return;
        }

        form.delete(route('system.users.force-delete', user.id), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) =>
                !form.processing && onOpenChange(nextOpen)
            }
        >
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--rose flex size-10 items-center justify-center rounded-lg">
                            <AlertTriangle className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>Hapus permanen user?</DialogTitle>
                            <DialogDescription className="mt-1">
                                Tindakan ini tidak dapat dibatalkan.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <div className="dashboard-subcard rounded-xl border p-4 text-sm">
                    <p className="font-semibold">{user?.name ?? 'User'}</p>
                    <p className="mt-1 text-muted-foreground">
                        {user?.email ?? ''}
                    </p>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        disabled={form.processing}
                    >
                        Batal
                    </Button>
                    <LoadingButton
                        type="button"
                        variant="destructive"
                        onClick={submit}
                        loading={form.processing}
                        disabled={
                            !user || user.isProtected || user.deletedAt === null
                        }
                    >
                        <Trash2 className="size-4" />
                        {form.processing ? 'Menghapus...' : 'Hapus permanen'}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
