export default function Card({
  children,
  className = '',
}) {
  return (
    <article
      className={`
        rounded-2xl border border-slate-200
        bg-white p-6 shadow-sm
        ${className}
      `}
    >
      {children}
    </article>
  );
}