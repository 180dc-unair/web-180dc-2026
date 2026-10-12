export function pickRelated<T extends { id: string }>(
    items: T[],
    current: T,
    isSameGroup: (item: T) => boolean,
    limit: number,
): T[] {
    const others = items.filter((item) => item.id !== current.id);
    const sameGroup = others.filter(isSameGroup);
    const rest = others.filter((item) => !isSameGroup(item));

    return [...sameGroup, ...rest].slice(0, limit);
}
