import { router } from '@inertiajs/react';
import { AlertTriangle, Trash2 } from 'lucide-react';
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
import { LoadingButton } from '@/components/ui/loading-button';
import route from '@/lib/route';

type BulkOperation = 'archive' | 'force-delete';

type Props = {
    open: boolean;
    operation: BulkOperation | null;
    userIds: string[];
    onOpenChange: (open: boolean) => void;
    onCompleted: () => void;
};

export function BulkUserLifecycleDialog({
    open,
    operation,
    userIds,
    onOpenChange,
    onCompleted,
}: Props) {
    const [isProcessing, setIsProcessing] = useState(false);
    const isForceDelete = operation === 'force-delete';
    const targetLabel = `${userIds.length} user`;

    const submit = () => {
        if (operation === null || userIds.length === 0) {
            return;
        }

        setIsProcessing(true);
        router.delete(
            route(
                isForceDelete
                    ? 'system.users.bulk-force-delete'
                    : 'system.users.bulk-destroy',
            ),
            {
                data: { user_ids: userIds },
                preserveScroll: true,
                onSuccess: () => {
                    onCompleted();
                    onOpenChange(false);
                },
                onFinish: () => setIsProcessing(false),
            },
        );
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(nextOpen) => !isProcessing && onOpenChange(nextOpen)}
        >
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--rose flex size-10 items-center justify-center rounded-lg">
                            <AlertTriangle className="size-5" />
                        </span>
                        <div>
                            <DialogTitle>
                                {isForceDelete
                                    ? 'Hapus permanen user terpilih?'
                                    : 'Arsipkan user terpilih?'}
                            </DialogTitle>
                            <DialogDescription className="mt-1">
                                {isForceDelete
                                    ? 'Tindakan ini tidak dapat dibatalkan.'
                                    : 'User tidak akan dihapus permanen dan tidak dapat masuk daftar aktif.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>
                <div className="dashboard-subcard rounded-xl border p-4 text-sm">
                    <p className="font-semibold">{targetLabel} dipilih</p>
                    <p className="mt-1 text-muted-foreground">
                        Operasi hanya berjalan bila seluruh user terpilih sesuai
                        state lifecycle dan bukan SuperSystem.
                    </p>
                </div>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        disabled={isProcessing}
                    >
                        Batal
                    </Button>
                    <LoadingButton
                        type="button"
                        variant="destructive"
                        onClick={submit}
                        loading={isProcessing}
                        disabled={operation === null || userIds.length === 0}
                    >
                        <Trash2 className="size-4" />
                        {isProcessing
                            ? isForceDelete
                                ? 'Menghapus...'
                                : 'Mengarsipkan...'
                            : isForceDelete
                              ? 'Hapus permanen'
                              : 'Arsipkan user'}
                    </LoadingButton>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
