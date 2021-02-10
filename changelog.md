# v3.3.0
> 08/02/2021
1. Agora você pode escolher duas formas de licenciamento: R$9,90/mês ou R$0,25/boleto recebido. A comissão de R$0,25 / boleto só é adicionada quando não houver uma licença ativa nas configurações do módulo. O módulo não para de funcionar sem a licença;
1. Simplificação dos logs e debug, menos processamento de dados e maior desempenho em grande escala;
1. User-Agent no header da requisição ao verificar atualizações no gofas.net;

## v3.2.1
> 24/01/2021
1. Melhoria: Garante a criação de boletos e inclusão dos dados nos emails das faturas recém criadas ao rodar o cron;
1. Melhoria: Corrige formatação de itens de linha dos Boletos gerados por faturas mescladas, de acordo com as exigências da API Gerencianet;
1. Melhoria: Corrige formatação de itens de linha dos Boletos gerados por faturas referentes a upgrades;

## v3.2.0
> 22/10/2020
1. Nova opção: 5 - Administrador do WHMCS - Define o administrador com permissões para utilizar a API interna do WHMCS. Evita erros ocasionados por alterações introduzidas no WHMCS v8.+.;
1. Dispensa a necessidade de instalar o módulo addon Gerencianet para rodar as funções:
    1. Informações do Boleto no email;
    1. Substituir link da fatura por link do boleto;
    1. Cancelar Boleto ao cancelar Fatura;
1. Descontinuado e removido do pacote de download o módulo addon Gerencianet;

## v3.1.0
> 08/10/2020
1. Simplificação do método de adição de código customizado;
1. Melhorias para garantir compatibilidade completa com WHMCS 8.0+ e o com o novo Auto Login para WHMCS v3.0+;

## v3.0.3
1. Corrigida a exibição de informações de diagnóstico na fatura com debug desativado (report 12954);

## v3.0.2
1. Melhoria na verificação de atualizações;

## v3.0.1
1. Agora descontos são exibidos no Boleto como itens de linha, tornando a aparência do boleto semelhante às faturas;
1. Corrigido o erro 3500073 ao aplicar desconto aos boletos.

## v3.0.0
1. Eliminada a dependência da SDK Gerencianet para PHP, evitando erros comuns relacionados à incompatibilidade da biblioteca GuzzleHttp com versões específicas do PHP e WHMCS;
1. Redução de 1,4MB para 426KB no tamanho do módulo;
1. Módulo Addon agora cancela transações também do módulo Gerencianet Cartão para WHMCS;
1. Corrigido bug que retornava em algumas situações, devido a conflito nas configurações, o erro "A data do vencimento deve ser maior que a data atual";
1. Corrigido bug que podia impedir o módulo de obter sempre o boleto mais recente gerado por cada fatura, ao anexar informações do boleto aos emails.
1. Corrigido bug que podia causar erro 404 em URL das imagens no admin antes de salvar as configurações;

## v2.2.3
1. Substituição de versão da SDK Gerencianet para corrigir erros relacionados à biblioteca guzzlehttp;

## v2.2.2
1. Configuração "Administrador do WHMCS" não é mais requerida para versões do WHMCS maiores que 7.2;
1. Correção na numeração das opções presentes nas configurações do módulo;
1. Atualizada a SDK Gerencianet para versão 2.4.0;

