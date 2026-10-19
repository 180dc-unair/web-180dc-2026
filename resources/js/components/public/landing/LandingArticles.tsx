import { useRef } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { ArticleCard } from '@/components/public/articles/ArticleCard';
import { FeaturedArticleCard } from '@/components/public/articles/FeaturedArticleCard';
import { LANDING_ARTICLES } from '@/contants/public/landing';
import { useArticles } from '@/hooks/public/useArticles';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingArticles() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    const articlesQuery = useArticles({});
    const apiArticles = articlesQuery.data ?? [];
    const articles = apiArticles.length > 0 ? apiArticles : LANDING_ARTICLES.fallbackItems;
    const [featured, ...others] = articles;
    const sideArticles = others.slice(0, LANDING_ARTICLES.sideLimit);

    return (
        <section ref={sectionRef} className="bg-secondary py-16 lg:py-24">
            <Container className="flex flex-col gap-10">
                <div data-reveal className="flex items-center justify-between gap-4">
                    <h2 className="text-3xl font-extrabold text-muted-foreground lg:text-4xl">{LANDING_ARTICLES.title}</h2>
                    <Link
                        href={LANDING_ARTICLES.viewAll.href}
                        className="flex shrink-0 items-center gap-1 text-sm font-bold text-primary hover:underline"
                    >
                        {LANDING_ARTICLES.viewAll.label}
                        <ArrowRight className="size-4" aria-hidden="true" />
                    </Link>
                </div>
                <div className="grid gap-10 lg:grid-cols-3">
                    <div data-reveal className="lg:col-span-2">
                        {featured && <FeaturedArticleCard article={featured} variant="stacked" />}
                    </div>
                    <ul className="flex flex-col gap-6">
                        {sideArticles.map((article) => (
                            <li key={article.id} data-reveal>
                                <ArticleCard article={article} variant="compact" />
                            </li>
                        ))}
                    </ul>
                </div>
            </Container>
        </section>
    );
}