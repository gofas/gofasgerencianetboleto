<?php
/**
 * 
 * Módulo EFÍ Boleto para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 -> 2023 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.9.0
 */
use WHMCS\Aplication;
use WHMCS\Database\Capsule;
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
if(!function_exists('ggnb_file_exists_check') ){ #10
    function ggnb_file_exists_check($file,$sucess_msg=NULL,$error_msg=NULL){
		$file = ggnb_whmcs_url('root_dir').$file;
    	if(!file_exists($file)){
			if(!$error_msg){
				$error_msg .= '<p style="color: red;padding: 10px;border-left: 2px solid red;padding: 5px 10px 12px 12px;">';
				$error_msg .= '<span style="font-size: 24px;">Atenção!</span><br>';
	    	    $error_msg .= 'Arquivo <b>'.$file.'</b> não encontrado.';
				$error_msg .= '<br>É necessário instalar o <i>hook</i> que acompanha o módulo para todos os recursos funcionarem. <a style="text-decoration:underline;color:red" target="_blank" href="https://gofas.net/ggnb/#instalation">Saiba mais </a>&#10138;';
				$error_msg .= '</p>';
				return $error_msg;
			}
			if($error_msg){
				return $error_msg;
			}
		}
		else{
			if($sucess_msg){
				return $sucess_msg;
			}
    	    return;
    	}
    }
}
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
		$addtransresults = localAPI( "addtransaction", $addtransvalues, ggnb_setup_admin('id'));
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
			Ocorreu uma falha ao gerar um Boleto para a <a href="'.ggnb_whmcs_url('admin_url').'/invoices.php?action=edit&id='.$invoice_id.'">Fatura #'.$invoice_id.'</a>.<br/><br/>
			Detalhes do erro:<br/>
			<b>Cliente:</b> <a href="'.ggnb_whmcs_url('admin_url').'/clientssummary.php?userid='.$user_id.'">'.$first_name.' '.$last_name.'</a><br/><br/>
			<b>Erro exibido na Fatura:</b><br/><i>"'.$error.'"</i><br/><br/>
			Email gerado de acordo com às configurações do gateway <a title="Ir para as configurações do módulo ↗" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?updated=gofasgerencianetboleto#m_gofasgerencianetboleto">Gofas EFÍ Boleto</a>.<br/><br/>';
	 	$sendEOEvalues['type'] = 'system';
	 	$sendEOEvalues['deptid'] = $dept_id;
	 	$sendEOEresults = @localAPI($sendEmailonError,$sendEOEvalues, ggnb_setup_admin('id'));
		 return array('result'=>$sendEOEresults);
	}
}
/**
 * 
 * v3.7.0
 * 
 */
