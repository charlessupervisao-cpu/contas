#!/usr/bin/env bash
# Empacota deploy COMPLETO do CONTAS para cPanel (todos os arquivos necessários).
# - Valida sintaxe PHP antes de zipar (evita 500 por parse error)
# - Gera MANIFEST + DEPLOY.txt dentro do pacote
# - Publica em releases/ com nomes estáveis para download
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT_DIR="${ROOT}/dist"
RELEASES_DIR="${ROOT}/releases"
STAMP="$(date +%Y%m%d-%H%M)"
DAY="$(date +%Y%m%d)"
ZIP_NAME="CONTAS-DEPLOY-COMPLETO-${STAMP}.zip"
STAGE="${OUT_DIR}/stage"
BUILD_ID="unknown"

if [[ -f "${ROOT}/lib/Constants.php" ]]; then
  BUILD_ID="$(php -r 'require "'"${ROOT}"'/lib/Constants.php"; echo APP_BUILD;' 2>/dev/null || echo unknown)"
fi

rm -rf "${STAGE}"
mkdir -p "${STAGE}" "${OUT_DIR}" "${RELEASES_DIR}"

echo "==> Validando sintaxe PHP..."
mapfile -t PHP_FILES < <(find "${ROOT}" -type f -name '*.php' \
  ! -path '*/.git/*' ! -path '*/dist/*' ! -path '*/releases/*' ! -path '*/vendor/*')
FAIL=0
for f in "${PHP_FILES[@]}"; do
  if ! php -l "$f" >/dev/null 2>&1; then
    echo "ERRO DE SINTAXE: $f"
    php -l "$f" || true
    FAIL=1
  fi
done
if [[ "$FAIL" -ne 0 ]]; then
  echo "Abortando empacotamento: corrija os erros PHP acima."
  exit 1
fi
echo "    OK (${#PHP_FILES[@]} arquivos)"

# Arquivos e pastas necessários ao funcionamento completo
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

echo "==> Copiando arquivos para o stage..."
for item in "${INCLUDE[@]}"; do
  if [[ ! -e "${ROOT}/${item}" ]]; then
    echo "FALTANDO: ${item}"
    exit 1
  fi
  cp -a "${ROOT}/${item}" "${STAGE}/"
done

# Garantias: nada sensível / lixo
rm -f "${STAGE}/.env" "${STAGE}/.env.local" 2>/dev/null || true
find "${STAGE}" -type d -name '.git' -prune -exec rm -rf {} + 2>/dev/null || true
find "${STAGE}" -type f \( -name '.DS_Store' -o -name '*.log' -o -name '*.tmp' -o -name '*~' \) -delete
# uploads: mantém estrutura e .htaccess / .gitkeep, remove arquivos enviados
find "${STAGE}/uploads" -type f ! -name '.htaccess' ! -name '.gitkeep' -delete 2>/dev/null || true
mkdir -p "${STAGE}/uploads/contracts" "${STAGE}/uploads/candidates"
touch "${STAGE}/uploads/contracts/.gitkeep" "${STAGE}/uploads/candidates/.gitkeep"

# Checklist de arquivos críticos (deploy quebrado se faltar)
CRITICAL=(
  bootstrap.php
  install.php
  login.php
  .htaccess
  .env.example
  lib/Schema.php
  lib/Reports.php
  lib/ContaJeExport.php
  lib/ElectoralRules.php
  lib/Constants.php
  lib/Database.php
  lib/Auth.php
  admin/index.php
  admin/relatorios.php
  admin/entrega.php
  admin/base-legal.php
  admin/inconsistencias.php
  admin/representantes.php
  admin/contas.php
  admin/lancamento.php
  data/tre-go-2026.json
  sql/schema.sql
  templates/relatorio_oficial.php
  assets/css/relatorio-oficial.css
  assets/css/app.css
  assets/js/app.js
  api/health.php
)
echo "==> Verificando arquivos críticos..."
for c in "${CRITICAL[@]}"; do
  if [[ ! -f "${STAGE}/${c}" ]]; then
    echo "CRÍTICO AUSENTE NO PACOTE: ${c}"
    exit 1
  fi
done
echo "    OK (${#CRITICAL[@]} críticos)"

# Instruções dentro do ZIP
cat > "${STAGE}/LEIA-ME-DEPLOY.txt" <<EOF
================================================================================
CONTAS — PACOTE DE DEPLOY COMPLETO (cPanel)
Build: ${BUILD_ID}
Gerado em: $(date -u +%Y-%m-%dT%H:%M:%SZ)
================================================================================

