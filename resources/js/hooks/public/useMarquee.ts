import { useLayoutEffect, type RefObject } from 'react';
import gsap from 'gsap';

export function useMarquee(track: RefObject<HTMLElement | null>, duration = 30) {
    useLayoutEffect(() => {
        if (!track.current || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const tween = gsap.to(track.current, { xPercent: -50, duration, ease: 'none', repeat: -1 });

        return () => {
            tween.kill();
        };
    }, [track, duration]);
}