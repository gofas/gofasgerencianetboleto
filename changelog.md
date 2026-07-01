# Changelog

## [v3.12.1 - 01/07/2026](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.12.1)


**Melhorias:**

- Token anônimo de telemetria usa a versão do arquivo (ggnb_module_version) - [#193](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/193) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.12.0...v3.12.1)

## [v3.12.0 - 01/07/2026](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.12.0)


**Melhorias:**

- Telemetria com consentimento opt-in e confirmações anônimas por módulo+versão - [#192](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/192) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Boleto cancelado e recriado ao visualizar fatura quando API retorna rate_limit_exceeded - [#191](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/191) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.11.3...v3.12.0)

## [v3.11.3  - 09/06/2026](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.11.3)


**Melhorias:**

- Bugs #188 e #189: arredondamento R$0,01 na comparação de valores do boleto - [#190](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/190)

**Correções:**

- Boleto cancelado e recriado indevidamente por diferença de R$0,01 causada por ponto flutuante - [#189](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/189) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.11.2...v3.11.3)

## [v3.11.2 - 07/06/2026](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.11.2)


**Melhorias:**

- Agora a verificação de pagamentos via cron job vem desativada por padrão e é possível desativar definindo como 0 ou vazio o campo "número de requisições" - [#186](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/186) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Agora todos os logs são gerados apenas quando a opção "salvar logs" estiver ativada - [#185](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/185) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- TypeError: Cannot access offset of type string on string no PHP 8 ao gerar boleto - [#188](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/188) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Corrigido o tratamento de erros que ainda são desconhecidos com adição de mensagem de erro mais abrangente - [#187](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/187) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

**Vale mencionar:**

- Corrigido o link nas configurações do módulo que aponta para a documentação > mergetags - [#182](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/182) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Adocumentation" style="color:#a99c9c;text-decoration:none"><code>documentation</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.10.0...v3.11.2)

## [v3.10.0 - 03/03/2025](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.10.0)


**Melhorias:**

- Compatibilidade com PHP 8.2 e 8.3 - [#181](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/181) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Melhoria ao passar o crédito aplicado à fatura como desconto no boleto - [#180](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/180) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.8...v3.10.0)

## [v3.9.8 - 02/12/2024](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.8)


**Correções:**

- Melhoria ao passar o crédito aplicado à fatura como desconto no boleto - [#180](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/180) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.7...v3.9.8)

## [v3.9.7 - 04/11/2024](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.7)


**Melhorias:**

- Alteração das rotas-bases da API (gerencianet.com.br > efipay.com.br) - [#178](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/178) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Melhoria na verificação de boletos salvos localmente - [#179](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/179) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.6...v3.9.7)

## [v3.9.6 - 25/08/2024](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.6)


**Correções:**

- Corrigido bug que excluía boletos não pagos salvos no db do WHMCS, o que impedia a baixa das respectivas faturas quando os mesmos eram pagos em atraso - [#177](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/177) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.5...v3.9.6)

## [v3.9.5 - 28/02/2024](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.5)


**Correções:**

- Evita erro ao disparar emails manualmente em faturas com dados incoerentes - [#176](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/176) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Resolvido o erro ao acionar a recuperação de senha no admin - [#175](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/175) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.4...v3.9.5)

## [v3.9.4 - 23/10/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.4)


**Correções:**

- Corrigido o conflito entre hooks que impedia a inserção de daods do boleto no email - [#174](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/174) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Corrigido o bug que acontecia quando créditos eram a adicionados à faturas mescladas - [#173](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/173) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Previne falha ao invocar arquivos essenciais do WHMCS 8.7.* - [#172](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/172) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.3...v3.9.4)

## [v3.9.3 - 13/06/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.3)


**Correções:**

- Corrigido a versão do módulo exibida na tela de configuração - [#170](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/170) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Evita erro getGatewayVariables not defined na área do cliente - [#169](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/169) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.2...v3.9.3)

## [v3.9.2 - 20/05/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.2)


**Correções:**

- Corrigido mensagem de atualização de versão exibida nas configurações do módulo - [#168](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/168) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Resolvido o erro "gateway functions not found" no cadastro - [#167](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/167) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.1...v3.9.2)

## [v3.9.1 - 06/04/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.1)


**Melhorias:**

- Adicionada compatibilidade com php 7.1 e 8.4 no mesmo módulo - [#160](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/160)

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.9.0...v3.9.1)

## [v3.9.0 - 04/04/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.9.0)


**Melhorias:**

- Diretório raíz do WHMCS agora é obtido via banco de dados consultando o diretório do template ativo (para casos onde localizações relativas podem falhar) - [#165](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/165) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionado o botão "Verificar atualizações" nas configurações que reseta a informação local e verifica a versão do módulo mais recente disponível - [#164](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/164) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Exibe nas configurações data e hora da última verificação de versão  - [#163](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/163)
- Nova opção: Máximo de verificações por requisição. Cria fila de processamento para executar a verificação de transações a fim de evitar sobrecarga e bloqueios da API - [#162](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/162) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Nova opção: Horário da verificação. A hora do dia em que o módulo deve verificar o status de pagamento dos boletos associados às faturas não pagas - [#161](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/161) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Redução e unificação de arquivos e pastas do módulo - [#159](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/159)
- Ampliada a função de carregar configurações e parâmetros personalizados - [#158](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/158)
- Corrigido erro "call to undefined function" ao inserir mergetags de email - [#157](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/157)
- Melhorar cabeçalho padrão de comentários - [#129](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/129)
- Hook: Alterar a data de vencimento do boleto, ao alterar a data de vencimento da fatura - [#37](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/37) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Hook: Atualizar boleto ao enviar lembretes de fatura em aberto - [#26](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/26) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Salvar apenas um boleto por fatura no DB - [#16](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/16) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.8.0...v3.9.0)

## [v3.8.0 - 29/03/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.8.0)


**Melhorias:**

- Apaga do banco de dados boletos pagos ou que não serão reaproveitados - [#156](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/156) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Removido o campo "chave de licença" das configurações / versões premium são disponibilizadas separadamente - [#155](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/155) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Melhorias na confirmação de pagamentos via callback - [#154](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/154) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Confirmação de pagamento ao acessar a fatura (quando boleto foi pago mas a baixa automática ainda não ocorreu) - [#153](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/153) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Removida a necessidade de criar transações com valor de R$0.00 para boletos gerados - [#150](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/150) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Melhoria na verificação de atualizações e registros de estatísticas - [#149](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/149) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.7.0...v3.8.0)

## [v3.7.0 - 28/03/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.7.0)


**Melhorias:**

- Registros mais apurados de estatística de uso - [#152](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/152) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Dispensa configuração Admin do WHMCS e define o admin incarregado da instalação como responsável pelas chamadas à API interna do WHMCS - [#151](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/151)
- Reduz de 90 para 29 dias o tempo depois de emitido que é possível atualizar um boleto  - [#148](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/148) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a> <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Verificação de status das transações e baixa automática via tarefa cron do WHMCS - [#147](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/147) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Hooks transferidos para a pasta /includes/hooks/  - [#146](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/146)

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.6.1...v3.7.0)

## [v3.6.1 - 05/03/2023](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.6.1)


**Melhorias:**

- Atualiza número da versão no código do arquivo config.php - [#144](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/144)
- Atualiza número da versão no código do arquivo config.php - [#143](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/143)
- Corrige diferença de 0,01 no valor da notificação - [#142](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/142)
- Renomear arquivo /gofasgerencianetboleto/gofasgerencianetboleto.php > /gofasgerencianetboleto/index.php - [#141](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/141)
- compatibilidade php8+ - [#140](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/140)
- Compatibilidade com PHP 8+ e ioncube encoder 12+ - [#139](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/139) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a> <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Módulo agora gratuito. Não há mais comissionamento via marketplace - [#138](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/138) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.4.0...v3.6.1)

## [v3.4.0 - 07/10/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.4.0)


**Melhorias:**

- Módulo agora gratuito. Não há mais comissionamento via marketplace - [#138](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/138) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.3.1...v3.4.0)

## [v3.3.1 - 09/07/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.3.1)


**Melhorias:**

- Evita erro ao tentar atualizar a data de vencimento de boleto gerado a mais de 90 dias - [#137](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/137) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Incorporar changelog na documentação pública do módulo - [#136](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/136) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Adocumentation" style="color:#a99c9c;text-decoration:none"><code>documentation</code></a> <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Automatizar a edição do changelog - [#135](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/135) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Corrigida a conta de recebimento de repasses via marketplace para "Gofas Software"  - [#134](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/134) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.3.0...v3.3.1)

## [v3.3.0 - 13/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.3.0)


**Melhorias:**

- Definir user_agent nas verificação de versão - evita bloqueio de acesso do firewall - [#133](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/133) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Criar versão do módulo com valor da licença fixo / mês - [#130](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/130) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Simplificar logs e debug - [#18](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/18) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.2.1...v3.3.0)

## [v3.2.1 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.2.1)


**Correções:**

- Verificar Boletos gerados por faturas mescladas - [#87](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/87) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Verificar Boletos gerados por faturas referentes a upgrade - [#11](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/11) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- melhoria na criação de boletos e inclusão dos dados nas faturas criadas ao rodar o cron - [#2](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/2) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.2.0...v3.2.1)

## [v3.2.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.2.0)


**Melhorias:**

- versão do módulo no debug - [#7](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/7) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- administrador do WHMCS - [#6](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/6) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- roda Hooks sem addon - [#4](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/4) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Descontinuado e removido do download o módulo addon - [#3](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/3) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.0.1...v3.2.0)

## [v3.0.1 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.0.1)


[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.0.0...v3.0.1)

## [v3.0.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v3.0.0)


[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.3.0...v3.0.0)

## [v2.3.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.3.0)


**Melhorias:**

- Substituir SDK Gerencianet e eliminar dependências - [#17](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/17) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Corrigir 404 no URL das imagens no admin antes de salvar as configs - [#24](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/24) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Melhorar tratamento de erros - [#22](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/22) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Obter sempre o boleto mais recente gerado por cada fatura. - [#20](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/20) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- A data do vencimento deve ser maior que a data atual - [#15](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/15) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.2...v2.3.0)

## [v2.2.2 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.2.2)


**Melhorias:**

- Configuração "Administrador do WHMCS" não é mais requerida para versões do WHMCS maiores que 7.2 - [#23](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/23) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Corrigir numeração das opções - [#27](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/27) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Evitar redeclarar funções - [#21](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/21) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.1...v2.2.2)

## [v2.2.1 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.2.1)


**Melhorias:**

- campo no modulo informando a versao instalada e qual versao atual disponivel - [#99](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/99) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Melhoria na inclusão de configurações personalizadas - [#30](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/30) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.0...v2.2.1)

## [v2.2.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.2.0)


**Melhorias:**

- Opção Customizada: Desativar criação do boleto ao gerar a fatura para IDs de grupos de produtos - [#42](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/42) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Possibilitar a inclusão de configurações customizadas que alteram as variáveis - [#34](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/34) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar Link PDF à tag {$ggnb_billet_info} - [#33](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/33) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Editar fatura no callback para abater desconto do módulo - [#32](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/32) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.1.0...v2.2.0)

## [v2.1.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.1.0)


**Melhorias:**

- Opção Customizada: Desativar criação do boleto via campo do perfil do cliente (yes/no) - [#43](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/43) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.2...v2.1.0)

## [v2.0.2 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.0.2)


**Melhorias:**

- Salvar debug nos logs de módulo do WHMCS - [#55](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/55) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Cancela o Boleto ao gerar um novo, não só apenas quando vencido o boleto anterior - [#38](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/38) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Corrigir verificação de validade de desconto - [#40](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/40) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Melhoria no cálculo de juros - [#39](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/39) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.1...v2.0.2)

## [v2.0.1 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.0.1)


**Melhorias:**

- Mostrar multa e Juros na Fatura - [#65](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/65) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar descrição do serviço nas linhas/itens do Boleto quando há taxas adicionais configuradas - [#64](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/64) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar taxas como itens de linha no boleto - [#45](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/45) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Erro que subtrai R$0,01 nos cálculos de juros + multa + taxa - desconto/crédito - [#46](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/46) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.0...v2.0.1)

## [v2.0.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v2.0.0)


**Melhorias:**

- Substituir URL da fatura por URL do Boleto na tag padrão dos templates de email {$invoice_link} - [#101](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/101) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Copiar a linha digitável do boleto com um clique - [#74](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/74) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Gerar o boleto quando a fatura é gerada - [#58](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/58) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Gerar merge tags - hook - [#56](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/56) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Obter CPF e CNPJ sem necessidade de configuração - [#53](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/53) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Cancelar Boleto ao Cancelar Fatura - [#52](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/52) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Remover opção Exige CPF para PJ - [#51](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/51) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Numerar as opções do módulo - [#50](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/50) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Redireciona para o boleto ao adicionar o parâmetro redirectToBillet=true no URL da fatura e desativa o redirecionamento ao adicionar o parâmetro redirectToBillet=false no URL da fatura (se esta opção estiver ativada nas configurações do módulo ). - [#49](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/49) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Gerar novo boleto ao aplicar crédito à fatura - ou quando o valor da fatura mudou - [#48](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/48) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar informações do boleto no email - [#29](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/29) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Erro ao gerar novo boleto quando a transação foi cancelada na GN - [#63](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/63) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- corrigir o link da conta de cliente para apontar corretamente para a área do cliente em caso de erros - [#57](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/57) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Erro ao gerar novo boleto quando há boleto cancelado associado à fatura - [#54](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/54) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.2.3...v2.0.0)

## [v1.2.3 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v1.2.3)


**Correções:**

- Configurações adicionais para evitar erros os gerar o URL do sistema/WHMCS - [#60](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/60) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.1.0...v1.2.3)

## [v1.1.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v1.1.0)


**Melhorias:**

- Limitar nº de caracteres dos itens da fatura para 250 - [#88](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/88) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Atualizar descrição da transação a cada atualização na API - [#75](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/75) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Corrigir erros das configurações - [#68](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/68) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Atualizar SDK - v2.0.0 - [#67](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/67) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.0.0...v1.1.0)

## [v1.0.0 - 12/02/2021](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v1.0.0)


**Melhorias:**

- Verificar se já existe um boleto associado à fatura antes de gerar um novo boleto - [#127](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/127) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Incluir id da fatura no `custom_id` do boleto - [#126](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/126) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Configurar `callback` - [#125](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/125) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Exibir erros para diagnóstico: sim ou não - [#124](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/124) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- 2ª Via do boleto automática gerada ao acessar a fatura após o vencimento - [#123](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/123) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Cor do link do boleto - [#122](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/122) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Cor do fundo do botão - [#121](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/121) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Permitir transação Pessoa Jurídica - [#120](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/120) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Notifica admin em caso de falhas nas transações - [#119](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/119) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: fatura redireciona para o boleto, sim ou não - [#118](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/118) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Exibição de erros amigáveis e com links para as soluções possíveis - [#117](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/117) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: inserir IDs dos campos personalizadas CPF e CNPJ - [#116](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/116) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Renomear opção Sandbox -> desenvolvimento - [#115](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/115) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Testar pagamento em massa - várias faturas ao mesmo tempo - [#114](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/114) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Gerar fatura com CPF se CNPJ for errado ou inválido (padrão) - [#111](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/111) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Exibir erro CNPJ Incorreto quando a cobrança não é realizada devido a CNPJ inválido - [#110](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/110) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Exigir CNPJ e CPF. Obriga o envio do nome e CPF da pessoa física que está concretizando o pagamento em nome da empresa - [#109](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/109) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar um 0 (zero) no começo do CPF se esse tem 10 dígitos - [#108](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/108) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar um 0 (zero) no começo do CNPJ se esse tem 13 dígitos - [#107](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/107) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Definir valor mínimo da fatura para aceitar pagamentos via boleto - [#106](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/106) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: numero de dias a serem somados à data de vencimento da fatura, Ex.: 3 = fatura gerada dia 10 , boleto vencimento dia 13 - [#105](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/105) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Desconto fixo ou % para boleto - [#104](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/104) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Taxa fixa ou % para emissão de boletos - [#103](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/103) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Cancelar boleto anterior quando a fatura gera um novo boleto - [#102](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/102) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Escrever sobre requirimentos do sistema: versão do PHP e WHMCS - [#100](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/100) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Atualizar vencimento do boleto quando vencido - [#97](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/97) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- indicar no descritivo os serviços produtos associados à fatura - [#96](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/96) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- adicionar "configurations" e "message": - [#95](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/95) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- campo onde vc configura a taxa cobrada pela GN para que o sistema calcule e exiba o valor já com a comissão abatida do valor do boleto - [#94](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/94) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Verificar se ID ou Username do admin inseridos na configuração do módulo é válido, se não, usar `1` - [#93](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/93) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- opção: Remover desconto após o vencimento - [#92](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/92) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Remover link contribuir - [#86](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/86) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Tipo de desconto personalizado - custom field - [#84](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/84) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Valor do desconto personalizado - custom field - [#83](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/83) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Opção: Desconto para pagamento até XX dias antes do vencimento - [#82](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/82) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- configurações - reorganização e divisores - [#80](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/80) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Estilização para tornar a configuração mais intuitiva - [#79](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/79) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar instruções iniciais nas configurações - [#78](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/78) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Adicionar todas as configurações no Debug - [#73](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/73) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Add Gerencianet Dashboard ao pacote - [#71](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/71) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Tela de configurações - [#70](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/70) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>
- Link do boleto abre em nova Guia - [#69](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/69) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Aenhancement" style="color:#a99c9c;text-decoration:none"><code>enhancement</code></a>

**Correções:**

- Não exibir debug na página administrativa  ao gerar fatura - [#113](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/113) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Não redirecionar para o boleto no admin (ao salvar ou disparar email de fatura) - [#112](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/112) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Verificar variável $diasParaVencimento - [#98](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/98) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Erro ao gravar a transação - API WHMCS. Admin User var is required if no admin is logged in - [#91](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/91) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Verificar parâmetro $whmcsAdmin - [#90](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/90) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Verificar erros - [#89](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/89) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Corrigir erro desconto - [#77](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/77) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>
- Verificar transação localmente antes de verificar API - evitar erro - [#76](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/76) <a href="https://github.com/mauriciogofas/gofasgerencianetboleto/issues?q=is%3Aissue+state%3Aclosed+label%3Abug" style="color:#a99c9c;text-decoration:none"><code>bug</code></a>

[Comparar versões](https://github.com/mauriciogofas/gofasgerencianetboleto/releases/tag/v1.0.0)

