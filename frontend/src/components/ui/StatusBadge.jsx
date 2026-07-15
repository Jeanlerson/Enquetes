export default function StatusBadge({ expired }) {
  return (
    <span
      className={`
        inline-flex items-center gap-2 rounded-full
        px-3 py-1 text-xs font-bold
        ${
          expired
            ? 'bg-slate-200 text-slate-700'
            : 'bg-green-100 text-green-700'
        }
      `}
    >
      <span
        className={`
          h-2 w-2 rounded-full
          ${expired ? 'bg-slate-600' : 'bg-green-600'}
        `}
      />

      {expired ? 'Finalizada' : 'Ativa'}
    </span>
  );
}