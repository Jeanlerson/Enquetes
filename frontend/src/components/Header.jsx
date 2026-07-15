import {
  Link,
  NavLink,
  useNavigate,
} from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import Button from './ui/Button';
import logo from '../assets/logo.png';

export default function Header() {
  const {
    user,
    isAuthenticated,
    logout,
  } = useAuth();

  const navigate = useNavigate();

  function handleLogout() {
    logout();
    navigate('/login');
  }

  const navClassName = ({ isActive }) => `
    rounded-lg px-3 py-2 text-sm font-semibold transition-colors
    ${
      isActive
        ? 'bg-brand-50 text-brand-700'
        : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700'
    }
  `;

  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
      <div className="mx-auto flex min-h-18 w-full max-w-[1180px] items-center justify-between gap-6 px-4 md:px-8">
        <Link
          to="/"
          className="flex items-center"
        >
          <img
            src={logo}
            alt="Enquetes Tec"
            className="h-16 w-auto"
          />
        </Link>

        <nav className="flex items-center gap-2">
          <NavLink to="/" className={navClassName}>
            Enquetes
          </NavLink>

          {isAuthenticated ? (
            <>
              <NavLink
                to="/polls/create"
                className={navClassName}
              >
                Criar enquete
              </NavLink>

              <div className="ml-2 hidden items-center gap-2 sm:flex">
                <span className="grid h-9 w-9 place-items-center rounded-full bg-brand-500 font-bold text-white">
                  {user?.name?.charAt(0)?.toUpperCase()}
                </span>

                <span className="max-w-32 truncate text-sm font-semibold text-slate-700">
                  {user?.name}
                </span>
              </div>

              <Button
                variant="neutral"
                className="ml-1 px-3 py-2"
                onClick={handleLogout}
              >
                Sair
              </Button>
            </>
          ) : (
            <>
              <NavLink
                to="/login"
                className={navClassName}
              >
                Entrar
              </NavLink>

              <Link
                to="/register"
                className="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600"
              >
                Criar conta
              </Link>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}