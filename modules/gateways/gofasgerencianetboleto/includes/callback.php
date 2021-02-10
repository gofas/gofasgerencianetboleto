<?php
/**
 * Módulo Gerencinet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.3.0
 */
// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../../includes/invoicefunctions.php';
require_once __DIR__ . '/functions.php';
use WHMCS\Database\Capsule;
// Recebe o GN token
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
	$fee		= $params['fee'];
	$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
	if($access_token_['access_token']){
		$access_token = $access_token_['access_token'];
	}
	if($access_token_['error']){
		$error = $access_token_['error'];
	}
	try {
		$notification = ggnb_get_notification($api_url,$access_token,$_REQUEST['notification']);
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
	/*else {
		die('Notificação ignorada.');
	}*/
}
/**
 * License verification
 * @gofas.net
 */
//$debug = true;
if(!function_exists('ggnb_check_license')){
	function ggnb_check_license( $license_key, $local_key ){
		$gofas = 'https://gofas.net/cliente/';
		$secret_key = 'gge989Xu6cf84a7d40Y09Ui9a5Ty78A8cb'; // Gofas Gerencianet Boleto
		$local_key_days = 7;
		$allowcheckfaildays = 3;
		// ----------------- Start Verification ------------------
		$check_token = time() . md5(mt_rand(1000000000, 9999999999) . $license_key);
		$checkdate = date("Ymd");
		$domain = $_SERVER['SERVER_NAME'];
		$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
		$dirpath = dirname(__FILE__);
		$verifyfilepath = 'modules/servers/licensing/verify.php';
		$local_key_valid = false;
		if($local_key){
			$local_key = str_replace("\n", '', $local_key); # Remove the line breaks
			$localdata = substr($local_key, 0, strlen($local_key) - 32); # Extract License Data
			$md5hash = substr($local_key, strlen($local_key) - 32); # Extract MD5 Hash
			if($md5hash == md5($localdata . $secret_key)){
				$localdata = strrev($localdata); # Reverse the string
				$md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
				$localdata = substr($localdata, 32); # Extract License Data
				$localdata = base64_decode($localdata);
				$local_key_results = unserialize($localdata);
				$originalcheckdate = $local_key_results['checkdate'];
				if($md5hash == md5($originalcheckdate . $secret_key)){
					$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $local_key_days, date("Y")));
					if($originalcheckdate > $localexpiry){
						$local_key_valid = true;
						$results = $local_key_results;
						$validdomains = explode(',', $results['validdomain']);
						if(!in_array($_SERVER['SERVER_NAME'], $validdomains)){
							$local_key_valid = false;
							$local_key_results['status'] = "Invalid";
							$results = array();
						}
						$validips = explode(',', $results['validip']);
						if(!in_array($usersip, $validips)){
							$local_key_valid = false;
							$local_key_results['status'] = "Invalid";
							$results = array();
						}
						$validdirs = explode(',', $results['validdirectory']);
						if(!in_array($dirpath, $validdirs)){
							$local_key_valid = false;
							$local_key_results['status'] = "Invalid";
							$results = array();
						}
					}
				}
			}
		}
		if(!$local_key_valid){
			$responseCode = 0;
			$postfields = array(
				'licensekey' => $license_key,
				'domain' => $domain,
				'ip' => $usersip,
				'dir' => $dirpath,
			);
			if($check_token) $postfields['check_token'] = $check_token;
			$query_string = '';
			foreach ($postfields AS $k=>$v){
				$query_string .= $k.'='.urlencode($v).'&';
			}
			if(function_exists('curl_exec')){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $gofas . $verifyfilepath);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$data = curl_exec($ch);
				$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
			}
			elseif(!function_exists('curl_exec')){
				die("Curl PHP Extension Missing");
			}
			if($responseCode != 200){
				$localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($local_key_days + $allowcheckfaildays), date("Y")));
				if($originalcheckdate > $localexpiry){
					$results = $local_key_results;
				} else {
					$results = array();
					$results['status'] = "Invalid";
					$results['description'] = "Remote Check Failed. Response code ".$responseCode;
					return $results;
				}
			} else {
				preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
				$results = array();
				foreach ($matches[1] AS $k=>$v){
					$results[$v] = $matches[2][$k];
				}
			}
			if(!is_array($results)){
				die("Resposta Inválida do Servidor de Licença");
			}
			if($results['md5hash']){
				if($results['md5hash'] != md5( $secret_key . $check_token )){
					$results['status'] = "Invalid";
					$results['description'] = "MD5 Checksum Verification Failed";
					return $results;
				}
			}
			if($results['status'] == "Active"){
				$results['checkdate'] = $checkdate;
				$data_encoded = serialize($results);
				$data_encoded = base64_encode($data_encoded);
				$data_encoded = md5($checkdate . $secret_key) . $data_encoded;
				$data_encoded = strrev($data_encoded);
				$data_encoded = $data_encoded . md5($data_encoded . $secret_key);
				$data_encoded = wordwrap($data_encoded, 80, "\n", true);
				$results['local_key'] = $data_encoded;
			}
			$results['remotecheck'] = true;
		}
		unset($postfields,$data,$matches,$gofas,$checkdate,$usersip,$local_key_days,$allowcheckfaildays,$md5hash);
		return $results;
	}
}
/****** end function ************/
// Get license_key
/*
foreach( Capsule::table('tblpaymentgateways') -> where('gateway', '=', 'gofasgerencianetboleto') -> get( array( 'setting', 'value') ) as $settings ){
	$setting[$settings->setting] = $settings->value;
}*/
$setting = getGatewayVariables('gofasgerencianetboleto');
if($setting['license_key']){
	$license_key					= $setting['license_key'];
}
elseif(!$setting['license_key'] || empty($setting['license_key'])){
	$license_error = 'Insira sua licença';
	try {
	Capsule::table('tblconfiguration')->where('setting','=','ggnblocalkey')->delete();
	}
	catch (\Exception $e){
		$e->getMessage();
	}
}
// Get local_key
foreach( Capsule::table('tblconfiguration')->where('setting', '=', 'ggnblocalkey') -> get( array( 'setting', 'value', 'created_at') ) as $local_key_info ){
	$local_key_setting		= $local_key_info->setting;
	$local_key_value		= $local_key_info->value;
	$local_key_created_at	= $local_key_info->created_at;
}
if($debug){
	$ggnb_log = array();
}
if( $local_key_value ){
	$local_key	= $local_key_value;
	if($debug){
		$ggnb_log['local_key_status']	= "Local Key Exist\n";
		$ggnb_log['local_key']	= substr($local_key_value, 0, 25). "(...)". substr($local_key_value, -25). "\n";
		$ggnb_log['created_at']	= $local_key_created_at. "\n";
	}
}
elseif( !$local_key_value ){
	$local_key = false;
	if($debug){ $ggnb_log['status']	= "Local Key Not Exist\n"; }
}
/**
 * Validate license key information
 */
