import grainTexture from '@/assets/hero/grain-texture.webp';

export function GrainOverlay({ loading = 'eager' }: { loading?: 'eager' | 'lazy' }) {
    return (
        <img
            src={grainTexture}
            alt=""
            loading={loading}
            decoding="async"
            className="pointer-events-none absolute inset-0 size-full object-cover opacity-16 mix-blend-multiply"
        />
    );
}
