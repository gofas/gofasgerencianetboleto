<?php
/*
use WHMCS\Mail\Message;
use WHMCS\Database\Capsule;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;


add_hook('EmailPreSend', 1, function($vars) {

    // Lista de templates de e-mail de fatura onde a lógica será aplicada
    $invoice_emails = [
        'Invoice Created',
        'Invoice Payment Reminder',
        'First Invoice Overdue Notice',
        'Second Invoice Overdue Notice',
        'Third Invoice Overdue Notice'
    ];

    // Verifica se o e-mail é um dos templates da lista
    if (in_array($vars['messagename'], $invoice_emails)) {

        $invoiceId = $vars['relid'];
        $invoice = localAPI('GetInvoice', ['invoiceid' => $invoiceId]);

        // Prossegue apenas se a fatura for do gateway 'gofasgerencianetboleto'
        if ($invoice['paymentmethod'] === 'gofasgerencianetboleto') {
            
            try {
                
                // Busca o boleto mais recente associado a esta fatura
                $billet_saved = Capsule::table('gofasgerencianetboleto')
                    ->where('invoice_id', '=', $invoiceId)
                    ->orderBy('charge_id', 'desc')
                    ->first();

                // Se um boleto com PDF válido for encontrado
                if ($billet_saved && !empty($billet_saved->pdf)) {
                    $pdfUrl = $billet_saved->pdf;
                    $pdfContent = @file_get_contents($pdfUrl);

                    if ($pdfContent !== false) {
                        
                        // Inicia uma nova instância do PHPMailer, pois a do WHMCS não está acessível
                        $newMailer = new PHPMailer(true);
                        $message = new Message();
                        
                        try {
                            // 1. CONFIGURAR O TIPO DE ENVIO (SMTP ou mail)
                            $mailType = Capsule::table('tblconfiguration')->where('setting', 'MailType')->value('value');
                            
                            if ($mailType === 'smtp') {
                                $smtpConfig = Capsule::table('tblconfiguration')
                                    ->whereIn('setting', ['SMTPHost', 'SMTPPort', 'SMTPUsername', 'SMTPPassword', 'SMTPSSL'])
                                    ->get()
                                    ->pluck('value', 'setting');
                                
                                $newMailer->isSMTP();
                                $newMailer->Host = $smtpConfig['SMTPHost'];
                                $newMailer->Port = $smtpConfig['SMTPPort'];
                                $newMailer->SMTPAuth = true;
                                $newMailer->Username = $smtpConfig['SMTPUsername'];
                                $newMailer->Password = decrypt($smtpConfig['SMTPPassword']);
                                $newMailer->SMTPSecure = $smtpConfig['SMTPSSL'] === 'none' ? '' : $smtpConfig['SMTPSSL'];
                            }

                            // 2. REPLICAR OS DADOS DO E-MAIL USANDO AS VARIÁVEIS DO HOOK
                            $newMailer->CharSet = 'UTF-8';
                            
                            // Remetente (extraído dos $vars)
                            $newMailer->setFrom($message->getFromEmail(),$message->getFromName());

                            // Destinatário principal (extraído dos $vars)
                            $newMailer->addAddress($vars['mergefields']['client_email'], $vars['mergefields']['client_email']);
                            
                            // Destinatários em cópia (CC e BCC), se existirem
                            if (!empty($vars['cc']) && is_array($vars['cc'])) {
                                foreach ($vars['cc'] as $ccAddress) {
                                    $newMailer->addCC($ccAddress[0], $ccAddress[1] ?? '');
                                }
                            }
                            if (!empty($vars['bcc']) && is_array($vars['bcc'])) {
                                foreach ($vars['bcc'] as $bccAddress) {
                                    $newMailer->addBCC($bccAddress[0], $bccAddress[1] ?? '');
                                }
                            }
                            
                            // Assunto e Corpo (extraídos dos $vars)
                            $newMailer->Subject = $vars['subject'];
                            $newMailer->isHTML(true);
                            $newMailer->Body = $message->getBody(); // $vars['message'] aqui contém o corpo HTML
                            $newMailer->AltBody = $message->getPlainText(); // Corpo em texto simples

                            // 3. ADICIONAR O ANEXO DO BOLETO
                            $filename = 'Boleto-Fatura-' . $vars['relid'] . '.pdf';
                            $newMailer->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');

                            // 4. ENVIAR O NOVO E-MAIL
                            $newMailer->send();

                            // 5. ABORTAR O ENVIO DO E-MAIL ORIGINAL DO WHMCS
                            $vars['abortsend'] = true;
                            
                            logModuleCall('gofasgerencianetboleto', 'Email_Replaced_And_Sent', 'E-mail original abortado. Novo e-mail com anexo enviado com sucesso.', ['invoice_id' => $invoiceId,'getAttachments'=>$message->getAttachments()]);

                        } catch (PHPMailerException $e) {
                            logModuleCall('gofasgerencianetboleto', 'Email_Replace_Failed', 'Falha ao enviar e-mail replicado. O WHMCS tentará enviar a versão original.', ['invoice_id' => $invoiceId, 'error' => $e->errorMessage(),'messageBody'=>$message->getBody(),'getAttachments'=>$message->getAttachments()]);
                        }
                    }
                }
            } catch (\Exception $e) {
                logModuleCall('gofasgerencianetboleto', 'Email_Replace_Hook_Exception', $e->getMessage(), $e->getTraceAsString());
            }
        }
    }
    // Retorna a variável $vars. Se 'abortsend' for true, o WHMCS cancelará o envio.
    return $vars;
});
*/