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

export type BreadcrumbEntry = {
    label: string;
    href?: string;
};

export type Shard = {
    x: string;
    y: string;
    width: number;
    height: number;
    opacity: number;
};

export type FilterOption = {
    label: string;
    value: string;
};

export type MediaRef = {
    id: string;
    url: string;
};

export type ArticleCategory = {
    id: string;
    name: string;
    slug: string;
};

export type ArticleAuthor = {
    id: string;
    name: string;
    username: string;
};

export type Article = {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    status: string;
    view_count: number;
    published_at: string | null;
    author: ArticleAuthor | null;
    category: ArticleCategory | null;
    thumbnail: MediaRef | null;
    created_at: string;
    updated_at: string;
};
