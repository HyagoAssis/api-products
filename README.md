# API de Produtos

API REST para gerenciamento de **produtos** e **categorias**, construída com **Laravel 13** (PHP 8.5).
Conta com autenticação via **Laravel Sanctum** (tokens), busca de produtos com **Elasticsearch** (via Laravel Scout),
filas com **Redis + Horizon**, log de alterações de produtos e monitoramento de erros com **Sentry**.

---

## 🧱 Stack

| Serviço          | Tecnologia                        | Porta |
|------------------|-----------------------------------|-------|
| App (PHP-FPM)    | Laravel 13 / PHP 8.5 + Supervisor | —     |
| Web              | Nginx                             | 80    |
| Banco de dados   | PostgreSQL 17                     | 5432  |
| Cache / Filas    | Redis 7.4                         | 6379  |
| Busca            | Elasticsearch 9.1.2               | 9200  |
| Visualização     | Kibana 9.1.2                      | 5601  |

O container `app` roda o PHP-FPM e o **Horizon** juntos, gerenciados pelo Supervisor.

---

## 🚀 Como rodar o projeto localmente com Docker

### Pré-requisitos

- [Git](https://git-scm.com/)
- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose v2](https://docs.docker.com/compose/) (já incluso no Docker recente)

### Passo a passo

1. Clone o repositório:
    ```bash
    git clone git@github.com:HyagoAssis/api-products.git
    cd api-products
    ```

2. Crie o arquivo `.env` a partir do `.env.example` (o script também define o `UID`/`GID`
   usados pelo container `app` para manter as permissões dos arquivos corretas):
    ```bash
    ./project create-envs
    # ou informando um UID/GID específico:
    # ./project create-envs 1000 1000
    ```
   > **Sentry (opcional):** preencha o `SENTRY_LARAVEL_DSN` no `.env` com o DSN do seu projeto no Sentry
   > para ativar o monitoramento de erros. Se deixar **vazio**, o Sentry fica desativado e a API funciona
   > normalmente. Veja como obter o DSN em
   > [Monitoramento de erros com Sentry](#-monitoramento-de-erros-com-sentry).

3. Edite o arquivo `hosts` do sistema operacional
    - Tutorial: https://docs.rackspace.com/docs/como-modifico-meus-arquivos-de-hosts
    - Insira a linha abaixo:
    ```bash
    127.0.0.1 projeto.site
    ```

4. Suba os containers da aplicação (a primeira vez pode demorar um pouco):
    ```bash
    ./project up
    ```
   O `entrypoint` do container já executa automaticamente `composer install`, `php artisan key:generate`,
   as **migrations** e a criação do **índice do Elasticsearch**. Não é necessário rodar esses passos manualmente.

5. **Popule o banco com dados de exemplo** (usuário de teste, categorias e produtos)
   e indexe os produtos no Elasticsearch:
    ```bash
    ./project artisan db:seed
    ```
   O seed cria um usuário de teste e importa os produtos para a busca (`scout:import`):
    - **E-mail:** `test@example.com`
    - **Senha:** `password`

6. Pronto! A API estará disponível em `http://projeto.site`.
    - Kibana (opcional, para inspecionar o Elasticsearch): `http://localhost:5601`

7. Nas próximas vezes, para rodar a aplicação basta subir os containers novamente:
    ```bash
    ./project up
    ```

> **Observações**
> - Se precisar mudar nomes/portas dos containers, ajuste o `docker-compose.yml`, o `.env`
>   (`DB_HOST`, `DB_PORT`, etc.) e o arquivo de configuração do Nginx (`docker/nginx/conf.d/app.conf`).

---

## 🛠️ Manual dos scripts (`./project`)

O arquivo `./project` é um wrapper para os comandos mais comuns do dia a dia. Rodar `./project` sem argumentos
exibe a ajuda. Comandos disponíveis:

| Comando                        | O que faz                                                                                  |
|--------------------------------|--------------------------------------------------------------------------------------------|
| `./project up`                 | Sobe todos os containers (`docker compose up -d`).                                          |
| `./project down`               | Derruba todos os containers.                                                                |
| `./project prune`              | Derruba containers + volumes + imagem local do projeto (preserva imagens de registry, como postgres/redis). |
| `./project bash [cmd]`         | Abre um terminal no container `app`. Com argumentos, executa o comando (ex.: `./project bash ls -la`). |
| `./project test [args]`        | Roda os testes em paralelo (`php artisan test --parallel`). Aceita filtros (ex.: `./project test --filter=ProductTest`). |
| `./project pint [args]`        | Roda o [Laravel Pint](https://laravel.com/docs/pint) para formatar o código.               |
| `./project artisan <cmd>`      | Executa um comando Artisan no container (ex.: `./project artisan migrate`, `./project artisan db:seed`). |
| `./project create-envs [uid] [gid]` | Cria o `.env` a partir do `.env.example` e define `UID`/`GID` (padrão `1000:1000`). Chama `scripts/create_envs.sh`. |

Exemplos rápidos:

```bash
./project up                       # sobe o ambiente
./project artisan db:seed          # popula o banco
./project test                     # roda a suíte de testes
./project artisan migrate:fresh    # recria o banco
./project bash                     # entra no container
```

---

## 🔌 Endpoints da API

A autenticação é feita com **Laravel Sanctum**. Primeiro obtenha um token e depois envie-o no
header `Authorization: Bearer <token>` nas rotas protegidas.

### Autenticação

| Método | Rota                 | Descrição                                                                     |
|--------|----------------------|-------------------------------------------------------------------------------|
| POST   | `/api/token/create`  | Gera um token de API a partir de credenciais via **HTTP Basic Auth** (e-mail + senha). |
| GET    | `/api/user`          | Retorna o usuário autenticado. *(protegida)*                                   |

> As rotas de `register`, `login`, `logout`, verificação de e-mail e recuperação de senha
> também estão disponíveis (ver `routes/auth.php`).

### Recursos (v1) — *rotas protegidas por `auth:sanctum`*

Prefixo: `/api/v1`

| Método | Rota                      | Descrição                    |
|--------|---------------------------|------------------------------|
| GET    | `/api/v1/categories`      | Lista categorias             |
| POST   | `/api/v1/categories`      | Cria categoria               |
| GET    | `/api/v1/categories/{id}` | Detalha categoria            |
| PUT    | `/api/v1/categories/{id}` | Atualiza categoria           |
| DELETE | `/api/v1/categories/{id}` | Remove categoria             |
| GET    | `/api/v1/products`        | Lista produtos (com filtros) |
| POST   | `/api/v1/products`        | Cria produto                 |
| GET    | `/api/v1/products/{id}`   | Detalha produto              |
| PUT    | `/api/v1/products/{id}`   | Atualiza produto             |
| DELETE | `/api/v1/products/{id}`   | Remove produto               |

### Filtros da listagem de produtos

A rota `GET /api/v1/products` aceita os parâmetros de query abaixo. Eles podem ser **combinados** livremente
(todos são aplicados em conjunto, com lógica "E"):

| Parâmetro     | Tipo    | Descrição                                                                 |
|---------------|---------|---------------------------------------------------------------------------|
| `search`      | string  | Texto livre — busca em **nome**, **descrição** e **nome da categoria**.    |
| `category_id` | int     | Retorna apenas produtos da categoria informada.                           |
| `has_stock`   | boolean | Quando `true`, retorna apenas produtos com estoque disponível (`stock > 0`). |
| `min_price`   | number  | Preço mínimo (`price >=`).                                                 |
| `max_price`   | number  | Preço máximo (`price <=`).                                                 |
| `page`        | int     | Página atual (paginação).                                                  |
| `per_page`    | int     | Itens por página (padrão: 15).                                             |

Exemplo:

```http
GET /api/v1/products?search=teclado&category_id=3&has_stock=true&min_price=50&max_price=300&per_page=20
```

### 🔎 Busca com Elasticsearch

A listagem de produtos usa **Elasticsearch** (via [Laravel Scout](https://laravel.com/docs/scout) +
`elastic-scout-driver-plus`) em vez de uma consulta `LIKE` tradicional no banco. Na prática, isso traz:

- **Busca full-text em múltiplos campos** — o `search` é um `multi_match` sobre `name`, `description` e
  `category.name` ao mesmo tempo, e não apenas uma coluna.
- **Tolerância a erros de digitação (fuzziness `AUTO`)** — buscar por `"tecaldo"` ainda encontra `"teclado"`.
  Uma busca `LIKE` no banco não faria isso.
- **Relevância (scoring)** — os resultados vêm ordenados pela relevância do texto (parte `must` da query),
  enquanto categoria, estoque e faixa de preço entram como `filter` — ou seja, restringem o resultado **sem
  distorcer o score**, e ainda se beneficiam do cache de filtros do Elasticsearch.
- **Índice dedicado** — os produtos são indexados no Elasticsearch (com a categoria já embutida no documento,
  via `toSearchableArray`), o que mantém a busca rápida mesmo com a base crescendo.

**Fallback automático para o banco:** se o Elasticsearch estiver indisponível (`TransportException`), o
controller captura o erro, registra o log e cai automaticamente para uma busca equivalente no **PostgreSQL**
(`Product::buildFilteredQuery`), usando os mesmos filtros. A API continua respondendo — apenas sem os recursos
de fuzziness/relevância do full-text. Assim a indisponibilidade do Elasticsearch **não derruba** o endpoint.

> Os produtos são indexados automaticamente ao rodar `./project artisan db:seed` (`scout:import`) e o índice
> é criado no `entrypoint` ao subir o container. Para reindexar manualmente:
> `./project artisan scout:import "App\Models\Product"`.

### 🛡️ Monitoramento de erros com Sentry

A aplicação está integrada ao **[Sentry](https://sentry.io/)** (via `sentry/sentry-laravel`) para captura de
exceções e logs. Na prática:

- **Exceções não tratadas** são reportadas automaticamente (`Integration::handles` no `bootstrap/app.php`).
- **Fallback do Elasticsearch** envia a exceção explicitamente com `\Sentry\captureException()` — assim você
  fica sabendo toda vez que a busca cai para o banco, mesmo com a API respondendo normalmente.
- **Logs da aplicação** também sobem para o Sentry pelo canal `sentry_logs`, incluído no stack de log
  (`LOG_STACK=single,sentry_logs`).

**Configuração:** preencha o `SENTRY_LARAVEL_DSN` no `.env` com o DSN do seu projeto:

```env
SENTRY_LARAVEL_DSN=https://<sua-chave>@<sua-org>.ingest.us.sentry.io/<id-do-projeto>
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_ENABLE_LOGS=true
```

> Se o `SENTRY_LARAVEL_DSN` ficar **vazio**, o Sentry é automaticamente desativado (nenhum evento é enviado)
> e a aplicação roda normalmente — ideal para ambientes locais/CI sem telemetria. Como o projeto não usa
> config cacheada, o DSN pode ser preenchido a qualquer momento e passa a valer na próxima requisição HTTP
> (para os processos de background — Horizon/scheduler — reinicie o worker com `./project artisan horizon:terminate`).

**Como obter o DSN:** crie uma conta/organização e um projeto **Laravel** no Sentry e copie o DSN gerado.
Passo a passo na central de ajuda oficial:

- [Getting Started — Laravel (Sentry Docs)](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Onde encontrar o seu DSN](https://docs.sentry.io/concepts/key-terms/dsn-explainer/#where-to-find-your-dsn)

---

## 🧪 Testes

A suíte é escrita com **[Pest](https://pestphp.com/) 4** (rodando sobre o **PHPUnit 12**), cobrindo
autenticação, CRUD de produtos e categorias, filtros/paginação, disparo dos logs em Job e o fallback
da busca (Elasticsearch → banco). São **79 testes / 227 asserts**, executados em paralelo.

### Testes com Pest

A escolha do **Pest** se deve à sintaxe mais expressiva e legível — os cenários são descritos com
`it('should ...')` e helpers como `postJson`/`assertDatabaseHas`, deixando claro o comportamento
esperado sem o boilerplate de classes. Como o Pest roda **em cima do PHPUnit**, mantém total
compatibilidade com o ecossistema (mesmo runner, mesmas asserts, mesmo `php artisan test`).

Os testes rodam dentro do container `app`:

```bash
./project test                          # toda a suíte (em paralelo)
./project test --filter=ProductTest     # um teste específico
```

---

## 📮 Coleção do Postman

Uma coleção com **todos os endpoints da API** está disponível em
[`api-products-collection.postman_collection.json`](api-products-collection.postman_collection.json).
Ela cobre a autenticação (CSRF/sessão e emissão de token Bearer) e o CRUD completo de **categorias** e
**produtos** — todas as rotas podem ser testadas por lá. Importe o arquivo no Postman e rode
**`6. Create Token`** (ou o fluxo de sessão) para autenticar; o token fica salvo em `{{api_token}}` e é
enviado automaticamente nas demais requisições.

---

## 📂 Organização

```bash
api-products/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Auth/                    # Controllers de autenticação
│   │       ├── CategoryController.php   # CRUD de categorias
│   │       ├── ProductController.php    # CRUD de produtos
│   │       └── TokenManagerController.php # Emissão de tokens (Basic Auth)
│   ├── Models/
│   │   ├── Category.php                 # Model de categorias
│   │   ├── Product.php                  # Model de produtos (Searchable via Scout)
│   │   ├── User.php                     # Model de usuários
│   │   └── UserLog.php                  # Log de alterações
│   └── Support/
│       ├── MorphRelation.php
│       └── Search/                      # Helpers de busca (Elasticsearch/DB) e parâmetros
├── database/
│   ├── factories/                       # Factories dos models
│   ├── migrations/                      # Migrations
│   └── seeders/                         # DatabaseSeeder, CategorySeeder, ProductSeeder
├── routes/
│   ├── api.php                          # Rotas da API (tokens + recursos v1)
│   └── auth.php                         # Rotas de autenticação
├── tests/
│   └── Feature/                         # Testes de feature
├── docker/
│   ├── Dockerfile                       # Imagem do container app (PHP-FPM + Horizon)
│   ├── entrypoint.sh                    # Setup automático ao subir o container
│   ├── supervisord.conf                 # Configuração do Supervisor
│   ├── nginx/                           # Configurações do Nginx
│   └── php/                             # Configurações do PHP
├── scripts/
│   └── create_envs.sh                   # Criação do .env + UID/GID
├── api-products-collection.postman_collection.json  # Coleção do Postman (todos os endpoints)
├── docker-compose.yml                   # Definição dos serviços
└── project                              # Wrapper de comandos do dia a dia
```
