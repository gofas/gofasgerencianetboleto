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

if(!defined('WHMCS')){ die('Esse arquivo não pode ser acessado diretamente'); }
use WHMCS\Database\Capsule;
/**
 *
 * Verify Instalation
 * @return Array
 *
 */
if(!function_exists('ggnb_verifyInstall')){
function ggnb_verifyInstall(){
	if( !Capsule::schema()->hasTable('gofasgerencianetboleto') ){
    	try {
			Capsule::schema()->create('gofasgerencianetboleto', function($table){
				// incremented id
        		$table->increments('id');
       			// whmcs info
				$table->string('invoice_id');
				$table->string('charge_id');
				$table->string('link');
				$table->string('pdf');
				$table->string('expire_at');
				$table->string('total');
				$table->string('barcode');
				$table->string('status');
				$table->string('api_mode');
    		});
		}
		catch (\Exception $e){
    		$error = "Não foi possível criar a tabela do módulo no banco de dados: {$e->getMessage()}";
		}
	}
	if(!$error){
		return array('success'=>1);
	}
	if($error){
		return array('error'=>$error);
	}
}}
/**
 *
 * Obter OAuth Token
 * @ggnb_get_token
 * $token = ggnb_get_token($client_id, $client_secret, $api_url);
 *
 */
if( !function_exists('ggnb_get_token') ){
	function ggnb_get_token($api_url,$client_id,$client_secret){
		$curl = curl_init($api_url.'authorize');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic '. base64_encode("$client_id:$client_secret"),
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		curl_setopt($curl, CURLOPT_POST, true);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('grant_type'=>'client_credentials','partner_token'=>'baaf5b95d55433890bd835cf006772b9462bde8f')));
  		$json = json_decode(curl_exec($curl), true);
		if($json['access_token']){
			return array('access_token'=>$json['access_token']);
		}
		if($json['error']){
  			return array('error'=>$json['error_description']);
		}
	}
}

/**
 *
 * Consultar informações da transação
 * @ggnb_detail_charge
 *
 */
if(!function_exists('ggnb_detail_charge')){
	function ggnb_detail_charge($api_url,$access_token,$trans_id){
		$curl = curl_init($api_url.'charge/'.$trans_id );
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		$json = json_decode(curl_exec($curl), true);
		if($json['data']['charge_id']){
			return array('result'=>$json);
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error);
		}
	}
}

/**
 *
 * Cancelar transação
 * ggnb_cancel_charge
 *
 */
if(!function_exists('ggnb_cancel_charge')){
	function ggnb_cancel_charge($api_url,$access_token,$trans_id){
		$curl = curl_init($api_url.'charge/'.$trans_id.'/cancel');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
  		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('id'=>$trans_id)));
		$json = json_decode(curl_exec($curl), true);
		if((int)$json['code'] === 200 ){
			return array('result'=>(string)'success');
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error);
		}
	}
}
/**
 *
 * Atualizar vencimento do Boleto
 * ggnb_update_billet
 *
 */
if(!function_exists('ggnb_update_billet')){
	function ggnb_update_billet($api_url,$access_token,$trans_id,$billet_duedate){
		$curl = curl_init($api_url.'charge/'.$trans_id.'/billet');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
  		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array('expire_at'=>$billet_duedate)));
		$json = json_decode(curl_exec($curl), true);
		if((int)$json['code'] === 200 ){
			return array('result'=>(string)'success');
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error);
		}
	}
}
/**
 *
 * Criar transação
 * @ggnb_create_charge
 *
 */
