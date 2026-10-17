import { AboutHero } from '@/components/public/about/AboutHero';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function AboutIndex() {
    return (
        <PublicLayout title="About">
            <AboutHero />
        </PublicLayout>
    );
}