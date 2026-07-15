import { useState } from 'react';
import {
  Link,
  useLocation,
  useNavigate,
} from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import '../styles/auth.css';

export default function Login() {
  const navigate = useNavigate();
  const location = useLocation();
  const { login } = useAuth();

  const [form, setForm] = useState({
    email: '',
    password: '',
  });

  const [message, setMessage] = useState('');
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

    try {
      const response = await api.post('/login', form);

      login(
        response.data.user,
        response.data.token,
      );

      const destination =
        location.state?.from?.pathname || '/';

      navigate(destination, {
        replace: true,
      });
    } catch (error) {
      setMessage(
        error.response?.data?.message
          || 'Não foi possível realizar o login.',
      );
    } finally {
      setLoading(false);
    }
  }

  return (
    <section className="auth-page">
      <form className="form-card" onSubmit={handleSubmit}>
        <h1>Entrar</h1>

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
          value={form.password}
          onChange={handleChange}
          required
        />

        {message && (
          <p className="message error">
            {message}
          </p>
        )}

        <button type="submit" disabled={loading}>
          {loading ? 'Entrando...' : 'Entrar'}
        </button>

        <p>
          Ainda não possui conta?{' '}
          <Link to="/register">Cadastre-se</Link>
        </p>
      </form>
    </section>
  );
}