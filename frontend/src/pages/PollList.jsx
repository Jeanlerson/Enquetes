import {
  useEffect,
  useMemo,
  useState,
} from 'react';
import { Link } from 'react-router-dom';
import PollCard from '../components/PollCard';
import LoadingState from '../components/ui/LoadingState';
import Message from '../components/ui/Message';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';

export default function PollList() {
  const { isAuthenticated } = useAuth();

  const [polls, setPolls] = useState([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');

  useEffect(() => {
    async function loadPolls() {
      try {
        const response = await api.get('/polls');
        setPolls(response.data.polls);
      } catch (error) {
        setMessage(
          error.response?.data?.message
            || 'Não foi possível carregar as enquetes.',
        );
      } finally {
        setLoading(false);
      }
    }

    loadPolls();
  }, []);

  const statistics = useMemo(() => {
    const totalVotes = polls.reduce(
      (total, poll) => (
        total + Number(poll.votes_count || 0)
      ),
      0,
    );

    return {
      totalPolls: polls.length,
      totalVotes,
      activePolls: polls.filter(
        (poll) => !poll.is_expired,
      ).length,
    };
  }, [polls]);

  if (loading) {
    return <LoadingState>Carregando enquetes...</LoadingState>;
  }

  return (
    <section>
      <div className="mb-8 flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
          <span className="text-xs font-extrabold uppercase tracking-widest text-brand-500">
            Painel de enquetes
          </span>

          <h1 className="mt-2 text-3xl font-bold text-slate-900 md:text-4xl">
            Enquetes disponíveis
          </h1>

          <p className="mt-2 max-w-2xl leading-7 text-slate-600">
            Participe das votações e acompanhe os resultados
            em tempo real.
          </p>
        </div>

        {isAuthenticated && (
          <Link
            to="/polls/create"
            className="
              inline-flex items-center justify-center gap-2
              rounded-xl bg-brand-500 px-5 py-3
              font-bold text-white shadow-lg shadow-blue-200
              hover:bg-brand-600
            "
          >
            <span>＋</span>
            Criar enquete
          </Link>
        )}
      </div>

      <div className="mb-8 grid gap-4 md:grid-cols-3">
        <StatisticCard
          icon="📊"
          label="Total de enquetes"
          value={statistics.totalPolls}
          iconClassName="bg-blue-100"
        />

        <StatisticCard
          icon="👥"
          label="Votos registrados"
          value={statistics.totalVotes}
          iconClassName="bg-orange-100"
        />

        <StatisticCard
          icon="✓"
          label="Enquetes ativas"
          value={statistics.activePolls}
          iconClassName="bg-slate-200"
        />
      </div>

      {message && (
        <Message>{message}</Message>
      )}

      {!message && polls.length === 0 && (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
          <h2 className="text-xl font-bold text-slate-900">
            Nenhuma enquete disponível
          </h2>

          <p className="mt-2 text-slate-600">
            Ainda não existem enquetes cadastradas.
          </p>

          {isAuthenticated && (
            <Link
              to="/polls/create"
              className="mt-5 inline-flex rounded-xl bg-brand-500 px-5 py-3 font-bold text-white"
            >
              Criar primeira enquete
            </Link>
          )}
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-2">
        {polls.map((poll) => (
          <PollCard
            key={poll.id}
            poll={poll}
          />
        ))}
      </div>
    </section>
  );
}

function StatisticCard({
  icon,
  label,
  value,
  iconClassName,
}) {
  return (
    <article className="flex items-center gap-4 rounded-2xl border border-slate-200 bg-brand-50 p-5">
      <div
        className={`
          grid h-12 w-12 shrink-0 place-items-center
          rounded-xl text-xl
          ${iconClassName}
        `}
      >
        {icon}
      </div>

      <div>
        <span className="block text-xs font-bold uppercase tracking-wider text-slate-500">
          {label}
        </span>

        <strong className="mt-1 block text-2xl text-slate-900">
          {value}
        </strong>
      </div>
    </article>
  );
}