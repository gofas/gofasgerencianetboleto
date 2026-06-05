# Gofas Efí Boleto (Gerencianet Boleto)

Módulo de gateway de pagamento para WHMCS que integra geração, consulta e baixa automática de boletos bancários via API Efí (antiga Gerencianet). Desenvolvido pela Gofas Software.

## Funcionalidades

- Geração de boletos via API Efí (EFI Pay)
- Verificação de pagamentos via cron job (desativada por padrão)
- Cancelamento e recriação automática de boletos vencidos
- Logs de operação configuráveis (desativados por padrão)
- Anexo de boleto em PDF ao email de fatura (opcional)
- Registro de transação R$0,00 ao emitir boleto (opcional, facilita identificação)
- Suporte a múltiplas contas Efí

## Requisitos

- WHMCS 7.x ou superior
- PHP 8.x
- Conta Efí (EFI Pay) com API habilitada
- Credenciais: Client ID, Client Secret e certificado `.p12`

## Instalação

1. Copiar a pasta `modules/gateways/` para o diretório `modules/gateways/` do WHMCS
2. Ativar o gateway em **Configurações > Formas de Pagamento**
3. Informar Client ID, Client Secret e fazer upload do certificado

## Changelog

Ver [changelog.md](changelog.md).