## V2.2.1
1. Informações do boleto no email. Agora em conjunto com o addon Gerencianet que acompanha o pacote de download, opcionalmente, o módulo gera o boleto quando a fatura é gerada e fornece mergetags para os modelos de email de faturas que, exibem diretamente no corpo do email enviado ao cliente, a linha digitável, link para versão em HTML e PDF, data de vencimento e ID do boleto;
1. Link direto para o Boleto utilizando a tag padrão {$invoice_link} dos templates de email. Essa opção substitui automaticamente os links das faturas nos emails, pelo link direto para o Boleto, sem necessidade de redirecionamentos ou autenticação;
1. Aviso de atualização e verificação de versão diretamente nas configurações do módulo;
1. Campos de configurações customizadas (avançado). Agora é possível (com conhecimento básico em php), incluir , funções e alterar o funcionamento do gateway, sem editar diretamente o código original, apenas incluindo as customizações em pastas específicas do módulo. Dessa forma é possível atualizar o módulo no futuro sem perder as alterações. Como demonstração do funcionamento das opções personalizadas, oferecemos duas customizações cedidas generosamente pela Go Suite e mais uma customização criada por nós para contornar a situação descrita neste tópico do fórum. Vaja as customizações e saiba mais sobre como incluir configurações adicionais e manipular as variáveis do módulo neste artigo;
1. Debug no log de módulo do WHMCS. Agora o módulo além de exibir informações de diagnóstico na tela das faturas, também oferece a opção de salvar o debug no log de módulo do WHMCS;
1. Cancelar o Boleto anterior ao gerar um novo. Diferente da opção já existente que cancelava o boleto apenas quando já havia vencido;
1. Cancela o Boleto ao cancelar Fatura. Dispensa a necessidade de acessar o painel de controle Gerencianet para cancelar um boleto, basta cancelar a fatura no WHMCS e o boleto associado à ela será cancelado também;
1. Melhoria nos cálculos de taxas, descontos, multa, juros, crédito e demais valores variáveis que devem ser calculados nas mais variadas hipóteses;
1. Exibe mais informações na Fatura, sobre descontos, multa , juros, taxas, etc;
1. Taxas exibidas como itens de linha no boleto;
1. Descrição do serviço agora são exibidas nas linhas/itens do Boleto quando há taxas adicionais configuradas (antes era exibido Fatura ID #XX)
1. Gera novo boleto ao aplicar crédito à fatura, (e cancela o anterior se a opção descrita no item 7 estiver ativada) ou quando o valor da fatura mudou e já existe um boleto associado à essa fatura;
1. Opções numeradas nas configurações, para localização mais rápida;
1. Mais opções de redirecionamento para o Boleto: Ao adicionar o parâmetro redirectToBillet=true no URL da fatura, o cliente será redirecionamento para o boleto ao acessar a fatura e ao adicionar o parâmetro redirectToBillet=false no URL da fatura (se a opção de redirecionamento estiver ativada nas configurações do módulo), o redirecionamento da fatura para o boleto será desativado apenas para essa fatura. Muito útil para aditar os templates de email, adicionando regras de redirecionamento para o boleto específicas para cada situação Exemplo: substitua a tag {$invoice_link} por {$invoice_link}&redirectToBillet=false nos templates de email e será formado um URL semelhante a este: https://whmcs.gofas.net/viewinvoice.php?id=138&redirectToBillet=false
1. Copiar a linha digitável do boleto com um clique;
1. Copiar debug com um clique;
1. Obtém CPF e CNPJ sem necessidade de configuração, agora o módulo detecta automaticamente os campos personalizados de cliente referentes ao CPF e CNPJ;
1. Selecionar os campos de perfil do cliente em um menu suspenso, quando requeridos nas configurações do módulo, ao invés de definir o nº de ordem , ID ou qualquer informação confusa ou complexa de obter como no passado, agora é exibido um menu com todos os campos personalizados existentes no WHMCS, onde o só admin só precisa selecionar o custom field;
1. Selecionar o departamento de suporte com um clique na opção “Notificar suporte do WHMCS sobre erros” das configurações do módulo. Semelhante a opção anterior, agora o módulo obtém uma lista dos departamentos de suporte configurados para o administrador selecionar;
1. Removida a opção CPF + CNPJ para Pessoa Jurídica por não ser mais obrigatória ao usar a API Gerencianet;
1. Corrigido o link que aponta para a área do cliente em caso de erros, quando o admin do WHMCS não definiu nas configurações gerais do WHMCS o URL da instalação incluindo o diretório da instalação;
1. ## Módulo Addon Gerencianet atualizado,
1. Agora ele é compatível com as versões mais recentes do gateway e do WHMCS
1. Traz os hooks necessários para ações em segundo plano (como gerar o boleto ao gerar a fatura)
1. Corrigido o erro que estava atrapalhando o cancelamento dos Boletos a partir do ID da fatura
1. Corrigido o erro que subtraia R$0,01 nos cálculos de juros + multa + taxa – desconto/crédito, em versões específicas do php e php-fpm;
1. Melhoria no callback de boletos com desconto, agora o módulo edita a fatura adicionando um item de linha com o mesmo valor do desconto evitando que a fatura fique em aberto.
## v1.2.3
1. Configurações adicionais para corrigir o URL do sistema/WHMCS, nas mensagens de erro, quando a instalação do WHMCS está numa subpasta e o admin configurou o URL do sistemas nas configurações, sem essa subpasta.
## v1.2.2
1. Atualização da SDK Gerencianet;

## v1.2.1
1. Optimização e limpeza do código, reduzindo parâmetros que não são mais necessários e melhorando o desempenho de diversas funções;
1. Corrigido o bug que poderia causar uma diferença de R$0,01 (um centavo) na confirmação de pagamento;
1. Agora quando o valor do pagamento realizado é maior que o valor total da fatura( devido à adição de taxas, multa e/ou juros ao boleto, por exemplo), antes de dar baixa no pagamento, ao processar o callback, o módulo edita a fatura adicionando um novo item com a descrição “Acréscimo” e o valor de diferença entre o total da fatura e o valor pago. Dessa forma os valores das transações passam a ser o mesmo valor do pagamento.
## v1.1.0
1. Agora nomes de itens de serviços/produtos da Fatura é limitado à 255 caracteres, caracteres adicionais são removidos para evitar o erro "A string é muito longa, máximo 255 caracteres: /items/0/name";
1. Correção do link da imagem de exemplo nas configurações do módulo, referente aos campos personalizados de desconto;
1. Outras melhorias nos textos explicativos das configurações do módulo;
1. Atualização da SDK Gerencianet da versão 1.0.11 para a versão 2.0.0. A partir de agora o módulo obrigatoriamente requer php versão 5.5 ou maior;

## v1.0.3
1. Correção: Corrigido o bug que ignorava os centavos do desconto personalizado inserido na versão 1.0.2.
## v1.0.2
1. Nova funcionalidade: Valor do desconto personalizado. Agora você pode criar um custom_field visível apenas para admins do WHMCS e determinar o valor do desconto personalizado para clientes específicos. Para ativar essa opção e aplicar o desconto ao Boleto, basta preencher esse campo com a “Ordem de Exibição” do campo personalizado de cliente “Valor do desconto”;
1. Nova funcionalidade: Tipo de desconto personalizado. Assim como o valor de desconto personalizado citado acima, agora você pode criar um campo personalizado para determinar o tipo de desconto que algum cliente específico vai receber no Boleto. Para ativar essa opção, você precisa criar um custom_field do tipo “Lista de opções” com as duas opções de tipo de desconto, Reais (R$) ou Porcentagem (%), depois disso, basta preencher esse campo nas configurações do módulo com a  “Ordem de Exibição” do campo personalizado de cliente “Tipo de desconto”. Use os sinais $ e % nos nomes das opções dos custom_fields;
1. Nova funcionalidade: Validade do desconto. Agora o módulo possui um campo de texto nas configurações onde é possível determinar o número de dias antes do vencimento, que o desconto deve ser aplicado, ex.: “Oferecer desconto para pagamento até 5 dias antes do vencimento”;
1. Prevenção: Reformulação completa da tela de configurações do módulo, com ainda mais explicações, links para mais informações e tutoriais. Configurações opcionais e obrigatórias agora são diferenciadas pelas cores verde e vermelho para facilitar o entendimento e as configurações foram divididas em sessões;
1. Diagnóstico: Debug agora inclui mais informações úteis para a identificação de erros de integração;
1. Melhoria: Foram acrescentadas verificações que previnem possíveis erros ao aplicar desconto aos Boletos, descobertos em instalações que utilizam configurações de desconto juntamente com configurações de multa e juros;
1. Melhoria: Agora o link para visualizar o Boleto abre em uma nova guia do navegador, mantendo a página da Fatura aberta lado a lado com a visualização do Boleto;
1. Melhoria: Agora o módulo é capaz de identificar se as transações associadas à Fatura foram geradas em modo Live ou Sandbox, transações geradas por outros módulos são ignoradas. Essa opção previne um dos erros mais comuns da API Gerencianet, o erro “Tentativa de obter detalhes da transação, mas informou um charge_id incorreto“, relacionado diretamente a forma errada de manejar as Faturas associadas aos Boletos;
1. Melhoria: Agora o módulo verifica o status da transação antes de consumir a API Gerencianet, evitando o erro “Apenas transações com status ## new], ## link], ## waiting] ou ## unpaid] podem ser canceladas” retornado quando o usuário do módulo esquece de excluir as transações associadas à Fatura, antes de gerar um novo Boleto ou ao alternar do modo Desenvolvimento (sandbox) para produção (live);
1. Melhoria: O código está mais eficiente, organizado e preparado para novas atualizações.
## 0.2.7
1. Callback: Verifica se um admin foi definido nas configurações do módulo, caso contrário, atribui o usuário ID 1 a operação.
## 0.2.6
1. Melhorias na verificação da origem da requisição ao script do módulo,
para impedir que as funções do módulo sejam executadas ao gerar faturas
e/ou emails em massa, evitando assim a interrupção de processos em
massa ao invocar o módulo.
## 0.2.5
1. Agora cupons de desconto (itens com valor negativo na fatura) são adicionados ao campo "desconto" padrão da API Gerencianet, somados ao desconto configurado no módulo (se configurado). Essa atualização corrige o erro "O valor -XXX é menor que o mínimo 0" retornado pela API Gerencianet.
## 0.2.4
1. Corrige erro que em alguns casos pode gerar mais de um Boleto para cada fatura;
1. Adiciona novas linhas ao debug para diagnóstico específico dos campos CPF e CNPJ;

## 0.2.3
1. Nova opção: Valor da tarifa por Boleto. Agora é possível configurar o valor relativo à comissão paga à Gerencianet a cada Boleto com pagamento confirmado. Essa informação servirá para calcular e preencher o campo "Taxas" (fees) da lista de transações, fazendo o cálculo do valor líquido recebido via Boletos;
1. Nova opção: Mensagem ao cliente. Agora é possível configurar uma mensagem personalizada com até 80 caracteres, que será exibida entre o cabeçalho e o descritivo do Boleto com o título "Observação". O intuito dessa nova funcionalidade é que não seja necessário utilizar as linhas de instrução para mensagens direcionadas ao cliente, afinal, as linhas de instrução são mensagens destinadas ao Caixa e não devem ser utilizadas para outro propósito;
1. Nova opção: Multa após o vencimento. Agora é possível configurar multa em percentagem (%), que será adicionada ao valor total do Boleto quando o mesmo for gerado após o vencimento e também adiciona instruções ao Caixa informando a multa;
1. Nova opção: Juros por dia após o vencimento. Agora é possível configurar juros por dia em percentagem (%), que será adicionado ao valor total do Boleto quando o mesmo for gerado após o vencimento e também adiciona instruções ao Caixa informando sobre os juros;
1. Melhoria: Agora a opção "Dias adicionais à data de vencimento do Boleto" permite gerar o Boleto com vencimento para a mesma data que foi gerado, ao configurar essa opção com 0 (zero).
1. Melhoria: Textos explicativos das configurações foram reformulados e as configurações mais sensíveis contém links com mais informações;
1. Melhoria: Modo debug agora contém mais explicações, links para artigos com mais informações e destaca as mensagens de erro em letras vermelhas para localização mais rápida do trecho onde o script foi interrompido;
1. Melhoria: Otimização e revisão completa do código, agora trechos do script que eram repetidos em situações diferentes foram convertidos em funções, que podem ser executadas separadamente e em casos diferentes apenas alterando os argumentos;
1. Melhoria: Callback agora inclui um debug completo do recebimento da notificação, verificação dos dados do Boleto e da Fatura (GN API e WHMCS API). O resultado do recebimento de notificações pode ser visualizado na sua conta Gerencianet > API > Aplicação > Produção ou Desenvolvimento > Histórico de Notificações;
1. Corrigido o bug que adicionava o valor da taxa do Boleto com crédito ao cliente, após a confirmação do pagamento;
1. Corrigido o bug que adicionava/abatia o valor da taxa ou desconto fixo (R$) de cada item da fatura, especificamente quando o admin configurava taxa ou desconto fixo e o sistema gerava faturas com mais de um item;

## 0.2.2
1. Evita falhas ao gerar a data de vencimento do Boleto quando o admin não configura ou configura com 0(zero) a opção "Dias adicionais à data de vencimento do Boleto";
1. Alerta nas configurações que as opções "Debug" e "Redirecionar para o Boleto" não podem ser usadas simultaneamente;
1. Melhores mensagens de erro atualizadas na API Gerencianet;

## 0.2.0
1. Agora os Boletos são gerados apenas quando o cliente acessa a Fatura;
1. Agora quando uma fatura é acessada após o vencimento, o Boleto gerado anteriormente tem a data de vencimento atualizada, ao invés de gerar um novo boleto;
1. Nova opção: Cancelar o boleto vencido e gerar um novo. Ao marcar essa opção, quando o cliente acessar uma Fatura vencida o módulo vai cancelar o Boleto associado à ela e gerar um novo;
1. Adicionada a capacidade de reportar todos os erros e avisos do php, independente da configuração do servidor;
1. Melhorias de segurança;
1. Melhorias de compatibilidade com PHP < 5.6;
1. Melhorias no callback, como a adição de um debugque grava as respostas do servidor nos logs das transações na API Gerencianet;

## 0.1.9
1. Corrige erro "Fatal error: Class 'Gerencianet\Gerencianet' not found " quando o boleto é gerado no fluxo de contratação do WHMCS;

## 0.1.8
1. Nova opção: Valor mínimo para aceitar pagamentos via boleto;
1. Nova opção: Dias adicionais à data de vencimento do Boleto. Se aplica a faturas vencidas, faturas que não venceram geram boletos com a mesma data de vencimento da fatura;
1. Nova opção: Desconto ou Taxa adicional, em R$ (reais) ou % (porcentagem);
1. Nova opção: Exibir Desconto ou Taxa na fatura;
1. Nova opção: Novos campos para adicionar as credenciais Client_Id e Client_Secret de Desenvolvimento. Agora para alternar rapidamente entre Desenvolvimento/Produção basta assinalar/desassinalar a opção "Sandbox";
1. Melhoria: Exibir Data de vencimento e Linha digitável agora são duas opções distintas e podem ser configuradas separadamente;
1. Nova opção: Agora é possível configurar apenas um campo personalizado para CPF ou CNPJ, o módulo reconhece o tipo de documento e gera a cobrança como pessoa física ou jurídica de acordo com o documento informado. Ainda é possível utilizar dois campos, um campo para cada documento;
1. Melhoria: Ao gerar uma cobrança como Pessoa Jurídica (CNPJ), se o cliente não preencheu o campo "Empresa", o módulo preenche esse campo automaticamente com o Nome e Sobrenome do cliente, evitando erros de validação da API Gerencianet;

## 0.1.7
1. Agora não é mais obrigatório enviar o nome e CPF do cliente quando a cobrança é para Pessoa Jurídica, por padrão, o módulo vai ignorar o CPF se eles estiver incorreto ou ausente;
1. Adicionada a opção "Exigir CPF e CNPJ de Pessoa Jurídica", no caso do admin desejar tornar obrigatório o envio também do nome e CPF da pessoa física que está concretizando o pagamento em nome da empresa, nesse caso será exibido um erro se o CPF ou o CNPJ estiverem incorretos;
1. Agora se ocorrer um erro ao gerar Boleto devido ao CNPJ do cliente estar incorreto, o sistema vai tentar gerar o boleto com o CPF antes de exibir um erro;
1. Adicionada a opção "Mostrar erro "CNPJ incorreto", no caso do admin desejar exibir um erro quando a cobrança não é realizada devido a CNPJ inválido, do contrário, o sistema tenta associar a cobrança ao CPF do cliente no caso de CNPJ incorreto ou ausente, como descrito na feature anterior;
1. Agora o módulo adiciona um 0 (zero) no começo do CPF se esse tem 10 dígitos, como forma de impedir o erro "CPF Incorreto". O motivo dessa feature é por que muitas vezes que o CPF do cliente inicia com dígito zero, ele acredita que não precisa preencher esse dígito;
1. Agora o módulo adiciona um 0 (zero) no começo do CNPJ se esse tem 13 dígitos, como forma de impedir o erro "CNPJ Incorreto" ou associar o Boleto ao CPF do cliente. O motivo dessa feature é o mesmo da anterior;

## 0.1.6
Quando ativo o Debug é exibido apenas na visualização da fatura e não no admin;
1. Quando ativo o Redirecionamento da fatura para o boleto só ocorre na visualização da fatura e não no admin.

## 0.1.5
Adicionadas as opções "Ordem do campo CPF" e "Ordem do campo CNPJ" às configurações do módulo, que permitem apontar a ordem de exibição dos campos personalizados;
1. Melhorias nos textos, adição de links e nova ordem das opções do módulo, tornando-o ainda mais intuitivo;

## 0.1.4
Adicionada a opção de redirecionar o link da fatura diretamente para o URL do boleto;
1. Melhoria na opção de envio de email em caso de erro, agora é possível escolher o departamento de suporte que será notificado.

## 0.1.3
Agora o cliente associado à transação pode ser uma Pessoa Jurídica. Nesse caso, além do CPF, devem ser informados a Razão Social e o CNPJ da empresa. Se os campos Empresa e CNPJ (segundo campo personalizado) estiverem preenchidos no perfil do cliente o boleto será gerado com as informações de pessoa jurídica, se algum desses dois capos estiver em branco, o boleto será gerado com as informações de pessoa física.

## 0.1.2
Adicionada a opção de notificar o admin por email em caso de falha ao gerar o Boleto;
1. Removido o comando gettransactions que consultava transações geradas pela fatura atual, a consulta por transações agora é realizada analisando o resultado do comando getinvoice.

## 0.1.1
Redução de variáveis desnecessárias tornando a validação de dados mais rápida e eficaz;
1. Debug mais completo: adicionadas novas informações de resultados às consultas às APIs;

## 0.1.0
Lançamento 🎉