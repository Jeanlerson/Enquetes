export default function Message({
  children,
  type = 'error',
}) {
  const style = type === 'success'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800';

  return (
    <p className={`mt-4 rounded-lg p-3 ${style}`}>
      {children}
    </p>
  );
}