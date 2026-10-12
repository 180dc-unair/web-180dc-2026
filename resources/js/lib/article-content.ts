import { sanitizeHtml } from './html';

export type ArticleHeading = {
    id: string;
    text: string;
};

export type PreparedArticleContent = {
    html: string;
    headings: ArticleHeading[];
};

function slugify(text: string): string {
    return text
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export function prepareArticleContent(html: string): PreparedArticleContent {
    const { body } = new DOMParser().parseFromString(sanitizeHtml(html), 'text/html');
    const usedIds = new Map<string, number>();
    const headings: ArticleHeading[] = [];

    body.querySelectorAll('h1, h2').forEach((element) => {
        const text = element.textContent?.trim() ?? '';

        if (!text) {
            return;
        }

        const baseId = slugify(text) || 'section';
        const occurrence = usedIds.get(baseId) ?? 0;
        const id = occurrence ? `${baseId}-${occurrence}` : baseId;

        usedIds.set(baseId, occurrence + 1);
        element.id = id;
        headings.push({ id, text });
    });

    return { html: body.innerHTML, headings };
}
