import { useForm } from '@inertiajs/react';
import { LogIn, ShieldAlert } from 'lucide-react';
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
import route from '@/lib/route';
import type { UserManagementUser } from '../types';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    onOpenChange: (open: boolean) => void;
};

export function ImpersonateUserDialog({ open, user, onOpenChange }: Props) {
    const form = useForm({ reason: '' });
    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (!user) {
            return;
        }

        form.post(route('system.users.impersonate', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onOpenChange(false);
            },
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
                    <div className="mb-2 flex size-11 items-center justify-center rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-300">
                        <ShieldAlert className="size-5" />
                    </div>
                    <DialogTitle>Masuk sebagai user ini?</DialogTitle>
                    <DialogDescription className="leading-relaxed">
                        Anda akan berpindah sementara ke akun{' '}
                        {user?.name ?? 'user ini'}. Aktivitas start dan stop
                        akan tercatat.
                    </DialogDescription>
                </DialogHeader>
                {user ? (
                    <div className="rounded-lg border bg-muted/50 p-4 text-sm">
                        <p className="font-medium">{user.name}</p>
                        <p className="mt-1 text-muted-foreground">
                            {user.email}
                        </p>
                    </div>
                ) : null}
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <label
                            htmlFor="user-impersonation-reason"
                            className="text-sm font-medium"
                        >
                            Alasan impersonation
                        </label>
                        <Input
                            id="user-impersonation-reason"
                            value={form.data.reason}
                            onChange={(event) =>
                                form.setData('reason', event.target.value)
                            }
                            placeholder="Contoh: pemeriksaan tiket support"
                            minLength={3}
                            maxLength={500}
                            required
                        />
                        {form.errors.reason ? (
                            <p
                                className="text-xs text-destructive"
                                role="alert"
                            >
                                {form.errors.reason}
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
                        <Button type="submit" disabled={form.processing}>
                            <LogIn className="size-4" />
                            {form.processing ? 'Memulai...' : 'Login-as'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
