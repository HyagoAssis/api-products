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
    echo ".env já existe, mantendo o arquivo atual."
else
    cp "${EXAMPLE_FILE}" "${ENV_FILE}"
    echo ".env criado a partir de .env.example."
fi

# Garante UID/GID no .env (usados pelo docker-compose para o serviço app).
set_env_var() {
    local key="$1"
    local value="$2"

    if grep -q "^${key}=" "${ENV_FILE}"; then
        sed -i "s/^${key}=.*/${key}=${value}/" "${ENV_FILE}"
    else
        printf '\n%s=%s\n' "${key}" "${value}" >> "${ENV_FILE}"
    fi
}

set_env_var "UID" "${SCRIPT_UID}"
set_env_var "GID" "${SCRIPT_GID}"

echo "UID=${SCRIPT_UID} e GID=${SCRIPT_GID} definidos no .env."
echo "Pronto."
