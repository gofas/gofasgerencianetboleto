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
 
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
require_once __DIR__ . '/../includes/functions.php';

$params = getGatewayVariables('gofasgerencianetboleto');
if(!$params['type']){die("Module Not Activated");}

// Recebe o GN token
$notification_token = $_REQUEST['notification'];
if($notification_token){
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

	$fee		= $params['fee'];
	$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
	if($access_token_['access_token']){
		$access_token = $access_token_['access_token'];
	}
	if($access_token_['error']){
		$error = $access_token_['error'];
	}
	try {
		$notification = ggnb_get_notification($api_url,$access_token,$notification_token);
		// Prepara dados retornados
		@$notificationDataEnd	= end($notification['data']);
		$notificationData 		= $notificationDataEnd;
		$invoiceId				= $notificationData['custom_id']; // Captura ID da fatura
		$charge_id				= $notificationData['identifiers']['charge_id']; // Captura ID da transação
		$paymentAmount			= (float)($notificationData['value'] / 100); // Valor da transação
		$previousStatus			= $notificationData['status']['previous']; // Status anterior
		$chargeStatus			= $notificationData['status']['current']; // Status atual
		// Debug
		echo 'Notificacao: ',json_encode($notification);
		// Consulta dados da fatura
		$getinvoiceid['invoiceid']	= $invoiceId;
		$invoice_data				= localAPI('getinvoice', $getinvoiceid, $params['admin']);
		$invoice_amount				= (float)$invoice_data['total'];
		$invoiceStatus				= $invoice_data['status'];
		$user_id 					= $invoice_data['userid'];
		// Debug
		echo 'Dados da Fatura: ', json_encode($invoice_data);
	}
	catch (Exception $e){
		die( print_r($e->getMessage()) );
	}
	// Confirma pagamento
	if( $chargeStatus === 'paid' and $invoiceStatus === 'Unpaid'){	
		if( (int)$paymentAmount * 100 > (int)$invoice_amount * 100 ){
			$UpdateInvoice = localAPI('updateinvoice', array( 'invoiceid' => $invoiceId, 'newitemdescription' => array('Acréscimos'),'newitemamount' => array((float)($paymentAmount - $invoice_amount))/* , 'total' => $paymentAmount */), $params['admin'] );
		}
		echo 'UpdateInvoice: ', json_encode($UpdateInvoice);
 		$addtransvalues['userid']			= $user_id;
 		$addtransvalues['invoiceid']		= $invoiceId;
 		$addtransvalues['description']		= 'Boleto pago!';
 		$addtransvalues['amountin']			= $paymentAmount;
 		if($fee){
 			$addtransvalues['fees']			= $fee;
 		} elseif(!$fee){
 			$addtransvalues['fees']			= '0.00';
 		}
 		$addtransvalues['paymentmethod']	= 'gofasgerencianetboleto';
 		$addtransvalues['transid']			= 'ggnb_'.$api_mode.'_paid-'.$charge_id.'';
 		$addtransvalues['date']				= date('d/m/Y');
		$addtransresult						= localAPI( 'addtransaction' , $addtransvalues, $params['admin'] );

		// Debug
		echo 'Add transaction: ', json_encode($addtransresult);

	}
	
	// Marca como "Não pago" a transação
	if( $chargeStatus === 'unpaid' and $invoiceStatus === 'Unpaid'){
		$addtransvalues['userid']			= $user_id;
 		$addtransvalues['invoiceid']		= $invoiceId;
 		$addtransvalues['description']		= 'Boleto inadimplente';
 		$addtransvalues['amountin']			= '0.00';
 		$addtransvalues['fees']				= '0.00';
 		$addtransvalues['paymentmethod']	= 'gofasgerencianetboleto';
 		$addtransvalues['transid']			= 'ggnb_'.$api_mode.'_unpaid-'.$charge_id.'';
 		$addtransvalues['date']				= date('d/m/Y');
		$addtransresult						= localAPI( 'addtransaction' , $addtransvalues, $params['admin'] );

		// Debug
		echo 'Resultado: ', json_encode($addtransresult);
	}
	else {
		die('Notificação ignorada.');
	}
	
} elseif( !$notification_token ){
	die();
}