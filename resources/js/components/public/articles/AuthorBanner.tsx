import grainTexture from '@/assets/hero/grain-texture.jpg';
import { AUTHOR_BANNER_SHARDS, AUTHOR_ORGANIZATION } from '@/contants/public/articles';
import { BRAND_HASHTAGS } from '@/contants/public/brand';
import { Container } from '../layout/Container';
import { ShardCluster } from '../ShardCluster';
import { AuthorAvatar } from './AuthorAvatar';

export function AuthorBanner({ name }: { name: string }) {
    return (
        <section aria-label="Author" className="relative overflow-hidden bg-primary">
            <img
                src={grainTexture}
                alt=""
                className="pointer-events-none absolute inset-0 size-full object-cover opacity-16 mix-blend-multiply"
            />
            <Container className="relative flex items-center gap-6 py-11">
                <ShardCluster shards={AUTHOR_BANNER_SHARDS} className="hidden md:block" />
                <AuthorAvatar name={name} size="lg" className="relative" />
                <div className="relative flex min-w-0 flex-1 flex-col gap-1 text-white">
                    <span className="text-xs leading-4.5 font-semibold tracking-[1.4px] text-white/75">Written by</span>
                    <span className="text-[22px] leading-7.5 font-extrabold">{name}</span>
                    <span className="text-sm leading-5.5 font-medium text-white/85">{AUTHOR_ORGANIZATION}</span>
                </div>
                <p className="relative hidden font-accent text-[28px] leading-8.5 font-bold text-white/90 md:block">
                    {BRAND_HASHTAGS[0]}
                </p>
            </Container>
        </section>
    );
}
