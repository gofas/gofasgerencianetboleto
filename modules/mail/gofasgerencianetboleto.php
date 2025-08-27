<?php
namespace WHMCS\Module\Mail\GofasEfi;

use WHMCS\Mail\Message;
use WHMCS\Module\Contracts\SenderModuleInterface;
use WHMCS\Database\Capsule;

class gofasgerencianetboleto implements SenderModuleInterface
{
    public function getName() {
        return 'gofasgerencianetboleto';
    }

    public function getDisplayName() {
        return 'Gofas Efí Boleto – PDF em Anexo';
    }

    public function settings() {
        return []; // Se precisar de configurações, adicione aqui
    }

    public function testConnection(array $params) {
        return true; // testável, opcional
    }

    public function send(array $params, Message $message)
    {
        // Filtrar templates de e-mail alvo
        $alvos = [
            'Invoice Created',
            'Invoice Payment Reminder',
            'First Invoice Overdue Notice',
            'Second Invoice Overdue Notice',
            'Third Invoice Overdue Notice',
        ];
        if (in_array($message->getMessageName(), $alvos, true)) {
            $invoiceId = (int) $message->getRelId();

            // Busca URL do boleto na tabela gofasgerencianetboleto
            $entity = Capsule::table('gofasgerencianetboleto')
                        ->where('invoice_id', $invoiceId)
                        ->orderBy('charge_id', 'desc')
                        ->first(['pdf']);
            if ($entity && !empty($entity->pdf)) {
                $pdfUrl = $entity->pdf;
                $pdfData = $this->downloadPdf($pdfUrl);
                if ($pdfData !== null) {
                    // Salva em /downloads com CHMOD 777
                    $downloadsDir = ROOTDIR . '/downloads/';
                    if (!is_dir($downloadsDir)) {
                        mkdir($downloadsDir, 0755, true);
                    }
                    $filename = 'boleto_' . $invoiceId . '.pdf';
                    $filepath = $downloadsDir . $filename;
                    file_put_contents($filepath, $pdfData);
                    @chmod($filepath, 0777);

                    // Anexa via string (recomendado)
                    $message->addStringAttachment($filename, $pdfData);
                }
            }
        }

        // Aqui você deve enviar com $params e $message
        // Veja o exemplo oficial "Send Notification" na docs
        // Exemplo genérico (ajuste com seu provider real):
        $mailService = new \WHMCS\Mail\Mailer();
        return $mailService->send([
            'messagename' => $message->getMessageName(),
            'id' => $message->getRelId(),
            'mergefields' => [], // ou passe os merge fields
        ], $message);
    }

    private function downloadPdf(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Accept: application/pdf', 'User-Agent: WHMCS-Boleto/1.0'],
        ]);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 200 && stripos((string)$contentType, 'pdf') !== false && $data) {
            return $data;
        }
        return null;
    }
}
