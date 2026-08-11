# POLLICONTAS

Prestação de contas eleitorais — **PHP 8.1+ · MySQL · cPanel**  
Host: [pollicontas.synetiq.com.br](https://pollicontas.synetiq.com.br)

Sistema **interno** (sem portal público). Quem não é Master entra só para **consultar**, sem alterar dados.

## Deploy limpo (cPanel)

### Download no GitHub

Pacote pronto para cPanel (branch desta PR):

- [`releases/pollicontas-cpanel-deploy.zip`](releases/pollicontas-cpanel-deploy.zip)

Ou gere localmente:

```bash
bash bin/pack-deploy.sh
# → dist/pollicontas-deploy-YYYYMMDD.zip
```



### O que sobe no servidor

```
pollicontas/
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

```bash
bash bin/pack-deploy.sh
# → dist/pollicontas-deploy-YYYYMMDD.zip
```

### Passos no cPanel

1. Crie banco MySQL + usuário (ALL PRIVILEGES).
2. Upload do ZIP no document root do subdomínio.
3. Acesse `/install.php` e configure o MySQL.
4. Apague `install.php`.
5. Garanta permissão de escrita em `uploads/candidates/` (755 ou 775).
6. Confirme `/api/health.php`.

### Credenciais demo

Senha: `admin123`

| E-mail | Perfil |
|---|---|
| master@pollicontas.synetiq.com.br | Master (altera / lança) |
| financeiro@pollicontas.synetiq.com.br | Financeiro (só leitura) |
| rh@pollicontas.synetiq.com.br | RH (só leitura) |
| consulta@pollicontas.synetiq.com.br | Consulta (só leitura) |

### Foto do deputado

No **Wizard** (`admin/wizard.php`), envie a foto em retrato **3∶4**, ideal **600 × 800 px** (JPG/PNG/WebP, até 2,5 MB).  
Ela aparece na **página inicial** (dashboard) ao lado do nome.

### Fontes

Source Serif 4 (títulos) + Source Sans 3 (texto) — tipografia séria e legível.

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
