import { FormEvent, useState } from 'react';
import { validateCreateTodoInput } from '../schemas/todoSchema';
import type { CreateTodoPayload } from '../types/todoTypes';

type TodoCreateFormProps = {
    isSubmitting: boolean;
    errorMessage?: string;
    onCreate: (payload: CreateTodoPayload) => void;
};

export function TodoCreateForm({
    isSubmitting,
    errorMessage,
    onCreate,
}: TodoCreateFormProps) {
    const [title, setTitle] = useState('');
    const [validationError, setValidationError] = useState<string | null>(null);

    function handleSubmit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const result = validateCreateTodoInput({ title });

        if (!result.valid) {
            setValidationError(result.error);
            return;
        }

        console.log('FORM VALID PAYLOAD:', result.data);

        setValidationError(null);
        onCreate(result.data);
        setTitle('');
    }

    return (
        <section className="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
            <form onSubmit={handleSubmit} className="flex flex-col gap-3 md:flex-row">
                <input
                    type="text"
                    value={title}
                    onChange={(event) => {
                        setTitle(event.target.value);
                        setValidationError(null);
                    }}
                    placeholder="Tulis todo baru..."
                    className="min-h-12 flex-1 rounded-2xl border border-slate-200 bg-white px-5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-blue-500"
                />

                <button
                    type="submit"
                    disabled={isSubmitting || !title.trim()}
                    className="min-h-12 rounded-2xl bg-blue-600 px-6 text-sm font-bold text-white shadow-sm transition hover:bg-blue-500 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {isSubmitting ? 'Menyimpan...' : 'Tambah Todo'}
                </button>
            </form>

            {validationError && (
                <p className="mt-3 text-sm text-amber-600">
                    {validationError}
                </p>
            )}

            {errorMessage && (
                <p className="mt-3 text-sm text-red-600">
                    Gagal menambah todo: {errorMessage}
                </p>
            )}
        </section>
    );
}
