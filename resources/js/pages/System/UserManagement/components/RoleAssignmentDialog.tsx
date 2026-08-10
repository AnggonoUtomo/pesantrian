import { useForm } from '@inertiajs/react';
import { ShieldPlus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import type { UserManagementRole, UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    roles: UserManagementRole[];
    onOpenChange: (open: boolean) => void;
};

export function RoleAssignmentDialog({
    open,
    user,
    roles,
    onOpenChange,
}: Props) {
    const form = useForm<{ roles: string[] }>({
        roles: user?.roles ?? [],
    });

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!user || user.isProtected || form.data.roles.length === 0) {
            return;
        }

        form.patch(route('system.users.roles', user.id), {
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
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <ShieldPlus className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>Atur role user</DialogTitle>
                            <DialogDescription className="mt-1">
                                Pilih role untuk {user?.name ?? 'user'}.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <p className="text-sm font-medium">Role</p>
                        <div className="space-y-3 rounded-md border p-3">
                            {roles.map((role) => {
                                const checked = form.data.roles.includes(
                                    role.name,
                                );

                                return (
                                    <label
                                        key={role.id}
                                        className="flex cursor-pointer items-center gap-3 text-sm"
                                    >
                                        <Checkbox
                                            checked={checked}
                                            onCheckedChange={(nextChecked) =>
                                                form.setData(
                                                    'roles',
                                                    nextChecked
                                                        ? [
                                                              ...form.data
                                                                  .roles,
                                                              role.name,
                                                          ]
                                                        : form.data.roles.filter(
                                                              (name) =>
                                                                  name !==
                                                                  role.name,
                                                          ),
                                                )
                                            }
                                        />
                                        {role.name}
                                    </label>
                                );
                            })}
                        </div>
                        {form.errors.roles ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.roles}
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
                            disabled={
                                !user ||
                                user.isProtected ||
                                form.data.roles.length === 0
                            }
                        >
                            {form.processing ? 'Menyimpan...' : 'Simpan role'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
