import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import PollCard from '../components/PollCard';
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

  if (loading) {
    return <p>Carregando enquetes...</p>;
  }

  return (
    <section>
      <div className="page-heading">
        <div>
          <h1>Enquetes</h1>
          <p>Participe e acompanhe os resultados.</p>
        </div>

        {isAuthenticated && (
          <Link
            to="/polls/create"
            className="primary-link"
          >
            Criar enquete
          </Link>
        )}
      </div>

      {message && (
        <p className="message error">
          {message}
        </p>
      )}

      {!message && polls.length === 0 && (
        <div className="empty-state">
          <h2>Nenhuma enquete disponível</h2>
          <p>Seja o primeiro a criar uma enquete.</p>
        </div>
      )}

      <div className="poll-grid">
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