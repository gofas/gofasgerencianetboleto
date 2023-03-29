<?php
/**
 * Módulo Gerencianet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.8.0
 */
use WHMCS\Database\Capsule;
add_hook("DailyCronJob",1,"ggnb_check_status_updates");
if(!function_exists('ggnb_check_status_updates')){
	function ggnb_check_status_updates($vars){
		require_once __DIR__.'/../../modules/gateways/gofasgerencianetboleto/includes/functions.php';
		$params = getGatewayVariables('gofasgerencianetboleto');
		if(!$params['croncallback']){
			return;
		}
		if( $params['sandbox'] ){
			$sandbox		= true;
			$client_id		= $params['clientidsandbox'];
			$client_secret	= $params['clientsecretsandbox'];
			$api_mode		= 'sandbox';
			$api_url		= 'https://sandbox.gerencianet.com.br/v1/';
		}
		elseif( !$params['sandbox'] ){
			$sandbox		= false;
			$client_id		= $params['clientid'];
			$client_secret	= $params['clientsecret'];
			$api_mode		= 'live';
			$api_url		= 'https://api.gerencianet.com.br/v1/';
		}
		//$params_api = ggnb_api_connect();
		// Get Billets
		try {
			// Add Payment to Invoices
			$log = array();
			$boleto = array();
			$invoices = array();
			// Unpaid invoices IDs
			foreach( Capsule::table('tblinvoices') -> where( 'status','=','Unpaid' )->where('paymentmethod','=','gofasgerencianetboleto')->get() as $tblinvoices){
				foreach( Capsule::table('gofasgerencianetboleto')->where('invoice_id','=',$tblinvoices->id)->where('api_mode','=',$api_mode)->get(['charge_id']) as $local_boleto ){
					$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
					if($access_token_['access_token']){
						$access_token = $access_token_['access_token'];
					}
					if($access_token_['error']){
						$error = $access_token_['error'];
					}
					$boleto			= ggnb_detail_charge($api_url,$access_token,$local_boleto->charge_id);
					$boletos[$local_boleto->charge_id]=$boleto;
					
					if((int)$boleto['result']['code'] !== (int)200){
						$error	.= 'Erro ao verificar Boleto: ' . json_encode($boleto);
					}
					if($boleto['result']['data']['status'] === 'paid') {
						$invoices[$tblinvoices->id] = [
							'invoice_id'=>$tblinvoices->id,
							'trans_id'=>$local_boleto->charge_id,
							'transaction_id'=>$local_boleto->charge_id,
							'total'=>$tblinvoices->total,
							'user_id'=>$tblinvoices->userid,
							'paid_amount'=>(float)number_format(($boleto['result']['data']['total']/100), 2,'.',''),
							'fee'=>$params['fee'],
						];
					}
					if($boleto['result']['data']['status'] === 'unpaid' || $boleto['result']['data']['status'] === 'canceled') {
						$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('invoice_id', '=',$tblinvoices->id)->delete();
					}
				} // End Foreach
			} // End Foreach
			// Add Payments
			if (!empty($invoices)) {
				foreach ($invoices as $key => $value) {
					$log['invoice_value'][$value['invoice_id']] = $value;
					$log['invoice_id'][$value['invoice_id']] = $value['invoice_id'];
					if ( (float)$value['paid_amount'] > (float)$value['total'] ) {
						$update_invoice = localAPI('updateinvoice', array( 'invoiceid' => $value['invoice_id'], 'newitemdescription' => array('Acréscimos calculados na emissão do Boleto'),'newitemamount' => array((float)($value['paid_amount'] - $value['total']))), (int)ggnb_setup_admin()['id'] );
					}
					// - Billet amount is less than the invoice amount
					if ( (float)$value['paid_amount'] < (float)$value['total'] ) {
						$update_invoice = localAPI('updateinvoice', array( 'invoiceid' => $value['invoice_id'], 'newitemdescription' => array('Descontos calculados na emissão do Boleto'),'newitemamount' => array((float)($value['paid_amount'] - $value['total']))), (int)ggnb_setup_admin()['id'] );
					}
					$add_trans = localAPI( 'addtransaction' ,
						[
							'userid'=>$value['user_id'],
							'invoiceid'=>$value['invoice_id'],
							'description'=>'Boleto pago - confirmação via cron job',
							'amountin'=>$value['paid_amount'],
							'fees'=>$value['fee'],
							'paymentmethod'=>'gofasgerencianetboleto',
							'transid'=>'ggnb-'.$api_mode.'-'.$value['trans_id'],
						],
						(int)ggnb_setup_admin()['id']
					);
					$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('invoice_id', '=',$value['invoice_id'])->delete();
					$ggnb_update_stats = ggnb_update_stats();
					$update_invoice_log[$value['invoice_id']]=$update_invoice;
					$add_trans_log[$value['invoice_id']]=$add_trans;
				}
			}
		}
		catch (Exception $e) {
			$error	.= 'Erro ao listar boletos pagos: ' . $e->getMessage();
			$log['error'] = $error;
		}
		$log['boletos'] = $boletos;
		$log['invoices'] = $invoices;
		$log['update_invoice'] = $update_invoice;
		$log['add_trans'] = $add_trans;
		if($params['log']){
			logModuleCall('gofasgerencianetboleto','AfterCronJob',array('module_version'=>'3.7.0','params'=>$params),'', array($log) );
			//echo '<pre>',print_r($boletos),'</pre>';
		}
		return;
	}
}
/////
/////
/////
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
		$invoice			= localAPI( 'GetInvoice', array('invoiceid' => $vars['relid']), (int)(int)ggnb_setup_admin()['id']);
		
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
//Output additional merge fields in the list when editing an email template
add_hook('EmailTplMergeFields', 1, function($vars){
    $ggnb_merge_fields = array();
	$ggnb_merge_fields['ggnb_billet_info']	= 'Gerencianet: Informações do boleto';
    $ggnb_merge_fields['ggnb_link']			= 'Gerencianet: Link para o boleto';
	$ggnb_merge_fields['ggnb_pdf']			= 'Gerencianet: Link para o boleto em PDF';
	$ggnb_merge_fields['ggnb_barcode']		= 'Gerencianet: Linha digitável do boleto';
	$ggnb_merge_fields['ggnb_expire_at']	= 'Gerencianet: Vencimento do boleto';
	$ggnb_merge_fields['ggnb_total']		= 'Gerencianet: Total do boleto';
	$ggnb_merge_fields['ggnb_charge_id']	= 'Gerencianet: ID da transação';
	$ggnb_merge_fields['ggnb_api_mode']		= 'Gerencianet: API mode (sandbox ou live)';
	$ggnb_merge_fields['ggnb_debug']		= 'Gerencianet: Debug nos emails';
    return $ggnb_merge_fields;
});
add_hook('InvoiceCancelled', 1, function($vars){
	require_once __DIR__ . '/../../modules/gateways/gofasgerencianetboleto/includes/functions.php';
	$params = getGatewayVariables('gofasgerencianetboleto');
	if($params['cancelbilletoncancelinvoice']){
		// Parâmetros do Módulo
		$sandbox	= $params['sandbox'];
		if($sandbox){
			$sandbox		= true;
			$client_id		= $params['clientidsandbox'];
			$client_secret	= $params['clientsecretsandbox'];
			$api_mode		= 'sandbox';
			$api_url		= 'https://sandbox.gerencianet.com.br/v1/';
		}
		elseif(!$sandbox){
			$sandbox		= false;
			$client_id		= $params['clientid'];
			$client_secret	= $params['clientsecret'];
			$api_mode		= 'live';
			$api_url		= 'https://api.gerencianet.com.br/v1/';
		}
	}
	$invoice	= localAPI('GetInvoice',array( 'invoiceid' => $vars['invoiceid'], ), (int)(int)ggnb_setup_admin()['id']);	
	// Parâmetros das transações associadas à Fatura
	foreach( Capsule::table('gofasgerencianetboleto')->where('invoice_id','=',$vars['invoiceid'])->where('api_mode','=',$api_mode)->get(['charge_id']) as $charge_id_local){
		if($charge_id_local->charge_id){
			$trans_id = $charge_id_local->charge_id;
		}
		else {
			$trans_id = false;
		}
	}
	if($trans_id){
		$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
		if($access_token_['access_token']){
			$access_token = $access_token_['access_token'];
		}
		if($access_token_['error']){
			$error = $access_token_['error'];
		}
		logModuleCall('gofasgerencianetboleto','access_token',array($api_url,$client_id,$client_secret), $access_token_ );
		try {
			//$id = array('id' => (int)$trans_id);
			$cancel_charge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
			
			if((string)$cancel_charge['result'] === (string)'success'){
				$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('charge_id', '=',$trans_id)->delete();
				logModuleCall('gofasgerencianetboleto','cancel_transaction',array('Sucesso:'=>$cancel_charge), $access_token_ );
			}
			else {
				$error	= 'Erro ao cancelar Transação: ' . $cancel_charge['error'];
				logModuleCall('gofasgerencianetboleto','cancel_transaction_1',array('Error:'=>$cancel_charge), $access_token_);
			}
		}
		catch (Exception $e){
			$error	= 'Erro ao cancelar Transação: ' . $e->getMessage();
			logModuleCall('gofasgerencianetboleto','cancel_transaction_2',array('Error:'=>$error), '');
		}
	}
	logModuleCall('gofasgerencianetboleto', 'InvoiceCancelled', array('params'=>$params,'invoice'=>$invoice),array('cancel_charge'=>$cancel_charge),'');
});