import { useLayoutEffect, type RefObject } from 'react';
import gsap from 'gsap';

export function useFloat(scope: RefObject<HTMLElement | null>, selector = '[data-float]') {
    useLayoutEffect(() => {
        if (!scope.current || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const context = gsap.context(() => {
            gsap.utils.toArray<HTMLElement>(selector, scope.current).forEach((element, index) => {
                gsap.to(element, {
                    y: index % 2 === 0 ? -10 : 10,
                    duration: 2 + (index % 3) * 0.4,
                    ease: 'sine.inOut',
                    repeat: -1,
                    yoyo: true,
                    delay: index * 0.2,
                });
            });
        }, scope);

        return () => context.revert();
    }, [scope, selector]);
}