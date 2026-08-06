import { useForm } from '@inertiajs/react';
import { ShieldPlus } from 'lucide-react';
import { useState } from 'react';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    const [search, setSearch] = useState('');
    const form = useForm<{ role: string }>({ role: roles[0]?.name ?? '' });
    const visibleRoles = roles.filter((role) =>
        role.name.toLowerCase().includes(search.toLowerCase()),
    );

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!user || user.isProtected || form.data.role === '') {
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
                        <label
                            htmlFor="role-search"
                            className="text-sm font-medium"
                        >
                            Cari role
                        </label>
                        <Input
                            id="role-search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Ketik nama role..."
                        />
                    </div>
                    <div className="space-y-2">
                        <label
                            htmlFor="user-role"
                            className="text-sm font-medium"
                        >
                            Role
                        </label>
                        {visibleRoles.length ? (
                            <Select
                                value={form.data.role}
                                onValueChange={(value) =>
                                    form.setData('role', value)
                                }
                            >
                                <SelectTrigger id="user-role">
                                    <SelectValue placeholder="Pilih role" />
                                </SelectTrigger>
                                <SelectContent>
                                    {visibleRoles.map((role) => (
                                        <SelectItem
                                            key={role.id}
                                            value={role.name}
                                        >
                                            {role.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        ) : (
                            <p
                                role="status"
                                className="text-sm text-muted-foreground"
                            >
                                Role tidak ditemukan.
                            </p>
                        )}
                        {form.errors.role ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.role}
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
                                form.data.role === ''
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