if(!function_exists('ggnb_create_charge')){
	function ggnb_create_charge($api_url,$access_token,$body){
		$curl = curl_init($api_url.'charge');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_POST, 1);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
		$json = json_decode(curl_exec($curl), true);
		if($json['data']['charge_id']){
			return array('result'=>(int)$json['data']['charge_id']);
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error);
		}
	}
}
if(!function_exists('ggnb_pay_charge')){
	function ggnb_pay_charge($api_url,$access_token,$charge_id,$body2){
		$curl = curl_init($api_url.'charge/'.$charge_id.'/pay');
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  		curl_setopt($curl, CURLOPT_POST, 1);
  		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body2));
		$json = json_decode(curl_exec($curl), true);
		if($json['data']['charge_id']){
			return array('result'=>$json);
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error);
		}
	}
}
if(!function_exists('ggnb_get_notification')){
	function ggnb_get_notification($api_url,$access_token,$notification_token){
		$curl = curl_init($api_url.'notification/'.$notification_token );
  		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer '.$access_token,
			'Content-Type: application/json',
			'partner-token: baaf5b95d55433890bd835cf006772b9462bde8f',
		));
  		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  		$json = json_decode(curl_exec($curl), true);
		if($json['data']){
			return $json;
		}
		if($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);
			return array('error'=> $error);
		}
	}
}
if( !function_exists('ggnb_add_trans') ){
	function ggnb_add_trans( $user_id, $invoice_id, $amount, $fee, $charge_id, $description ){
		$params = getGatewayVariables('gofasgerencianetboleto');
 		$addtransvalues['userid'] = $user_id;
 		$addtransvalues['invoiceid'] = $invoice_id;
 		$addtransvalues['description'] = $description;
 		$addtransvalues['amountin'] = $amount;
 		$addtransvalues['fees'] = $fee;
 		$addtransvalues['paymentmethod'] = 'gofasgerencianetboleto';
 		$addtransvalues['transid'] = $charge_id;
 		$addtransvalues['date'] = date('d/m/Y');
		$addtransresults = localAPI( "addtransaction", $addtransvalues, (int)ggnb_setup_admin()['id']);
		$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('invoice_id', '=',$invoice_id)->delete();
		$ggnb_update_stats = ggnb_update_stats();
		if( $addtransresults['result'] === 'success'){
			return array('values'=>$addtransvalues, 'result'=>$addtransresults);
		}
		elseif($addtransresults['result'] !== 'success'){
			$error = '<b>Não foi possível gravar a transação.</b>';
			return array('error'=>$error, 'values'=>$addtransvalues, 'result'=>$addtransresults,'update_stats'=>$ggnb_update_stats);
		}
	}
}
if( !function_exists('ggnb_store_billet') ){
	function ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode){
		$date = str_replace('/', '-', $pay_charge['data']['charges']['0']['dueDate']);
		$dueDate = date("Y-m-d", strtotime($date));
		$data = array(
			'invoice_id'=>$invoice_id,
			'charge_id'=>$pay_charge['data']['charge_id'],
			'link'=>$pay_charge['data']['link'],
			'pdf'=>$pay_charge['data']['pdf']['charge'],
			'expire_at'=> $pay_charge['data']['expire_at'],
			'total'=> $pay_charge['data']['total'],
			'barcode'=>$pay_charge['data']['barcode'],
			'status'=>$pay_charge['data']['status'],
			'api_mode'=>$api_mode,
		);
		try{
			$save_billet = Capsule::table('gofasgerencianetboleto') ->insert($data);
		}
		catch (\Exception $e){
			$error = "Não foi possível gravar o Boleto no banco de dados. {$e->getMessage()}";
		}
		if($error){
			return array('error'=>$error);
		}
		if(!$error){
			return array('success'=>true);
		}
	}
}
if(!function_exists('ggnb_send_error_email')){
	function ggnb_send_error_email( $invoice_id, $user_id, $first_name, $last_name, $dept_id, $error){
		$sendEmailonError = "sendadminemail";
	 	$sendEOEvalues['customsubject'] = 'Erro ao gerar boleto - fatura #'.$invoice_id;
		$sendEOEvalues['custommessage'] = '<br/>Olá administrador,<br/>
			Ocorreu uma falha ao gerar um Boleto para a <a href="'.ggnb_whmcs_url()['admin_url'].'/invoices.php?action=edit&id='.$invoice_id.'">Fatura #'.$invoice_id.'</a>.<br/><br/>
			Detalhes do erro:<br/>
			<b>Cliente:</b> <a href="'.ggnb_whmcs_url()['admin_url'].'/clientssummary.php?userid='.$user_id.'">'.$first_name.' '.$last_name.'</a><br/><br/>
			<b>Erro exibido na Fatura:</b><br/><i>"'.$error.'"</i><br/><br/>
			Email gerado de acordo com às configurações do gateway <a title="Ir para as configurações do módulo ↗" href="'.ggnb_whmcs_url()['admin_url'].'/configgateways.php?updated=gofasgerencianetboleto#m_gofasgerencianetboleto">Gofas Gerencianet Boleto</a>.<br/><br/>';
	 	$sendEOEvalues['type'] = 'system';
	 	$sendEOEvalues['deptid'] = $dept_id;
	 	$sendEOEresults = @localAPI($sendEmailonError,$sendEOEvalues, (int)ggnb_setup_admin()['id']);
		 return array('result'=>$sendEOEresults);
	}
}

/**
 * 
 * v3.7.0
 * 
 */
