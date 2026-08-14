# CONTAS

Sistema independente de **prestação de contas para políticos** — PHP 8.1+ · MySQL · cPanel  
Host: [contas.synetiq.com.br](https://contas.synetiq.com.br)  
Desenvolvido por [Synetiq](https://synetiq.com.br) — Soluções Digitais Inteligentes

Sistema **interno** (sem portal público). Quem não é Master entra só para **consultar**, sem alterar dados.

## Conformidade Conta+JE / TRE-GO / TSE 2026

Alinhado ao Manual Conta+JE e à legislação eleitoral, com base estadual do TRE-GO:

- Hub TRE-GO: [Prestação de Contas Eleições 2026](https://www.tre-go.jus.br/eleicoes/prestacao-de-contas-eleitorais/prestacao-de-contas-eleicoes-2026)
- Catálogo embutido: `data/tre-go-2026.json` (prazos, limites GO, GRU/PagTesouro e 30+ links oficiais do hub e subpáginas)
- Tela no sistema: **Base legal TRE-GO 2026**
- Relatórios Conta+JE §11 (diversos, receitas, despesas, recibos) + RF 72h / parcial / final TRE-GO
- Lei nº 9.504/1997 · Res.-TSE 23.607/2019 · Res.-TSE 23.610/2019
- Manual Conta+JE (PDF)

O CONTAS prepara e organiza a prestação; a **entrega oficial** continua no Conta+JE do TSE.

Recursos implementados:

- Contas bancárias com fonte: Doações para Campanha, Fundo Partidário, FEFC (obrigatórias no TRE-GO)
- Limites de gastos e de militância Goiás 2026
- Prazos parcial/final, RAC (10 dias), relatórios em 72h
- Tipos de receita Conta+JE + espécies + recibo eleitoral
- Representantes legais (advogado OAB / contabilista CRC)
- Verificação de inconsistências (impeditivas / não impeditivas)
- Relatórios imprimíveis / CSV para conferência antes da entrega
- Qualificação com endereço e contatos

## Deploy limpo (cPanel)

### Download no GitHub

Pacote pronto para cPanel (branch desta PR):

- [`releases/contas-cpanel-deploy.zip`](releases/contas-cpanel-deploy.zip)

Ou gere localmente:

```bash
bash bin/pack-deploy.sh
# → dist/contas-deploy-YYYYMMDD.zip
```

### O que sobe no servidor

```
contas/
├── .htaccess
├── .env.example
├── bootstrap.php
├── index.php · login.php · logout.php
├── install.php          ← apagar depois de instalar
├── admin/               ← painel + dashboard
├── api/
├── assets/
├── config/ · lib/ · sql/ · templates/
└── uploads/candidates/  ← foto do deputado (gravável)
```

### Banco de dados (cPanel)

- Banco: `synetiqcombr_contas`
- Domínio: `contas.synetiq.com.br`

### Passos no cPanel

1. Confirme o banco MySQL `synetiqcombr_contas` + usuário (ALL PRIVILEGES).
2. Upload do ZIP no document root do subdomínio `contas.synetiq.com.br`.
3. Acesse `/install.php` e configure o MySQL (use o banco `synetiqcombr_contas`).
4. Apague `install.php`.
5. Garanta permissão de escrita em `uploads/candidates/` (755 ou 775).
6. Confirme `/api/health.php`.

### Credenciais demo

Senha: `admin123`

| E-mail | Perfil |
|---|---|
| master@contas.synetiq.com.br | Master (altera / lança) |
| financeiro@contas.synetiq.com.br | Financeiro (só leitura) |
| rh@contas.synetiq.com.br | RH (só leitura) |
| consulta@contas.synetiq.com.br | Consulta (só leitura) |

### Foto do deputado

No **Wizard** (`admin/wizard.php`), envie a foto em retrato **3∶4**, ideal **600 × 800 px** (JPG/PNG/WebP, até 2,5 MB).  
Ela aparece na **página inicial** (dashboard) ao lado do nome.

### Fontes

Sora (títulos) + Manrope (texto) — tipografia séria e legível.

### Dashboard e menu

- Home mostra só **dados já lançados** (KPIs, gráficos, ranking de despesas), no espírito do DivulgaCandContas/TSE.
- **Configuração** (campanha/foto, vínculos, usuários, lançamento) fica no menu lateral, fora da home.
- NF-e válida abre o **espelho DANFE completo** — não uma página para colar a chave.

### App instalável (PWA)

O portal funciona como aplicativo (Progressive Web App):

- **Android / Chrome:** banner *Instalar app* ou menu ⋮ → Instalar aplicativo  
- **iPhone / Safari:** Compartilhar → Adicionar à Tela de Início  
- Abre em tela cheia (`standalone`), com ícone próprio e atalhos (Dashboard, Lançar, Despesas, Manual)  
- Exige **HTTPS** no domínio de produção (cPanel + SSL)

Arquivos: `manifest.php`, `sw.js`, `assets/img/icons/*`.

## Requisitos

PHP 8.1+ (`pdo_mysql`, `mbstring`, `gd` recomendado para redimensionar foto, `fileinfo`) · MySQL 5.7+ / MariaDB 10.3+

---

© Synetiq — [synetiq.com.br](https://synetiq.com.br)
