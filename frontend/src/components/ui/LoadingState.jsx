export default function LoadingState({
  children = 'Carregando...',
}) {
  return (
    <div className="grid min-h-72 place-items-center text-slate-500">
      {children}
    </div>
  );
}