import { useForm } from '@inertiajs/react';
import { MailPlus } from 'lucide-react';
import InputError from '@/components/input-error';
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
import route from '@/lib/route';

export function InviteUserDialog({
    open,
    onOpenChange,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const form = useForm({ name: '', email: '' });

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => !form.processing && onOpenChange(next)}
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <MailPlus className="size-5" />
                        Kirim undangan user
                    </DialogTitle>
                    <DialogDescription>
                        User menerima email untuk menetapkan password. Link
                        hanya dapat digunakan sekali.
                    </DialogDescription>
                </DialogHeader>
                <form
                    className="space-y-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.post(route('system.users.invitations.store'), {
                            preserveScroll: true,
                            onSuccess: () => onOpenChange(false),
                        });
                    }}
                >
                    <div className="space-y-2">
                        <label
                            htmlFor="invite-user-name"
                            className="text-sm font-medium"
                        >
                            Nama
                        </label>
                        <Input
                            id="invite-user-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            aria-describedby={
                                form.errors.name
                                    ? 'invite-name-error'
                                    : undefined
                            }
                            aria-invalid={Boolean(form.errors.name)}
                            required
                        />
                        <InputError
                            id="invite-name-error"
                            role="alert"
                            message={form.errors.name}
                        />
                    </div>
                    <div className="space-y-2">
                        <label
                            htmlFor="invite-user-email"
                            className="text-sm font-medium"
                        >
                            Email
                        </label>
                        <Input
                            id="invite-user-email"
                            type="email"
                            value={form.data.email}
                            onChange={(event) =>
                                form.setData('email', event.target.value)
                            }
                            aria-describedby={
                                form.errors.email
                                    ? 'invite-email-error'
                                    : undefined
                            }
                            aria-invalid={Boolean(form.errors.email)}
                            required
                        />
                        <InputError
                            id="invite-email-error"
                            role="alert"
                            message={form.errors.email}
                        />
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
                        <LoadingButton type="submit" loading={form.processing}>
                            {form.processing ? 'Mengirim...' : 'Kirim undangan'}
                        </LoadingButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
