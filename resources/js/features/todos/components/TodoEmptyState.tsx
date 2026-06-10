export function TodoEmptyState() {
    return (
        <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
            <p className="text-lg font-semibold">Belum ada todo.</p>
            <p className="mt-2 text-sm text-slate-500">
                Tambahkan todo pertama kamu dari form di atas.
            </p>
        </div>
    );
}
