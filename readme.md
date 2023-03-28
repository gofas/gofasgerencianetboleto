# Changelog

## [v3.7.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.7.0) (28/03/2023)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.6.1...v3.7.0)

**Implemented enhancements:**

- Registros mais apurados de estatística de uso [\#152](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/152)
- Reduz de 90 para 29 dias o tempo depois de emitido que é possível atualizar um boleto  [\#148](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/148)
- Verificação de status das transações e baixa automática via tarefa cron do WHMCS [\#147](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/147)

**Closed issues:**

- Dispensa configuração Admin do WHMCS e define o admin incarregado da instalação como responsável pelas chamadas à API interna do WHMCS [\#151](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/151)
- Hooks transferidos para a pasta /includes/hooks/  [\#146](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/146)

## [v3.6.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.6.1) (05/03/2023)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.4.0...v3.6.1)

**Implemented enhancements:**

- Compatibilidade com PHP 8+ e ioncube encoder 12+ [\#139](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/139)
- Módulo agora gratuito. Não há mais comissionamento via marketplace [\#138](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/138)

**Closed issues:**

- Atualiza número da versão no código do arquivo config.php [\#144](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/144)
- Atualiza número da versão no código do arquivo config.php [\#143](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/143)
- Corrige diferença de 0,01 no valor da notificação [\#142](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/142)
- Renomear arquivo /gofasgerencianetboleto/gofasgerencianetboleto.php \> /gofasgerencianetboleto/index.php [\#141](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/141)
- compatibilidade php8+ [\#140](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/140)

## [v3.4.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.4.0) (07/10/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.3.1...v3.4.0)

## [v3.3.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.3.1) (09/07/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.3.0...v3.3.1)

**Implemented enhancements:**

- Evita erro ao tentar atualizar a data de vencimento de boleto gerado a mais de 90 dias [\#137](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/137)
- Incorporar changelog na documentação pública do módulo [\#136](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/136)
- Automatizar a edição do changelog [\#135](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/135)

**Fixed bugs:**

- Corrigida a conta de recebimento de repasses via marketplace para "Gofas Software"  [\#134](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/134)

## [v3.3.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.3.0) (13/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.2...v3.3.0)

**Implemented enhancements:**

- Definir user\_agent nas verificação de versão - evita bloqueio de acesso do firewall [\#133](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/133)
- Criar versão do módulo com valor da licença fixo / mês [\#130](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/130)
- Simplificar logs e debug [\#18](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/18)

## [v2.2.2](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.2.2) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.0.1...v2.2.2)

**Implemented enhancements:**

- Configuração "Administrador do WHMCS" não é mais requerida para versões do WHMCS maiores que 7.2 [\#23](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/23)

**Fixed bugs:**

- Corrigir numeração das opções [\#27](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/27)
- Evitar redeclarar funções [\#21](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/21)

## [v3.0.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.0.1) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.0...v3.0.1)

## [v2.2.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.2.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.2.1...v2.2.0)

**Implemented enhancements:**

- Opção Customizada: Desativar criação do boleto ao gerar a fatura para IDs de grupos de produtos [\#42](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/42)
- Possibilitar a inclusão de configurações customizadas que alteram as variáveis [\#34](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/34)
- Adicionar Link PDF à tag {$ggnb\_billet\_info} [\#33](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/33)
- Editar fatura no callback para abater desconto do módulo [\#32](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/32)

## [v2.2.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.2.1) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.2.1...v2.2.1)

**Implemented enhancements:**

- campo no modulo informando a versao instalada e qual versao atual disponivel [\#99](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/99)
- Melhoria na inclusão de configurações personalizadas [\#30](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/30)

## [v3.2.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.2.1) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.3.0...v3.2.1)

**Fixed bugs:**

- Verificar Boletos gerados por faturas mescladas [\#87](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/87)
- Verificar Boletos gerados por faturas referentes a upgrade [\#11](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/11)
- melhoria na criação de boletos e inclusão dos dados nas faturas criadas ao rodar o cron [\#2](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/2)

## [v2.3.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.3.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.0.0...v2.3.0)

**Implemented enhancements:**

- Substituir SDK Gerencianet e eliminar dependências [\#17](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/17)

**Fixed bugs:**

- Corrigir 404 no URL das imagens no admin antes de salvar as configs [\#24](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/24)
- Melhorar tratamento de erros [\#22](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/22)
- Obter sempre o boleto mais recente gerado por cada fatura. [\#20](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/20)
- A data do vencimento deve ser maior que a data atual [\#15](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/15)

## [v3.0.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.0.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v3.2.0...v3.0.0)

## [v3.2.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v3.2.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.1.0...v3.2.0)

**Implemented enhancements:**

- versão do módulo no debug [\#7](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/7)
- administrador do WHMCS [\#6](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/6)
- roda Hooks sem addon [\#4](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/4)
- Descontinuado e removido do download o módulo addon [\#3](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/3)

## [v2.1.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.1.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.2...v2.1.0)

**Implemented enhancements:**

- Opção Customizada: Desativar criação do boleto via campo do perfil do cliente \(yes/no\) [\#43](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/43)

## [v2.0.2](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.0.2) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.0...v2.0.2)

**Implemented enhancements:**

- Salvar debug nos logs de módulo do WHMCS [\#55](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/55)
- Opção: Cancela o Boleto ao gerar um novo, não só apenas quando vencido o boleto anterior [\#38](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/38)

**Fixed bugs:**

- Corrigir verificação de validade de desconto [\#40](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/40)
- Melhoria no cálculo de juros [\#39](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/39)

## [v2.0.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.0.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v2.0.1...v2.0.0)

**Implemented enhancements:**

- Substituir URL da fatura por URL do Boleto na tag padrão dos templates de email {$invoice\_link} [\#101](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/101)
- Copiar a linha digitável do boleto com um clique [\#74](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/74)
- Gerar o boleto quando a fatura é gerada [\#58](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/58)
- Gerar merge tags - hook [\#56](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/56)
- Obter CPF e CNPJ sem necessidade de configuração [\#53](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/53)
- Cancelar Boleto ao Cancelar Fatura [\#52](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/52)
- Remover opção Exige CPF para PJ [\#51](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/51)
- Numerar as opções do módulo [\#50](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/50)
- Redireciona para o boleto ao adicionar o parâmetro redirectToBillet=true no URL da fatura e desativa o redirecionamento ao adicionar o parâmetro redirectToBillet=false no URL da fatura \(se esta opção estiver ativada nas configurações do módulo \). [\#49](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/49)
- Gerar novo boleto ao aplicar crédito à fatura - ou quando o valor da fatura mudou [\#48](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/48)
- Adicionar informações do boleto no email [\#29](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/29)

**Fixed bugs:**

- Erro ao gerar novo boleto quando a transação foi cancelada na GN [\#63](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/63)
- corrigir o link da conta de cliente para apontar corretamente para a área do cliente em caso de erros [\#57](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/57)
- Erro ao gerar novo boleto quando há boleto cancelado associado à fatura [\#54](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/54)

## [v2.0.1](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v2.0.1) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.2.3...v2.0.1)

**Implemented enhancements:**

- Mostrar multa e Juros na Fatura [\#65](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/65)
- Adicionar descrição do serviço nas linhas/itens do Boleto quando há taxas adicionais configuradas [\#64](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/64)
- Adicionar taxas como itens de linha no boleto [\#45](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/45)

**Fixed bugs:**

- Erro que subtrai R$0,01 nos cálculos de juros + multa + taxa - desconto/crédito [\#46](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/46)

## [v1.2.3](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v1.2.3) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.1.0...v1.2.3)

**Fixed bugs:**

- Configurações adicionais para evitar erros os gerar o URL do sistema/WHMCS [\#60](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/60)

## [v1.1.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v1.1.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.0.0...v1.1.0)

**Implemented enhancements:**

- Limitar nº de caracteres dos itens da fatura para 250 [\#88](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/88)
- Atualizar descrição da transação a cada atualização na API [\#75](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/75)

**Fixed bugs:**

- Corrigir erros das configurações [\#68](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/68)
- Atualizar SDK - v2.0.0 [\#67](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/67)

## [v1.0.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v1.0.0) (12/02/2021)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/0105aa2b2d122c069c13fc43f2f0a1a34935a937...v1.0.0)

**Implemented enhancements:**

- Verificar se já existe um boleto associado à fatura antes de gerar um novo boleto [\#127](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/127)
- Incluir id da fatura no `custom\_id` do boleto [\#126](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/126)
- Configurar `callback` [\#125](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/125)
- Opção: Exibir erros para diagnóstico: sim ou não [\#124](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/124)
- 2ª Via do boleto automática gerada ao acessar a fatura após o vencimento [\#123](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/123)
- Opção: Cor do link do boleto [\#122](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/122)
- Opção: Cor do fundo do botão [\#121](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/121)
- Permitir transação Pessoa Jurídica [\#120](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/120)
- Opção: Notifica admin em caso de falhas nas transações [\#119](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/119)
- Opção: fatura redireciona para o boleto, sim ou não [\#118](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/118)
- Exibição de erros amigáveis e com links para as soluções possíveis [\#117](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/117)
- Opção: inserir IDs dos campos personalizadas CPF e CNPJ [\#116](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/116)
- Renomear opção Sandbox -\> desenvolvimento [\#115](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/115)
- Testar pagamento em massa - várias faturas ao mesmo tempo [\#114](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/114)
- Gerar fatura com CPF se CNPJ for errado ou inválido \(padrão\) [\#111](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/111)
- Opção: Exibir erro CNPJ Incorreto quando a cobrança não é realizada devido a CNPJ inválido [\#110](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/110)
- Opção: Exigir CNPJ e CPF. Obriga o envio do nome e CPF da pessoa física que está concretizando o pagamento em nome da empresa [\#109](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/109)
- Adicionar um 0 \(zero\) no começo do CPF se esse tem 10 dígitos [\#108](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/108)
- Adicionar um 0 \(zero\) no começo do CNPJ se esse tem 13 dígitos [\#107](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/107)
- Opção: Definir valor mínimo da fatura para aceitar pagamentos via boleto [\#106](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/106)
- Opção: numero de dias a serem somados à data de vencimento da fatura, Ex.: 3 = fatura gerada dia 10 , boleto vencimento dia 13 [\#105](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/105)
- Opção: Desconto fixo ou % para boleto [\#104](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/104)
- Opção: Taxa fixa ou % para emissão de boletos [\#103](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/103)
- Opção: Cancelar boleto anterior quando a fatura gera um novo boleto [\#102](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/102)
- Escrever sobre requirimentos do sistema: versão do PHP e WHMCS [\#100](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/100)
- Atualizar vencimento do boleto quando vencido [\#97](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/97)
- indicar no descritivo os serviços produtos associados à fatura [\#96](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/96)
- adicionar "configurations" e "message": [\#95](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/95)
- campo onde vc configura a taxa cobrada pela GN para que o sistema calcule e exiba o valor já com a comissão abatida do valor do boleto [\#94](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/94)
- Verificar se ID ou Username do admin inseridos na configuração do módulo é válido, se não, usar `1` [\#93](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/93)
- opção: Remover desconto após o vencimento [\#92](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/92)
- Remover link contribuir [\#86](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/86)
- Opção: Tipo de desconto personalizado - custom field [\#84](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/84)
- Opção: Valor do desconto personalizado - custom field [\#83](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/83)
- Opção: Desconto para pagamento até XX dias antes do vencimento [\#82](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/82)
- configurações - reorganização e divisores [\#80](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/80)
- Estilização para tornar a configuração mais intuitiva [\#79](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/79)
- Adicionar instruções iniciais nas configurações [\#78](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/78)
- Adicionar todas as configurações no Debug [\#73](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/73)
- Add Gerencianet Dashboard ao pacote [\#71](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/71)
- Tela de configurações [\#70](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/70)
- Link do boleto abre em nova Guia [\#69](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/69)

**Fixed bugs:**

- Não exibir debug na página administrativa  ao gerar fatura [\#113](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/113)
- Não redirecionar para o boleto no admin \(ao salvar ou disparar email de fatura\) [\#112](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/112)
- Verificar variável $diasParaVencimento [\#98](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/98)
- Erro ao gravar a transação - API WHMCS. Admin User var is required if no admin is logged in [\#91](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/91)
- Verificar parâmetro $whmcsAdmin [\#90](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/90)
- Verificar erros [\#89](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/89)
- Corrigir erro desconto [\#77](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/77)
- Verificar transação localmente antes de verificar API - evitar erro [\#76](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/76)



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