if( !function_exists('ggnb_whmcs_url') ){
	function ggnb_whmcs_url(){
		$self = App::self();
		$whmcs_admin_path = ggnb_get_protected_property($self, 'customadminpath');
		$whmcs_url = App::getSystemUrl();
		$admin_url = $whmcs_url.$whmcs_admin_path;
		return ['url'=>$whmcs_url,'admin_url'=>$admin_url,'admin_path'=>$whmcs_admin_path];
	}
}
if(!function_exists('ggnb_verify_module_updates')){
	function ggnb_verify_module_updates($page_id,$referer,$module_version){
		foreach( Capsule::table('tblconfiguration')->where('setting','=','ggnb_version')->get(['value','created_at','updated_at']) as $version_ ){
			$version		= json_decode($version_->value, true);
			$local_version	= $version['local_version'];
			$last_version	= $version['last_version'];
			$embed			= $version['check'];
			$created_at		= $version_->created_at;
			$updated_at		= $version_->updated_at;
			//$available_version	= (int)preg_replace("/[^0-9]/","",$version['last_version']);
		}
		///// Get
		if(!$version){
			$get_version = ggnb_get_version($page_id,$referer,$module_version);
			$get_embed	 = ggnb_get_embed($page_id,$referer,$module_version);
			
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and strtotime($updated_at) < strtotime("-1 day")){
			$get_version = ggnb_get_version($page_id,$referer,$module_version);
			$get_embed	 = ggnb_get_embed($page_id,$referer,$module_version);
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and (string)$module_version !== (string)$local_version){
			$get_version = ggnb_get_version($page_id,$referer,$module_version);
			$get_embed	 = ggnb_get_embed($page_id,$referer,$module_version);
			if((int)$get_version['http_code'] !== 200){
				$error .= $get_version['http_code'].' '.$get_version['version'];
			}
			else{
				$available_version = $get_version['version'];
			}
		}
		if($version and strtotime($updated_at) > strtotime("-1 day")){
			$available_version = $last_version;
		}
		// insert
		if(!$version and $get_version['version'] and $get_embed['embed']){
			$local_version = $module_version;
			$last_version = $get_version['version'];
			$embed		  = ggnb_encrypt($get_embed['embed']);
			$created_at		= date("Y-m-d H:i:s");
			$updated_at		= date("Y-m-d H:i:s");

			try { Capsule::table('tblconfiguration')->insert(array(
				'setting' => 'ggnb_version',
				'value' => json_encode([
					'local_version'=>$module_version,
					'last_version'=>$get_version['version'],
					'check'=>ggnb_encrypt($get_embed['embed']),
					'admin'=>ggnb_current_admin(),
				]),
				'created_at' => $created_at,
				'updated_at' => $updated_at
			));
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		// update
		if($version and $get_version['version'] and $get_embed['embed'] and strtotime($updated_at) < strtotime("-1 day") and (
			$available_version !== $module_version ||
			$local_version !== $module_version ||
			$last_version !== $available_version
		)){
			try {
				Capsule::table('tblconfiguration')->where('setting','ggnb_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>ggnb_encrypt($get_embed['embed']),
						'admin'=>ggnb_current_admin(),
					]),
					'created_at' =>  $created_at,
					'updated_at' => date("Y-m-d H:i:s")]
				);
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		// update
		if($version and $get_version['version'] and $get_embed['embed'] and (string)$local_version !== (string)$module_version){
			try {
				Capsule::table('tblconfiguration')->where('setting','ggnb_version')->update([
					'value' => json_encode([
						'local_version'=>$module_version,
						'last_version'=>$available_version,
						'check'=>ggnb_encrypt($get_embed['embed']),
						'admin'=>ggnb_current_admin(),
					]),
					'created_at' =>  $created_at,
					'updated_at' => date("Y-m-d H:i:s")]
				);
			}
			catch (\Exception $e){
				$error .= $e->getMessage();
			}
		}
		$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
		$available_version_int = (int)preg_replace("/[^0-9]/", "", $available_version);
		if( $available_version_int === $module_version_int ){
			$message = '<p style="color: green"><i class="fas fa-check-square"></i> Você está executando a versão mais recente do módulo.</p>';
		}
		if( $available_version_int > $module_version_int ){
			$message = '<p style="font-size: 14px; color: red;"><i class="fas fa-exclamation-triangle"></i> Atualização disponível, verifique a <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">versão '.$available_version.'</a>';
		}
		if( $available_version_int < $module_version_int ){
			$message = '<p style="font-size: 14px; color: orange;"><i class="fas fa-exclamation-triangle"></i> Você está executando uma versão Beta desse módulo.<br>Baixar versão estável: <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">v'.$available_version.'</a>';
		}
		return [
			'version'=>$version,
			'get_version'=>$get_version,
			'message' => $message,
			'check'=> $embed,
			'error' => $error,
		];
	}
}
if(!function_exists('ggnb_version')){
	function ggnb_version($opt=1){
		foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggnb_version') -> get( array( 'value','created_at') ) as $ggnb_version_ ){
			$ggnb_version				= $ggnb_version_->value;
			$ggnb_version_created_at	= $ggnb_version_->created_at;
		}
		if($opt=1){ // local_version string
			$version = json_decode($ggnb_version, true);
			return $version['local_version'];
		}
		if($opt=2){ // local_version integer
			$version = json_decode($ggnb_version, true);
			return (int)preg_replace("/[^0-9]/", "", $version['local_version']);
		}
		if($opt=3){ // full
			return$ggnb_version;
		}
	}
}
if(!function_exists('ggnb_current_admin')){
	function ggnb_current_admin(){
		$currentUser = new \WHMCS\Authentication\CurrentUser;
		$admin = json_decode(json_encode($currentUser->admin()),true);
		return $admin;
	}
}
if(!function_exists('ggnb_setup_admin')){
	function ggnb_setup_admin(){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','ggnb_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		$admin			= $version['admin'];
	}
	return $admin;
}}
if(!function_exists('ggnb_update_stats') ){
	function ggnb_update_stats(){
		$params = getGatewayVariables('gofasgerencianetboleto');
		if($params['sandbox']){
			return;
		}
		$whmcs_url = ggnb_whmcs_url();
		$setup_admin = ggnb_setup_admin();
		$query = '?software_id=7893&install_url='.$whmcs_url['admin_url'].'&current_version='.ggnb_get_local_version().'&installer_email='.$setup_admin['email'].'&installer_firstname='.$setup_admin['firstname'].'&installer_lastname='.$setup_admin['lastname'].'&action=charge'.ggnb_sysinfo();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, 'https://gofas.net/br/updates/stats.php'.$query);
		$response = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		$return = ['query'=>$query,'response'=>$response,'http_code'=>$http_status];
		return $return;
	}
}
if(!function_exists('ggnb_get_local_version')){
	function ggnb_get_local_version(){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','ggnb_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		$local_version			= $version['local_version'];
	}
	return $local_version;
}}
if(!function_exists('ggnb_sysinfo')){
	function ggnb_sysinfo(){
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','Version')
		->get(['value']) as $data1 ){
			$Version = $data1->value;
		}
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','CronPHPVersion')
		->get(['value']) as $data1 ){
			$PHPVersion = $data1->value;
		}
		return '&whmcs_version='.$Version.'&php_version='.$PHPVersion;
	}
}
if(!function_exists('ggnb_get_protected_property')){
	function ggnb_get_protected_property($object, $property){
	    $reflectedClass = new \ReflectionClass($object);
	    $reflection = $reflectedClass->getProperty($property);
	    $reflection->setAccessible(true);
	    return $reflection->getValue($object);
	}
}
if(!function_exists('ggnb_get_version') ){
	function ggnb_get_version($page_id,$referer,$module_version){
		//$currentUser = new \WHMCS\Authentication\CurrentUser;
		$current_admin = ggnb_current_admin();
		//$admin_ = json_decode(json_encode($currentUser->admin()),true);
		//$admin = ['email'=>$admin_['email'],'firstname'=>$admin_['firstname'],'lastname'=>$admin_['lastname']];
		$query = '?software_id='.$page_id.'&install_url='.$referer.'&current_version='.$module_version.'&installer_email='.$current_admin['email'].'&installer_firstname='.$current_admin['firstname'].'&installer_lastname='.$current_admin['lastname'].'&action=verify'.ggnb_sysinfo();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, 'https://gofas.net/br/updates/stats.php'.$query);
		$available_version_ = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		//logModuleCall('gofasgerencianetboleto','ggnb_get_version',array('available_version'=>$available_version_),'','' );
		return ['version'=>$available_version_,'http_code'=>$http_status];
	}
}
if( !function_exists('ggnb_get_embed') ){
	function ggnb_get_embed($page_id,$referer,$module_version){
		$query = 'https://gofas.net/cliente/gofas/updates/?embed='.$page_id.'&referer='.$referer.'&version='.$module_version;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_URL, $query);
		$embed = curl_exec($curl);
		$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return ['embed'=>$embed,'http_code'=>$http_status];
	}
}
if(!function_exists('ggnb_encrypt')){
	function ggnb_encrypt($q) {
	    $encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_encrypt($q, $encryptionMethod, $secretHash);
	}
}
if(!function_exists('ggnb_decrypt')){
	function ggnb_decrypt($q){
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "535ba9979bc6c7ff151f2136cd13b0f9";
	    return openssl_decrypt($q, $encryptionMethod, $secretHash);
	}
}
if( !function_exists('ggnb_get_string_between') ){
	function ggnb_get_string_between($string, $start, $end){
		$string = " ".$string;
		$ini = strpos($string,$start);
		if ($ini == 0) return "";
		$ini += strlen($start);   
		$len = strpos($string,$end,$ini) - $ini;
		return substr($string,$ini,$len);
	}
}