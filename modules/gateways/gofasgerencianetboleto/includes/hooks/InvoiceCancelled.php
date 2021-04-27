<?php
/**
 * Módulo Gerencianet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.3.0
 */
use WHMCS\Database\Capsule;
add_hook('InvoiceCancelled', 1, function($vars){
	require_once __DIR__ . '/../functions.php';
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
	$invoice	= localAPI('GetInvoice',array( 'invoiceid' => $vars['invoiceid'], ), (int)$params['admin']);	
	// Parâmetros das transações associadas à Fatura
	$trans_idendA				= $invoice['transactions'];
	if($trans_idendA){
		$trans_idend			= $trans_idendA['transaction'];
	}
	if($trans_idend){
		$trans_idp				= end( $trans_idend );
		$trans_id_				= $trans_idp['transid'];
		// Verifica se a transação pertence ao módulo
		if( (strpos( $trans_id_, 'ggnb') !== false) and (strpos( $trans_id_, 'unpaid') !== false or strpos( $trans_id_, 'waiting') !== false ) and strpos( $trans_id_, $api_mode) ){
			$trans_id					= (int)preg_replace('/[^0-9]/', '', $trans_id_ ); // ggnb_waiting_213630
		}
		else {
			$trans_id				= false;
		}
	}
	else {
		$trans_id				= false;
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
			
			if($cancel_charge['result'] === (string)'success'){
				$UpdateTransaction = localAPI('UpdateTransaction', array('transactionid' => $trans_idp['id'], 'transid' => 'ggnb_'.$api_mode.'_canceled-'.$trans_id, ), (int)$params['admin']);
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