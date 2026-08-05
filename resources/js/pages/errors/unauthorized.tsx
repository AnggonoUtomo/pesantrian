import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Home, LockKeyhole, ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';
import route from '@/lib/route';

type Props = {
    status?: number;
    message?: string;
};

export default function Unauthorized({ status = 403, message }: Props) {
    return (
        <>
            <Head title="Akses Ditolak" />
            <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-6 py-12 text-white">
                <div className="pointer-events-none absolute -top-32 -left-24 size-80 rounded-full bg-primary/30 blur-3xl" />
                <div className="pointer-events-none absolute -right-24 -bottom-32 size-96 rounded-full bg-blue-500/20 blur-3xl" />
                <section className="relative w-full max-w-xl rounded-3xl border border-white/10 bg-white/10 p-8 text-center shadow-2xl backdrop-blur-xl sm:p-12">
                    <div className="mx-auto flex size-20 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-300 ring-1 ring-rose-300/20">
                        <ShieldAlert className="size-10" aria-hidden="true" />
                    </div>
                    <p className="mt-8 text-sm font-semibold tracking-[0.3em] text-primary-foreground/60 uppercase">
                        Error {status}
                    </p>
                    <h1 className="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                        Akses belum tersedia
                    </h1>
                    <p className="mx-auto mt-4 max-w-md text-sm leading-6 text-slate-300">
                        {message ??
                            'Kamu tidak memiliki izin untuk membuka halaman ini.'}
                    </p>
                    <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <Button
                            asChild
                            className="bg-white text-slate-950 hover:bg-slate-100"
                        >
                            <Link href={route('home')}>
                                <Home className="size-4" /> Kembali ke beranda
                            </Link>
                        </Button>
                        <Button
                            asChild
                            variant="outline"
                            className="border-white/20 bg-transparent text-white hover:bg-white/10 hover:text-white"
                        >
                            <Link href={route('login')}>
                                <LockKeyhole className="size-4" /> Login dengan
                                akun lain
                            </Link>
                        </Button>
                    </div>
                    <button
                        type="button"
                        onClick={() => window.history.back()}
                        className="mx-auto mt-6 inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-white"
                    >
                        <ArrowLeft className="size-4" /> Kembali ke halaman
                        sebelumnya
                    </button>
                </section>
            </main>
        </>
    );
}
