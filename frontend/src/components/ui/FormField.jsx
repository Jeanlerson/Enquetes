export default function FormField({
  label,
  id,
  children,
}) {
  return (
    <div className="grid gap-2">
      <label
        htmlFor={id}
        className="font-semibold text-slate-700"
      >
        {label}
      </label>

      {children}
    </div>
  );
}