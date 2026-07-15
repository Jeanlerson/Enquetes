import {
  Navigate,
  Route,
  Routes,
} from 'react-router-dom';
import Header from './components/Header';
import ProtectedRoute from './components/ProtectedRoute';
import CreatePoll from './pages/CreatePoll';
import Login from './pages/Login';
import PollDetails from './pages/PollDetails';
import PollList from './pages/PollList';
import Register from './pages/Register';

export default function App() {
  return (
    <>
      <Header />

      <main className="container">
        <Routes>
          <Route path="/" element={<PollList />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />

          <Route
            path="/polls/:id"
            element={<PollDetails />}
          />

          <Route
            path="/polls/create"
            element={
              <ProtectedRoute>
                <CreatePoll />
              </ProtectedRoute>
            }
          />

          <Route
            path="*"
            element={<Navigate to="/" replace />}
          />
        </Routes>
      </main>
    </>
  );
}