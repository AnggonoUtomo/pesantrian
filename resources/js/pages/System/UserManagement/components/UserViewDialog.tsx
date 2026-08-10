import { router } from '@inertiajs/react';
import {
    Clock3,
    Eye,
    MailCheck,
    MailX,
    Pencil,
    ShieldCheck,
} from 'lucide-react';
import { useState } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
import { Input } from '@/components/ui/input';
import route from '@/lib/route';
import type { UserManagementUser } from '../types';
import { AvatarCropDialog } from './AvatarCropDialog';

type Props = {
    open: boolean;
    user: UserManagementUser | null;
    canEdit: boolean;
    canImpersonate: boolean;
    canAssignRole: boolean;
    onOpenChange: (open: boolean) => void;
    onEdit: () => void;
    onImpersonate: () => void;
    onAssignRole: () => void;
    onAvatarUpdated: (previewUrl: string) => void;
};

export function UserViewDialog({
    open,
    user,
    canEdit,
    canImpersonate,
    canAssignRole,
    onOpenChange,
    onEdit,
    onImpersonate,
    onAssignRole,
    onAvatarUpdated,
}: Props) {
    const [uploading, setUploading] = useState(false);
    const [avatarFile, setAvatarFile] = useState<File | null>(null);
    const [pendingCropFile, setPendingCropFile] = useState<File | null>(null);

    if (!user) {
        return null;
    }

    const statusLabel = {
        active: 'Aktif',
        inactive: 'Tidak aktif',
        suspended: 'Ditangguhkan',
    }[user.status];
    const initials = user.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
    const uploadAvatar = (file: File | null) => {
        if (!file) {
            return;
        }

        setUploading(true);
        router.post(
            route('system.users.avatar.update', user.id),
            { avatar: file },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => setUploading(false),
                onSuccess: () => onAvatarUpdated(URL.createObjectURL(file)),
            },
        );
    };

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
                <div className="space-y-4">
                    <section className="dashboard-subcard space-y-4 rounded-xl border p-4">
                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Identitas
                        </p>
                        <div className="flex items-center gap-3">
                            <Avatar className="size-11 rounded-lg">
                                {user.avatarUrl ? (
                                    <AvatarImage src={user.avatarUrl} alt="" />
                                ) : null}
                                <AvatarFallback className="rounded-lg bg-primary/10 text-primary">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                            <div className="min-w-0">
                                <p className="truncate font-semibold">
                                    {user.name}
                                </p>
                                <p className="truncate text-sm text-muted-foreground">
                                    {user.email}
                                </p>
                            </div>
                            <Badge className="ml-auto">{statusLabel}</Badge>
                        </div>
                        {canEdit && !user.isProtected && user.deletedAt === null ? (
                            <div className="flex flex-wrap items-center gap-2">
                                <label
                                    htmlFor="user-avatar-upload"
                                    className="sr-only"
                                >
                                    Upload avatar
                                </label>
                                <Input
                                    id="user-avatar-upload"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    disabled={uploading}
                                    onChange={(event) =>
                                        setPendingCropFile(
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                    className="max-w-xs"
                                />
                                <Button
                                    type="button"
                                    disabled={avatarFile === null || uploading}
                                    onClick={() => uploadAvatar(avatarFile)}
                                >
                                    {uploading
                                        ? 'Menyimpan avatar...'
                                        : 'Simpan avatar'}
                                </Button>
                                {user.avatarUrl ? (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        disabled={uploading}
                                        onClick={() =>
                                            router.delete(
                                                route(
                                                    'system.users.avatar.delete',
                                                    user.id,
                                                ),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        Hapus avatar
                                    </Button>
                                ) : null}
                                <p className="text-xs text-muted-foreground">
                                    JPEG, PNG, atau WebP, maksimal 2 MB.
                                </p>
                            </div>
                        ) : null}
                    </section>
                    <section className="dashboard-subcard space-y-3 rounded-xl border p-4">
                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Akses
                        </p>
                        <dl className="grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Role efektif
                                </dt>
                                <dd className="mt-1 flex flex-wrap gap-1">
                                    {user.roles.length > 0
                                        ? user.roles.map((role) => (
                                              <Badge
                                                  key={role}
                                                  variant="outline"
                                                  className="dashboard-badge"
                                              >
                                                  {role}
                                              </Badge>
                                          ))
                                        : 'Belum ada role'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Status
                                </dt>
                                <dd className="mt-1">{statusLabel}</dd>
                            </div>
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Verifikasi email
                                </dt>
                                <dd className="mt-1 flex items-center gap-1">
                                    {user.emailVerified ? (
                                        <>
                                            <MailCheck className="size-4 text-emerald-500" />{' '}
                                            Terverifikasi
                                        </>
                                    ) : (
                                        <>
                                            <MailX className="size-4 text-amber-500" />{' '}
                                            Belum verifikasi
                                        </>
                                    )}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs text-muted-foreground">
                                    Perlindungan
                                </dt>
                                <dd className="mt-1 flex items-center gap-1">
                                    {user.isProtected ? (
                                        <>
                                            <ShieldCheck className="size-4 text-emerald-500" />{' '}
                                            SuperSystem
                                        </>
                                    ) : (
                                        'Standar'
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </section>
                    <section className="dashboard-subcard space-y-3 rounded-xl border p-4">
                        <p className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Aktivitas
                        </p>
                        <dl className="grid gap-3 text-sm sm:grid-cols-2">
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
                                    Terakhir login
                                </dt>
                                <dd className="mt-1 flex items-center gap-1">
                                    <Clock3 className="size-4 text-muted-foreground" />
                                    {user.lastLoginAt
                                        ? new Intl.DateTimeFormat('id-ID', {
                                              dateStyle: 'medium',
                                              timeStyle: 'short',
                                          }).format(new Date(user.lastLoginAt))
                                        : 'Belum pernah login'}
                                </dd>
                            </div>
                        </dl>
                    </section>
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
                    {canAssignRole && !user.isProtected ? (
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={onAssignRole}
                        >
                            Atur role
                        </Button>
                    ) : null}
                </DialogFooter>
            </DialogContent>
            <AvatarCropDialog
                file={pendingCropFile}
                onCancel={() => setPendingCropFile(null)}
                onConfirm={(file) => {
                    setAvatarFile(file);
                    setPendingCropFile(null);
                }}
            />
        </Dialog>
    );
}
