import { AboutAlumni } from '@/components/public/about/AboutAlumni';
import { AboutHero } from '@/components/public/about/AboutHero';
import { AboutJourney } from '@/components/public/about/AboutJourney';
import { AboutPartners } from '@/components/public/about/AboutPartners';
import { AboutVisionMission } from '@/components/public/about/AboutVisionMission';
import { PublicLayout } from '@/components/public/layout/PublicLayout';

export default function AboutIndex() {
    return (
        <PublicLayout title="About">
            <AboutHero />
            <AboutVisionMission />
            <AboutPartners />
            <AboutAlumni />
            <AboutJourney />
        </PublicLayout>
    );
}