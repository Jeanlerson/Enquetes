import { Link } from 'react-router-dom';

export default function PollCard({ poll }) {
  return (
    <article className="poll-card">
      <div className="poll-card-header">
        <span className={poll.is_expired ? 'badge expired' : 'badge active'}>
          {poll.is_expired ? 'Encerrada' : 'Ativa'}
        </span>

        <span className="poll-votes">
          {poll.votes_count} voto(s)
        </span>
      </div>

      <h2>{poll.title}</h2>

      {poll.description && (
        <p className="poll-description">
          {poll.description}
        </p>
      )}

      <div className="poll-meta">
        <span>Por {poll.author.name}</span>
        <span>{poll.options_count} opções</span>
      </div>

      {poll.expires_at && (
        <p className="poll-expiration">
          Expira em: {formatDate(poll.expires_at)}
        </p>
      )}

      <Link
        to={`/polls/${poll.id}`}
        className="primary-link"
      >
        Ver enquete
      </Link>
    </article>
  );
}

function formatDate(date) {
  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(new Date(date.replace(' ', 'T')));
}