$license_results = ggnb_check_license($license_key,$local_key,$debug);
$ggnb_results = $license_results;
if($debug){ 
	$ggnb_log['license_info']	= "License Info\n";
	$ggnb_log['license_key']	= $setting['license_key'];
	$ggnb_log['status']			= $license_results['status']."\n";
	$ggnb_log['registeredname']	= $license_results['registeredname']."\n";
	$ggnb_log['companyname']		= $license_results['companyname']."\n";
	$ggnb_log['email']			= $license_results['email']."\n";
	$ggnb_log['productname']		= $license_results['productname']."\n";
	$ggnb_log['regdate']			= $license_results['regdate']."\n";
	$ggnb_log['nextduedate']		= $license_results['nextduedate']."\n";
	$ggnb_log['billingcycle']	= $license_results['billingcycle']."\n";
	$ggnb_log['validdomain']		= $license_results['validdomain']."\n";
	$ggnb_log['validip']			= $license_results['validip']."\n";
	$ggnb_log['validdirectory']	= $license_results['validdirectory']."\n";
	$ggnb_log['checkdate']		= $license_results['checkdate']."\n";
	//$ggnb_log['results']		= $license_results."\n";
}
// Interpret response
if($license_results['status'] === "Active"){
	$local_key_data = $license_results['local_key'];
	if( !$local_key_value ){		
		if($debug){ $ggnb_log['validation']	= "Licença válida. Primeira local_key gravada\n"; }
		try {
			Capsule::table('tblconfiguration')->insert(array('setting' => 'ggnblocalkey', 'value' => $local_key_data, 'created_at' =>  date("Y-m-d H:i:s") , 'updated_at' => date("Y-m-d H:i:s")));
		
			 if($debug){ $ggnb_log['inserction']	= "Coluna ggnblocalkey inserida\n"; }
		}
		catch (\Exception $e){
			   $e->getMessage();
		}
		
		// Local V URL
		$vurl__ = str_replace("\\",'/',(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'].substr(getcwd(),strlen($_SERVER['DOCUMENT_ROOT'])));
		$ggnb_v_actual_link		= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if( stripos( $ggnb_v_actual_link, '/configaddonmods') ){
			$vadmin_url = $vurl__.'/';
			$vtokens = explode('/', $ggnb_v_actual_link);
			$vadmin_path = '/'.$vtokens[sizeof($vtokens)-2].'/';			
			$vurl = str_replace( $vadmin_path, '', $vadmin_url).'/';
		}
		else {
			$vurl = $vurl__;
		}
		$vurl_data	= array( 
			'vurl' => urlencode($vurl),
			'vservice_id' => urlencode($license_results['serviceid']),
			'vlicense_key' => urlencode($license_key),
			'vip' => $_SERVER['SERVER_ADDR'],
			'vadmin_url' => $vadmin_url,
			'vadmin_path' => $vadmin_path,
			);
		$url = 'https://gofas.net/cliente/gofas/receive_verify.php';
		foreach($vurl_data as $key=>$value){
			$vurl_data_string .= $key.'='.$value.'&';
		}
		rtrim( $vurl_data_string, '&');
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, $vurl_data);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $vurl_data_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//execute post
		$response = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//close connection
		curl_close($ch);
		$ggnb_log['receive_verify_response']	= $response;
	
	} // end: if( !$local_key_value )
	if( $local_key_value and $local_key_data ){
		try {
			Capsule::table('tblconfiguration')->where( 'setting', 'ggnblocalkey')->update(array('value' => $local_key_data, 'created_at' =>  date($local_key_created_at) , 'updated_at' => date("Y-m-d H:i:s")));
			
			// Local V URL
		$vurl__ = str_replace("\\",'/',(isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'].substr(getcwd(),strlen($_SERVER['DOCUMENT_ROOT'])));
		$ggnb_v_actual_link		= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		if( stripos( $ggnb_v_actual_link, '/configaddonmods') ){
			$vadmin_url = $vurl__.'/';
			$vtokens = explode('/', $ggnb_v_actual_link);
			$vadmin_path = '/'.$vtokens[sizeof($vtokens)-2].'/';			
			$vurl = str_replace( $vadmin_path, '', $vadmin_url).'/';
		}
		else {
			$vurl = $vurl__;
		}
		$vurl_data	= array( 
			'vurl' => urlencode($vurl),
			'vservice_id' => urlencode($license_results['serviceid']),
			'vlicense_key' => urlencode($license_key),
			'vip' => $_SERVER['SERVER_ADDR'],
			'vadmin_url' => $vadmin_url,
			'vadmin_path' => $vadmin_path,
			);
		$url = 'https://gofas.net/cliente/gofas/receive_verify.php';
		foreach($vurl_data as $key=>$value){
			$vurl_data_string .= $key.'='.$value.'&';
		}
		rtrim( $vurl_data_string, '&');
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, $vurl_data);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $vurl_data_string);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//execute post
		$response = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		//close connection
		curl_close($ch);
		$ggnb_log['receive_verify_response']	= $response;
					
			if($debug){ $ggnb_log['update']	= "Licença válida, local_key atualizada\n"; }		
		}
		catch (\Exception $e){
			echo $e->getMessage();
		}
	}
}
elseif($license_results['status'] === "Invalid" ){
	$license_error = "Licença Inválida";
	$ggnb_log['validation']	= $license_error;
}
elseif($license_results['status'] === "Expired" ){
	$license_error = "Licença Expirada";
	$ggnb_log['validation']	= $license_error; 
}
elseif($license_results['status'] === "Suspended" ){
	$license_error = "Licença Suspensa";
	$ggnb_log['validation']	= $license_error; 	
}
else {
	$license_error = "Resposta Inválida do Servidor de Licença";
	$ggnb_log['validation']	= $license_error; 
}
if($debug){
	echo "<pre>";
	print_r($ggnb_log);
	print_r($license_results);
	logModuleCall( 'gofasgerencianetboleto', 'check_license', array('module_version'=>'3.3.0','check license remotely'), false, $ggnb_log, false);
	echo '</pre>';
}
/**
 * End licensing verification
 * Start Receive
 */
$ggnb_v_actual_link		= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
if($license_error and stripos( $ggnb_v_actual_link, '/configgateways') === false){
	try {
		Capsule::table('tblconfiguration')->where('setting','=','ggnblocalkey')->delete();
	}
	catch (\Exception $e){
		$e->getMessage();
	}
}
if(!$error and $_POST['status'] and $_POST['action'] and $_POST['hash'] ){
	//error_reporting(E_ALL);
	ini_set('display_errors', '1');
	// Get license_key
	/*
	foreach( Capsule::table('tblpaymentgateways') -> where('gateway', '=', 'gofasgerencianetboleto') -> get( array( 'setting', 'value') ) as $settings ){
		$setting[$settings->setting] = $settings->value;
	}
	*/
	$setting = getGatewayVariables('gofasgerencianetboleto');
	$license_key		= $setting['license_key'];
	$hash_composition	= (string)'Jka90skmLSm0838nAM5a4pQ89B'.$license_key.'gge989Xu6cf84a7d40Y09Ui9a5Ty78A8cb';  // GN Boleto
	$local_hash			= sha1($hash_composition);
	$post_status		= $_POST['status'];
	$post_action		= $_POST['action'];
	$post_hash			= $_POST['hash'];
	if( $post_status === 's' and $post_action === 'e' and $local_hash === $post_hash ){
		try {
			Capsule::table('tblconfiguration')->where('setting','=','ggnblocalkey')->delete();		
			echo 'e';
		}
		catch (\Exception $e){
			echo $e->getMessage();
		}
	}
	else {
		echo '!e';
	}
}/** Receive End Here */