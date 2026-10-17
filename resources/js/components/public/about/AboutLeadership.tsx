import { useRef } from 'react';
import { ABOUT_LEADERSHIP } from '@/contants/public/about';
import { useReveal } from '@/hooks/public/useReveal';
import { getInitials } from '@/lib/format';
import type { LeadershipMember } from '@/types/public';
import { Container } from '../layout/Container';

function LinkedinBadge({ member }: { member: LeadershipMember }) {
    const badge = (
        <span className="flex size-5 items-center justify-center rounded-[4px] bg-foreground text-[11px] leading-none font-bold text-white">
            in
        </span>
    );

    if (!member.linkedinUrl) {
        return <span aria-hidden="true">{badge}</span>;
    }

    return (
        <a href={member.linkedinUrl} target="_blank" rel="noopener noreferrer" aria-label={`LinkedIn ${member.name}`}>
            {badge}
        </a>
    );
}

function MemberCard({ member }: { member: LeadershipMember }) {
    return (
        <article className="flex h-full flex-col gap-3 rounded-xl bg-card p-4 shadow-md">
            <div className="aspect-square overflow-hidden rounded-lg bg-muted">
                {member.photo ? (
                    <img src={member.photo} alt="" loading="lazy" decoding="async" className="size-full object-cover" />
                ) : (
                    <span className="flex size-full items-center justify-center text-2xl font-bold text-muted-foreground">
                        {getInitials(member.name)}
                    </span>
                )}
            </div>
            <div className="flex flex-1 flex-col gap-1">
                <h3 className="leading-tight font-bold text-muted-foreground">{member.name}</h3>
                <p className="text-xs leading-4 text-primary">{member.position}</p>
            </div>
            <LinkedinBadge member={member} />
        </article>
    );
}

export function AboutLeadership() {
    const sectionRef = useRef<HTMLElement>(null);
    useReveal(sectionRef);

    return (
        <section ref={sectionRef} id="team" className="scroll-mt-36 bg-muted-foreground py-16 lg:py-24">
            <Container className="flex flex-col items-center gap-12">
                <div data-reveal className="flex flex-col items-center gap-2 text-center text-white">
                    <h2 className="text-3xl font-extrabold lg:text-4xl">{ABOUT_LEADERSHIP.title}</h2>
                    <p className="text-sm text-white/85">{ABOUT_LEADERSHIP.description}</p>
                </div>
                <div className="flex w-full flex-col gap-6">
                    {ABOUT_LEADERSHIP.rows.map((row, rowIndex) => (
                        <ul key={rowIndex} className="flex flex-wrap justify-center gap-6">
                            {row.map((member) => (
                                <li
                                    key={member.id}
                                    data-reveal
                                    className="w-full sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)]"
                                >
                                    <MemberCard member={member} />
                                </li>
                            ))}
                        </ul>
                    ))}
                </div>
            </Container>
        </section>
    );
}