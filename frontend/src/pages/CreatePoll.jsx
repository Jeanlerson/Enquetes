import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

export default function CreatePoll() {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    title: '',
    description: '',
    expires_at: '',
  });

  const [options, setOptions] = useState([
    '',
    '',
  ]);

  const [message, setMessage] = useState('');
  const [isError, setIsError] = useState(false);
  const [loading, setLoading] = useState(false);

  function handleChange(event) {
    const { name, value } = event.target;

    setForm((current) => ({
      ...current,
      [name]: value,
    }));
  }

  function handleOptionChange(index, value) {
    setOptions((current) => (
      current.map((option, optionIndex) => (
        optionIndex === index ? value : option
      ))
    ));
  }

  function addOption() {
    if (options.length >= 8) {
      return;
    }

    setOptions((current) => [
      ...current,
      '',
    ]);
  }

  function removeOption(index) {
    if (options.length <= 2) {
      return;
    }

    setOptions((current) => (
      current.filter((_, optionIndex) => (
        optionIndex !== index
      ))
    ));
  }

  function formatExpiration(dateTimeValue) {
    if (!dateTimeValue) {
      return null;
    }

    return dateTimeValue
      .replace('T', ' ')
      .concat(':00');
  }

  async function handleSubmit(event) {
    event.preventDefault();

    setLoading(true);
    setMessage('');
    setIsError(false);

    const normalizedOptions = options
      .map((option) => option.trim())
      .filter(Boolean);

    try {
      const response = await api.post('/polls', {
        title: form.title.trim(),
        description: form.description.trim(),
        expires_at: formatExpiration(form.expires_at),
        options: normalizedOptions,
      });

      setMessage(response.data.message);

      navigate(`/polls/${response.data.poll.id}`);
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível criar a enquete.',
      );
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="create-poll-page">
      <form
        className="form-card create-poll-form"
        onSubmit={handleSubmit}
      >
        <h1>Criar enquete</h1>

        <label htmlFor="title">
          Título
        </label>

        <input
          id="title"
          name="title"
          type="text"
          value={form.title}
          onChange={handleChange}
          maxLength={255}
          required
        />

        <label htmlFor="description">
          Descrição
        </label>

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

        <div className="options-heading">
          <div>
            <h2>Opções</h2>
            <p>Adicione entre 2 e 8 opções.</p>
          </div>

          <button
            type="button"
            className="secondary-button"
            onClick={addOption}
            disabled={options.length >= 8}
          >
            Adicionar opção
          </button>
        </div>

        <div className="create-option-list">
          {options.map((option, index) => (
            <div
              className="create-option-row"
              key={`option-${index}`}
            >
              <input
                type="text"
                value={option}
                placeholder={`Opção ${index + 1}`}
                onChange={(event) => {
                  handleOptionChange(
                    index,
                    event.target.value,
                  );
                }}
                maxLength={255}
                required
              />

              <button
                type="button"
                className="remove-option-button"
                onClick={() => removeOption(index)}
                disabled={options.length <= 2}
                aria-label={`Remover opção ${index + 1}`}
              >
                Remover
              </button>
            </div>
          ))}
        </div>

        {message && (
          <p
            className={
              isError
                ? 'message error'
                : 'message success'
            }
          >
            {message}
          </p>
        )}

        <button
          type="submit"
          className="primary-button"
          disabled={loading}
        >
          {loading
            ? 'Criando enquete...'
            : 'Criar enquete'}
        </button>
      </form>
    </section>
  );
}