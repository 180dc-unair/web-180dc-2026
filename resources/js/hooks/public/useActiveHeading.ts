import { useEffect, useState } from 'react';

const HEADING_OBSERVER_MARGIN = '-144px 0px -60% 0px';

export function useActiveHeading(headingIds: string[]): string | null {
    const [activeId, setActiveId] = useState<string | null>(null);

    useEffect(() => {
        const elements = headingIds
            .map((id) => document.getElementById(id))
            .filter((element): element is HTMLElement => element !== null);

        if (elements.length === 0) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const topmost = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];

                if (topmost) {
                    setActiveId(topmost.target.id);
                }
            },
            { rootMargin: HEADING_OBSERVER_MARGIN },
        );

        elements.forEach((element) => observer.observe(element));

        return () => observer.disconnect();
    }, [headingIds]);

    return activeId ?? headingIds[0] ?? null;
}
