import loginHero from '@/assets/auth/login-hero.jpg';
import registerHero from '@/assets/auth/register-hero.jpg';
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

export const REGISTER_PAGE = {
    hero: {
        image: registerHero,
        title: 'Join the',
        highlight: '180DC UNAIR Community',
        points: [
            { icon: 'book', text: 'Access exclusive case studies and learning resources' },
            { icon: 'users', text: 'Grow with a network of student consultants' },
        ],
    },
    form: {
        title: 'Create Account',
        description: 'Fill in your details to get started',
        nameLabel: 'Full Name',
        namePlaceholder: 'Your full name',
        usernameLabel: 'Username',
        usernamePlaceholder: 'yourusername',
        emailLabel: 'Email Address',
        emailPlaceholder: 'consultant@180dc.org',
        passwordLabel: 'Password',
        confirmLabel: 'Confirm Password',
        submitLabel: 'Create Account',
        submittingLabel: 'Creating account...',
        errorTitle: 'Registrasi gagal',
    },
    terms: {
        prefix: 'I agree to the',
        and: 'and',
        termsLink: { label: 'Terms of Service', href: '/terms' } satisfies NavLink,
        privacyLink: { label: 'Privacy Policy', href: '/privacy-policy' } satisfies NavLink,
    },
    divider: 'or',
    signIn: { prompt: 'Already have an account?', label: 'Sign In', href: '/login' },
};