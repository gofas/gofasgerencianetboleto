# Changelog

## [v1.1.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v1.1.0) (2021-02-12)

[Full Changelog](https://github.com/mauriciogofas/gofasgerencianetboleto/compare/v1.0.0...v1.1.0)

**Implemented enhancements:**

- Limitar nº de caracteres dos itens da fatura para 250 [\#88](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/88)
- Atualizar descrição da transação a cada atualização na API [\#75](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/75)

**Fixed bugs:**

- Corrigir erros das configurações [\#68](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/68)
- Atualizar SDK - v2.0.0 [\#67](https://github.com/mauriciogofas/gofasgerencianetboleto/issues/67)

## [v1.0.0](https://github.com/mauriciogofas/gofasgerencianetboleto/tree/v1.0.0) (2021-02-12)

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
