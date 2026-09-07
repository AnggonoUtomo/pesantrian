import { BookOpen, ClipboardPlus, Target } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    canManage: boolean;
    canRecord: boolean;
    onCreateProgram: () => void;
    onCreateTarget: () => void;
    onCreateSubmission: () => void;
};

export function TahfidzActionBar({
    canManage,
    canRecord,
    onCreateProgram,
    onCreateTarget,
    onCreateSubmission,
}: Props) {
    if (!canManage && !canRecord) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-2">
            {canManage ? (
                <>
                    <Button type="button" variant="outline" onClick={onCreateProgram}>
                        <BookOpen className="size-4" aria-hidden="true" />
                        Tambah program
                    </Button>
                    <Button type="button" variant="outline" onClick={onCreateTarget}>
                        <Target className="size-4" aria-hidden="true" />
                        Tambah target
                    </Button>
                </>
            ) : null}
            {canRecord ? (
                <Button type="button" onClick={onCreateSubmission}>
                    <ClipboardPlus className="size-4" aria-hidden="true" />
                    Tambah setoran
                </Button>
            ) : null}
        </div>
    );
}
