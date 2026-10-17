import loginHero from '@/assets/auth/login-hero.jpg';
import type { NavLink } from '@/types/public';

export const LOGIN_PAGE = {
    hero: {
        image: loginHero,
        title: 'Welcome Back to',
        highlight: '180DC UNAIR',
        description:
            'Sign in to access exclusive content, case studies, and advanced features for our student consultants.',
    },
    form: {
        title: 'Sign In',
        description: 'Enter your credentials to continue',
        emailLabel: 'Email Address',
        emailPlaceholder: 'consultant@180dc.org',
        passwordLabel: 'Password',
        rememberLabel: 'Remember me',
        submitLabel: 'Sign In',
        submittingLabel: 'Signing in...',
        errorTitle: 'Login gagal',
    },
    forgotPassword: { label: 'Forgot Password?', href: '#' } satisfies NavLink,
    register: { prompt: "Don't have an account?", label: 'Register', href: '/register' },
    termsPrefix: 'By signing in, you agree to our',
    termsLink: { label: 'Terms of Service', href: '/terms' } satisfies NavLink,
    privacyLink: { label: 'Privacy Policy', href: '/privacy-policy' } satisfies NavLink,
};