import { LandingHero } from '@/components/public/landing/LandingHero';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function LandingPage() {
    return (
        <PublicLayout title="180DC UNAIR">
            <LandingHero />
        </PublicLayout>
    );
}