if(!function_exists('ggnb_whmcs_url') ){
	function ggnb_whmcs_url($type='all'){
        $info=[];
        $self = App::self();
		$info['root_dir'] = '/'.ggnb_get_string_between(ggnb_get_protected_property(ggnb_get_protected_property(ggnb_get_protected_property(ggnb_get_protected_property($self, 'clientTemplate'), 'config'),'configFile'),'path'),'/','/templates/');
		$info['whmcs_url'] = App::getSystemUrl();
		$info['admin_path'] = ggnb_get_protected_property($self, 'customadminpath');
        $info['admin_url'] = $info['whmcs_url'].$info['admin_path'];
		if((string)$type===(string)'all'){
			return $info;
		}
        return $info[$type];
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
			$message .= '<p style="color: green"><i class="fas fa-check-square"></i> Você está executando a versão mais recente do módulo.</p>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';
		}
		if( $available_version_int > $module_version_int ){
			$message .= '<p style="font-size: 14px; color: red;"><i class="fas fa-exclamation-triangle"></i> Atualização disponível, verifique a <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">versão '.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';
		}
		if( $available_version_int < $module_version_int ){
			$message = '<p style="font-size: 14px; color: orange;"><i class="fas fa-exclamation-triangle"></i> Você está executando uma versão Beta desse módulo.<br>Baixar versão estável: <a style="color:#CC0000;text-decoration:underline;" href="https://gofas.net/?p='.$page_id.'" target="_blank">v'.$available_version.'</a>';
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';
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
	function ggnb_setup_admin($key=NULL){
	foreach( Capsule::table('tblconfiguration')->where('setting','=','ggnb_version')->get(['value']) as $version_ ){
		$version		= json_decode($version_->value, true);
		if($key){
			$admin			= $version['admin'][$key];
		}
		else{
			$admin			= $version['admin'];
		}
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
if(!function_exists('ggnb_reset_local_version')){
	function ggnb_reset_local_version(){
        try{
	        Capsule::table('tblconfiguration')->where('setting','=','ggnb_version')->delete();
	        return 'sucess';
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
}}
/**
 * 
 * Start Gateway Functions
 * 
 */
 if(!function_exists('gofasgerencianetboleto_config')){
	function gofasgerencianetboleto_config(){
		$module_version = '3.9.0';
		$module_version_int = (int)preg_replace("/[^0-9]/", "", $module_version);
		$module_page	= '7893';
		$verify_install = ggnb_verifyInstall();
		$whmcs_url = ggnb_whmcs_url();
		$check_updates = ggnb_verify_module_updates($module_page,$whmcs_url['admin_url'],$module_version);
		if($_REQUEST['resetversion'] === 'gofasgerencianetboleto'){ #9
			ggnb_reset_local_version();
			header_remove();
			header("Location: ".$whmcs_url['admin_url'].'/configgateways.php?manage=gofasgerencianetboleto#m_gofasgerencianetboleto',true,303);
			exit;
		}
		//echo '<pre>',print_r($sysinfo),'</pre>';
		foreach( Capsule::table('tblconfiguration')
		->where('setting','=','Version')
		->get(['value']) as $data1 ){
			$Version = $data1->value;
		}
		$whmcs_version=(int)preg_replace('/[^\da-z]/i', '',  ggnb_get_string_between('#'.$Version, '#', '-'));
		if($whmcs_version<861){
			return [
				'FriendlyName' => [
					'Type' => 'System',
					'Value' => 'Gofas EFÍ Boleto',
				],
				'separator_1' => [
					'Description' => '
					<div class="ggnb_separator" style="padding: 1px 15px 9px;">
					'.(string)ggnb_decrypt($check_updates['check']).'
						<div style="margin-left: 10px;">
							<h4 style="padding-top: 5px; color: red;">Gofas EFÍ Boleto para WHMCS v'.$module_version.' | requer WHMCS versão 8.6.1 ou superior</h4>
							'.$check_updates['message'].'
							'.ggnb_file_exists_check('/includes/hooks/gofasgerencianetboleto.php').'
						</div>
					</div>',
				],
				'footer' => [
					'Description' => '<div class="ggp_section">
					<p>&copy; '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/?p=14641#changelog">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=14641">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte" href="https://gofas.net/foruns/">Suporte</a>.</p>
					<p style="font-size: 11px;">
					Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net/?p=9340">contrato de licença de uso de software</a>.
					</p>
					'.$check_updates['message'].'
					</div>',
				],
			];
		}
		foreach(Capsule::table('tblpaymentgateways')->where('gateway','=','gofasgerencianetboleto')->get() as $set ){
			$ggnb_settings[$set->setting] = $set->value;
		}
	
		$customfields = array();
		$customfields[] = '';
		foreach( Capsule::table('tblcustomfields') -> where( 'type', '=', 'client') -> get( array( 'fieldname', 'sortorder', 'id') ) as $customfield ){
			$customfield_id		= $customfield->id;
			$customfield_name	= $customfield->fieldname;
			$customfields[]		= $customfield_id.' - '.$customfield_name;
		}
		$tblticketdepartments = array();
		$tblticketdepartments[] = '';
		foreach( Capsule::table('tblticketdepartments') -> get() as $tblticketdepartments_ ){
			$tblticketdepartments_id			= $tblticketdepartments_->id;
			$tblticketdepartments_name			= $tblticketdepartments_->name;
			$tblticketdepartments[]				= $tblticketdepartments_id.' - '.$tblticketdepartments_name;
		}
		
		// Options count
		$opt_num = 1;
		/// Display Options	
		$renderize = array(
			// Nome de exibição amigável para o gateway
			'FriendlyName' => array(
				'Type' => 'System',
				'Value' => 'Gofas EFÍ Boleto',
				'Size' => '40',
			),
			/*
			 * Separador 1
			 * Configurações Básicas
			 *
			*/
			'separator_1' => array(
				'Description' => '
				<style type="text/css">
				.ggnb_section {
					background: #dcdcdc; padding: 10px 15px 1px;
				}
				.ggnb_separator {
					background: #dcdcdc; padding: 1px 15px 1px;
				}
				.ggnb_separator p {
					font-size: 12px;
						margin: 0px 0px 5px 0px;
				}
				.ggnb_required {
					color: #CC0000;
					font-size: 20px;
					line-height: 0;
				}
				.ggnb_required_txt {
					color: #CC0000;
				}
				.ggnb_optional_txt {
					color: #02bb04;
				}
				#Payment-Gateway-Config-gofasgerencianetboleto td.fieldlabel {
					background-color: #fff;
					text-align: right;
					vertical-align: text-top;
				}
				#Payment-Gateway-Config-gofasgerencianetboleto td.input-inline {
					display: inline-block;
					float: left;
					clear: left;
				}
				#Payment-Gateway-Config-gofasgerencianetboleto td.fieldarea input {
					margin-right: 5px;
				}
				</style>
				<div class="ggnb_separator">
				
				'.ggnb_decrypt($check_updates['check']).'
					<div style="padding: 10px 10px 20px 10px;">
						<h4 style="padding-top: 5px;">Módulo EFÍ Boleto para WHMCS v'.$module_version.'</h4>
						'.$check_updates['message'].'
						'.ggnb_file_exists_check('/includes/hooks/gofasgerencianetboleto.php').'
						<h5>Antes de iniciar a configuração, lembre-se de:</h5>
						<p>- Criar um <a style="text-decoration:underline;" target="_blank" href="'.$whmcs_url['admin_url'].'configcustomfields.php">campo personalizado de cliente</a> para CPF e/ou CNPJ, ou se preferir, criar dois campos distintos, um campo apenas para CPF e outro campo para CNPJ. O módulo identifica os campos do perfil do cliente automaticamente.</p>
						<p>- Criar uma Aplicação e obter as credencians <i>Client_ID</i> e <i>Client_Secret</i> da <a style="text-decoration: underline;" target="_blank" href="https://sistema.gerencianet.com.br/api/introducao">API EFÍ</a>. Veja <a style="text-decoration: underline;" target="_blank" href="https://s3.amazonaws.com/uploads.gofas.me/wp-content/uploads/2021/02/07004154/Gerencianet_api.png">aqui</a> onde encontrar.</p>
						<p><a style="text-decoration:underline;" target="_blank" href="https://gofas.net/ggnb/">Documentação do módulo</a>.<br></p>	
					</div>
		
				</div>',
			),
			// Client ID
			'clientid' => array(
				'FriendlyName' => $opt_num++.'- Client_Id Produção<span class="ggnb_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="ggnb_required_txt">(Obrigatório)</span>',
			),
			// Client Secret
			'clientsecret' => array(
				'FriendlyName' => $opt_num++.'- Client_Secret Produção<span class="ggnb_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="ggnb_required_txt">(Obrigatório)</span>',
			),
			// Client ID Sandbox
			'clientidsandbox' => array(
				'FriendlyName' => $opt_num++.'- Client_Id Desenvolvimento<span class="ggnb_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="ggnb_required_txt">(Obrigatório)</span>',
			),
			// Client Secret Sandbox
			'clientsecretsandbox' => array(
				'FriendlyName' => $opt_num++.'- Client_Secret Desenvolvimento<span class="ggnb_required">*</span>',
				'Type' => 'text',
				'Size' => '40',
				'Default' => '',
				'Description' => '<span class="ggnb_required_txt">(Obrigatório)</span>',
			),
			// Testar?
			'sandbox' => array(
				'FriendlyName' => $opt_num++.'- Modo de Testes / Sandbox',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => 'Marque essa opção para utilizar a API EFÍ em modo "Desenvolvimento" (modo de testes). <a style="text-decoration: underline;" href="https://sistema.gerencianet.com.br/api/introducao" target="_blank">Painel da API</a>.',
			),
			// Log
			'log' => array(
				'FriendlyName' => $opt_num++.'- Salvar Logs',
				'Type' => 'yesno',
				'Default' => 'no',
				'Description' => 'Salva informações de diagnóstico em <a target="_blank" style="text-decoration: underline;" href="'.$ggnbwhmcsadminurl.'systemmodulelog.php">Utilitários > Logs > Log de Módulo</a>. Para funcionar, antes é necessário ativar o debug de módulo clicando em "Ativar Log de Debug". <a target="_blank" style="text-decoration: underline;" href="'.$ggnbwhmcsadminurl.'systemmodulelog.php">VER LOG</a>.',
			),
			'fee' => array(
				'FriendlyName'      => $opt_num++.'- Valor da tarifa por Boleto',
				'Type'              => 'text',
				'Size' => '10',
				'Description'       => '<span class="ggnb_optional_txt">(Opcional)</span> Insira o valor da comissão paga à EFÍ a cada Boleto com pagamento confirmado. Essa informação servirá para calcular e preencher o campo "Taxas" (fee) da lista de transações do WHMCS. Use ponto(.) para separar casas decimais, ex.: 1.5',
			),
			'minimunamount' => array(
				'FriendlyName' => $opt_num++.'- Valor mínimo do Boleto',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '5',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Insira o valor mínimo da fatura para permitir pagamento via Boleto, 5 equivale à R$ 5,00. O valor mínimo padrão é R$5,00.',
			),
			/*
			 * Separador 2
			 * Ações Automatizadas
			 *
			*/
			'separator_2' => array(
				'Description' => '
				<div class="ggnb_separator">
					<h4>Ações Automatizadas</h4>
					'.ggnb_file_exists_check('/includes/hooks/gofasgerencianetboleto.php',NULL,$error_msg='<br><span style="color: red;border-left: 2px solid red;padding-left: 5px;">Atenção! <b>Instale o hook que acompanha o módulo</b> para ativar a confirmação de pagamento automática - <a style="text-decoration:underline;color:red" target="_blank" href="https://gofas.net/ggnb/#instalation">Saiba mais </a>&#10138;.</span><br><br>').'
                    
				</div>',
			),
    		'verifypaymentsat' => array(
                'FriendlyName'      => $opt_num++.'- Horário da verificação',
                'Type'              => 'text',
    			'Size'				=> '2',
    			'Default' 			=> '05:00',
                'Description'       => 'Horário em que módulo deve verificar o status de pagamento dos boletos e confirmar o pagamento das faturas. Formato: HH:MM',
            ),
    		'maxinvoicespercheck' => array(
                'FriendlyName'      => $opt_num++.'- Verificações por requisição',
                'Type'              => 'text',
    			'Size'				=> '2',
    			'Default' 			=> '100',
                'Description'       => 'Número máximo de transações consultadas por vez. As consultas à API são realizadas em fila onde todas as faturas a verificar são divididas em lotes, cuja quantidade é o valor definido nesse campo.',
            ),
			'billetonemail' => array(
					'FriendlyName' => $opt_num++.'- Informações do Boleto no email',
					'Type' => 'yesno',
					'Default' => 'yes',
					'Description' => 'Adiciona link, linha digitável, vencimento e outras informações do boleto no corpo dos emails de faturas. Essa opção faz o módulo gerar os boletos no momento em que a fatura é gerada, (do contrário o Boleto é gerado no 1º acesso à Fatura). <a style="font-weight: bold;text-decoration:underline;" target="_blank" href="https://gofas.net/?p=7893#mergetags">Veja aqui a lista de tags de mesclagem disponíveis</a> .',
				),
			
			// Replace Invoice link for Billet link on email
			'linkbilletonemail' => array(
					'FriendlyName' => $opt_num++.'- Substituir link da fatura por link do boleto',
					'Type' => 'yesno',
					'Description' => 'Substitui o URL da Fatura pelo URL do Boleto nos emails de "Nova Fatura" (tag <code>{$invoice_link}</code> do template de email <i>Invoice Created</i>).',
				),
			
			// Altera a data ou cancela e cria um novo boleto
			'cancelbilletoncancelinvoice' => array(
					'FriendlyName' => $opt_num++.'- Cancelar Boleto ao cancelar Fatura',
					'Type' => 'yesno',
					'Default' => 'yes',
					'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Cancela o Boleto associado à uma fatura quando essa fatura é cancelada no WHMCS.',
				),
			
			// Altera a data ou cancela e cria um novo boleto
			'cancelbillet' => array(
				'FriendlyName' => $opt_num++.'- Cancelar Boleto Vencido',
				'Type' => 'yesno',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Cancela o Boleto gerado anteriormente pela Fatura antes de gerar novo Boleto/segunda via. Sem essa opção definida, o módulo altera a data de vencimento do Boleto vencido associado à Fatura, ou gera um novo boleto mas não altera o status do Boleto anterior.',
			),
	
			// Dias + vencimento
			'diasparavencimento' => array(
				'FriendlyName'      => $opt_num++.'- Dias adicionais para nova data de vencimento de Boletos',
				'Type'              => 'text',
				'Size'				=> '10',
				'Description'       => '<span class="ggnb_optional_txt">(Opcional)</span> Número de dias que serão somados a data do vencimento do Boleto ao gerar segunda via ou atualizar um boleto vencido. Essa opção aplica-se apenas a Faturas vencidas, faturas que ainda não venceram sempre irão gerar Boletos com a mesma data de vencimento da Fatura.',
			),
			
			// Notificar admin sobre erros
			'emailonerror' => array(
				'FriendlyName' => $opt_num++.'- Notificar admins do WHMCS sobre erros',
				'Type'          => 'dropdown',
				'Default' 		=> '0',
				'Options'       => $tblticketdepartments,
				'Description' => 'Escolha o departamento de suporte que receberá notificação por email quando houver erros ao gerar o boleto. Esse recurso possibilita uma tomada de ação antes que o cliente contacte o suporte ou desista da compra, como por exemplo, quando o boleto não é gerado por um erro de cadastro do cliente.',
			),
			/*
			 * Separador 3
			 * Campos Personalizados dos Clientes
			 *
			*/
			'separator_3' => array(
				'Description' => '
				<div class="ggnb_separator">
					<h4>Campos Personalizados</h4>
					<p>Certifique-se de ter criado e configurado corretamente os <i title="WHMCS > Opções > Campos personaliz. Clientes" style="cursor: help;">Campos Personalizados de Clientes</i> que se aplicam às regras de negócio da sua empresa. Observações importantes:<br>
					<ul>
						<li>Apenas o campo CPF é obrigatório;<br></li>
						<li>Você pode configurar apenas um campo personalizado para CPF e CNPJ, ou dois campos, um para cada tipo de documento;<br></li>
						<li>O módulo detecta automaticamente os campos CPF e CNPJ pelo nome do campo personalizado.<br></li>
					</ul>
					</p>
				</div>',
			),
	
			// customfield Desconto- Tipo
			'custom_discount_type' => array(
				'FriendlyName' => $opt_num++.'- Tipo de Desconto Personalizado',
				'Type'          => 'dropdown',
				'Default' 		=> '0',
				'Options'       => $customfields,
				'Description' => 'Selecione o Campo Personalizado de Clientes que define o tipo de desconto personalizado em R$(Reais) e %(Porcentagem). <a style="text-decoration: underline;" href="https://s3.amazonaws.com/uploads.gofas.me/wp-content/uploads/2017/04/WHMCS_-_Campos_Personalizados_dos_Clientes.png" target="_blank">Veja aqui</a> como configurar os <i title="WHMCS > Opções > Campos personaliz. Clientes" style="cursor: help;">Campos Personalizados de Clientes</i>.',
			),
			// customfield Desconto- Valor
			'custom_discount_value' => array(
				'FriendlyName' => $opt_num++.'- Valor do Desconto Personalizado',
				'Type'          => 'dropdown',
				'Default' 		=> '0',
				'Options'       => $customfields,
				'Description' => 'Escolha o <i title="WHMCS > Opções > Campos personaliz. Clientes" style="cursor: help;">Campo Personalizado de Clientes</i> usado para aplicar descontos diferenciados para clientes específicos. Formato: Decimal, separado por ponto. Maior ou igual a 0.00 e menor que o valor da cobrança.',
			),
	
			/*
			 * Separador 4
			 * Descontos e Acréscimos
			 *
			*/
			
			'separator_4' => array(
				'Description' => '
				<div class="ggnb_separator">
					<h4>Descontos e Acréscimos</h4>
				</div>',
			),
			
			// Desconto ou taxa
			'descontooutaxa'      => array(
				'FriendlyName'  => $opt_num++.'- Desconto ou Taxa adicional',
				'Type'          => 'dropdown',
				'Options'       => array(
					'1'         => 'Desconto',
					'2'         => 'Taxa adicional',
				),
				'Description'   => '<span class="ggnb_optional_txt">(Opcional)</span> Escolha de deseja oferecer desconto ou acrescentar taxa para pagamentos via Boleto.',
			),
			// % ou R$
			'tipodescontooutaxa'      => array(
				'FriendlyName'  => $opt_num++.'- Tipo de desconto/taxa',
				'Type'          => 'dropdown',
				'Options'       => array(
					'1'         => '% (Porcentagem)',
					'2'         => 'R$ (Reais)',
				),
				'Description'   => '<span class="ggnb_optional_txt">(Opcional)</span> Escolha se o desconto ou taxa será em Porcentagem ou em Reais',
			),
			
			// valor do desconto/taxa
			'valordescontooutaxa' => array(
				'FriendlyName' => $opt_num++.'- Valor do Desconto ou Taxa',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Valor que será abatido ou acrescentado ao valor total das faturas.',
			),
			
			// dias antes do vencimento para aplicar desconto
			'diasantesvencadddesconto' => array(
				'FriendlyName' => $opt_num++.'- Validade do desconto',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '0',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Defina o máximo de dias antes do vencimento para aplicar desconto.<br>
				- Deixe em branco para aplicar desconto mesmo após o vencimento;<br>
				- Insira 0 (zero) para aplicar desconto a Boletos gerados até a data de vencimento da Fatura;<br>
				- Insira de 1 a X, sendo X = <span style="cursor: help;" title="Opções > Configurações Gerais > Configurações de Automação">ao nº de dias antes do vencimento, que as Faturas são geradas</span> para aplicar desconto apenas a Boletos gerados entre 1 e X dias antes da data de vencimento da Fatura.<br> 
				Ao ativar essa opção a data de vencimento do Boleto será igual a data de vencimento da Fatura - (menos) o nº de dias definido nesse campo. Será adicionada a instrução ao caixa "não aceitar pagamento após o vencimento".',
			),
			
			// Multa por atraso
			'multa' => array(
				'FriendlyName' => $opt_num++.'- Multa após o vencimento',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Multa cobrada após o vencimento (máximo 10%). Use ponto(.) para separar casas decimais, ex.: 1.5',
			),
			// Multa por atraso
			'juros' => array(
				'FriendlyName' => $opt_num++.'- Juros após o vencimento',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Juros por dia cobrados após o vencimento (Mínimo de 0.001 e máximo de 0.33). Use ponto(.) para separar casas decimais.',
			),
			
			/*
			 * Separador 5
			 * Exibição da Fatura e do Boleto
			 *
			*/
			
			'separator_5' => array(
				'Description' => '
				<div class="ggnb_separator">
					<h4>Exibição da Fatura e do Boleto</h4>
				</div>',
			),
			// Linha digitável
			'showbarcode' => array(
				'FriendlyName' => $opt_num++.'- Exibir linha digitável',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Exibe a linha digitável/código de barras do Boleto, abaixo do botão "visualizar boleto".',
			),
			// Data de vencimento
			'showduedate' => array(
				'FriendlyName' => $opt_num++.'- Exibir data de Vencimento',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Exibe a data de vencimento do Boleto na fatura, abaixo do botão "visualizar boleto".',
			),
			// Exibir informação sobre Desconto / Taxa na fatura
			'exibedescontooutaxa' => array(
				'FriendlyName' => $opt_num++.'- Exibir Desconto / Taxa na fatura',
				'Type' => 'yesno',
				'Default' => 'yes',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Assinale se desejar informar sobre o Desconto ou Taxa na fatura.',
			),
			
			// Redirecionar para o link do boleto?
			'redirecttobillet' => array(
				'FriendlyName' => $opt_num++.'- Redirecionar para o Boleto',
				'Type' => 'yesno',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Redireciona o cliente diretamente para o URL do boleto ao acessar a fatura. Adicione &redirectToBillet=false ao URL da fatura para desativar em acessos específicos, exemplo: https://whmcs.gofas.net/viewinvoice.php?id=4800<b>&redirectToBillet=false</b>. Nos templates de email a mergetag ficaria desta forma: <b>{$invoice_link}&redirectToBillet=false</b>',
			),
			
			// Botão "Visualizar boleto"
			'paybutton' => array(
				'FriendlyName' => $opt_num++.'- Imagem do botão "Visualizar Boleto"',
				'Type' => 'text',
				'Size' => '90',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span><br/>Insira o URL da imagem que será usada como botão "Visualizar Boleto" (tamanho recomendado: 160x43px).',
			),
			// Message
			'message' => array(
				'FriendlyName' => $opt_num++.'- Mensagem ao cliente',
				'Type' => 'text',
				'Size' => '60',
				'Default' => 'Acesse '.$whmcs_url['whmcs_url'].' para gerar 2ª via.',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Inclui no boleto uma mensagem personalizada para o cliente.',
			),
			/*
			 * Separador 6
			 * Instruções do Boleto
			 *
			*/
			
			'separator_6' => array(
				'Description' => '
				<div class="ggnb_separator">
					<h4>Instruções do Boleto</h4>
					<p>O texto nas linhas de instrução do Boleto devem ser direcionadas ao caixa do banco, nunca para mensagens direcionadas ao cliente, para essa funcionalidade existe o campo acima "Mensagem ao cliente".</p>
					<p>As instruções do Boleto configuradas abaixo serão ignoradas e substituídas pelas instruções padrão da API EFÍ, quando multa e/ou juros estiverem ativos.</p>
				</div>',
			),
			// Instruções 1
			'instruction1' => array(
				'FriendlyName' => $opt_num++.'- 1ª Instrução do boleto',
				'Type' => 'text',
				'Size' => '60',
				'Default' => 'Após vencimento aceitar pagamento somente no banco emissor.',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Insira a 1ª linha de instruções do Boleto.',
			),
			// Instruções 2
			'instruction2' => array(
				'FriendlyName' => $opt_num++.'- 2ª Instrução do boleto',
				'Type' => 'text',
				'Size' => '60',
				'Default' => 'Não cobrar juros após o vencimento.',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Insira a 2ª linha de instruções do Boleto.',
			),
			// Instruções 3
			'instruction3' => array(
				'FriendlyName' => $opt_num++.'- 3ª Instrução do boleto',
				'Type' => 'text',
				'Size' => '60',
				'Default' => 'Não cobrar multa após o vencimento.',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Insira a 3ª linha de instruções do Boleto.',
			),
			// Instruções 4
			'instruction4' => array(
				'FriendlyName' => $opt_num++.'- 4ª Instrução do boleto',
				'Type' => 'text',
				'Size' => '60',
				'Default' => 'Aceitar apenas pagamento em dinheiro.',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Insira a 4ª linha de instruções do Boleto.',
			),
		);
		$footer = array('footer' => array(
				'Description' => '<div class="ggnb_section">
				<p>&copy; 2016 - '.date('Y').' <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net">Gofas.net</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Gofas.net" href="https://gofas.net/blog/">'.$module_version.'</a> | <a  style="text-decoration:underline;"target="_blank" title="↗ Documentação" href="https://gofas.net/?p=7893">Documentação</a> | <a style="text-decoration:underline;" target="_blank" title="↗ Fórum de Suporte Gratuito" href="https://gofas.net/?p=7856">Fórum de Suporte Gratuito</a>.</p>
				<p style="font-size: 11px;">
				Ao utilizar esse módulo você concorda com nosso <a style="text-decoration:underline;" target="_blank" title="↗ Contrato de licença de uso de software" href="https://gofas.net?p=9340">contrato de licença de uso de software</a>.
				</p>
				'.$check_updates['message'].'
				</div>',
			),);
			if(file_exists(__DIR__.'/custom/config.php')){
				include __DIR__.'/custom/config.php';
				if(is_array($ggnb_custom_config) and is_array($renderize) and is_array($footer)){
					$separator_custom = ['separator_custom' => [
						'Description' => '
							<div class="ggnb_separator">
								<h4>Configurações personalizadas</h4>
							</div>',
						],
					];
					$ggnb_config = array_merge($renderize,$separator_custom,$ggnb_custom_config,$footer);
				}
			}
			if(!file_exists(__DIR__.'/custom/config.php') || !$ggnb_custom_config and (is_array($renderize) and is_array($footer))){
				$ggnb_config = array_merge($renderize,$footer);
			}
			return $ggnb_config;
	}}
if(!function_exists('gofasgerencianetboleto_link')){
function gofasgerencianetboleto_link($params){
	$devFee = 0;
	$system_url = ggnb_whmcs_url('whmcs_url');
	$returnUrl = $system_url.'/modules/gateways/gofasgerencianetboleto.php';

	$langPayNow = $params['langpaynow'];
	$moduleDisplayName = $params['name'];
	$moduleName = $params['paymentmethod'];
	if( $params['sandbox'] ){
		$client_id = $params['clientidsandbox'];
		$client_secret = $params['clientsecretsandbox'];
		$api_mode = 'sandbox';
		$api_url = 'https://sandbox.gerencianet.com.br/v1/';

	} elseif(!$params['sandbox']){
		$client_id = $params['clientid'];
		$client_secret = $params['clientsecret'];
		$api_mode = 'live';
		$api_url = 'https://api.gerencianet.com.br/v1/';
	}
	$emailonError = $params['emailonerror'];
	$showDueDate = $params['showduedate'];
	$showBarCode = $params['showbarcode'];
	$requireCNPJandCPF = $params['requirecnpjandcpf'];
	$cancelBillet = $params['cancelbillet'];
	$customfCPF = $params['customfieldcpf'];
	$customfCNPJ = $params['customfieldcnpj'];
	$fine = (float)$params['multa'] * 100;
	$interest = (float)$params['juros'] * 1000;

	// Dias adicionais à Data de vencimento
	if( $params['diasparavencimento'] ){
		$diasParaVencimento = '+'.$params['diasparavencimento'].' days';

	} elseif( $params['diasparavencimento'] == '0'){
		$diasParaVencimento = 'zero';
	}
	elseif( !$params['diasparavencimento'] ){
		$diasParaVencimento = '+1 day';
	}
	else {
		$diasParaVencimento = false;
	}
	if($params['message']){ $message = $params['message'];
	}
	elseif(!$params['message'] || empty($params['message'])){
		$message = 'Acesse '.$system_url.' para gerar 2ª via.';
	}
	if( $params['minimunamount'] ){
		$minimunAmount = $params['minimunamount'];
	}
	elseif( !$params['minimunamount'] || $params['minimunamount'] < 5 ){
		$minimunAmount = '5.00' ;
	}
	if($params['paybutton']){
		$payButton = '<img alt="Visualizar Boleto" src="'.$params['paybutton'].'">';
	}elseif(!$params['paybutton']){
		$payButton = 'Visualizar Boleto';
	}
	// Instruções
	if($params['instruction1']){
		$instruction1 = $params['instruction1'];
	}elseif(!$instruction1){
		$instruction1 = 'Sr. Caixa, após vencimento aceitar somente no banco emissor.';
	}
	if($params['instruction2']){
		$instruction2 = $params['instruction2'];
	}elseif(!$instruction2){
		$instruction2 = 'Sr. Caixa, não cobrar juros após o vencimento.';
	}
	if($params['instruction3']){
		$instruction3 = $params['instruction3'];
	}elseif(!$instruction3){
		$instruction3 = 'Sr. Caixa, não cobrar multa após o vencimento.';
	}
	if($params['instruction4']){
		$instruction4 = $params['instruction4'];
	}elseif(!$instruction4){
		$instruction4 = 'Sr. Caixa, aceitar apenas pagamento em dinheiro.';
	}
	// Instruções ao caixa
	$instructions = array(
		(string)$instruction1,
		(string)$instruction2,
		(string)$instruction3,
		(string)$instruction4,
		);

	if( $fine and $interest){
		$configurations = array(
				'fine' => $fine,
				'interest' => $interest,
			);
	} elseif( $fine and !$interest ){
		$configurations = array(
				'fine' => $fine,
				//'interest' => $interest,
			);
	} elseif( !$fine and $interest ){
		$configurations = array(
				//'fine' => $fine,
				'interest' => $interest,
			);
	} elseif( !$fine and !$interest ){
		$configurations = false;
	}

	// Parâmetros da fatura
	$invoice_id = $params['invoiceid'];
	$getinvoiceid['invoiceid'] = $invoice_id;
	$GetInvoiceResults = localAPI('getinvoice',$getinvoiceid,$params['admin']);

	$invoice_duedate = $GetInvoiceResults['duedate']; // Data de vencimento da fatura

	if( $invoice_duedate > date('Y-m-d') ){
		$billet_duedate = $invoice_duedate;

	}
	elseif( $invoice_duedate === date('Y-m-d') ){
		$billet_duedate = date('Y-m-d', strtotime('+1 day'));

	}
	elseif( $invoice_duedate < date('Y-m-d') and !$diasParaVencimento ){
		$billet_duedate = date('Y-m-d', strtotime('+1 day')); // Se fatura já venceu, data de vencimento do boleto = Hoje + 1 dia

	}
	elseif( $invoice_duedate < date('Y-m-d') and $diasParaVencimento and $diasParaVencimento !== 'zero'){
		$billet_duedate = date('Y-m-d', strtotime( $diasParaVencimento )); // Se fatura já venceu, data de vencimento do boleto = Hoje + X dia(s)

	}
	elseif( $invoice_duedate < date('Y-m-d') and $diasParaVencimento and $diasParaVencimento === 'zero'){
		$billet_duedate = date('Y-m-d', strtotime('+1 day')); // Se fatura já venceu, data de vencimento do boleto = Hoje
	}
	$invoiceTotal =	$GetInvoiceResults['total'];
	$invoiceCredit =	(int)($GetInvoiceResults['credit'] * 100);

	// Parâmetros das transações associadas à Fatura
	// Parâmetros das transações associadas à Fatura
	foreach( Capsule::table('gofasgerencianetboleto')->where('invoice_id','=',$invoice_id)->where('api_mode','=',$api_mode)->get(['charge_id']) as $charge_id_local){
		if($charge_id_local->charge_id){
			$trans_id = $charge_id_local->charge_id;
		}
		else {
			$trans_id = false;
		}
	}
	// Serviços/produtos relacionados à fatura
	$invoiceItemsItem = $GetInvoiceResults['items']['item'];

	// Parametros do Cliente
	$user_id = $params['clientdetails']['id'];
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	//$phone = preg_replace('/[^0-9]/', '', $params['clientdetails']['phonenumber']);
	$phone = preg_replace('/[^\da-z]/i', '', $params['clientdetails']['phonenumber']);

	if( $params['clientdetails']['companyname'] ){
		$corporateName = $params['clientdetails']['companyname'];
	} elseif(!$params['clientdetails']['companyname']){
		$corporateName = $firstname . ' ' . $lastname;
	}
	/**
	 *
	 * Determine custom fields id
	 *
	 */
	//$customfields = array();
	foreach( Capsule::table('tblcustomfields') -> where( 'type', '=', 'client')  -> get( array( 'fieldname', 'id') ) as $customfield ){
		$customfield_id = $customfield->id;
		$customfield_name = ' '.strtolower( $customfield->fieldname );
		// cpf
		if( strpos( $customfield_name, 'cpf') and !strpos( $customfield_name, 'cnpj') ){
			foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $user_id ) -> get( array( 'value') ) as $customfieldvalue ){
				$cpf_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
			}
		}	
		// cnpj
		if( strpos( $customfield_name, 'cnpj') and !strpos( $customfield_name, 'cpf') ){
			foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $user_id ) -> get( array( 'value') ) as $customfieldvalue ){
				$cnpj_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
			}
		}
		// cpf + cnpj
		if( strpos( $customfield_name, 'cpf') and strpos( $customfield_name, 'cnpj') ){
			foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $customfield_id ) -> where( 'relid', '=', $user_id ) -> get( array( 'value') ) as $customfieldvalue ){
				$cpf_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
				$cnpj_customfield_value = preg_replace("/[^0-9]/", "", $customfieldvalue->value);
			}
		}
	}
	if(strlen($cpf_customfield_value) === 10){
		$cpf = '0'.$cpf_customfield_value;

		if(strlen($cnpj_customfield_value) === 13){

			$cnpj = '0'.$cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif(strlen($cnpj_customfield_value) === 14){
			$cnpj = $cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif( !$cnpj_customfield_value || strlen($cnpj_customfield_value) !== 14 || strlen($cnpj_customfield_value) !== 13){
			$cnpj = false;		
			$customer = array(
				'name' => $firstname.' '.$lastname,
				'cpf' => $cpf,
				'phone_number' => $phone,
			);

		}
	}
	elseif(strlen($cpf_customfield_value) === 11){
		$cpf = $cpf_customfield_value;

		if(strlen($cnpj_customfield_value) === 13){
			$cnpj = '0'.$cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif(strlen($cnpj_customfield_value) === 14){
			$cnpj = $cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif( !$cnpj_customfield_value || strlen($cnpj_customfield_value) !== 14 || strlen($cnpj_customfield_value) !== 13){
			$cnpj = false;
			$customer = array(
				'name' => $firstname.' '.$lastname,
				'cpf' => $cpf,
				'phone_number' => $phone,
			);
		}
	}
	elseif(strlen($cpf_customfield_value) === 13){
		$cpf = false; 
		$cnpj = '0'.$cpf_customfield_value;
		$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

		if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
		} elseif( !$requireCNPJandCPF ){
			$customer = array(
				'phone_number' => $phone,
				'juridical_person' => $juridical_data,
			);
		}
	}
	elseif(strlen($cpf_customfield_value) === 14){
		$cpf  = false;
		$cnpj = $cpf_customfield_value;
		$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

		if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
		} elseif( !$requireCNPJandCPF ){
			$customer = array(
				'phone_number' => $phone,
				'juridical_person' => $juridical_data,
			);
		}
	}
	elseif(!$cpf_customfield_value || strlen($cpf_customfield_value) !== 10 || strlen($cpf_customfield_value) !== 11 || strlen($cpf_customfield_value) !== 13 || strlen($cpf_customfield_value) !== 14 ){
		if(strlen($cnpj_customfield_value) === 13){

			$cnpj = '0'.$cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);
			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif(strlen($cnpj_customfield_value) === 14){
			$cnpj = $cnpj_customfield_value;
			$juridical_data = array(
	  			'corporate_name' => $corporateName,
	  			'cnpj' => $cnpj,
			);

			if( $requireCNPJandCPF ){
				$customer = array(
					'name' => $firstname.' '.$lastname,
					'cpf' => $cpf,
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			} elseif( !$requireCNPJandCPF ){
				$customer = array(
					'phone_number' => $phone,
					'juridical_person' => $juridical_data,
				);
			}
		} elseif( !$cnpj_customfield_value || strlen($cnpj_customfield_value) !== 14 || strlen($cnpj_customfield_value) !== 13){
			$cnpj = false;
			$cpf = false;
		}
	}
	// Dados pessoa física
	$customer_pf = array(
		'name' => $firstname.' '.$lastname,
		'cpf' => $cpf,
		'phone_number' => $phone,
	);


	// Verify if generate billet
	// CSS da fatura
	$css = '<style type="text/css">a, a:hover {cursor: pointer;}.ggnbp {font-size:12px;margin: 0;}.ggnbspan{font-size:12px;}span.ggnberror {color: red;}';
	$css		.= '
		.debug {
			padding:5px;
		}
		.debug .ok {
			color:#5cb85c;
			font-weight: 600;
		}
		.debug a,
		.debug p a {
			text-decoration: underline;
		}
		.erro,
		.debug .erro {
			color: red;
		}
		#ggnbclic {
			font-size:13px; font-weight: 700;color: #458ec9;
		}
		#linDig {
			font-size: 11px; border-bottom: 1px solid #9E9E9E; max-width: 360px; margin: 0 auto; padding: 0px 0px 10px 0px;
		}
		#ggnbbilletinfo {
			text-align: right; max-width: 300px; margin: 10px auto;
		}
		div#ggnbbilletinfo p {
	    	line-height: 1;
		}';
	if( !$params['paybutton'] ){
		$css .= '
			a#ggnbviewbillet {
				background: #1992c6;
				color: #fff;
				border:none;
				padding:10px 20px;
				position: relative;
				top: 8px;
				cursor:pointer;
			}
			a#ggnbviewbillet:hover, a#ggnbviewbillet:active {
				background:#20b1ef;
				text-decoration: none;
				cursor: pointer;
			}
		</style>';
	}
	if($params['paybutton']){
		$css .= '</style>';
	}
	/*
	 *
	 * Desconto / Tarifa
	 *
	*/
	$ItEm = array();

	// Define desconto personalizado 
	foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $params['custom_discount_type'] ) -> where( 'relid', '=', $user_id ) -> get( array( 'value') ) as $customfieldvalue ){
		$custom_discount_type = $customfieldvalue->value;

	}
	foreach( Capsule::table('tblcustomfieldsvalues') -> where( 'fieldid', '=', $params['custom_discount_value'] ) -> where( 'relid', '=', $user_id ) -> get( array( 'value') ) as $customfieldvalue ){
		$custom_discount_value = $customfieldvalue->value;	
	}
	// Define desconto personalizado 
	if( $custom_discount_value and $custom_discount_type ){
		$discount_tax = 1;
		$discount_tax_value = $custom_discount_value;

		if( strpos( $custom_discount_type, '%') !== false ){
			$discount_tax_type = 1;
		}
		if( strpos( $custom_discount_type, '$') !== false ){
			$discount_tax_type = 2;
		}
	}
	else {
		$discount_tax = (int)$params['descontooutaxa']; // Define se é desconto ou taxa: 1 = desconto | 2 = taxa
		$discount_tax_type = (int)$params['tipodescontooutaxa']; // 1 = % | 2 = $  
		$discount_tax_value  = $params['valordescontooutaxa'];
	}
	$days_for_discount = (string)$params['diasantesvencadddesconto'];
	if( (int)$days_for_discount >= 1 ){

		$limit_date = new DateTime( $invoice_duedate );
		$limit_date->sub(new DateInterval("P".$days_for_discount."D"));
		$discount_valid_until = $limit_date->format('Y-m-d');

	}
	if( (string)$days_for_discount === "0"){
		$discount_valid_until = $invoice_duedate;
	}
	if( (string)$days_for_discount !== "0" and !$days_for_discount){
		$discount_valid_until =  date('Y-m-d');
	}
	// Define data de validade do desconto
	if( $discount_tax === 1 and $discount_valid_until and $discount_valid_until < date( 'Y-m-d') ){
		$discount_tax_value = 0;
	}
	if( $discount_tax === 1 and $discount_tax_value > 0 and $discount_valid_until and $discount_valid_until >= date( 'Y-m-d') ){
			unset($configurations);
			$billet_duedate = $discount_valid_until;

			// Instruções ao caixa
			$instructions = array(
				(string)'Sr. Caixa, por favor: Não aceitar pagamento após o vencimento.',
				(string)' ',
				(string)' ',
				(string)' ',
			);
	}
	// Exibir info sobre desconto na fatura
	$discount_tax_visible = $params['exibedescontooutaxa'];

	// Desconto do WHMCS / Itens com valor negativo
	$disc_item = array();
	foreach( $invoiceItemsItem as $Key => $Value){	
			if($Value['amount'] < 0 ){
				$ItEm_discount[] = array('name'=>$Value['description'],'amount'=>1,'value' => (int)($Value['amount'] * 100) );
			}
	}
	if( $invoiceCredit ){
		$ItEm_discount = array_merge($ItEm_discount, array(array('name' => 'Crédito aplicado à fatura','amount'=>1,'value' => -($invoiceCredit))));
	}
	// Cálculo de multa e juros
	if(!function_exists('ggnb_calculate_fine_interest')){
		function ggnb_calculate_fine_interest( $VALUE, $fine, $interest, $invoice_duedate){
			$today = date('Y-m-d');
			$due_date = date('Y-m-d', strtotime($invoice_duedate));
			$startTimeStamp = strtotime($due_date);
			$endTimeStamp = strtotime($today);
			$timeDiff = abs($endTimeStamp - $startTimeStamp);
			$due_days = $timeDiff/86400;  // 86400 seconds in one day
			// and you might want to convert to integer
			$due_days = intval($due_days);
			if( $fine and $invoice_duedate >= date('Y-m-d') ){
				$fine_value = false;
			}
			elseif( $fine and $invoice_duedate < date('Y-m-d')){
				$fine_value = ( ( $fine / 100 ) * $VALUE );
			}
			if( $interest and $invoice_duedate >= date('Y-m-d') ){
				$interest_value = false;
			}
			elseif( $interest and $invoice_duedate < date('Y-m-d') ){
				$interest_value = ( ($due_days * $interest) / 1000 ) * $VALUE;
			}
			if( $fine and $interest ){
				return array(
					'fine_value'=>$fine_value,
					'interest_value'=>$interest_value,
					'due_days' => $due_days,
				);
			}
			elseif( $fine and !$interest){
				return array(
					'fine_value'=>$fine_value,
					'due_days' => $due_days,
				);
			}
			elseif( !$fine and $interest){
				return array(
					'interest_value'=>$interest_value,
					'due_days' => $due_days,
				);
			}
			elseif( !$fine and !$interest){
				return false;
			}
		}
	}
	// Desconto em porcentagem %
	if( $discount_tax === 1 and $discount_tax_type === 1 and $discount_tax_value ){
		$discount_tax_valueRS = (int)((((float)$invoiceTotal / (float)100 )*(float)$discount_tax_value)*100);
		$invoice_amount__  = (int)($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ - $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Desconto de '.$discount_tax_value.'% (R$'.number_format($discount_tax_valueRS/100,  2, ',', '.').') para Boleto';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name'=>substr($ItEmValue['description'],0,255),'amount'=>1,'value'=>(int)($ItEmValue['amount']*100));
			}
		}
		$discount_value = (int)($discount_tax_value*100);
		$ItEm_discount = array_merge($ItEm_discount, array(array('name' => 'Desconto de '.$discount_tax_value.'% para pagamento com boleto','amount'=>1,'value' => -($discount_tax_valueRS))));

		if($whmcs_discount > 0 ){
			$discount = array(
				'type' => 'currency',
				'value' => ($discount_tax_valueRS + $whmcs_discount),
			);
		}
		else {
			$discount = array(
				'type' => 'percentage',
				'value' => $discount_value,
			);
		}
	}
	// Desconto Fixo R$
	elseif( $discount_tax === 1 and $discount_tax_type === 2 and $discount_tax_value ){
		$discount_tax_valueRS = (int)($discount_tax_value*100);
		//$invoice_amount  = (int)($invoiceTotal - $discount_tax_value) * 100;
		$invoice_amount__  = (int)($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ - $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Desconto de R$'.number_format($discount_tax_value,  2, ',', '.').' para Boleto </p>';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ),'amount'=>1,'value' => (int)($ItEmValue['amount']*100),);
			}
		}
		$discount_value = (int)($discount_tax_value * 100);
		$ItEm_discount = array_merge($ItEm_discount, array(array('name' => 'Desconto fixo para pagamento com boleto','amount'=>1,'value' => -($discount_value))));
		//$discount_name .= 'Desconto para pagamento por boleto';

		if($whmcs_discount > 0 ){
			$discount = array(
				'type' => 'currency',
				'value' => $discount_value + $whmcs_discount,
			
			);

		}
		else {
			$discount = array(
				'type' => 'currency',
				'value' => $discount_value,
			
			);
		}
	}
	// Tarifa em porcentagem %
	elseif( $discount_tax === 2 and $discount_tax_type === 1 and $discount_tax_value ){
		$discount_tax_valueRS = (int)((((float)$invoiceTotal / (float)100 )*(float)$discount_tax_value)*100);
		$invoice_amount__  = (int)($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ + $discount_tax_valueRS;
		$discount_tax_visible_message	.= '<p>Tarifa de '.$discount_tax_value.'% (R$'.number_format($discount_tax_valueRS/100,  2, ',', '.') . ') para Boleto</p>';	

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ), 'amount'=>1,'value' => (int)($ItEmValue['amount']*100),);
			}
		}
		$ItEm[] = array('name' => 'Tarifa do boleto', 'amount'=>1, 'value' => $discount_tax_valueRS,);

		if($whmcs_discount > 0 ){
			$discount = array(
				'type' => 'currency',
				'value' => (int)$whmcs_discount,
			);
		}
	}
	// Tarifa Fixa R$
	elseif( $discount_tax === 2 and $discount_tax_type === 2 and $discount_tax_value ){

		$discount_tax_valueRS = (int)($discount_tax_value*100);
		$invoice_amount__  = (int)($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ + $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Tarifa de R$'.number_format($discount_tax_value,  2, ',', '.').' para Boleto</p>';	

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ), 'amount'=>1, 
				'value' => (int)($ItEmValue['amount']*100),);
			}
		}
		$ItEm[] = array('name' => 'Tarifa do Boleto', 'amount'=>1, 'value' => $discount_tax_valueRS,);
		if($whmcs_discount > 0 ){
			$discount = array(
				'type' => 'currency',
				'value' => (int)$whmcs_discount,
			);
		}
	}
	// Valor sem acréscimos ou descontos
	elseif( !$discount_tax_value ){
		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ), 'amount'=>1, 
				'value' => (int)($ItEmValue['amount']*100),);
			}
		}
		$invoice_amount_ = (int)($invoiceTotal * 100);
		if($whmcs_discount > 0 ){
			$discount = array(
				'type' => 'currency',
				'value' => (int)$whmcs_discount,
			);
		}
	}
	/// Determine Fine and Interest Line Itens
	$fine_interest_values = array();
	$fine_values_arr = array();
	$interest_values_arr = array();
	foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
		if($ItEmValue['amount'] >= 0 ){
			$fine_interest_values = ggnb_calculate_fine_interest( $ItEmValue['amount'], $fine, $interest, $invoice_duedate);
		}
		if($fine_interest_values['fine_value']){
			$fine_values_arr[] = $fine_interest_values['fine_value'];
		}
		if($fine_interest_values['interest_value'] ){
			$interest_values_arr[] = $fine_interest_values['interest_value'];
		}
	}
	if($fine_interest_values['fine_value']){
	$ItEm[] = array('name' => 'Multa por atraso', 'amount'=>1, 
			'value' => (int)array_sum($fine_values_arr), );
	}
	if($fine_interest_values['interest_value'] ){
		$ItEm[] = array('name' => 'Juros por atraso','amount'=>1, 
			'value' => (int)array_sum($interest_values_arr), );
	}
	if( $fine_interest_values['fine_value'] and !$fine_interest_values['interest_value'] ){
		$invoice_amount = (int)($invoice_amount_ + (int)array_sum($fine_values_arr)/100);
		$discount_tax_visible_message	.= '<p>Multa por atraso: R$'.number_format((int)array_sum($fine_values_arr)/100,  2, ',', '.'). '</p>';
		$billet_duedate = date('Y-m-d');
	}
	elseif( $fine_interest_values['fine_value'] and $fine_interest_values['interest_value']   ){
		$invoice_amount = (int)($invoice_amount_ + (int)((int)array_sum($fine_values_arr) + (int)array_sum($interest_values_arr)));
		$discount_tax_visible_message	.= '<p>Multa de '.$params['multa'].'% por atraso: R$'.number_format((int)array_sum($fine_values_arr)/100,  2, ',', '.'). '</p>';
		$discount_tax_visible_message	.= '<p>Juros ('.$params['juros'].'% /dia X '.$fine_interest_values['due_days'].' dias): R$'.number_format((int)array_sum($interest_values_arr)/100,  2, ',', '.'). '</p>';
		$billet_duedate = date('Y-m-d');
	}
	elseif( !$fine_interest_values['fine_value'] and $fine_interest_values['interest_value']   ){
		$invoice_amount = (int)($invoice_amount_ + (int)array_sum($interest_values_arr)/100);
		$discount_tax_visible_message	.= '<p>Juros de '.$fine_interest_values['due_days'].' dias de atraso: R$'.number_format((int)array_sum($interest_values_arr)/100,  2, ',', '.'). '</p>';
		$billet_duedate = date('Y-m-d');
	}
	else {
		$invoice_amount = (int)$invoice_amount_;
	}
	$discount_tax_visible_message	.= '<p>Total do Boleto: R$'.number_format((int)($invoice_amount)/100,  2, ',', '.'). '</p>';
	if($ItEm_discount){
		$ItEm = array_merge($ItEm, $ItEm_discount);
	}
	//$PaYeEe = 'b7ac135895cfb50a2a90cf28fe0d15e0'; // Gofas Software
	//$PaYeEe = '4c640ca051ab239b194ed2609967a831'; // Mauricio Gofas


	foreach($ItEm as $key => $value){
		$ItEm_values[$key] = $value['value'];
	}
	//$ItEm_start_key = array_search(max($ItEm_values), $ItEm_values);
	//$ItEm_start_ = $ItEm[$ItEm_start_key];
	//$ItEm_start = array(array('name' => substr(str_replace(array("\n", "\r","=>"), array(" ", " ","-"), $ItEm_start_['name']),0,255), 'marketplace' =>array('repasses'=>array(array('percentage'=>ggnb_percent_fee((int)$ItEm_start_['value'],$invoiceTotal,$devFee),'payee_code'=>$PaYeEe))),'amount'=>1,'value' => (int)$ItEm_start_['value']));

	///
	$total_items_invoice = count($ItEm);
	$metadata = array('custom_id' => (string)$invoice_id,'notification_url' => $returnUrl);
	//if((int)$total_items_invoice === 1){
		$body = array('items' => $ItEm,'metadata' => $metadata);
	//}
	//if((int)$total_items_invoice > 1){
		//unset($ItEm[$ItEm_start_key]);
		//$ItEm_pop = array_merge($ItEm_start,$ItEm);
		//$body = array('items' => $ItEm,'metadata' => $metadata);
	//}
	// $body2
	if( !$configurations and $ItEm_discount ){
		$body2 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'instructions' => $instructions
				)
			)
		);
		$body3 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'instructions' => $instructions
				)
			)
		);

	}
	if( !$configurations and !$ItEm_discount ){
		$body2 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'instructions' => $instructions
				)
			)
		);
		$body3 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'instructions' => $instructions
				)
			)
		);

	}
	if( $configurations and $ItEm_discount ){
		$body2 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'configurations' => $configurations
				)
			)
		);
		$body3 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'configurations' => $configurations
				)
			)
		);

	}
	if( $configurations and !$ItEm_discount ){
		$body2 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'configurations' => $configurations
				)
			)
		);
		$body3 = array(
			'payment' => array(
				'banking_billet' => array(
					'expire_at' => $billet_duedate,
					'customer' => $customer,
					//'discount' => $ItEm_discount,
					'message' => $message,
					'configurations' => $configurations
				)
			)
		);
	}
	if(file_exists(__DIR__.'/custom/params.php')){
		include __DIR__.'/custom/params.php';
	}
	// End params
	// Verify if generate billet
	if((stripos($_SERVER['REQUEST_URI'],'viewinvoice')) or (!stripos($_SERVER['REQUEST_URI'], 'viewinvoice') and ($params['billetonemail']))){
		$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
		if($access_token_['access_token']){
			$access_token = $access_token_['access_token'];
		}
		if($access_token_['error']){
			$error .= $access_token_['error'];
		}
		if($trans_id and !$error){
			$chargeExist_			= ggnb_detail_charge($api_url,$access_token,$trans_id);
			$chargeExist			= $chargeExist_['result'];
			$chargeExistID			= (int)$chargeExist['data']['charge_id']; // ID da transação gerada pela fatura
			$chargeExistTotal		= (int)$chargeExist['data']['total'];
			$chargeExistStatus		= (string)$chargeExist['data']['status'];
			$chargeExistDuedate		= $chargeExist['data']['payment']['banking_billet']['expire_at'];
			$chargeExistDiscount	= $chargeExist['data']['payment']['discount'];
			if($chargeExistDiscount and $chargeExistDiscount > 0){
				$chargeExistTotal	= $chargeExistTotal - $chargeExistDiscount;
			}
			if($chargeExist_['error']){
				$error .= $chargeExist_['error'];
			}
			if((string)$chargeExistStatus === (string)'paid'){
				$add_trans = ggnb_add_trans($params['clientdetails']['id'], $params['invoiceid'], (float)number_format( $chargeExistTotal/100,  2, '.', ''), (float)number_format( $params['fee'],  2, '.', ''), 'ggnb-'.$api_mode.'-'.$trans_id, 'Boleto pago - confirmação ao acessar a fatura');
				header_remove();
				header("Location: ".$system_url.'/viewinvoice.php?id='.$params['invoiceid'],true,303);
				exit;
			}
			if(!$error and (int)$chargeExistID === (int)$trans_id and (string)$chargeExistStatus !== (string)'canceled' and $chargeExistDuedate >= date('Y-m-d') and (float)$chargeExistTotal === (float)$invoice_amount){
				$link		= $chargeExist['data']['payment']['banking_billet']['link'];
				$expire_at	= $chargeExistDuedate;
				$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
			}
			if(!$error and  ($chargeExistID === $trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate < date('Y-m-d') and $chargeExistDuedate > date('Y-m-d',strtotime('-29 days'))) and !$configurations and !$cancelBillet ){
				// edita transação gerada anteriormente
				$updateBillet = ggnb_update_billet($api_url,$access_token,$trans_id,$billet_duedate);
				// segunda via do boleto com multa e juros
				if( $updateBillet['result'] === 'success'){	
					$link		= $chargeExist['data']['payment']['banking_billet']['link'];
					$expire_at	= $billet_duedate;
					$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
				}
				else {
					$error .= $updateBillet['error'];
				}
			}
			if((!$barcode and ($chargeExistID === $trans_id and ($chargeExistStatus === 'canceled' || $chargeExistStatus === 'unpaid'))) or (float)$chargeExistTotal !== (float)$invoice_amount){
				$cancelCharge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
				$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('charge_id', '=',$trans_id)->delete();
				
				$create_charge = ggnb_create_charge($api_url,$access_token,$body); // body
				$charge_id = $create_charge['result'];
				if($create_charge['error']){
					if($create_charge['error']['error_description']['property']){
						$error	.= $create_charge['error']['error_description']['property'].' '.$create_charge['error']['error_description']['message'];
					}
					elseif($create_charge['error']['error_description'] and !$create_charge['error']['error_description']['property']){
						$error	.= $create_charge['error']['error'].' '.$create_charge['error']['error_description'];
					}
				}
				// Definir método de pagamanto e Gerar a Cobrança (retorna link do boleto etc)
				if( is_int($charge_id) ){
					$pay_charge_	= ggnb_pay_charge($api_url,$access_token,$charge_id,$body2);
					$pay_charge		= $pay_charge_['result'];
					if($pay_charge_['error']){
						$error	.= $pay_charge_['error'];
						//$log_result['pay_charge_1_error']	= $pay_charge_;
					}
					if( is_array($pay_charge) ){
						$link		= $pay_charge['data']['link'];
						$expire_at	= $pay_charge['data']['expire_at'];
						$barcode	= $pay_charge['data']['barcode'];
						// Save billet on DB
						$ggnb_store_billet = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode);
						if($ggnb_store_billet['error']){
							$error .= $ggnb_store_billet['error'];
						}
					}
					elseif( is_string($pay_charge) ){
						$error .= $pay_charge['error'];
					}
				}
				else {
					$error .= $create_charge['error'];
				}
			}
			///
			if(!$error and $chargeExistID === $trans_id and $chargeExistDuedate >= date('Y-m-d') and (float)$chargeExistTotal !== (float)$invoice_amount ){
				if($cancelBillet){
					$cancelCharge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
					 if($cancelCharge['error']){
						$error .= $cancelCharge['error'];
					 }
					 else{
						$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('charge_id', '=',$trans_id)->delete();
					 }
				}
				$create_charge = ggnb_create_charge($api_url,$access_token,$body); // body
				$charge_id = $create_charge['result'];
				if($create_charge['error']){
					if($create_charge['error']['error_description']['property']){
						$error .= $create_charge['error']['error_description']['property'].' '.$create_charge['error']['error_description']['message'];
					}
					elseif($create_charge['error']['error_description'] and !$create_charge['error']['error_description']['property']){
						$error .= $create_charge['error']['error'].' '.$create_charge['error']['error_description'];
					}
				}
				// Definir método de pagamanto e Gerar a Cobrança (retorna link do boleto etc)
				if( is_int($charge_id) ){
					$pay_charge_	= ggnb_pay_charge($api_url,$access_token,$charge_id,$body2);
					$pay_charge		= $pay_charge_['result'];
					if($pay_charge_['error']){
						$error	.= $pay_charge_['error'];
					}
					if( is_array($pay_charge) ){
						$link		= $pay_charge['data']['link'];
						$expire_at	= $pay_charge['data']['expire_at'];
						$barcode	= $pay_charge['data']['barcode'];						
						// Save billet on DB
						$ggnb_store_billet = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode);
						if($ggnb_store_billet['error']){
							$error .= $ggnb_store_billet['error'];
						}
					}
					elseif( is_string($pay_charge) ){
						$error .= $pay_charge['error'];
					}
				}
				else {
					$error .= $create_charge['error'];

				}
			}
			///
			if(!$error and ($chargeExistID === $trans_id and $chargeExistDuedate < date('Y-m-d') ) and ($configurations or $cancelBillet) ){
				// cancela transação gerada anteriormente
				if( $chargeExistStatus === 'new' || $chargeExistStatus === 'paid' || $chargeExistStatus === 'unpaid'){
					$cancelCharge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
					 if($cancelCharge['error']){
						 $error .= $cancelCharge['error'];
					 }
					// segunda via do boleto com multa e juros
					if( $cancelCharge['result'] === 'success'){
						$delete_qrc = Capsule::table('gofasgerencianetboleto')->where('charge_id', '=',$trans_id)->delete();
						// Criar transação
						$create_charge = ggnb_create_charge($api_url,$access_token,$body); // body
						$charge_id = $create_charge['result'];						
						if($create_charge['error']){
							if($create_charge['error']['error_description']['property']){
								$error	.= $create_charge['error']['error_description']['property'].' '.$create_charge['error']['error_description']['message'];
							}
							elseif($create_charge['error']['error_description'] and !$create_charge['error']['error_description']['property']){
								$error	.= $create_charge['error']['error'].' '.$create_charge['error']['error_description'];
							}
						}
					}
					else {
						$error = $cancelCharge['error'];
					}
				}
				elseif($chargeExistStatus === 'canceled'){ // ignora transação cancelada
					$create_charge = ggnb_create_charge( $api, $body, $system_url); // body
					$charge_id = $create_charge['result'];					
					if($create_charge['error']){
						$error	.= $create_charge['error'];
					}
				}
				// Definir método de pagamanto e Gerar a Cobrança (retorna link do boleto etc)
				if( is_int($charge_id) ){
					$pay_charge_	= ggnb_pay_charge($api_url,$access_token,$charge_id,$body2);
					$pay_charge		= $pay_charge_['result'];
					if($pay_charge_['error']){
						$error	.= $pay_charge_['error'];
					}
					if( is_array($pay_charge) ){
						$link		= $pay_charge['data']['link'];
						$expire_at	= $pay_charge['data']['expire_at'];
						$barcode	= $pay_charge['data']['barcode'];
						// Save billet on DB
						$ggnb_store_billet = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode);
						
					} elseif( is_string($pay_charge) ){
						$error .= $pay_charge['error'];
					}
				} else {
					$error .= $create_charge['error'];
				}
			}			
		} // End of if( $trans_id and !$error)
		/// The firt billet for the invoice
		if( !$trans_id ){
			if( (float)$invoiceTotal >= (float)$minimunAmount){
				// Criar transação
				$create_charge = ggnb_create_charge($api_url,$access_token,$body); // body
				$charge_id = $create_charge['result'];
				if($create_charge['error']){
						$error	.= $create_charge['error'];
				}
				// Definir método de pagamanto e Gerar a Cobrança (retorna link do boleto etc)
				if($charge_id){
					$pay_charge_	= ggnb_pay_charge($api_url,$access_token,$charge_id,$body2);
					$pay_charge		= $pay_charge_['result'];
					if($pay_charge_['error']){
						$error	.= $pay_charge_['error'];
					}
					if( is_array($pay_charge) ){
						$link		= $pay_charge['data']['link'];
						$expire_at	= $pay_charge['data']['expire_at'];
						$barcode	= $pay_charge['data']['barcode'];
						// Save billet on DB
						$ggnb_store_billet = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode);
						if($ggnb_store_billet['error']){
							$error = $ggnb_store_billet['error'];
						}
					}
					elseif( is_string($pay_charge) ){
						$error = $pay_charge['error'];
					}
				}
				else {
						$error = $create_charge['error'];
				}
			}
			elseif( (float)$invoiceTotal < (float)$minimunAmount ){
				$error = '<b><span class="ggnberror">O valor total da fatura é R$'.number_format( $invoiceTotal,  2, ',', '.') .', mas o valor mínimo para pagamento via Boleto que é R$'. number_format( $minimunAmount,  2, ',', '.') .'.</span></b>';
				
			}
		} // end !$trans_id
		// Resultado impresso na área Visível na fatura/checkout
		if($error){
			// Email enviado ao admin em caso de erro
			if( $emailonError ){
				$sendEmailonError = ggnb_send_error_email( $invoice_id, $user_id, $firstname, $lastname, $system_url, $emailonError, $error);
			}
			logModuleCall("gofasgerencianetboleto","genarate_billet",get_defined_vars(),"", $error);
			return $error . $css;
		}
		if( !$error and $params['redirecttobillet'] and stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php') ){
			header_remove();
			header("Location: ".$link,true,303);
			exit;
		}
		if( !$error and !$params['redirecttobillet'] and stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php')){
			//////////// Resultado ///////////
			$expire_at_v = strtotime($expire_at);
			$expire_at_br = date('d/m/Y',$expire_at_v);
			$result .= '<a target="_blank" title="Visualizar Boleto" id="ggnbviewbillet" href="'.$link.'">'.$payButton.'</a><br/><br/>';						
			if( $showBarCode ){
				$result .= '<br><p id="ggnbclic">Clique para copiar a Linha Digitável do Boleto:</p>
				<p id="linDig" onfocus="select_all_and_copy(this)" onclick="select_all_and_copy(this)">'.$barcode.'</p>';
			}
			if( ($discount_tax_value and $discount_tax_visible) || $showDueDate ){
				$result .= '<div id="ggnbbilletinfo">';
			}
			if( $discount_tax_visible and $discount_tax_visible_message){
				$result .=  $discount_tax_visible_message;
			}
			if( $showDueDate ){
				$result .= '<p>Vencimento do Boleto: ' . $expire_at_br . '</p>';
			}
			if( ($discount_tax_value and $discount_tax_visible) || $showDueDate ){
				$result .= '</div>';
			}
			$result .= '<script type="text/javascript" src="'.$system_url.'/modules/gateways/gofasgerencianetboleto/assets/js/copy.js" charset="UTF-8"></script>';
			return $result.$css;
		}
	} // End of if( $generate_billet )
	else {
		if($params['log']){
			logModuleCall("gofasgerencianetboleto","genarate_billet",array(get_defined_vars()),"", array($result));
		}
		return;
	}
}}
/**
 * 
 * Callback
 * 
 */

 if($_REQUEST['notification']){
	require_once ggnb_whmcs_url('root_dir').'/init.php';
	require_once ggnb_whmcs_url('root_dir').'/includes/gatewayfunctions.php';
	require_once ggnb_whmcs_url('root_dir').'/includes/invoicefunctions.php';
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
		 $invoice_data				= localAPI('getinvoice', $getinvoiceid, ggnb_setup_admin('id'));
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
			 $UpdateInvoice = localAPI('updateinvoice', array( 'invoiceid' => $invoiceId, 'newitemdescription' => array('Acréscimos'),'newitemamount' => array((float)($paymentAmount - $invoice_amount))/* , 'total' => $paymentAmount */), ggnb_setup_admin('id') );
			 echo 'UpdateInvoice: ', json_encode($UpdateInvoice);
		 }
		 if( $paymentAmount < $invoice_amount){
			 $UpdateInvoice = localAPI('updateinvoice', array( 'invoiceid' => $invoiceId, 'newitemdescription' => array('Descontos'),'newitemamount' => array((float)-($invoice_amount-$paymentAmount))/* , 'total' => $paymentAmount */), ggnb_setup_admin('id') );
			 if($params['log']){
				 echo json_encode(['UpdateInvoice'=>$UpdateInvoice]);
			 }
		 }
		  $fee = $params['fee'] ?: '0.00';
		  $add_trans = ggnb_add_trans($user_id,$invoiceId,$paymentAmount,$fee, 'ggnb-'.$api_mode.'-'.$charge_id, 'Boleto pago - confirmação via notificação/callback');
				
		 if($params['log']){
			 echo json_encode(['Add transaction'=>$addtransresult]);
		 }
		 if($params['log']){
			 logModuleCall("gofasgerencianetboleto","receive_callback",array(get_defined_vars()),"", array($addtransresult));
		 }
	 }
 }
 /**
  * 
  * Hooks
  *
  */
  if(!function_exists('ggnb_check_schedule')){
    function ggnb_check_schedule(){
        $params = getGatewayVariables('gofasgerencianetboleto');
        $start_at = substr(preg_replace('/[^\da-z]/i','',$params['verifypaymentsat']),0,4) ?: '0500';
        $max_invoices = $params['maxinvoicespercheck'] ?: '100';
        $total_queue_invoices = Capsule::table('tblinvoices')->where('status','=','Unpaid')->where('paymentmethod','=','gofasgerencianetboleto')->count();
		foreach( Capsule::table('tbltransientdata')
            ->where('name','=','EFI.Boleto.Charge.Verification')
            ->orderBy('id','desc')
            ->take('1')
            ->get() as $value){
                $tbltransientdata=json_decode(json_encode($value),true);
                $tbltransientdata=json_decode($tbltransientdata['data'],true);
        }
		if((int)$start_at === 0){
			$start_at_ = '24';
		}
		else{
			$start_at_ = $start_at;
		}
		if((int)date('H') >= (int)$start_at_){
			$next_check_schedule = date('Ymd',strtotime('+1 day')).$start_at;
		}
		if((int)date('H') < (int)$start_at_){
			$next_check_schedule = date('Ymd').$start_at;
		}
        if((int)$total_queue_invoices >= 1){
            if(is_array($tbltransientdata) and (int)date('YmdHi') >= (int)$tbltransientdata['next']){
                foreach( Capsule::table('tblinvoices')
                    ->where('status','=','Unpaid')
                    ->where('paymentmethod','=','gofasgerencianetboleto')
                    ->orderBy('id','asc')
                    ->take($max_invoices)
                    ->whereNotIn('id', $tbltransientdata['skip_invoices'] ?: ['0'])
                    ->get(['id']) as $queue_invoices_){
                        if($queue_invoices_->id){
                            $queue_invoices[]=$queue_invoices_->id;
                        }
                        else{
                            $queue_invoices=false;
                        }
                }
                if($queue_invoices){ // <----
                    if($tbltransientdata['skip_invoices']){
                        $skip_invoices = $tbltransientdata['skip_invoices'];
                    }
                    else{
                        $skip_invoices = [];
                    }
                    $data = [
                        'name'=>'EFI.Boleto.Charge.Verification',
                        'data'=>json_encode([
                            'next'=>date('YmdHi',strtotime('+300 seconds')),
                            'skip_invoices'=> array_merge($skip_invoices,$queue_invoices),
                        ]),
                        'expires'=>strtotime('+2 days'),
                    ];
                    $transientdata = Capsule::table('tbltransientdata')->where('name','=','EFI.Boleto.Charge.Verification')->update($data);
					unset($transientdata);
                    return $queue_invoices;
                }
                if(!$queue_invoices){ // <----
                    $data = [
                        'name'=>'EFI.Boleto.Charge.Verification',
                        'data'=>json_encode([
                            'next'=>$next_check_schedule,
                            'skip_invoices'=> '',
                        ]),
                        'expires'=>strtotime('+2 days'),
                    ];
                    $transientdata = Capsule::table('tbltransientdata')->where('name','=','EFI.Boleto.Charge.Verification')->update($data);
					unset($transientdata);
                    return false;
                }
            }
            if(!is_array($tbltransientdata)){
                foreach( Capsule::table('tblinvoices')
                    ->where('status','=','Unpaid')
                    ->where('paymentmethod','=','gofasgerencianetboleto')
                    ->orderBy('id','asc')
                    ->take($max_invoices)
                    //->whereNotIn('id', $tbltransientdata['skip_invoices'])
                    ->get(['id']) as $queue_invoices_){
                        $queue_invoices[]=$queue_invoices_->id;
                }
                if($queue_invoices){ // <----
                    $data = [
                        'name'=>'EFI.Boleto.Charge.Verification',
                        'data'=>json_encode([
                            'next'=>date('YmdHi',strtotime('+300 seconds')),
                            'skip_invoices'=> $queue_invoices,
                        ]),
                        'expires'=>strtotime('+2 days'),
                    ];
                    $transientdata = Capsule::table('tbltransientdata')->insert($data);
					unset($transientdata);
                    return $queue_invoices;
                }
                if(!$queue_invoices){ // <----
                    $data = [
                        'name'=>'EFI.Boleto.Charge.Verification',
                        'data'=>json_encode([
                            'next'=>$next_check_schedule,
                            'skip_invoices'=> '',
                        ]),
                        'expires'=>strtotime('+2 days'),
                    ];
                    $transientdata = Capsule::table('tbltransientdata')->insert($data);
					unset($transientdata);
                    return false;
                }
            }
        }
		if((int)$total_queue_invoices <1 and !empty($tbltransientdata['skip_invoices'])){
			$data = [
				'name'=>'EFI.Boleto.Charge.Verification',
				'data'=>json_encode([
					'next'=>$next_check_schedule,
					'skip_invoices'=> '',
				]),
				'expires'=>strtotime('+2 days'),
			];
			$transientdata = Capsule::table('tbltransientdata')->update($data);
			unset($transientdata);
			return false;
		}
		if((int)$total_queue_invoices <1 and !is_array($tbltransientdata)){
			$data = [
				'name'=>'EFI.Boleto.Charge.Verification',
				'data'=>json_encode([
					'next'=>$next_check_schedule,
					'skip_invoices'=> '',
				]),
				'expires'=>strtotime('+2 days'),
			];
			$transientdata = Capsule::table('tbltransientdata')->insert($data);
			unset($transientdata);
			return false;
        }
		return;
    }
}
if(!function_exists('ggnb_check_status_updates')){
	function ggnb_check_status_updates($vars){
		$params = getGatewayVariables('gofasgerencianetboleto');
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
		$check_schedule = ggnb_check_schedule();
    	if(!is_array($check_schedule)){
        	return;
    	}
		if(is_array($check_schedule)){
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
						$update_invoice = localAPI('updateinvoice', array( 'invoiceid' => $value['invoice_id'], 'newitemdescription' => array('Acréscimos calculados na emissão do Boleto'),'newitemamount' => array((float)($value['paid_amount'] - $value['total']))), ggnb_setup_admin('id') );
				  	}
				  	// - Billet amount is less than the invoice amount
				  	if ( (float)$value['paid_amount'] < (float)$value['total'] ) {
						$update_invoice = localAPI('updateinvoice', array( 'invoiceid' => $value['invoice_id'], 'newitemdescription' => array('Descontos calculados na emissão do Boleto'),'newitemamount' => array((float)($value['paid_amount'] - $value['total']))), ggnb_setup_admin('id') );
				  	}
				  	$add_trans = ggnb_add_trans($value['user_id'],$value['invoice_id'],$value['paid_amount'],$value['fee'], 'ggnb-'.$api_mode.'-'.$value['trans_id'], 'Boleto pago - confirmação via tarefa cron');
				    $update_invoice_log[$value['invoice_id']]=$update_invoice;
				  	$add_trans_log[$value['invoice_id']]=$add_trans;
			  	}
			}
		 	}
			catch (Exception $e) {
			  	$error	.= 'Erro ao listar boletos pagos: ' . $e->getMessage();
			  	$log['error'] = $error;
		  	}
		}
		$log['boletos'] = $boletos;
		$log['invoices'] = $invoices;
		$log['update_invoice'] = $update_invoice;
		$log['add_trans'] = $add_trans;
		if($params['log']){
			logModuleCall('gofasgerencianetboleto','AfterCronJob',array('module_version'=>'3.9.0','params'=>$params),'',array($log) );
		}
		return;  
	}
}
add_hook("AfterCronJob",1,"ggnb_check_status_updates");
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
		  $invoice			= localAPI( 'GetInvoice', array('invoiceid' => $vars['relid']), ggnb_setup_admin('id'));
		  
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
	  $ggnb_merge_fields['ggnb_billet_info']	= 'EFÍ: Informações do boleto';
	  $ggnb_merge_fields['ggnb_link']			= 'EFÍ: Link para o boleto';
	  $ggnb_merge_fields['ggnb_pdf']			= 'EFÍ: Link para o boleto em PDF';
	  $ggnb_merge_fields['ggnb_barcode']		= 'EFÍ: Linha digitável do boleto';
	  $ggnb_merge_fields['ggnb_expire_at']	= 'EFÍ: Vencimento do boleto';
	  $ggnb_merge_fields['ggnb_total']		= 'EFÍ: Total do boleto';
	  $ggnb_merge_fields['ggnb_charge_id']	= 'EFÍ: ID da transação';
	  $ggnb_merge_fields['ggnb_api_mode']		= 'EFÍ: API mode (sandbox ou live)';
	  $ggnb_merge_fields['ggnb_debug']		= 'EFÍ: Debug nos emails';
	  return $ggnb_merge_fields;
  });
  add_hook('InvoiceCancelled', 1, function($vars){
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
	  $invoice	= localAPI('GetInvoice',array( 'invoiceid' => $vars['invoiceid'], ), ggnb_setup_admin('id'));	
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