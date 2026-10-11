export type NavLink = {
    label: string;
    href: string;
};

export type FooterLinkGroup = {
    title: string;
    links: NavLink[];
};

export type SocialLink = NavLink & {
    icon: string;
};
