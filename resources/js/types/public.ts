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

export type HeroImage = {
    src: string;
    className: string;
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

export type ProductType = 'digital' | 'physical';

export type ProductCategory = {
    id: string;
    name: string;
    slug: string;
};

export type Product = {
    id: string;
    title: string;
    slug: string;
    type: ProductType;
    status: string;
    short_description: string | null;
    description: string | null;
    price: string;
    stock: number;
    is_featured: boolean;
    is_best_seller: boolean;
    sold_count: number;
    digital_file_url: string | null;
    category: ProductCategory | null;
    image: MediaRef | null;
    created_at: string;
    updated_at: string;
};

export type Client = {
    id: string;
    name: string;
    slug: string;
    type: string;
    logo: MediaRef | null;
    website_url: string | null;
    is_featured: boolean;
    sort_order: number;
};

export type AuthUser = {
    id: string | number;
    name: string;
    username: string;
    email: string;
    role: string;
    created_at: string;
    updated_at: string;
};

export type LoginPayload = {
    email: string;
    password: string;
};

export type AuthResult = {
    user: AuthUser;
    token: string;
};

export type RegisterPayload = {
    name: string;
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
};