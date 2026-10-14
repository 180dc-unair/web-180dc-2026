import { PRODUCT_DETAIL_COPY, PRODUCT_TYPE_LABELS } from '@/contants/public/store';
import { formatNumber } from '@/lib/format';
import type { Product } from '@/types/public';
import { SidebarCard } from '../SidebarCard';

function getSpecRows(product: Product) {
    const rows = [{ label: 'Format', value: PRODUCT_TYPE_LABELS[product.type] ?? product.type }];

    if (product.category) {
        rows.push({ label: 'Kategori', value: product.category.name });
    }

    if (product.type === 'physical') {
        rows.push({ label: 'Stok', value: formatNumber(product.stock) });
    }

    if (product.sold_count > 0) {
        rows.push({ label: 'Terjual', value: formatNumber(product.sold_count) });
    }

    return rows;
}

export function ProductSpecCard({ product, className }: { product: Product; className?: string }) {
    return (
        <SidebarCard title={PRODUCT_DETAIL_COPY.specTitle} className={className}>
            <dl className="flex flex-col gap-3.5">
                {getSpecRows(product).map((row) => (
                    <div key={row.label} className="flex items-start justify-between gap-4 text-[13px] leading-5.5">
                        <dt className="text-muted-foreground/85">{row.label}</dt>
                        <dd className="text-right font-semibold text-muted-foreground">{row.value}</dd>
                    </div>
                ))}
            </dl>
        </SidebarCard>
    );
}
