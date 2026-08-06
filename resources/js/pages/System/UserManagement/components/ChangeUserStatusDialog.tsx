import { useForm } from '@inertiajs/react';
import { Activity } from 'lucide-react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import route from '@/lib/route';
import type { UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    onOpenChange: (open: boolean) => void;
};

export function ChangeUserStatusDialog({ open, user, onOpenChange }: Props) {
    const form = useForm<{ status: UserManagementUser['status'] }>({
        status: user?.status ?? 'active',
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!user || user.isProtected) {
            return;
        }

        form.patch(route('system.users.status', user.id), {
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
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <Activity className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>Ubah status user</DialogTitle>
                            <DialogDescription className="mt-1">
                                Perbarui status akun {user?.name ?? 'user'}.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <label
                            htmlFor="user-status"
                            className="text-sm font-medium"
                        >
                            Status
                        </label>
                        <Select
                            value={form.data.status}
                            onValueChange={(value) =>
                                form.setData(
                                    'status',
                                    value as UserManagementUser['status'],
                                )
                            }
                        >
                            <SelectTrigger id="user-status">
                                <SelectValue placeholder="Pilih status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="active">Aktif</SelectItem>
                                <SelectItem value="inactive">
                                    Tidak aktif
                                </SelectItem>
                                <SelectItem value="suspended">
                                    Ditangguhkan
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        {form.errors.status ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.status}
                            </p>
                        ) : null}
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
                            type="submit"
                            loading={form.processing}
                            disabled={!user || user.isProtected}
                        >
                            {form.processing ? 'Menyimpan...' : 'Simpan status'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
