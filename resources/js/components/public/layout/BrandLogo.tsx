import logoHorizontalWhite from '@/assets/brand/logo-horizontal-white.webp';
import logoStacked from '@/assets/brand/logo-stacked.webp';
import { cn } from '@/lib/utils';

export function BrandLogo({ className }: { className?: string }) {
    return (
        <span
            role="img"
            aria-label="180 Degrees Consulting Universitas Airlangga"
            className={cn('relative block h-[43px] w-[137px] shrink-0', className)}
        >
            <span className="absolute top-0 left-0 h-[41.5px] w-[42.5px] overflow-hidden rounded-full">
                <img src={logoHorizontalWhite} alt="" className="absolute top-0 left-[-0.29%] h-full w-[299.48%] max-w-none" />
            </span>
            <span className="absolute top-0.5 left-[48.5px] h-[41px] w-[88.5px] overflow-hidden">
                <img src={logoStacked} alt="" className="absolute top-[-148.6%] left-0 h-[248.75%] w-full max-w-none" />
            </span>
        </span>
    );
}
