import { useLayoutEffect, type RefObject } from 'react';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export function useReveal(scope: RefObject<HTMLElement | null>, selector = '[data-reveal]') {
    useLayoutEffect(() => {
        if (!scope.current || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const context = gsap.context(() => {
            gsap.from(selector, {
                opacity: 0,
                y: 24,
                duration: 0.7,
                ease: 'power3.out',
                stagger: 0.12,
                scrollTrigger: { trigger: scope.current, start: 'top 80%', once: true },
            });
        }, scope);

        return () => context.revert();
    }, [scope, selector]);
}