import { LandingAbout } from '@/components/public/landing/LandingAbout';
import { LandingCta } from '@/components/public/landing/LandingCta';
import { LandingHero } from '@/components/public/landing/LandingHero';
import { LandingImpact } from '@/components/public/landing/LandingImpact';
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
            <LandingCta />
        </PublicLayout>
    );
}