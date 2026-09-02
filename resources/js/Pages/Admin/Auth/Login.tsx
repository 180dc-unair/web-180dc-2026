import { useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { Head, router } from '@inertiajs/react';
import { ArrowRight, Eye, EyeOff, LockKeyhole, Mail } from 'lucide-react';
import { Controller, useForm } from 'react-hook-form';
import { z } from 'zod';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const loginSchema = z.object({
    email: z.email('Format email tidak valid.'),
    password: z.string().min(1, 'Password wajib diisi.'),
    remember: z.boolean(),
});

type LoginForm = z.infer<typeof loginSchema>;

export default function AdminLogin() {
    const [showPassword, setShowPassword] = useState(false);
    const [processing, setProcessing] = useState(false);
    const {
        control,
        formState: { errors },
        handleSubmit,
        register,
        setError,
    } = useForm<LoginForm>({
        defaultValues: { email: '', password: '', remember: false },
        resolver: zodResolver(loginSchema),
    });

    const submit = handleSubmit((data) => {
        router.post('/admin/login', data, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onError: (serverErrors) => {
                Object.entries(serverErrors).forEach(([field, message]) => {
                    if (field === 'email' || field === 'password') {
                        setError(field, { type: 'server', message });
                    }
                });
            },
            onFinish: () => setProcessing(false),
        });
    });

    return (
        <>
            <Head title="Login Admin" />
            <main className="relative grid min-h-screen place-items-center overflow-hidden bg-muted/40 px-4 py-10 text-black">
                <div className="absolute inset-x-0 top-0 h-2 bg-primary" aria-hidden="true" />
                <div className="absolute -left-24 top-24 size-72 rounded-full bg-primary/10 blur-3xl" aria-hidden="true" />
                <div className="absolute -right-24 bottom-12 size-80 rounded-full bg-secondary/70 blur-3xl" aria-hidden="true" />

                <div className="relative w-full max-w-md">
                    <div className="mb-7 flex items-center justify-center gap-3">
                        <span className="grid size-12 place-items-center rounded-2xl bg-primary text-lg font-extrabold text-primary-foreground shadow-lg shadow-primary/20">
                            180
                        </span>
                        <div>
                            <p className="font-bold tracking-tight text-black">180DC UNAIR</p>
                            <p className="text-xs text-black/60">Administration Workspace</p>
                        </div>
                    </div>

                    <Card className="border-border/70 text-black shadow-xl shadow-foreground/5">
                        <CardHeader className="space-y-2 text-center">
                            <CardTitle className="text-2xl">Selamat datang kembali</CardTitle>
                            <CardDescription className="text-black/60">
                                Masuk dengan akun administrator untuk mengelola konten.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-5">
                                {errors.email && (
                                    <Alert variant="destructive">
                                        <LockKeyhole />
                                        <AlertTitle>Login gagal</AlertTitle>
                                        <AlertDescription>{errors.email.message}</AlertDescription>
                                    </Alert>
                                )}

                                <div className="space-y-2">
                                    <Label htmlFor="email" className="text-black">Email</Label>
                                    <div className="relative">
                                        <Mail className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-black/45" />
                                        <Input
                                            id="email"
                                            type="email"
                                            autoComplete="email"
                                            autoFocus
                                            className="h-11 pl-10 text-black placeholder:text-black/40"
                                            placeholder="admin@180dc.com"
                                            {...register('email')}
                                            aria-invalid={Boolean(errors.email)}
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password" className="text-black">Password</Label>
                                    <div className="relative">
                                        <LockKeyhole className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-black/45" />
                                        <Input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            autoComplete="current-password"
                                            className="h-11 px-10 text-black"
                                            {...register('password')}
                                            aria-invalid={Boolean(errors.password)}
                                        />
                                        <button
                                            type="button"
                                            className="absolute right-2 top-1/2 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-black/60 transition-colors hover:bg-black/5 hover:text-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                            onMouseDown={(event) => event.preventDefault()}
                                            onClick={() => setShowPassword((value) => !value)}
                                            aria-controls="password"
                                            aria-pressed={showPassword}
                                            aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                                        >
                                            {showPassword ? <EyeOff aria-hidden="true" /> : <Eye aria-hidden="true" />}
                                        </button>
                                    </div>
                                    {errors.password && <p className="text-xs text-destructive">{errors.password.message}</p>}
                                </div>

                                <label className="flex cursor-pointer items-center gap-2 text-sm text-black/60">
                                    <Controller
                                        name="remember"
                                        control={control}
                                        render={({ field }) => (
                                            <Checkbox checked={field.value} onCheckedChange={(checked) => field.onChange(checked === true)} />
                                        )}
                                    />
                                    Ingat sesi saya
                                </label>

                                <Button type="submit" size="lg" className="h-11 w-full" disabled={processing}>
                                    {processing ? 'Memeriksa akun...' : 'Masuk ke Dashboard'}
                                    {!processing && <ArrowRight data-icon="inline-end" />}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                    <p className="mt-5 text-center text-xs text-black/60">
                        Area terbatas untuk administrator 180 Degrees Consulting UNAIR.
                    </p>
                </div>
            </main>
        </>
    );
}
