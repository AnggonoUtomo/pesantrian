import { Keyboard } from 'lucide-react';

export function UserShortcutBar() {
    return (
        <div className="dashboard-shortcut-bar flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border px-3 py-2 text-xs">
            <span className="flex items-center gap-2 font-medium">
                <Keyboard aria-hidden="true" className="size-4" />
                Shortcut
            </span>
            <span>
                <kbd>Shift</kbd> + <kbd>A</kbd> tambah user
            </span>
            <span>
                <kbd>/</kbd> cari user
            </span>
            <span>
                <kbd>Enter</kbd> buka detail
            </span>
            <span>
                <kbd>Esc</kbd> tutup modal
            </span>
        </div>
    );
}
