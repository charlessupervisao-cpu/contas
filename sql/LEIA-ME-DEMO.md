# Base DEMO Conta+JE / TSE

Arquivo: `synetiqcombr_contas.sql`

Dump completo enviado para demonstração das atualizações Conta+JE no CONTAS
(campanha Virmondes Cruvinel · GO 2026 · contas Doações / FP / FEFC, doações,
despesas com NF-e TSE, representantes, etc.).

## Como usar no cPanel / MySQL

1. Faça backup do banco atual.
2. Importe `synetiqcombr_contas.sql` no banco `synetiqcombr_contas` (ou o nome do `.env`).
3. Publique o código CONTAS build `2026.08.14-contamaisje-ui` (ou superior).
4. No primeiro acesso, o `Schema::ensure` adiciona colunas novas Conta+JE
   (DV agência/conta, RAC, forma de pagamento, qtd/valor unitário) se faltarem.
5. Confira `/api/health.php` → `"build":"2026.08.14-contamaisje-ui"`.
6. Em **Entrega ao Conta+JE / TSE**, baixe o pacote ZIP e compare com o portal.

## Alternativa sem reimportar o dump

Na tela **Qualificação**, use “Carregar dados de demonstração” (MASTER).
Isso recria um conjunto demo operacional sem substituir o schema inteiro.
