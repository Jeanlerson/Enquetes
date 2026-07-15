# Enquetes Tec

Sistema Full Stack para criação, gerenciamento e votação em enquetes com atualização de resultados em tempo real.

---

## Deploy

Frontend:
https://enquetes-ten.vercel.app/

Backend:
https://enquetes-production.up.railway.app/

## Sobre o Projeto

O Enquetes Tec é uma aplicação web que permite:

- Cadastro e autenticação de usuários
- Criação de enquetes personalizadas
- Votação em enquetes
- Atualização de resultados em tempo real utilizando Server-Sent Events (SSE)
- Gerenciamento de enquetes pelo criador
- Controle de permissões
- API REST segura utilizando JWT

---

## Funcionalidades

### Usuários

- Cadastro de usuários
- Login com JWT
- Consulta do usuário autenticado
- Senhas armazenadas com hash seguro

### Enquetes

- Criar enquete
- Listar enquetes
- Visualizar detalhes
- Editar enquete
- Excluir enquete
- Apenas o criador pode editar ou excluir

### Votação

- Registrar voto
- Impedir votos duplicados
- Atualizar resultados automaticamente

### Tempo Real

- Atualização dos resultados via Server-Sent Events (SSE)
- Comunicação em tempo real sem necessidade de atualizar a página

---

# Tecnologias Utilizadas

## Backend

- PHP 8
- Slim Framework 4
- PDO
- MySQL
- Firebase PHP-JWT
- PHP Dotenv

## Frontend

- React
- Vite
- React Router DOM
- Axios
- Tailwind CSS

## Banco de Dados

- MySQL
- Foreign Keys
- Transações
- Constraints
- Cascade Delete

---

# Estrutura do Projeto

```text
EnquetesTec/
│
├── backend/
│   ├── public/
│   ├── src/
│   │   ├── Config/
│   │   ├── Controllers/
│   │   ├── Helpers/
│   │   ├── Middleware/
│   │   ├── Models/
│   │   ├── Routes/
│   │   └── Services/
│   │
│   ├── .env.example
│   ├── composer.json
│   └── composer.lock
│
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   ├── contexts/
│   │   ├── pages/
│   │   ├── services/
│   │   ├── styles/
│   │   └── utils/
│   │
│   ├── .env.example
│   └── package.json
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
└── README.md
```

---

# Requisitos

Antes de executar o projeto, instale:

- PHP 8.1+
- Composer
- MySQL 8+
- Node.js 18+
- npm

---

# Configuração do Banco de Dados

Crie o banco:

```sql
CREATE DATABASE enquetes
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Execute o script:

```text
database/schema.sql
```

Opcional:

```text
database/seed.sql
```

---

# Configuração do Backend

Entre na pasta:

```bash
cd backend
```

Instale as dependências:

```bash
composer install
```

Copie o arquivo de ambiente:

### Windows

```bash
copy .env.example .env
```

### Linux / Mac

```bash
cp .env.example .env
```

Configure o arquivo `.env`:

```env
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_NAME=enquetes
DB_USER=root
DB_PASS=

JWT_SECRET=sua-chave-secreta-aqui
JWT_EXPIRATION=3600

FRONTEND_URL=http://localhost:5173

SSE_RETRY=2000
SSE_CONNECTION_DURATION=30
```

Gerar chave JWT:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

# Executando o Backend

Inicie a API:

```bash
php -S localhost:8000 -t public
```

A API ficará disponível em:

```text
http://localhost:8000
```

---

# Executando o SSE

Abra outro terminal:

```bash
cd backend
```

Execute:

```bash
php -S localhost:8001 -t public
```

Servidor SSE:

```text
http://localhost:8001
```

---

# Configuração do Frontend

Entre na pasta:

```bash
cd frontend
```

Instale as dependências:

```bash
npm install
```

Copie o ambiente:

### Windows

```bash
copy .env.example .env
```

### Linux / Mac

```bash
cp .env.example .env
```

Configure:

```env
VITE_API_URL=http://localhost:8000
VITE_SSE_URL=http://localhost:8001
```

Execute:

```bash
npm run dev
```

Aplicação disponível em:

```text
http://localhost:5173
```

---

# Endpoints da API

## Autenticação

### Cadastro

```http
POST /register
```

Body:

```json
{
  "name": "Jeanlerson",
  "email": "usuario@email.com",
  "password": "123456"
}
```

---

### Login

```http
POST /login
```

Body:

```json
{
  "email": "usuario@email.com",
  "password": "123456"
}
```

Resposta:

```json
{
  "success": true,
  "token": "JWT_TOKEN"
}
```

---

### Usuário Autenticado

```http
GET /me
```

Header:

```http
Authorization: Bearer TOKEN
```

---

## Enquetes

### Criar Enquete

```http
POST /polls
```

Header:

```http
Authorization: Bearer TOKEN
```

Body:

```json
{
  "title": "Qual tecnologia você prefere?",
  "description": "Escolha uma opção",
  "expires_at": "2026-12-31 23:59:59",
  "options": [
    "React",
    "Vue",
    "Angular"
  ]
}
```

---

### Listar Enquetes

```http
GET /polls
```

---

### Detalhes da Enquete

```http
GET /polls/{id}
```

---

### Atualizar Enquete

```http
PUT /polls/{id}
```

---

### Excluir Enquete

```http
DELETE /polls/{id}
```

---

### Resultados

```http
GET /polls/{id}/results
```

---

## Votação

### Registrar Voto

```http
POST /polls/{id}/vote
```

Body:

```json
{
  "option_id": 1
}
```

---

# SSE (Tempo Real)

Endpoint:

```http
GET /stream.php?poll_id={id}
```

Eventos recebidos:

```text
poll-results
```

---

# Segurança

- JWT para autenticação
- Password Hash utilizando `password_hash`
- Prepared Statements com PDO
- Controle de acesso por usuário
- Validação de permissões no backend
- Rotas protegidas por middleware

---

# Regras de Negócio

- Um usuário pode votar apenas uma vez por enquete
- Apenas o criador pode editar a enquete
- Apenas o criador pode excluir a enquete
- Enquetes expiradas não aceitam votos
- Exclusão de enquete remove votos e opções relacionadas

---

# Testes Realizados

## Backend

- Cadastro de usuário
- Login
- JWT
- Rotas protegidas
- CRUD de enquetes
- Registro de votos
- Resultados
- SSE

## Frontend

- Cadastro
- Login
- Dashboard
- Criação de enquete
- Votação
- Resultados em tempo real
- Edição
- Exclusão

---

# Melhorias Futuras

- Recuperação de senha
- Comentários em enquetes
- Compartilhamento de resultados
- Exportação PDF
- Dashboard administrativo
- WebSockets para escala maior
- Docker

---

# Autor

**Jeanlerson dos Santos da Silva**

Projeto desenvolvido para fins acadêmicos e demonstração de conhecimentos em:

- PHP
- Slim Framework
- MySQL
- React
- JWT
- SSE
- Arquitetura MVC
- APIs REST