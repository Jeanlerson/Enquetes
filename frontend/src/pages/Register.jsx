import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';

export default function Register() {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
  });

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

  async function handleSubmit(event) {
    event.preventDefault();

    setLoading(true);
    setMessage('');
    setIsError(false);

    try {
      const response = await api.post('/register', form);

      setMessage(response.data.message);

      setTimeout(() => {
        navigate('/login');
      }, 1000);
    } catch (error) {
      setIsError(true);
      setMessage(
        error.response?.data?.message
          || 'Não foi possível realizar o cadastro.',
      );
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="auth-page">
      <form className="form-card" onSubmit={handleSubmit}>
        <h1>Criar conta</h1>

        <label htmlFor="name">Nome</label>
        <input
          id="name"
          name="name"
          type="text"
          value={form.name}
          onChange={handleChange}
          required
        />

        <label htmlFor="email">E-mail</label>
        <input
          id="email"
          name="email"
          type="email"
          value={form.email}
          onChange={handleChange}
          required
        />

        <label htmlFor="password">Senha</label>
        <input
          id="password"
          name="password"
          type="password"
          minLength={6}
          value={form.password}
          onChange={handleChange}
          required
        />

        {message && (
          <p className={isError ? 'message error' : 'message success'}>
            {message}
          </p>
        )}

        <button type="submit" disabled={loading}>
          {loading ? 'Cadastrando...' : 'Cadastrar'}
        </button>

        <p>
          Já possui uma conta? <Link to="/login">Entrar</Link>
        </p>
      </form>
    </section>
  );
}