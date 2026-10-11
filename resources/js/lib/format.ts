const WORDS_PER_MINUTE = 200;

const dateFormatter = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
const numberFormatter = new Intl.NumberFormat('en-US');

export function formatDate(isoDate: string): string {
    return dateFormatter.format(new Date(isoDate));
}

export function formatNumber(value: number): string {
    return numberFormatter.format(value);
}

export function getInitials(name: string): string {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

export function estimateReadingMinutes(html: string): number {
    const words = html.replace(/<[^>]*>/g, ' ').trim().split(/\s+/).filter(Boolean).length;

    return Math.max(1, Math.ceil(words / WORDS_PER_MINUTE));
}
