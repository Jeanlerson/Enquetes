import { Link } from 'react-router-dom';
import StatusBadge from './ui/StatusBadge';

export default function PollCard({ poll }) {
  return (
    <article
      className={`
        flex overflow-hidden flex-col rounded-2xl
        border border-slate-200 bg-white shadow-sm
        transition hover:-translate-y-1 hover:shadow-lg
        ${poll.is_expired ? 'opacity-80' : ''}
      `}
    >
      <div className="flex flex-1 flex-col p-6">
        <div className="mb-5 flex items-start justify-between gap-4">
          <StatusBadge expired={poll.is_expired} />

          <span className="text-xs text-slate-500">
            {formatDate(poll.created_at, false)}
          </span>
        </div>

        <h2 className="text-xl font-bold leading-7 text-slate-900">
          {poll.title}
        </h2>

        {poll.description && (
          <p className="mt-2 line-clamp-2 leading-6 text-slate-600">
            {poll.description}
          </p>
        )}

        <div className="mt-6 flex items-end justify-between gap-4 border-t border-slate-100 pt-4">
          <div>
            <span className="block text-xs text-slate-500">
              Participação atual
            </span>

            <strong className="text-slate-900">
              {poll.votes_count} voto(s)
            </strong>
          </div>

          <span className="text-sm text-slate-500">
            {poll.options_count} opções
          </span>
        </div>

        <p className="mt-4 text-sm text-slate-500">
          Criada por{' '}
          <strong className="text-slate-700">
            {poll.author.name}
          </strong>
        </p>

        {poll.expires_at && (
          <p className="mt-2 text-sm text-slate-500">
            {poll.is_expired ? 'Encerrada em ' : 'Expira em '}
            {formatDate(poll.expires_at)}
          </p>
        )}
      </div>

      <footer className="border-t border-slate-200 bg-slate-50 px-6 py-4">
        <Link
          to={`/polls/${poll.id}`}
          className="
            inline-flex items-center justify-center
            rounded-lg bg-brand-100 px-4 py-2
            text-sm font-bold text-brand-700
            hover:bg-brand-200
          "
        >
          {poll.is_expired
            ? 'Ver resultados'
            : 'Ver detalhes'}
        </Link>
      </footer>
    </article>
  );
}

function formatDate(date, showTime = true) {
  if (!date) {
    return '';
  }

  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    ...(showTime ? { timeStyle: 'short' } : {}),
  }).format(new Date(date.replace(' ', 'T')));
}