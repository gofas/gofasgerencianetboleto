# Gofas Efí Boleto para WHMCS

Gerencianet agora é Efí. O nome mudou mas este continua sendo, desde 2016, um dos módulos mais completos do mercado para emissão de boleto bancário registrado via WHMCS, integrando geração, consulta e baixa automática de boletos pela API Efí (antiga Gerencianet). Desenvolvido pela Gofas Software, é 100% gratuito.

Configuração simples, objetiva e focada em atender todos os modelos de negócio. Nos bastidores o módulo automatiza os principais processos, cuidando dos detalhes da automatização de recebimentos.

## Download

Baixe a versão mais recente (código completo do repositório):

https://github.com/gofas/gofasgerencianetboleto/archive/refs/heads/master.zip

## Funcionalidades

- **Boletos registrados** sem taxas de registro/baixa
- **Juros e multa** após o vencimento do boleto
- **2ª via automática** com valor atualizado (cálculo de multa e juros) ao acessar a fatura
- **Confirmação automática de pagamento** e baixa nas faturas via notificações (callback) ou tarefa cron
- **Boleto com sua marca**: logotipo, mensagem e instruções da sua empresa
- **Informações do boleto na fatura**: desconto, taxas, cálculos e outras informações
- **Linha digitável na fatura**, com opção de copiar em um clique
- **Informações do boleto nos emails**: opcionalmente o módulo gera o boleto quando a fatura é criada e fornece mergetags para os templates de email do WHMCS (linha digitável, link HTML e PDF, vencimento, ID do boleto e mais). O cliente paga sem precisar acessar o WHMCS
- **Link direto para o boleto** usando a tag padrão `{$invoice_link}` dos templates de email, sem redirecionamentos, autenticação ou edição de templates
- **Redirecionamento para o boleto** ao acessar a fatura (opcional), ideal em conjunto com o Módulo Auto Login para WHMCS. Também via parâmetro `redirectToBillet=true` (ou `false` para desativar) no URL da fatura
- **Anexo do boleto em PDF** ao email de fatura (opcional)
- **Dispensa configuração de campos CPF/CNPJ**: o módulo detecta automaticamente os campos personalizados de clientes
- **Notifica administradores** por email sobre erros ao gerar boletos, agilizando o diagnóstico antes do cliente acionar o suporte
- **Imagem personalizada** para o botão de pagamento
- **Desconto por método de pagamento**, fixo (R$) ou em porcentagem (%), para todos os boletos deste método
- **Desconto antes do vencimento**: define até quantos dias antes do vencimento o desconto se aplica
- **Descontos personalizados** por cliente, fixo (R$) ou em porcentagem (%), via campos personalizados
- **Tarifa adicional** configurável, fixa (R$) ou em porcentagem (%)
- **Cancelamento automático do boleto**: ao cancelar a fatura, ao gerar 2ª via de boleto vencido, ou manualmente pelo addon incluso no download
- **Aviso de atualização** e verificação de versão na própria tela de configuração do módulo
- **Configuração simples e intuitiva**: seleção de campos, departamentos de suporte e afins via menus suspensos (dropdown)
- **Suporte a produção e a testes (sandbox)** e a múltiplas contas
- **Logs de diagnóstico** configuráveis
- **Configurações personalizadas** sem editar o código original (atualizável sem perder customizações)
- E muito mais.

## Requisitos

- WHMCS >= 8.6
- PHP 7.1 a 8.3
- Conta Efí (Efí Pay) com API habilitada
- Credenciais: Client ID e Client Secret (produção e desenvolvimento)

O certificado `.p12` não é necessário para o boleto. Ele é exigido apenas pelo módulo Efí Pix.

## Instalação

1. Baixe o arquivo pelo botão de download (é o código completo do repositório) e descompacte. Será criada a pasta `gofasgerencianetboleto-master`.
2. Copie as pastas `includes` e `modules` de dentro de `gofasgerencianetboleto-master` para a raiz da instalação do WHMCS, mesclando com as pastas existentes. Os itens `.github`, `readme.md` e `changelog.md` podem ser ignorados.
3. Ative o módulo em `Opções > Pagamentos > Portais para Pagamentos > Aba All Payment Gateways`.
4. Informe o Client ID e o Client Secret.

