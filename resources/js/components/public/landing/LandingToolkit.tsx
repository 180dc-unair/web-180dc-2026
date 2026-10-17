import { useRef } from 'react';
import { ProductCard } from '@/components/public/store/ProductCard';
import { LANDING_TOOLKIT } from '@/contants/public/landing';
import { useProducts } from '@/hooks/public/useProducts';
import { useReveal } from '@/hooks/public/useReveal';
import { Container } from '../layout/Container';

export function LandingToolkit() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    const productsQuery = useProducts();
    const apiProducts = productsQuery.data ?? [];
    const products = apiProducts.length > 0 ? apiProducts.slice(0, LANDING_TOOLKIT.limit) : LANDING_TOOLKIT.fallbackItems;

    return (
        <section ref={sectionRef} className="bg-background py-16 lg:py-24">
            <Container className="flex flex-col gap-12">
                <h2 data-reveal className="text-center text-3xl font-extrabold text-muted-foreground lg:text-4xl">
                    {LANDING_TOOLKIT.title}
                </h2>
                <ul className="grid items-start gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {products.map((product) => (
                        <li key={product.id} data-reveal>
                            <ProductCard product={product} variant="compact" />
                        </li>
                    ))}
                </ul>
            </Container>
        </section>
    );
}