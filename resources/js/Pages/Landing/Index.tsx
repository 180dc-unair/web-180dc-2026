import { LandingCta } from '@/components/public/landing/LandingCta';
import { LandingHero } from '@/components/public/landing/LandingHero';
import { LandingStats } from '@/components/public/landing/LandingStats';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function LandingPage() {
    return (
        <PublicLayout title="180DC UNAIR">
            <LandingHero />
            <LandingStats />
            <LandingCta />
        </PublicLayout>
    );
}