## Configuração

### Pré configuração

1. Depois de ativar o módulo no WHMCS, faça login na sua conta Efí, acesse API > Minhas Aplicações > Nome da Aplicação para criar sua aplicação e gerar as credenciais Client ID e Client Secret.

<img src="https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/painel-efi-credenciais.png" alt="Onde encontrar Client ID e Client Secret no painel Efi" width="640">

2. No painel WHMCS, navegue até `Opções > Pagamentos > Portais para Pagamentos > All Payment Gateways` e clique em "Gofas Gerencianet - Boleto" para ativar o módulo.
3. Crie um campo personalizado de cliente para CPF e/ou CNPJ (um campo unificado ou dois campos distintos). O módulo identifica os campos do perfil do cliente automaticamente.

<img src="https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/campos-personalizados-clientes.png" alt="Configurar campos personalizados de clientes no WHMCS" width="640">

### Opções do módulo

<img src="https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/tela-configuracoes-modulo.png" alt="Tela de configuracoes do modulo" width="640">

- **Client ID Produção**: (obrigatório) Client ID da aba Produção da sua aplicação.
- **Client Secret Produção**: (obrigatório) Client Secret da aba Produção da sua aplicação.
- **Client ID Desenvolvimento**: (obrigatório) Client ID da aba Desenvolvimento da sua aplicação.
- **Client Secret Desenvolvimento**: (obrigatório) Client Secret da aba Desenvolvimento da sua aplicação.
- **Modo de Testes / Sandbox**: alterna entre os ambientes de Desenvolvimento e Produção.
- **Modo Diagnóstico / Debug**: exibe resultados e erros retornados pela API Efí e pela API interna do WHMCS. Use apenas em testes ou para diagnóstico.
- **Baixa via cron**: verifica o status dos boletos a cada execução do cron do WHMCS e dá baixa nas faturas pagas. Ativar essa opção desativa a baixa via notificação (callback).
- **Salvar Logs**: grava informações de diagnóstico em `Utilitários > Logs > Log de Módulo`. É necessário ativar o "Log de Debug" nesse menu.
- **Administrador do WHMCS**: (obrigatório) usuário ou ID do administrador com permissão de uso da API interna do WHMCS.
- **Valor da tarifa por Boleto**: (opcional) valor pago à Efí por boleto confirmado. Não é somado ao total; preenche o campo "Taxas" (fee) da transação. Use ponto para decimais.
- **Valor mínimo do Boleto**: (opcional) valor mínimo da fatura para permitir pagamento via boleto. Padrão R$5,00.
- **Informações do Boleto no email**: adiciona link, linha digitável, vencimento e outras informações ao corpo dos emails de fatura. Faz o boleto ser gerado no momento em que a fatura é criada.
- **Substituir link da fatura por link do boleto**: substitui a tag `{$invoice_link}` do template "Invoice Created" pelo link do boleto.
- **Cancelar Boleto ao cancelar Fatura**: (opcional) cancela o boleto associado quando a fatura é cancelada no WHMCS.
- **Cancelar Boleto Vencido**: (opcional) cancela o boleto anterior antes de gerar segunda via. Sem essa opção, o módulo altera a data de vencimento do boleto vencido ou gera um novo sem alterar o status do anterior.
- **Dias adicionais para nova data de vencimento**: dias somados ao vencimento ao gerar segunda via ou atualizar boleto vencido. Aplica-se apenas a faturas vencidas.
- **Notificar admins sobre erros**: departamento que recebe email quando ocorre erro ao gerar o boleto, permitindo ação antes do contato do cliente.
- **Tipo de Desconto Personalizado**: campo personalizado de clientes que define o tipo de desconto (R$ ou %).
- **Valor do Desconto Personalizado**: campo personalizado usado para descontos por cliente. Decimal com ponto, maior ou igual a 0.00 e menor que o valor da cobrança.
- **Desconto ou Taxa adicional**: oferece desconto ou acrescenta taxa para pagamentos via boleto.
- **Tipo de desconto/taxa**: porcentagem ou reais.
- **Valor do Desconto ou Taxa**: valor abatido ou acrescentado ao total.
- **Validade do desconto**: máximo de dias antes do vencimento para aplicar desconto. Em branco aplica mesmo após o vencimento; 0 aplica a boletos gerados até o vencimento; de 1 a X aplica entre 1 e X dias antes do vencimento.
- **Multa após o vencimento**: máximo 10%. Use ponto para decimais.
- **Juros após o vencimento**: juros por dia (mínimo 0.001, máximo 0.33). Use ponto para decimais.
- **Exibir linha digitável**: exibe a linha digitável abaixo do botão "visualizar boleto".
- **Exibir data de Vencimento**: exibe o vencimento na fatura.
- **Exibir Desconto / Taxa na fatura**: informa desconto ou taxa na fatura.
- **Redirecionar para o Boleto**: redireciona o cliente ao URL do boleto ao acessar a fatura. Adicione `&redirectToBillet=false` ao URL para desativar em acessos específicos.
- **Imagem do botão "Visualizar Boleto"**: URL da imagem do botão (recomendado 160x43px).
- **Mensagem ao cliente**: mensagem no boleto (máximo 80 caracteres).
- **1ª a 4ª Instrução do boleto**: linhas de instruções do boleto.
- **Anexar PDF do Boleto no email**: adiciona o boleto em PDF como anexo aos emails de fatura.
- **Opções customizadas**: opções personalizadas adicionadas com conhecimento básico de PHP, sem editar o código do módulo.

