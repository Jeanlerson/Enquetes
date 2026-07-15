const variants = {
  primary:
    'bg-brand-500 text-white hover:bg-brand-600',
  secondary:
    'border border-brand-200 bg-brand-50 text-brand-700 hover:bg-brand-100',
  danger:
    'bg-red-700 text-white hover:bg-red-800',
};

export default function Button({
  children,
  variant = 'primary',
  className = '',
  type = 'button',
  ...props
}) {
  return (
    <button
      type={type}
      className={`
        inline-flex items-center justify-center gap-2
        rounded-lg px-4 py-3 font-semibold
        disabled:cursor-not-allowed disabled:opacity-60
        ${variants[variant]}
        ${className}
      `}
      {...props}
    >
      {children}
    </button>
  );
}