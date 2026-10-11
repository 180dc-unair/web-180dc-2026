import { cn } from '@/lib/utils';

const articleContentClassName = cn(
    'text-base leading-7.5 text-muted-foreground [&>*+*]:mt-6',
    '[&_h1]:scroll-mt-36 [&_h1]:text-[28px] [&_h1]:leading-9.5 [&_h1]:font-extrabold [&_h1]:text-foreground',
    '[&_h2]:scroll-mt-36 [&_h2]:text-[28px] [&_h2]:leading-9.5 [&_h2]:font-extrabold [&_h2]:text-foreground',
    '[&_h3]:text-xl [&_h3]:leading-8 [&_h3]:font-bold [&_h3]:text-foreground',
    '[&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4',
    '[&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mt-2',
    '[&_blockquote]:relative [&_blockquote]:rounded-md [&_blockquote]:bg-primary/8 [&_blockquote]:py-7 [&_blockquote]:pr-7 [&_blockquote]:pl-14',
    '[&_blockquote]:font-accent [&_blockquote]:text-xl [&_blockquote]:leading-8 [&_blockquote]:font-bold [&_blockquote]:text-foreground',
    'sm:[&_blockquote]:text-[26px] sm:[&_blockquote]:leading-9.5',
    "[&_blockquote]:before:absolute [&_blockquote]:before:inset-y-7 [&_blockquote]:before:left-7 [&_blockquote]:before:w-1 [&_blockquote]:before:rounded-xs [&_blockquote]:before:bg-primary [&_blockquote]:before:content-['']",
    '[&_img]:w-full [&_img]:rounded-md',
    '[&_hr]:border-border',
    '[&_code]:rounded-xs [&_code]:bg-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-sm',
    '[&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:bg-muted [&_pre]:p-4',
);

export function ArticleContent({ html }: { html: string }) {
    return <div className={articleContentClassName} dangerouslySetInnerHTML={{ __html: html }} />;
}
