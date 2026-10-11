import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { getInitials } from '@/lib/format';
import { cn } from '@/lib/utils';

const avatarSizes = {
    md: { root: 'size-11', fallback: 'text-sm' },
    lg: { root: 'size-19', fallback: 'text-[22px]' },
};

type AuthorAvatarProps = {
    name: string;
    size?: keyof typeof avatarSizes;
    className?: string;
};

export function AuthorAvatar({ name, size = 'md', className }: AuthorAvatarProps) {
    return (
        <Avatar className={cn(avatarSizes[size].root, className)}>
            <AvatarFallback className={cn('bg-accent font-bold text-accent-foreground', avatarSizes[size].fallback)}>
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}
