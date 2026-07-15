import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import api from '../services/api';
import '../styles/auth.css';
import '../styles/forms.css';

export default function EditPoll() {
  const { id } = useParams();
  const navigate = useNavigate();

  const [form, setForm] = useState({
    title: '',
    description: '',
    expires_at: '',
  });

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);

  useEffect(() => {
    async function loadPoll() {
      try {
        const response = await api.get(`/polls/${id}`);
        const poll = response.data.poll;

        setForm({
          title: poll.title ?? '',
          description: poll.description ?? '',
          expires_at: formatDateForInput(poll.expires_at),
        });
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

  function handleChange(event) {
    const { name, value } = event.target;

    setForm((current) => ({
      ...current,
      [name]: value,
    }));
  }

  async function handleSubmit(event) {
    event.preventDefault();

    setSaving(true);
    setMessage('');
    setIsError(false);

    try {
      const response = await api.put(`/polls/${id}`, {
        title: form.title.trim(),
        description: form.description.trim(),
        expires_at: formatDateForApi(form.expires_at),
      });

      navigate(`/polls/${response.data.poll.id}`);
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível atualizar a enquete.',
      );
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return <p>Carregando enquete...</p>;
  }

  return (
    <section className="create-poll-page">
      <form
        className="form-card create-poll-form"
        onSubmit={handleSubmit}
      >
        <Link to={`/polls/${id}`} className="back-link">
          ← Voltar
        </Link>

        <h1>Editar enquete</h1>

        <label htmlFor="title">Título</label>

        <input
          id="title"
          name="title"
          type="text"
          value={form.title}
          onChange={handleChange}
          maxLength={255}
          required
        />

        <label htmlFor="description">Descrição</label>

        <textarea
          id="description"
          name="description"
          rows={4}
          value={form.description}
          onChange={handleChange}
        />

        <label htmlFor="expires_at">
          Data de expiração
        </label>

        <input
          id="expires_at"
          name="expires_at"
          type="datetime-local"
          value={form.expires_at}
          onChange={handleChange}
        />

        <p className="form-help">
          As opções não podem ser alteradas depois da criação.
        </p>

        {message && (
          <p className={isError ? 'message error' : 'message success'}>
            {message}
          </p>
        )}

        <button
          type="submit"
          className="primary-button"
          disabled={saving}
        >
          {saving ? 'Salvando...' : 'Salvar alterações'}
        </button>
      </form>
    </section>
  );
}

function formatDateForInput(date) {
  if (!date) {
    return '';
  }

  return date.replace(' ', 'T').slice(0, 16);
}

function formatDateForApi(date) {
  if (!date) {
    return null;
  }

  return `${date.replace('T', ' ')}:00`;
}