CONTEÚDO
- Aplicação PHP completa (admin, api, lib, templates, assets, sql, data)
- Relatórios no formato oficial Conta+JE / TSE (templates/relatorio_oficial.php)
- Base legal TRE-GO 2026 (data/tre-go-2026.json)
- install.php para primeira instalação

INSTALAÇÃO / ATUALIZAÇÃO
1) Faça backup do .env atual e da pasta uploads/ (se já existir no servidor).
2) Extraia TODOS os arquivos deste ZIP na raiz do domínio
   (ex.: public_html/ ou a pasta de contas.synetiq.com.br).
3) NÃO apague o arquivo .env existente ao atualizar.
4) NÃO apague uploads/ (contratos e fotos).
5) Permissões sugeridas: pastas 755, arquivos 644; uploads/ gravável (775).
6) PHP 8.1+ com extensões: pdo_mysql, mbstring, json, curl, gd (foto).

PRIMEIRA INSTALAÇÃO
1) Crie o banco MySQL (ex.: synetiqcombr_contas) no cPanel.
2) Acesse https://SEU-DOMINIO/install.php
3) Informe host/usuário/senha/banco e conclua.
4) APAGUE ou proteja install.php após instalar.
5) Valide: https://SEU-DOMINIO/api/health.php
   Deve retornar "ok": true e "build": "${BUILD_ID}"

ATUALIZAÇÃO (site já instalado)
1) Sobrescreva os arquivos do ZIP (mantendo .env e uploads/).
2) Abra o sistema: o Schema::ensure() migra colunas automaticamente.
3) Confirme build em /api/health.php
4) Menu deve mostrar: Relatórios Conta+JE, Base legal TRE-GO, Inconsistências.

RELATÓRIOS OFICIAIS TSE
- Menu: Relatórios Conta+JE
- Formato: cabeçalho Justiça Eleitoral + qualificação + tabela + assinaturas
- Exportação: Imprimir/PDF (navegador) e CSV UTF-8 ( ContaJE-*.csv )
- Entrega oficial à Justiça Eleitoral continua no Conta+JE:
  https://contamaisje.tse.jus.br/

SUPORTE TÉCNICO
- Domínio: contas.synetiq.com.br
- Vendor: Synetiq — https://synetiq.com.br
================================================================================
EOF

# Manifesto com hashes
echo "==> Gerando MANIFEST.txt..."
(
  cd "${STAGE}"
  echo "CONTAS deploy manifest"
  echo "build=${BUILD_ID}"
  echo "generated=$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  echo
  find . -type f ! -name 'MANIFEST.txt' | sort | while read -r f; do
    sum="$(sha256sum "$f" | awk '{print $1}')"
    size="$(wc -c < "$f" | tr -d ' ')"
    echo "${sum}  ${size}  ${f}"
  done
) > "${STAGE}/MANIFEST.txt"

echo "==> Compactando ${ZIP_NAME}..."
(
  cd "${STAGE}"
  zip -qr "${OUT_DIR}/${ZIP_NAME}" .
)

# Publicação estável para download
cp -f "${OUT_DIR}/${ZIP_NAME}" "${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO.zip"
cp -f "${OUT_DIR}/${ZIP_NAME}" "${RELEASES_DIR}/contas-cpanel-deploy.zip"
cp -f "${OUT_DIR}/${ZIP_NAME}" "${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO-${DAY}.zip"

# Limpa zips diários antigos (mantém o do dia + aliases)
find "${RELEASES_DIR}" -maxdepth 1 -type f -name 'CONTAS-DEPLOY-COMPLETO-20*.zip' \
  ! -name "CONTAS-DEPLOY-COMPLETO-${DAY}.zip" -delete 2>/dev/null || true
rm -f "${RELEASES_DIR}/"*-cpanel-deploy.zip.bak 2>/dev/null || true

rm -rf "${STAGE}"

BYTES="$(wc -c < "${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO.zip" | tr -d ' ')"
echo
echo "OK — Deploy completo gerado"
echo "Build:   ${BUILD_ID}"
echo "Arquivo: ${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO.zip (${BYTES} bytes)"
echo "Alias:   ${RELEASES_DIR}/contas-cpanel-deploy.zip"
echo "Diário:  ${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO-${DAY}.zip"
echo "Amostra:"
unzip -l "${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO.zip" | sed -n '1,40p'
echo "..."
unzip -l "${RELEASES_DIR}/CONTAS-DEPLOY-COMPLETO.zip" | rg -n 'LEIA-ME|MANIFEST|relatorio-oficial|Reports\.php|Schema\.php|tre-go|relatorios\.php' || true
