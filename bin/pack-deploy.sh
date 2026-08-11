#!/usr/bin/env bash
# Gera pacote limpo para upload no cPanel (sem .git, .env, docs, dist).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT_DIR="${ROOT}/dist"
STAMP="$(date +%Y%m%d)"
ZIP_NAME="pollicontas-deploy-${STAMP}.zip"
STAGE="${OUT_DIR}/stage"

rm -rf "${STAGE}"
mkdir -p "${STAGE}" "${OUT_DIR}"

# Arquivos e pastas que sobem para o servidor
INCLUDE=(
  .htaccess
  .env.example
  bootstrap.php
  index.php
  login.php
  logout.php
  install.php
  publico.php
  esqueci-senha.php
  redefinir-senha.php
  manifest.php
  sw.js
  admin
  api
  assets
  config
  data
  lib
  sql
  templates
  uploads
)

for item in "${INCLUDE[@]}"; do
  cp -a "${ROOT}/${item}" "${STAGE}/"
done

# Garantias: nada sensível no pacote
rm -f "${STAGE}/.env" "${STAGE}/.env.local" 2>/dev/null || true
find "${STAGE}" -type d -name '.git' -prune -exec rm -rf {} + 2>/dev/null || true
find "${STAGE}" -type f \( -name '.DS_Store' -o -name '*.log' -o -name '*.tmp' \) -delete

(
  cd "${STAGE}"
  zip -qr "${OUT_DIR}/${ZIP_NAME}" .
)

rm -rf "${STAGE}"

echo "Pacote: ${OUT_DIR}/${ZIP_NAME}"
echo "Conteúdo:"
unzip -l "${OUT_DIR}/${ZIP_NAME}" | sed -n '1,120p'
