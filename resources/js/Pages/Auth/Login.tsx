import { useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from '@inertiajs/react';
import { Eye, EyeOff, LockKeyhole, Mail, RefreshCw } from 'lucide-react';
import { Controller, useForm } from 'react-hook-form';
import { z } from 'zod';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LOGIN_PAGE } from '@/contants/public/auth';
import { useLogin } from '@/hooks/public/useAuth';

const loginSchema = z.object({
    email: z.email('Format email tidak valid.'),
    password: z.string().min(1, 'Password wajib diisi.'),
    remember: z.boolean(),
});

type LoginForm = z.infer<typeof loginSchema>;

const inputClassName = 'h-12 rounded-xl border-white/10 bg-black/15 text-white placeholder:text-white/50';

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);
    const login = useLogin();
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
        login.mutate(data, {
            onSuccess: () => router.visit('/'),
            onError: (error) => setError('root', { type: 'server', message: error.message }),
        });
    });

    const { hero, form } = LOGIN_PAGE;

    return (
        <PublicLayout title="Sign In">
            <section className="grid lg:min-h-[calc(100vh-12rem)] lg:grid-cols-2">
                <div className="relative hidden items-center overflow-hidden lg:flex">
                    <img src={hero.image} alt="" decoding="async" className="absolute inset-0 size-full object-cover" />
                    <div aria-hidden="true" className="absolute inset-0 bg-linear-to-t from-black/70 via-black/40 to-primary/40" />
                    <div className="relative flex max-w-lg flex-col gap-6 px-16 py-24 text-white">
                        <h1 className="text-5xl leading-tight font-extrabold">
                            {hero.title} <span className="text-primary">{hero.highlight}</span>
                        </h1>
                        <p className="text-lg leading-8 text-white/90">{hero.description}</p>
                    </div>
                </div>

                <div className="flex items-center justify-center bg-muted px-4 py-12 sm:px-8">
                    <div className="w-full max-w-md rounded-3xl bg-muted-foreground p-8 text-white shadow-xl sm:p-10">
                        <div className="flex flex-col items-center gap-3 text-center">
                            <span className="flex size-12 items-center justify-center rounded-full bg-primary text-white">
                                <RefreshCw className="size-5" aria-hidden="true" />
                            </span>
                            <h2 className="text-3xl font-extrabold text-primary">{form.title}</h2>
                            <p className="text-sm text-white/80">{form.description}</p>
                        </div>

                        <form onSubmit={submit} className="mt-8 flex flex-col gap-5">
                            {errors.root && (
                                <Alert variant="destructive">
                                    <LockKeyhole />
                                    <AlertTitle>{form.errorTitle}</AlertTitle>
                                    <AlertDescription>{errors.root.message}</AlertDescription>
                                </Alert>
                            )}

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="email" className="text-sm font-semibold text-white">
                                    {form.emailLabel}
                                </Label>
                                <div className="relative">
                                    <Mail className="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-white/50" aria-hidden="true" />
                                    <Input
                                        id="email"
                                        type="email"
                                        autoComplete="email"
                                        placeholder={form.emailPlaceholder}
                                        className={`${inputClassName} pl-11`}
                                        aria-invalid={Boolean(errors.email)}
                                        {...register('email')}
                                    />
                                </div>
                                {errors.email && <p className="text-xs text-red-300">{errors.email.message}</p>}
                            </div>

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="password" className="text-sm font-semibold text-white">
                                    {form.passwordLabel}
                                </Label>
                                <div className="relative">
                                    <LockKeyhole className="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-white/50" aria-hidden="true" />
                                    <Input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        autoComplete="current-password"
                                        className={`${inputClassName} px-11`}
                                        aria-invalid={Boolean(errors.password)}
                                        {...register('password')}
                                    />
                                    <button
                                        type="button"
                                        className="absolute top-1/2 right-3 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-white/60 transition-colors hover:text-white focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
                                        onMouseDown={(event) => event.preventDefault()}
                                        onClick={() => setShowPassword((value) => !value)}
                                        aria-controls="password"
                                        aria-pressed={showPassword}
                                        aria-label={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                                    >
                                        {showPassword ? <EyeOff className="size-4" aria-hidden="true" /> : <Eye className="size-4" aria-hidden="true" />}
                                    </button>
                                </div>
                                {errors.password && <p className="text-xs text-red-300">{errors.password.message}</p>}
                            </div>

                            <div className="flex items-center justify-between gap-4 text-sm">
                                <label className="flex cursor-pointer items-center gap-2 font-medium">
                                    <Controller
                                        name="remember"
                                        control={control}
                                        render={({ field }) => (
                                            <Checkbox
                                                checked={field.value}
                                                onCheckedChange={(checked) => field.onChange(checked === true)}
                                                className="border-white/40"
                                            />
                                        )}
                                    />
                                    {form.rememberLabel}
                                </label>
                                <Link href={LOGIN_PAGE.forgotPassword.href} className="font-medium text-primary hover:underline">
                                    {LOGIN_PAGE.forgotPassword.label}
                                </Link>
                            </div>

                            <Button
                                type="submit"
                                size="lg"
                                className="h-12 w-full rounded-full font-semibold text-white shadow-lg shadow-primary/30 hover:bg-primary/90"
                                disabled={login.isPending}
                            >
                                {login.isPending ? form.submittingLabel : form.submitLabel}
                            </Button>
                        </form>

                        <p className="mt-6 text-center text-sm text-white/90">
                            {LOGIN_PAGE.register.prompt}{' '}
                            <Link href={LOGIN_PAGE.register.href} className="font-semibold text-primary hover:underline">
                                {LOGIN_PAGE.register.label}
                            </Link>
                        </p>

                        <p className="mt-6 border-t border-white/10 pt-6 text-center text-xs leading-5 text-white/60">
                            {LOGIN_PAGE.termsPrefix}{' '}
                            <Link href={LOGIN_PAGE.termsLink.href} className="underline">
                                {LOGIN_PAGE.termsLink.label}
                            </Link>{' '}
                            and{' '}
                            <Link href={LOGIN_PAGE.privacyLink.href} className="underline">
                                {LOGIN_PAGE.privacyLink.label}
                            </Link>
                            .
                        </p>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}