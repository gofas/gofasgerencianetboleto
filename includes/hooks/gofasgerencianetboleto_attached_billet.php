<?php
use WHMCS\Database\Capsule;
use WHMCS\Mail\Message;
use WHMCS\Config\Setting;

function ggnb_downloadBoleto($url, $invoiceId){
    try {
        // Caminho base do WHMCS
        if(defined('ROOTDIR')){
            $downloadsDir = ROOTDIR . '/downloads';
        } else {
            $downloadsDir = __DIR__ . '/../../../downloads';
        }

        // Garante que a pasta existe
        if(!is_dir($downloadsDir)){
            throw new Exception("Diretório de downloads não encontrado: " . $downloadsDir);
        }

        // Nome do arquivo
        $fileName = 'boleto_' . (int)$invoiceId . '.pdf';
        $filePath = rtrim($downloadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        // Baixa conteúdo do boleto
        $pdfContent = file_get_contents($url);
        if($pdfContent === false || strlen($pdfContent) < 100){
            throw new Exception("Falha ao baixar boleto em: " . $url);
        }

        // Salva localmente
        if(file_put_contents($filePath, $pdfContent) === false){
            throw new Exception("Erro ao salvar boleto em: " . $filePath);
        }

        // Retorna no formato esperado
		$return = [
            'data' => base64_encode($pdfContent),
            'name' => $fileName,
            'path' => $filePath
        ];
		logModuleCall(
            'gofas-boleto',
            'downloadBoleto',
            ['url' => $url, 'invoiceid' => $invoiceId],
            '',
            $return
        );
        return $return;
    } catch (Exception $e){
        logModuleCall(
            'gofas-c6-boleto',
            'downloadBoleto',
            ['url' => $url, 'invoiceid' => $invoiceId],
            '',
            $e->getMessage()
        );
        return [];
    }
}


add_hook('EmailPreSend',1, function($vars){
	if(
		$vars['messagename'] === 'Invoice Created' ||
		$vars['messagename'] === 'Invoice Payment Reminder' ||
		$vars['messagename'] === 'First Invoice Overdue Notice' ||
		$vars['messagename'] === 'Second Invoice Overdue Notice' ||
		$vars['messagename'] === 'Third Invoice Overdue Notice'
	){
		$params = getGatewayVariables('gofasgerencianetboleto');
		$ggnb_merge_fields	= [];
		$invoice = localAPI('GetInvoice',['invoiceid'=>$vars['relid']], ggnb_setup_admin('id'));
		if($params['log']){
			logModuleCall('gofasgerencianetboleto','EmailPreSend-Start',['vars'=>$vars,'params'=>$params],'',['invoice'=>$invoice]);
		}
		if((float)$invoice['total'] > 0.00 && $invoice['paymentmethod'] === 'gofasgerencianetboleto'){
			// Busca boletos já salvos
			$billets_for_invoice = [];
			foreach(Capsule::table('gofasgerencianetboleto')->where('invoice_id','=',$vars['relid'])->orderBy('charge_id','desc')->get() as $key=>$value){
				$billets_for_invoice[$key] = json_decode(json_encode($value),true);
			}
			if(is_array($billets_for_invoice[0])){
				$billet_saved = $billets_for_invoice[0];
			}
			if($params['attached_billet']){
			// Anexar PDF se existir
				try{
					if(!empty($billet_saved['pdf'])){
						$pdf = ggnb_downloadBoleto($billet_saved['pdf'], $vars['relid']);

						//$pdf = file_get_contents($billet_saved['pdf'], FALSE, NULL, 0, 999999999);
						if($params['log']){
							logModuleCall('gofasgerencianetboleto','EmailPreSend-pdf',[$billet_saved['pdf'],$vars['relid']],['pdf'=>$pdf]);
						}
						if($pdf['data']){
							$message   = new Message();
							$message->addStringAttachment($pdf['name'],base64_decode($pdf['data'],true));
							$ggnb_merge_fields['attachments'] = [$pdf['name']];
							if($params['log']){
								logModuleCall('gofasgerencianetboleto','attachments-pdf',['pdf_data'=>base64_decode($pdf['data'],true),'pdf_name'=>$pdf['name'],'message'=>$message],'',['getAttachments'=>$message->getAttachments(),'boleto_url'=>$billet_saved['pdf']]);
							}
							
						}
					}
				}
				catch(Exception $e){
					if($params['log']){
						logModuleCall('gofasgerencianetboleto','EmailPreSend-Error-Attach',$vars,$e->getMessage());
					}
				}
			}
		}
		return $ggnb_merge_fields;
	}
	return;
});