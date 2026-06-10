import type { Todo } from '../types/todoTypes';

type TodoItemProps = {
    todo: Todo;
    isToggling: boolean;
    isDeleting: boolean;
    onToggle: (todo: Todo) => void;
    onDelete: (id: number) => void;
};

export function TodoItem({
    todo,
    isToggling,
    isDeleting,
    onToggle,
    onDelete,
}: TodoItemProps) {
    return (
        <div className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:flex-row md:items-center md:justify-between">
            <button
                type="button"
                onClick={() => onToggle(todo)}
                disabled={isToggling}
                className="flex min-w-0 flex-1 items-center gap-3 text-left disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span
                    className={[
                        'flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-xs',
                        todo.is_completed
                            ? 'border-emerald-400 bg-emerald-400 text-slate-950'
                            : 'border-slate-300 bg-transparent text-transparent',
                    ].join(' ')}
                >
                    ✓
                </span>

                <span
                    className={[
                        'truncate text-sm font-semibold md:text-base',
                        todo.is_completed
                            ? 'text-slate-500 line-through'
                            : 'text-slate-950',
                    ].join(' ')}
                >
                    {todo.title}
                </span>
            </button>

            <button
                type="button"
                onClick={() => onDelete(todo.id)}
                disabled={isDeleting}
                className="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Hapus
            </button>
        </div>
    );
}
