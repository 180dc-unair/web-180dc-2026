import { useEffect, useState } from 'react';
import { Check } from 'lucide-react';
import { buildUrl } from '@/lib/url';

const COPIED_FEEDBACK_MS = 2000;

const shareButtonClassName =
    'flex size-10 items-center justify-center rounded bg-primary/12 text-xs leading-4 font-bold text-accent-foreground transition-colors hover:bg-primary hover:text-white';

export function ShareButtons({ title }: { title: string }) {
    const [copied, setCopied] = useState(false);
    const shareUrl = window.location.href;

    useEffect(() => {
        if (!copied) {
            return;
        }

        const timeout = window.setTimeout(() => setCopied(false), COPIED_FEEDBACK_MS);

        return () => window.clearTimeout(timeout);
    }, [copied]);

    const copyLink = async () => {
        try {
            await navigator.clipboard.writeText(shareUrl);
            setCopied(true);
        } catch {
            setCopied(false);
        }
    };

    const shareWithDevice = () => {
        if (!('share' in navigator)) {
            void copyLink();
            return;
        }

        navigator.share({ title, url: shareUrl }).catch(() => undefined);
    };

    return (
        <div className="flex gap-2.5">
            <a
                href={buildUrl('https://www.linkedin.com/sharing/share-offsite/', { url: shareUrl })}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Share on LinkedIn"
                className={shareButtonClassName}
            >
                in
            </a>
            <button type="button" onClick={shareWithDevice} aria-label="Share to Instagram" className={shareButtonClassName}>
                IG
            </button>
            <a
                href={buildUrl('https://x.com/intent/post', { url: shareUrl, text: title })}
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Share on X"
                className={shareButtonClassName}
            >
                X
            </a>
            <button
                type="button"
                onClick={copyLink}
                aria-label={copied ? 'Link copied' : 'Copy link'}
                className={shareButtonClassName}
            >
                {copied ? <Check className="size-4" aria-hidden="true" /> : 'link'}
            </button>
            <span aria-live="polite" className="sr-only">
                {copied ? 'Link copied to clipboard' : ''}
            </span>
        </div>
    );
}
