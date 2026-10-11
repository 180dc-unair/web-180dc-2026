import atSignIcon from '@/assets/icons/at-sign.svg';
import communityIcon from '@/assets/icons/community.svg';
import shareIcon from '@/assets/icons/share.svg';
import type { FooterLinkGroup, NavLink, SocialLink } from '@/types/public';

export const ANNOUNCEMENT_TEXT = 'Empowering Change Through Strategic Consulting';

export const MAIN_NAV_LINKS: NavLink[] = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Services', href: '/#services' },
    { label: 'Articles', href: '/articles' },
];

export const SIGN_IN_LINK: NavLink = { label: 'Sign In', href: '/login' };

export const FOOTER_TAGLINE = 'Empowering social impact through student-led excellence.';

export const FOOTER_CREDIT = 'Designed for excellence by 180DC UNAIR Branding Team';

export const NEWSLETTER_TEXT = 'Stay updated with our latest impact stories and insights.';

export const FOOTER_LINK_GROUPS: FooterLinkGroup[] = [
    {
        title: 'Brand Info',
        links: [
            { label: 'Our Mission', href: '/about' },
            { label: 'Team Members', href: '/about#team' },
            { label: 'Global Partners', href: '/#partners' },
            { label: 'Privacy Policy', href: '/privacy-policy' },
        ],
    },
    {
        title: 'Quick Links',
        links: [
            { label: 'Case Studies', href: '/articles' },
            { label: 'Services', href: '/#services' },
            { label: 'Articles', href: '/articles' },
            { label: 'Contact Us', href: '/contact' },
        ],
    },
];

export const SOCIAL_LINKS: SocialLink[] = [
    { label: 'Share', href: '#', icon: shareIcon },
    { label: 'Email', href: '#', icon: atSignIcon },
    { label: 'Community', href: '#', icon: communityIcon },
];
