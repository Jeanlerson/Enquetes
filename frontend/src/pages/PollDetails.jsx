import { useEffect, useMemo, useState } from 'react';
import {
  Link,
  useNavigate,
  useParams,
} from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import Button from '../components/ui/Button';
import Card from '../components/ui/Card';
import LoadingState from '../components/ui/LoadingState';
import Message from '../components/ui/Message';
import StatusBadge from '../components/ui/StatusBadge';

export default function PollDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();

  const [poll, setPoll] = useState(null);
  const [selectedOption, setSelectedOption] = useState('');
  const [loading, setLoading] = useState(true);
  const [voting, setVoting] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);
  const [realtimeStatus, setRealtimeStatus] = useState(
    'Conectando aos resultados em tempo real...',
  );

  useEffect(() => {
    async function loadPoll() {
      try {
        const response = await api.get(`/polls/${id}`);
        setPoll(response.data.poll);
      } catch (error) {
        setIsError(true);
        setMessage(
          error.response?.data?.message
            || 'Não foi possível carregar a enquete.',
        );
      } finally {
        setLoading(false);
      }
    }

    loadPoll();
  }, [id]);

  useEffect(() => {
    refreshResults();

    const intervalId = setInterval(
      refreshResults,
      3000,
    );

    return () => {
      clearInterval(intervalId);
    };
  }, [id]);

  const isOwner = Boolean(
    user
    && poll
    && Number(user.id) === Number(poll.author.id),
  );

  const winningOptionId = useMemo(() => {
    if (!poll?.options?.length || poll.total_votes === 0) {
      return null;
    }

    const highestVotes = Math.max(
      ...poll.options.map(
        (option) => Number(option.votes_count || 0),
      ),
    );

    const winners = poll.options.filter(
      (option) => Number(option.votes_count) === highestVotes,
    );

    return winners.length === 1
      ? winners[0].id
      : null;
  }, [poll]);

  async function refreshResults() {
    const response = await api.get(`/polls/${id}/results`);
    const results = response.data.data;

    setPoll((currentPoll) => ({
      ...currentPoll,
      total_votes: results.total_votes,
      expires_at: results.expires_at,
      is_expired: results.is_expired,
      options: results.results.map((result) => ({
        id: result.option_id,
        text: result.option,
        votes_count: result.votes,
        percentage: result.percentage,
      })),
    }));
  }

  async function handleVote(event) {
    event.preventDefault();

    if (!selectedOption) {
      setIsError(true);
      setMessage('Selecione uma opção para votar.');
      return;
    }

    setVoting(true);
    setMessage('');
    setIsError(false);

    try {
      const response = await api.post(
        `/polls/${id}/vote`,
        {
          option_id: Number(selectedOption),
        },
      );

      setMessage(response.data.message);
      setSelectedOption('');

      await refreshResults();
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível registrar o voto.',
      );
    } finally {
      setVoting(false);
    }
  }

  async function handleDelete() {
    const confirmed = window.confirm(
      'Tem certeza de que deseja excluir esta enquete?',
    );

    if (!confirmed) {
      return;
    }

    setDeleting(true);
    setMessage('');
    setIsError(false);

    try {
      await api.delete(`/polls/${id}`);
      navigate('/');
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível excluir a enquete.',
      );
      setDeleting(false);
    }
  }

  if (loading) {
    return (
      <LoadingState>
        Carregando enquete...
      </LoadingState>
    );
  }

  if (!poll) {
    return (
      <section className="empty-state">
        <h1>Enquete não encontrada</h1>

        {message && (
          <p className="message error">
            {message}
          </p>
        )}

        <Link to="/" className="primary-link">
          Voltar para enquetes
        </Link>
      </section>
    );
  }

  return (
    <section>
      <Link
        to="/"
        className="mb-6 inline-flex items-center gap-2 font-bold text-brand-600"
      >
        ← Voltar para enquetes
      </Link>

      <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
        <main className="grid gap-6">
          <Card>
            <StatusBadge expired={poll.is_expired} />

            <h1 className="mt-4 text-3xl font-bold leading-tight text-slate-900">
              {poll.title}
            </h1>

            {poll.description && (
              <p className="mt-3 leading-7 text-slate-600">
                {poll.description}
              </p>
            )}

            <div className="mt-5 flex flex-wrap gap-4 text-sm text-slate-500">
              <span>
                Criada por{' '}
                <strong className="text-slate-700">
                  {poll.author.name}
                </strong>
              </span>

              <span>{poll.total_votes} voto(s)</span>

              {poll.expires_at && (
                <span>
                  {poll.is_expired ? 'Encerrada em ' : 'Expira em '}
                  {formatDate(poll.expires_at)}
                </span>
              )}
            </div>

            <div className="mt-5 inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-sm font-semibold text-brand-700">
              <span className="h-2 w-2 animate-pulse rounded-full bg-green-500" />
              {realtimeStatus}
            </div>

            {message && (
              <Message type={isError ? 'error' : 'success'}>
                {message}
              </Message>
            )}

            <div className="mt-8 space-y-5">
              {poll.options.map((option) => (
                <div
                  key={option.id}
                  className="rounded-xl bg-slate-50 p-4"
                >
                  <div className="mb-3 flex justify-between gap-4">
                    <div>
                      <strong className="text-lg text-slate-900">
                        {option.text}
                      </strong>

                      <p className="mt-1 text-sm text-slate-500">
                        {option.votes_count} voto(s)
                      </p>
                    </div>

                    <strong className="text-xl text-brand-500">
                      {Number(
                        option.percentage || 0,
                      ).toFixed(2)}
                      %
                    </strong>
                  </div>

                  <div className="h-3 overflow-hidden rounded-full bg-slate-200">
                    <div
                      className="h-full rounded-full bg-brand-500 transition-all duration-700"
                      style={{
                        width: `${option.percentage || 0}%`,
                      }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </Card>

          {!poll.is_expired && (
            <Card>
              <span className="text-xs font-extrabold uppercase tracking-wider text-brand-500">
                Participe
              </span>

              <h2 className="mt-2 text-2xl font-bold text-slate-900">
                Escolha uma opção
              </h2>

              <p className="mt-1 text-slate-600">
                Cada usuário pode votar apenas uma vez.
              </p>

              {isAuthenticated ? (
                <form onSubmit={handleVote}>
                  <div className="mt-6 grid gap-3">
                    {poll.options.map((option) => (
                      <label
                        key={option.id}
                        className={`
                          flex cursor-pointer items-center gap-3
                          rounded-xl border p-4 transition
                          ${
                            Number(selectedOption) === option.id
                              ? 'border-brand-500 bg-brand-50 font-bold text-brand-700'
                              : 'border-slate-200 hover:border-brand-200 hover:bg-slate-50'
                          }
                        `}
                      >
                        <input
                          type="radio"
                          name="option"
                          value={option.id}
                          checked={
                            Number(selectedOption) === option.id
                          }
                          onChange={(event) => {
                            setSelectedOption(event.target.value);
                          }}
                        />

                        <span>{option.text}</span>
                      </label>
                    ))}
                  </div>

                  <Button
                    type="submit"
                    className="mt-5 w-full"
                    disabled={voting}
                  >
                    {voting
                      ? 'Registrando voto...'
                      : 'Confirmar voto'}
                  </Button>
                </form>
              ) : (
                <div className="mt-5 rounded-xl bg-slate-50 p-5 text-center">
                  <p className="text-slate-600">
                    Entre na sua conta para registrar seu voto.
                  </p>

                  <Link
                    to="/login"
                    className="mt-4 inline-flex rounded-xl bg-brand-500 px-5 py-3 font-bold text-white"
                  >
                    Entrar
                  </Link>
                </div>
              )}
            </Card>
          )}
        </main>

        <aside className="grid gap-6">
          <Card>
            <span className="text-xs font-extrabold uppercase tracking-wider text-brand-500">
              Resumo
            </span>

            <h2 className="mt-2 text-xl font-bold text-slate-900">
              Informações
            </h2>

            <dl className="mt-5 divide-y divide-slate-100">
              <SummaryItem
                label="Total de votos"
                value={poll.total_votes}
              />

              <SummaryItem
                label="Opções"
                value={poll.options.length}
              />

              <SummaryItem
                label="Status"
                value={poll.is_expired ? 'Finalizada' : 'Ativa'}
              />

              <SummaryItem
                label="Criador"
                value={poll.author.name}
              />
            </dl>
          </Card>

          {isOwner && (
            <Card className="border-orange-200">
              <span className="text-xs font-extrabold uppercase tracking-wider text-orange-700">
                Gerenciamento
              </span>

              <h2 className="mt-2 text-xl font-bold text-slate-900">
                Administrar enquete
              </h2>

              <p className="mt-2 leading-6 text-slate-600">
                Apenas o criador pode editar ou excluir esta enquete.
              </p>

              <div className="mt-5 grid gap-3">
                <Link
                  to={`/polls/${poll.id}/edit`}
                  className="
                    inline-flex items-center justify-center
                    rounded-xl bg-brand-100 px-4 py-3
                    font-bold text-brand-700
                    hover:bg-brand-200
                  "
                >
                  Editar enquete
                </Link>

                <Button
                  variant="danger"
                  onClick={handleDelete}
                  disabled={deleting}
                >
                  {deleting
                    ? 'Excluindo...'
                    : 'Excluir enquete'}
                </Button>
              </div>
            </Card>
          )}
        </aside>
      </div>
    </section>
  );
}

function formatDate(date) {
  if (!date) {
    return '';
  }

  return new Intl.DateTimeFormat('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short',
  }).format(new Date(date.replace(' ', 'T')));
}

function SummaryItem({ label, value }) {
  return (
    <div className="flex justify-between gap-4 py-4">
      <dt className="text-slate-500">
        {label}
      </dt>

      <dd className="m-0 text-right font-bold text-slate-900">
        {value}
      </dd>
    </div>
  );
}