import { LandingAbout } from '@/components/public/landing/LandingAbout';
import { LandingArticles } from '@/components/public/landing/LandingArticles';
import { LandingCta } from '@/components/public/landing/LandingCta';
import { LandingHero } from '@/components/public/landing/LandingHero';
import { LandingImpact } from '@/components/public/landing/LandingImpact';
import { LandingPartners } from '@/components/public/landing/LandingPartners';
import { LandingServices } from '@/components/public/landing/LandingServices';
import { LandingStats } from '@/components/public/landing/LandingStats';
import { LandingToolkit } from '@/components/public/landing/LandingToolkit';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function LandingPage() {
    return (
        <PublicLayout title="180DC UNAIR">
            <LandingHero />
            <LandingStats />
            <LandingAbout />
            <LandingImpact />
            <LandingServices />
            <LandingToolkit />
            <LandingPartners />
            <LandingArticles />
            <LandingCta />
        </PublicLayout>
    );
}