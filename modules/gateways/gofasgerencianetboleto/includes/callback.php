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
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
require_once __DIR__ . '/functions.php';
use WHMCS\Database\Capsule;
if($_REQUEST['notification']){
	$params = getGatewayVariables('gofasgerencianetboleto');
	if(!$params['type']){die("Module Not Activated");}
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
	$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
	if($access_token_['access_token']){
		$access_token = $access_token_['access_token'];
	}
	if($access_token_['error']){
		$error = $access_token_['error'];
	}
	try {
		$notification = ggnb_get_notification($api_url,$access_token,$_REQUEST['notification']);
		if($notification['data']){
			$notificationDataEnd	= end($notification['data']);
		}
		$notificationData 		= $notificationDataEnd;
		$invoiceId				= $notificationData['custom_id']; // Captura ID da fatura
		$charge_id				= $notificationData['identifiers']['charge_id']; // Captura ID da transação
		$chargeExist_			= ggnb_detail_charge($api_url,$access_token,$charge_id);
		$chargeExist			= $chargeExist_['result'];
		$paymentAmount			= (float)number_format(($chargeExist['data']['total']/100), 2,'.',''); // issue #142
		$chargeStatus			= $chargeExist['data']['status']; // Status atual
		$getinvoiceid['invoiceid']	= $invoiceId;
		$invoice_data				= localAPI('getinvoice', $getinvoiceid, (int)ggnb_setup_admin()['id']);
		$invoice_amount				= (float)$invoice_data['total'];
		$invoiceStatus				= $invoice_data['status'];
		$user_id 					= $invoice_data['userid'];
		foreach( Capsule::table('gofasgerencianetboleto')->where('invoice_id','=',$invoiceId)->where('api_mode','=',$api_mode)->get(['charge_id']) as $charge_id_local){
			if($charge_id_local->charge_id){
				$trans_id = $charge_id_local->charge_id;
			}
			else {
				$trans_id = false;
			}
		}
	}
	catch (Exception $e){
		echo $e->getMessage();
	}
	if((string)$trans_id ===  (string)$charge_id and $chargeStatus === 'paid' and $invoiceStatus === 'Unpaid' and $paymentAmount > 0){
		if( $paymentAmount > $invoice_amount){
			$UpdateInvoice = localAPI('updateinvoice', array( 'invoiceid' => $invoiceId, 'newitemdescription' => array('Acréscimos'),'newitemamount' => array((float)($paymentAmount - $invoice_amount))/* , 'total' => $paymentAmount */), (int)ggnb_setup_admin()['id'] );
			echo 'UpdateInvoice: ', json_encode($UpdateInvoice);
		}
		if( $paymentAmount < $invoice_amount){
			$UpdateInvoice = localAPI('updateinvoice', array( 'invoiceid' => $invoiceId, 'newitemdescription' => array('Descontos'),'newitemamount' => array((float)-($invoice_amount-$paymentAmount))/* , 'total' => $paymentAmount */), (int)ggnb_setup_admin()['id'] );
			if($params['log']){
				echo json_encode(['UpdateInvoice'=>$UpdateInvoice]);
			}
		}
		 $add_trans = localAPI( 'addtransaction' ,
		 [
			 'userid'=>$user_id,
			 'invoiceid'=>$invoiceId,
			 'description'=>'Boleto pago - confirmação via notificação',
			 'amountin'=>$paymentAmount,
			 'fees'=>$params['fee'] ?: '0.00',
			 'paymentmethod'=>'gofasgerencianetboleto',
			 'transid'=>'ggnb-'.$api_mode.'-'.$charge_id,
		 ],
		 (int)ggnb_setup_admin()['id']
	 	);
	 	$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('invoice_id', '=',$invoiceId)->delete();
	 	$ggnb_update_stats = ggnb_update_stats();
		if($params['log']){
			echo json_encode(['Add transaction'=>$addtransresult]);
		}
		if($params['log']){
			logModuleCall("gofasgerencianetboleto","receive_callback",array(get_defined_vars()),"", array($addtransresult));
		}
	}
}