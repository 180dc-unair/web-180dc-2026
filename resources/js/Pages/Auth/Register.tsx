import { useState } from 'react';
import { zodResolver } from '@hookform/resolvers/zod';
import { Link, router } from '@inertiajs/react';
import { AtSign, BookOpen, Eye, EyeOff, LockKeyhole, Mail, User, UserPlus, Users } from 'lucide-react';
import { Controller, useForm } from 'react-hook-form';
import type { UseFormRegisterReturn } from 'react-hook-form';
import { z } from 'zod';
import { PublicLayout } from '@/components/public/layout/PublicLayout';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { REGISTER_PAGE } from '@/contants/public/auth';
import { useRegister } from '@/hooks/public/useAuth';

const registerSchema = z
    .object({
        name: z.string().trim().min(1, 'Nama wajib diisi.').max(255),
        username: z.string().trim().min(3, 'Username minimal 3 karakter.').max(255),
        email: z.email('Format email tidak valid.'),
        password: z.string().min(8, 'Password minimal 8 karakter.'),
        password_confirmation: z.string().min(1, 'Konfirmasi password wajib diisi.'),
        terms: z.boolean().refine((value) => value, 'Kamu harus menyetujui Terms dan Privacy Policy.'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Konfirmasi password tidak cocok.',
        path: ['password_confirmation'],
    });

type RegisterForm = z.infer<typeof registerSchema>;

const inputClassName = 'h-12 rounded-xl border-white/10 bg-black/15 text-white placeholder:text-white/50';
const iconClassName = 'absolute top-1/2 left-4 size-4 -translate-y-1/2 text-white/50';

type FieldProps = {
    id: string;
    label: string;
    icon: React.ReactNode;
    error?: string;
    children: React.ReactNode;
};

function Field({ id, label, icon, error, children }: FieldProps) {
    return (
        <div className="flex flex-col gap-2">
            <Label htmlFor={id} className="text-sm font-semibold text-white">
                {label}
            </Label>
            <div className="relative">
                {icon}
                {children}
            </div>
            {error && <p className="text-xs text-red-300">{error}</p>}
        </div>
    );
}

type PasswordFieldProps = {
    id: string;
    label: string;
    autoComplete: string;
    error?: string;
    registration: UseFormRegisterReturn;
};

function PasswordField({ id, label, autoComplete, error, registration }: PasswordFieldProps) {
    const [visible, setVisible] = useState(false);

    return (
        <Field
            id={id}
            label={label}
            error={error}
            icon={<LockKeyhole className={iconClassName} aria-hidden="true" />}
        >
            <Input
                id={id}
                type={visible ? 'text' : 'password'}
                autoComplete={autoComplete}
                className={`${inputClassName} px-11`}
                aria-invalid={Boolean(error)}
                {...registration}
            />
            <button
                type="button"
                className="absolute top-1/2 right-3 inline-flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-white/60 transition-colors hover:text-white focus-visible:ring-2 focus-visible:ring-primary focus-visible:outline-none"
                onMouseDown={(event) => event.preventDefault()}
                onClick={() => setVisible((value) => !value)}
                aria-controls={id}
                aria-pressed={visible}
                aria-label={visible ? 'Sembunyikan password' : 'Tampilkan password'}
            >
                {visible ? <EyeOff className="size-4" aria-hidden="true" /> : <Eye className="size-4" aria-hidden="true" />}
            </button>
        </Field>
    );
}

const pointIcons = { book: BookOpen, users: Users } as const;

export default function Register() {
    const registerUser = useRegister();
    const {
        control,
        formState: { errors },
        handleSubmit,
        register,
        setError,
    } = useForm<RegisterForm>({
        defaultValues: { name: '', username: '', email: '', password: '', password_confirmation: '', terms: false },
        resolver: zodResolver(registerSchema),
    });

    const submit = handleSubmit(({ name, username, email, password, password_confirmation }) => {
        registerUser.mutate(
            { name, username, email, password, password_confirmation },
            {
                onSuccess: () => router.visit('/login'),
                onError: (error) => setError('root', { type: 'server', message: error.message }),
            },
        );
    });

    const { hero, form, terms } = REGISTER_PAGE;

    return (
        <PublicLayout title="Create Account">
            <section className="grid lg:min-h-[calc(100vh-12rem)] lg:grid-cols-2">
                <div className="relative hidden items-center overflow-hidden lg:flex">
                    <img src={hero.image} alt="" decoding="async" className="absolute inset-0 size-full object-cover" />
                    <div aria-hidden="true" className="absolute inset-0 bg-linear-to-t from-black/70 via-black/40 to-primary/40" />
                    <div className="relative flex max-w-lg flex-col gap-6 px-16 py-24 text-white">
                        <h1 className="text-5xl leading-tight font-extrabold">
                            {hero.title} <span className="text-primary">{hero.highlight}</span>
                        </h1>
                        <ul className="flex flex-col gap-4">
                            {hero.points.map((point) => {
                                const Icon = pointIcons[point.icon as keyof typeof pointIcons];

                                return (
                                    <li key={point.text} className="flex items-center gap-3 text-lg text-white/90">
                                        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-white">
                                            <Icon className="size-5" aria-hidden="true" />
                                        </span>
                                        {point.text}
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                </div>

                <div className="flex items-center justify-center bg-muted px-4 py-12 sm:px-8">
                    <div className="w-full max-w-md rounded-3xl bg-muted-foreground p-8 text-white shadow-xl sm:p-10">
                        <div className="flex flex-col items-center gap-3 text-center">
                            <span className="flex size-12 items-center justify-center rounded-full bg-primary text-white">
                                <UserPlus className="size-5" aria-hidden="true" />
                            </span>
                            <h2 className="text-3xl font-extrabold text-primary">{form.title}</h2>
                            <p className="text-sm text-white/80">{form.description}</p>
                        </div>

                        <form onSubmit={submit} className="mt-8 flex flex-col gap-5" noValidate>
                            {errors.root && (
                                <Alert variant="destructive">
                                    <LockKeyhole />
                                    <AlertTitle>{form.errorTitle}</AlertTitle>
                                    <AlertDescription>{errors.root.message}</AlertDescription>
                                </Alert>
                            )}

                            <Field
                                id="name"
                                label={form.nameLabel}
                                error={errors.name?.message}
                                icon={<User className={iconClassName} aria-hidden="true" />}
                            >
                                <Input
                                    id="name"
                                    autoComplete="name"
                                    placeholder={form.namePlaceholder}
                                    className={`${inputClassName} pl-11`}
                                    aria-invalid={Boolean(errors.name)}
                                    {...register('name')}
                                />
                            </Field>

                            <Field
                                id="username"
                                label={form.usernameLabel}
                                error={errors.username?.message}
                                icon={<AtSign className={iconClassName} aria-hidden="true" />}
                            >
                                <Input
                                    id="username"
                                    autoComplete="username"
                                    placeholder={form.usernamePlaceholder}
                                    className={`${inputClassName} pl-11`}
                                    aria-invalid={Boolean(errors.username)}
                                    {...register('username')}
                                />
                            </Field>

                            <Field
                                id="email"
                                label={form.emailLabel}
                                error={errors.email?.message}
                                icon={<Mail className={iconClassName} aria-hidden="true" />}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    autoComplete="email"
                                    placeholder={form.emailPlaceholder}
                                    className={`${inputClassName} pl-11`}
                                    aria-invalid={Boolean(errors.email)}
                                    {...register('email')}
                                />
                            </Field>

                            <PasswordField
                                id="password"
                                label={form.passwordLabel}
                                autoComplete="new-password"
                                error={errors.password?.message}
                                registration={register('password')}
                            />

                            <PasswordField
                                id="password_confirmation"
                                label={form.confirmLabel}
                                autoComplete="new-password"
                                error={errors.password_confirmation?.message}
                                registration={register('password_confirmation')}
                            />

                            <div className="flex flex-col gap-2">
                                <label className="flex cursor-pointer items-start gap-2 text-xs leading-5 text-white/90">
                                    <Controller
                                        name="terms"
                                        control={control}
                                        render={({ field }) => (
                                            <Checkbox
                                                checked={field.value}
                                                onCheckedChange={(checked) => field.onChange(checked === true)}
                                                className="mt-0.5 border-white/40"
                                            />
                                        )}
                                    />
                                    <span>
                                        {terms.prefix}{' '}
                                        <Link href={terms.termsLink.href} className="text-primary underline">
                                            {terms.termsLink.label}
                                        </Link>{' '}
                                        {terms.and}{' '}
                                        <Link href={terms.privacyLink.href} className="text-primary underline">
                                            {terms.privacyLink.label}
                                        </Link>
                                    </span>
                                </label>
                                {errors.terms && <p className="text-xs text-red-300">{errors.terms.message}</p>}
                            </div>

                            <Button
                                type="submit"
                                size="lg"
                                className="h-12 w-full rounded-full font-semibold text-white shadow-lg shadow-primary/30 hover:bg-primary/90"
                                disabled={registerUser.isPending}
                            >
                                {registerUser.isPending ? form.submittingLabel : form.submitLabel}
                            </Button>
                        </form>

                        <div className="my-6 flex items-center gap-4 text-xs text-white/60">
                            <span className="h-px flex-1 bg-white/10" />
                            {REGISTER_PAGE.divider}
                            <span className="h-px flex-1 bg-white/10" />
                        </div>

                        <p className="text-center text-sm text-white/90">
                            {REGISTER_PAGE.signIn.prompt}{' '}
                            <Link href={REGISTER_PAGE.signIn.href} className="font-semibold text-primary hover:underline">
                                {REGISTER_PAGE.signIn.label}
                            </Link>
                        </p>
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}