#!/bin/sh
set -e

# Instala dependências PHP
composer install

# Gera a APP_KEY
if ! grep -qE '^APP_KEY=.+' .env; then
  echo "Gerando APP_KEY..."
  php artisan key:generate
else
  echo "APP_KEY já definida, mantendo."
fi

# Clear configurations to avoid caching issues in development
echo "Clearing configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Rodando migrations..."
php artisan migrate

# Aguarda o Elasticsearch aceitar conexões antes de criar o índice
ELASTIC_HOST="${ELASTIC_HOST:-elasticsearch:9200}"
echo "Aguardando o Elasticsearch em ${ELASTIC_HOST}..."
until curl -s -o /dev/null "http://${ELASTIC_HOST}"; do
  echo "Elasticsearch indisponível, tentando novamente em 3s..."
  sleep 3
done

echo "Criando índice do Elasticsearch (se ainda não existir)..."
php artisan scout:index "App\Models\Product"

echo "🔁 Iniciando Supervisor"
exec supervisord -c /usr/local/supervisord.conf