### Mergetags disponíveis para os emails

As tags disponíveis aparecem abaixo do editor dos templates de email do WHMCS:

<img src="https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/tags-mesclagem-emails.png" alt="Tags de mesclagem abaixo do editor de emails" width="640">

- `{$ggnb_billet_info}`: bloco HTML com todas as informações do boleto ([exemplo](https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/info-boleto-email-exemplo.png)).
- `{$ggnb_link}`: link do boleto.
- `{$ggnb_pdf}`: link do boleto em PDF.
- `{$ggnb_barcode}`: linha digitável (representação numérica do código de barras).
- `{$ggnb_expire_at}`: data de vencimento.
- `{$ggnb_total}`: valor total.
- `{$ggnb_charge_id}`: ID da transação na API Efí.
- `{$ggnb_api_mode}`: `sandbox` ou `live`.
- `{$ggnb_debug}`: informações de depuração no corpo do email.

## Informações importantes

- A tarifa do boleto é paga separadamente à Efí (R$2,37 para usuários do módulo, sujeito a alteração pela Efí).
- Sempre faça backup antes de mudar algo no seu sistema.
- Ao editar templates de email, use o editor HTML do WHMCS para evitar erros de formatação ao copiar e colar.

<img src="https://raw.githubusercontent.com/gofas/gofasgerencianetboleto/master/docs/img/editor-html-templates-email.png" alt="Onde encontrar o editor HTML dos templates de email" width="640">

- Configurações personalizadas e alterações de funcionamento podem ser feitas sem editar o código original, mantendo o módulo atualizável.

### Erros comuns

Além dos códigos de erro da API Efí, o módulo fornece mensagens específicas e legíveis para facilitar o diagnóstico. Erros que dependem do cliente (como CPF ou dados cadastrais inválidos) exibem mensagem com instruções e link direto para o cliente corrigir o cadastro, sem acionar o suporte.

- Tela branca ou erro 500: verifique logs do servidor e do WHMCS.
- "Erro de comunicação na 1ª conexão com a API": verifique Client ID e Client Secret (produção e desenvolvimento são diferentes).
- "Falha ao gerar a transação na 2ª conexão com a API": verifique os campos "Descrição" e "Valor total" da fatura.
- "Erro de comunicação na 2ª/3ª conexão com a API": ative o Debug do módulo para diagnóstico detalhado.
- "CPF/Telefone/Nome incorretos": dados cadastrais do cliente precisam ser corrigidos.
