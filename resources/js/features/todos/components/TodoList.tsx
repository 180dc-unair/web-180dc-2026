import { TodoEmptyState } from './TodoEmptyState';
import { TodoErrorState } from './TodoErrorState';
import { TodoItem } from './TodoItem';
import { TodoLoadingState } from './TodoLoadingState';
import type { Todo } from '../types/todoTypes';

type TodoListProps = {
    todos: Todo[];
    isLoading: boolean;
    isError: boolean;
    errorMessage?: string;
    isFetching: boolean;
    isToggling: boolean;
    isDeleting: boolean;
    onToggle: (todo: Todo) => void;
    onDelete: (id: number) => void;
};

export function TodoList({
    todos,
    isLoading,
    isError,
    errorMessage,
    isFetching,
    isToggling,
    isDeleting,
    onToggle,
    onDelete,
}: TodoListProps) {
    return (
        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div className="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h2 className="text-xl font-bold">Daftar Todo</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Data diambil dari /api/todos.
                    </p>
                </div>

                {isFetching && (
                    <span className="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        Syncing...
                    </span>
                )}
            </div>

            {isLoading && <TodoLoadingState />}

            {isError && <TodoErrorState message={errorMessage} />}

            {!isLoading && !isError && todos.length === 0 && <TodoEmptyState />}

            {todos.length > 0 && (
                <div className="space-y-3">
                    {todos.map((todo) => (
                        <TodoItem
                            key={todo.id}
                            todo={todo}
                            isToggling={isToggling}
                            isDeleting={isDeleting}
                            onToggle={onToggle}
                            onDelete={onDelete}
                        />
                    ))}
                </div>
            )}
        </section>
    );
}
