export type PageItem = number | 'ellipsis';

const VISIBLE_SIBLINGS = 1;

export function getPageItems(currentPage: number, totalPages: number): PageItem[] {
    const maxPlainPages = VISIBLE_SIBLINGS * 2 + 5;

    if (totalPages <= maxPlainPages) {
        return Array.from({ length: totalPages }, (_, index) => index + 1);
    }

    const start = Math.max(2, currentPage - VISIBLE_SIBLINGS);
    const end = Math.min(totalPages - 1, currentPage + VISIBLE_SIBLINGS);
    const items: PageItem[] = [1];

    if (start > 2) {
        items.push('ellipsis');
    }

    for (let page = start; page <= end; page++) {
        items.push(page);
    }

    if (end < totalPages - 1) {
        items.push('ellipsis');
    }

    items.push(totalPages);

    return items;
}

export function paginate<T>(items: T[], page: number, perPage: number): T[] {
    const start = (page - 1) * perPage;

    return items.slice(start, start + perPage);
}
