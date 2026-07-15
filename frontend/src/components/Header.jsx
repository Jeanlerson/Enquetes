import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function Header() {
  const { user, isAuthenticated, logout } = useAuth();
  const navigate = useNavigate();

  function handleLogout() {
    logout();
    navigate('/login');
  }

  return (
    <header className="header">
      <Link to="/" className="brand">
        Sistema de Enquetes
      </Link>

      <nav className="navigation">
        <Link to="/">Enquetes</Link>

        {isAuthenticated ? (
          <>
            <Link to="/polls/create">Criar enquete</Link>

            <span>Olá, {user.name}</span>

            <button
              type="button"
              className="link-button"
              onClick={handleLogout}
            >
              Sair
            </button>
          </>
        ) : (
          <>
            <Link to="/login">Entrar</Link>
            <Link to="/register">Cadastrar</Link>
          </>
        )}
      </nav>
    </header>
  );
}