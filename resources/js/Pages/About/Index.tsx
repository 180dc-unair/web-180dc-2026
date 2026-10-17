import { AboutHero } from '@/components/public/about/AboutHero';
import { AboutVisionMission } from '@/components/public/about/AboutVisionMission';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function AboutIndex() {
    return (
        <PublicLayout title="About">
            <AboutHero />
            <AboutVisionMission />
        </PublicLayout>
    );
}