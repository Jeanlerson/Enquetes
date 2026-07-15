import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';

export default function PollDetails() {
  const { id } = useParams();
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  const [poll, setPoll] = useState(null);
  const [selectedOption, setSelectedOption] = useState('');
  const [loading, setLoading] = useState(true);
  const [voting, setVoting] = useState(false);
  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);
  const [realtimeStatus, setRealtimeStatus] = useState('Conectando...');

  const isOwner = Boolean(
    user
    && poll
    && Number(user.id) === Number(poll.author.id),
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
    if (!poll) {
      return undefined;
    }

    const sseUrl = import.meta.env.VITE_SSE_URL;

    const eventSource = new EventSource(
      `${sseUrl}/stream.php?poll_id=${id}`,
    );

    eventSource.onopen = () => {
      setRealtimeStatus('Resultados atualizados em tempo real');
    };

    eventSource.addEventListener('poll-results', (event) => {
      const response = JSON.parse(event.data);
      const results = response.data;

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
    });

    eventSource.onerror = () => {
      setRealtimeStatus('Tentando restabelecer a conexão...');
    };

    return () => {
      eventSource.close();
    };
  }, [id, poll?.id]);

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
      const response = await api.post(`/polls/${id}/vote`, {
        option_id: Number(selectedOption),
      });

      setMessage(response.data.message);
      setSelectedOption('');
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

  if (loading) {
    return <p>Carregando enquete...</p>;
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

  async function handleDelete() {
    const confirmed = window.confirm(
      'Tem certeza de que deseja excluir esta enquete?',
    );

    if (!confirmed) {
      return;
    }

    try {
      await api.delete(`/polls/${id}`);
      navigate('/');
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível excluir a enquete.',
      );
    }
  }

  return (
    <section className="poll-details">
      <div className="poll-details-header">
        <div>
          <Link to="/" className="back-link">
            ← Voltar
          </Link>

          <h1>{poll.title}</h1>

          {poll.description && (
            <p>{poll.description}</p>
          )}

          <div className="poll-information">
            <span>Por {poll.author.name}</span>
            <span>{poll.total_votes} voto(s)</span>

            <span className={poll.is_expired ? 'badge expired' : 'badge active'}>
              {poll.is_expired ? 'Encerrada' : 'Ativa'}
            </span>
          </div>

          {isOwner && (
            <div className="owner-actions">
              <Link
                to={`/polls/${poll.id}/edit`}
                className="secondary-link"
              >
                Editar enquete
              </Link>

              <button
                type="button"
                className="danger-button"
                onClick={handleDelete}
              >
                Excluir enquete
              </button>
            </div>
          )}
        </div>
      </div>

      <p className="realtime-status">
        {realtimeStatus}
      </p>

      {message && (
        <p className={isError ? 'message error' : 'message success'}>
          {message}
        </p>
      )}

      {!poll.is_expired && (
        <form className="vote-card" onSubmit={handleVote}>
          <h2>Escolha uma opção</h2>

          <div className="option-list">
            {poll.options.map((option) => (
              <label
                key={option.id}
                className={`vote-option ${
                  Number(selectedOption) === option.id
                    ? 'selected'
                    : ''
                }`}
              >
                <input
                  type="radio"
                  name="option"
                  value={option.id}
                  checked={Number(selectedOption) === option.id}
                  onChange={(event) => {
                    setSelectedOption(event.target.value);
                  }}
                />

                <span>{option.text}</span>
              </label>
            ))}
          </div>

          {isAuthenticated ? (
            <button
              type="submit"
              className="primary-button"
              disabled={voting}
            >
              {voting ? 'Registrando voto...' : 'Votar'}
            </button>
          ) : (
            <p>
              <Link to="/login">Entre na sua conta</Link>
              {' '}para votar.
            </p>
          )}
        </form>
      )}

      <div className="results-card">
        <h2>Resultados</h2>

        {poll.options.map((option) => (
          <div className="result-item" key={option.id}>
            <div className="result-header">
              <span>{option.text}</span>

              <strong>
                {Number(option.percentage || 0).toFixed(2)}%
              </strong>
            </div>

            <div className="result-track">
              <div
                className="result-fill"
                style={{
                  width: `${option.percentage || 0}%`,
                }}
              />
            </div>

            <small>
              {option.votes_count} voto(s)
            </small>
          </div>
        ))}
      </div>
    </section>
  );
}