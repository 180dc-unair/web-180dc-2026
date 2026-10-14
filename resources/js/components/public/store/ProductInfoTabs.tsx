import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PRODUCT_DETAIL_COPY, PRODUCT_FAQ } from '@/contants/public/store';
import { cn } from '@/lib/utils';
import { RichContent } from '../RichContent';

const tabTriggerClassName =
    'h-auto flex-none rounded-none border-0 border-b-3 border-muted-foreground/18 px-6 py-3.5 text-base leading-6 font-medium text-muted-foreground/85 after:hidden data-active:border-primary data-active:font-bold data-active:text-foreground';

type ProductInfoTabsProps = {
    descriptionHtml: string;
    className?: string;
};

export function ProductInfoTabs({ descriptionHtml, className }: ProductInfoTabsProps) {
    return (
        <Tabs defaultValue="description" className={cn('gap-7', className)}>
            <TabsList variant="line" className="h-auto gap-0 rounded-none p-0">
                <TabsTrigger value="description" className={tabTriggerClassName}>
                    {PRODUCT_DETAIL_COPY.descriptionTab}
                </TabsTrigger>
                <TabsTrigger value="faq" className={tabTriggerClassName}>
                    {PRODUCT_DETAIL_COPY.faqTab}
                </TabsTrigger>
            </TabsList>

            <TabsContent value="description">
                {descriptionHtml ? (
                    <RichContent html={descriptionHtml} />
                ) : (
                    <p className="text-muted-foreground/85">{PRODUCT_DETAIL_COPY.emptyDescription}</p>
                )}
            </TabsContent>

            <TabsContent value="faq">
                <dl className="flex flex-col gap-6">
                    {PRODUCT_FAQ.map((item) => (
                        <div key={item.question} className="flex flex-col gap-1.5">
                            <dt className="font-semibold text-foreground">{item.question}</dt>
                            <dd className="leading-7 text-muted-foreground">{item.answer}</dd>
                        </div>
                    ))}
                </dl>
            </TabsContent>
        </Tabs>
    );
}
