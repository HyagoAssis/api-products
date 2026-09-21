#!/bin/bash
set -e

# UID/GID padrão 1000:1000. Podem ser passados como argumentos:
# ./scripts/create_envs.sh <uid> <gid>
SCRIPT_UID="${1:-1000}"
SCRIPT_GID="${2:-1000}"

# Raiz do projeto (um nível acima deste script).
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
EXAMPLE_FILE="${ROOT_DIR}/.env.example"

echo "Criando ${ENV_FILE}..."

if [ -f "${ENV_FILE}" ]; then
    echo ".env já existe, substituindo pelo .env.example."
else
    echo ".env criado a partir de .env.example."
fi

cp "${EXAMPLE_FILE}" "${ENV_FILE}"

# UID/GID usados pelo docker-compose para o serviço app.
{
    printf '\nUID=%s\n' "${SCRIPT_UID}"
    printf 'GID=%s\n' "${SCRIPT_GID}"
} >> "${ENV_FILE}"

echo "UID=${SCRIPT_UID} e GID=${SCRIPT_GID} definidos no .env."
echo "Pronto."
