import { cn } from '@/lib/utils';
import type { Shard } from '@/types/public';

export function ShardCluster({ shards, className }: { shards: Shard[]; className?: string }) {
    return (
        <div aria-hidden="true" className={cn('pointer-events-none absolute inset-0', className)}>
            {shards.map((shard) => (
                <span
                    key={`${shard.x}-${shard.y}`}
                    className="absolute -translate-1/2 rotate-[35.7deg] bg-white"
                    style={{ left: shard.x, top: shard.y, width: shard.width, height: shard.height, opacity: shard.opacity }}
                />
            ))}
        </div>
    );
}
