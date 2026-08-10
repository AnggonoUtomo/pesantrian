import { useForm } from '@inertiajs/react';
import { UserPlus, UserRoundPen } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { LoadingButton } from '@/components/ui/loading-button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import route from '@/lib/route';
import type { UserManagementRole, UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    roles: UserManagementRole[];
    canChangeStatus: boolean;
    onOpenChange: (open: boolean) => void;
};
type FormData = { name: string; email: string; password: string };

export function UserFormDialog({ open, user, roles, canChangeStatus, onOpenChange }: Props) {
    const form = useForm<FormData & { status: string; role: string }>({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        status: 'active',
        role: '',
    });
    const isEdit = user !== null;

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (isEdit && user) {
            form.put(route('system.users.update', user.id), {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            });

            return;
        }

        form.post(route('system.users.store'), {
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
            <DialogContent className="max-h-[90vh] max-w-lg overflow-y-auto">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            {isEdit ? (
                                <UserRoundPen className="size-5" />
                            ) : (
                                <UserPlus className="size-5" />
                            )}
                        </span>
                        <div>
                            <DialogTitle>
                                {isEdit ? 'Edit user' : 'Tambah user'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                {isEdit
                                    ? 'Perbarui identitas user yang dipilih.'
                                    : 'Buat akun user baru pada area System.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <label
                            htmlFor="user-form-name"
                            className="text-sm font-medium"
                        >
                            Nama
                        </label>
                        <Input
                            id="user-form-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            required
                            minLength={2}
                        />
                        {form.errors.name ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.name}
                            </p>
                        ) : null}
                    </div>
                    <div className="space-y-2">
                        <label
                            htmlFor="user-form-email"
                            className="text-sm font-medium"
                        >
                            Email
                        </label>
                        <Input
                            id="user-form-email"
                            type="email"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                            required
                        />
                        {form.errors.email ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.email}
                            </p>
                        ) : null}
                    </div>
                    {!isEdit ? (
                        <>
                        <div className="space-y-2">
                            <label
                                htmlFor="user-form-password"
                                className="text-sm font-medium"
                            >
                                Password awal
                            </label>
                            <Input
                                id="user-form-password"
                                type="password"
                                value={form.data.password}
                                onChange={(event) =>
                                    form.setData('password', event.target.value)
                                }
                                required
                                minLength={8}
                            />
                            {form.errors.password ? (
                                <p
                                    className="text-xs text-destructive"
                                    role="alert"
                                >
                                    {form.errors.password}
                                </p>
                            ) : null}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2">
                                <label htmlFor="user-form-status" className="text-sm font-medium">Status awal</label>
                                <Select name="status" value={form.data.status} onValueChange={(value) => form.setData('status', value)} disabled={!canChangeStatus}>
                                    <SelectTrigger id="user-form-status"><SelectValue /></SelectTrigger>
                                    <SelectContent><SelectItem value="active">Aktif</SelectItem><SelectItem value="inactive">Tidak aktif</SelectItem><SelectItem value="suspended">Ditangguhkan</SelectItem></SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <label htmlFor="user-form-role" className="text-sm font-medium">Role awal</label>
                                <Select name="role" value={form.data.role || 'none'} onValueChange={(value) => form.setData('role', value === 'none' ? '' : value)}>
                                    <SelectTrigger id="user-form-role"><SelectValue placeholder="Tanpa role" /></SelectTrigger>
                                    <SelectContent><SelectItem value="none">Tanpa role</SelectItem>{roles.map((role) => <SelectItem key={role.id} value={role.name}>{role.name}</SelectItem>)}</SelectContent>
                                </Select>
                            </div>
                        </div>
                        </>
                    ) : null}
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                            disabled={form.processing}
                        >
                            Batal
                        </Button>
                        <LoadingButton type="submit" loading={form.processing}>
                            {form.processing
                                ? 'Menyimpan...'
                                : isEdit
                                  ? 'Simpan perubahan'
                                  : 'Buat user'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
