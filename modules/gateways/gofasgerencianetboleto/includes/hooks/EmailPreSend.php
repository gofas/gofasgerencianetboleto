<?php
/**
 * Módulo Gerencinet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.2.1
 */
use WHMCS\Database\Capsule;
add_hook('EmailPreSend',1, function($vars){
	$params = getGatewayVariables('gofasgerencianetboleto');
    if(
		($vars['messagename'] === 'Invoice Created' ||
		$vars['messagename'] === 'Invoice Payment Reminder' ||
		$vars['messagename'] === 'First Invoice Overdue Notice' ||
		$vars['messagename'] === 'Second Invoice Overdue Notice' ||
		$vars['messagename'] === 'Third Invoice Overdue Notice') and ($params['billetonemail'])
	){
		$ggnb_merge_fields	= array();
		$invoice			= localAPI( 'GetInvoice', array('invoiceid' => $vars['relid']), (int)$params['admin']);
		
		//logModuleCall('gofasgerencianetboleto', 'EmailPreSend', 'invoice', $invoice);
		
		if( (float)$invoice['total'] > (float)'0.00' and $invoice['paymentmethod'] === 'gofasgerencianetboleto'){
			// Saved Billets
			$billet_saved = array();
			foreach( Capsule::table('gofasgerencianetboleto') -> where('invoice_id','=',$vars['relid'])->orderBy('charge_id','desc')->get(
				array( 'invoice_id', 'charge_id', 'link', 'pdf', 'expire_at', 'total', 'barcode', 'charge_id', 'status', 'api_mode') ) as $key => $value ){
				$billets_for_invoice[$key]					= json_decode(json_encode($value), true);
			}
			if(is_array($billets_for_invoice['0'])){
				$billet_saved = $billets_for_invoice['0']; // Array			
				// Merge Fields
				$ggnb_merge_fields['ggnb_link']			= $billet_saved['link'];
				$ggnb_merge_fields['ggnb_pdf']			= $billet_saved['pdf'];
				$ggnb_merge_fields['ggnb_barcode']		= $billet_saved['barcode'];
				$ggnb_merge_fields['ggnb_expire_at']	= date('d/m/Y', strtotime($billet_saved['expire_at']));
				$ggnb_merge_fields['ggnb_total']		= number_format($billet_saved['total']/100,  2, ',', '.');
				$ggnb_merge_fields['ggnb_charge_id']	= $billet_saved['charge_id'];
				$ggnb_merge_fields['ggnb_api_mode']		= $billet_saved['api_mode'];
				if($params['linkbilletonemail']){
					$ggnb_merge_fields['invoice_link']		= $billet_saved['link'];
				}
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br>------------------------------------------------------';
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br>Linha digitável do Boleto:<br><b>'.$billet_saved['barcode'];
				$ggnb_merge_fields['ggnb_billet_info']	.= '</b><br>Vencimento do Boleto: '.date('d/m/Y', strtotime($billet_saved['expire_at'])) ;
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br>Total do Boleto:  R$ '.number_format(($billet_saved['total']/100),  2, ',', '.') ;
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br>Código do Boleto: '.$billet_saved['charge_id'];
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br><b><a href="'.$billet_saved['link'].'">Visualizar Boleto em HTML</a></b>';
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br><b><a href="'.$billet_saved['pdf'].'">Visualizar Boleto em PDF</a></b>';
				$ggnb_merge_fields['ggnb_billet_info']	.= '<br>------------------------------------------------------';
				$ggnb_merge_fields['ggnb_debug'] .= "Debug:\n".(string)json_encode($vars).json_encode($invoice);
				logModuleCall('gofasgerencianetboleto', 'EmailPreSend', array('invoice'=>$invoice,'billet_saved'=>$billet_saved),'',array('ggnb_merge_fields'=>$ggnb_merge_fields));
				return $ggnb_merge_fields;
			}
			return;
		}
		return;
	}
	return;
});