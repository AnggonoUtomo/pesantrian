import { useState } from 'react';
import Cropper from 'react-easy-crop';
import type {Area} from 'react-easy-crop';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Props = {
    file: File | null;
    onCancel: () => void;
    onConfirm: (file: File) => void;
};

async function cropFile(
    source: string,
    area: Area,
    name: string,
): Promise<File> {
    const image = new Image();
    image.src = source;
    await image.decode();
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const context = canvas.getContext('2d');

    if (!context) {
throw new Error('Canvas crop tidak tersedia.');
}

    context.drawImage(
        image,
        area.x,
        area.y,
        area.width,
        area.height,
        0,
        0,
        512,
        512,
    );
    const blob = await new Promise<Blob | null>((resolve) =>
        canvas.toBlob(resolve, 'image/webp', 0.9),
    );

    if (!blob) {
throw new Error('Crop avatar gagal dibuat.');
}

    return new File([blob], name.replace(/\.[^.]+$/, '') + '.webp', {
        type: 'image/webp',
    });
}

export function AvatarCropDialog({ file, onCancel, onConfirm }: Props) {
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [area, setArea] = useState<Area | null>(null);
    const [processing, setProcessing] = useState(false);
    const source = file ? URL.createObjectURL(file) : null;

    if (!file || !source) {
return null;
}

    return (
        <Dialog open onOpenChange={(open) => !open && onCancel()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Atur potongan avatar</DialogTitle>
                    <DialogDescription>
                        Geser gambar dengan cursor, lalu atur zoom sebelum
                        menyimpan.
                    </DialogDescription>
                </DialogHeader>
                <div className="relative h-72 overflow-hidden rounded-lg bg-muted">
                    <Cropper
                        image={source}
                        crop={crop}
                        zoom={zoom}
                        aspect={1}
                        onCropChange={setCrop}
                        onZoomChange={setZoom}
                        onCropComplete={(_, pixels) => setArea(pixels)}
                    />
                </div>
                <label className="space-y-2 text-sm">
                    Zoom
                    <input
                        aria-label="Zoom avatar"
                        className="w-full"
                        type="range"
                        min="1"
                        max="3"
                        step="0.1"
                        value={zoom}
                        onChange={(event) =>
                            setZoom(Number(event.target.value))
                        }
                    />
                </label>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                        disabled={processing}
                    >
                        Batal
                    </Button>
                    <Button
                        type="button"
                        disabled={!area || processing}
                        onClick={async () => {
                            if (!area) {
return;
}

                            setProcessing(true);
                            onConfirm(await cropFile(source, area, file.name));
                        }}
                    >
                        {processing ? 'Menyiapkan...' : 'Gunakan potongan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
