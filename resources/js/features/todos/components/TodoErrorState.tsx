type TodoErrorStateProps = {
    message?: string;
};

export function TodoErrorState({ message }: TodoErrorStateProps) {
    return (
        <div className="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
            Gagal mengambil data: {message || 'Unknown error'}
        </div>
    );
}
