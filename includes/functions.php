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
	elseif($error){
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
		elseif($json['error']){
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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
		}
	}
}
/**
 *
 * Associar forma de pagamento à cobrança
 * @ggnb_pay_charge
 *
 */
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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
		}
	}
}

/**
 *
 * Consultar informações da transação
 * @ggnb_detail_charge
 *
 */

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
		elseif($json['error']){
			$error	.= 'Erro: '.implode(', ', $json);;
			return array('error'=> $error, 'debug'=> $json);
		}
	}
}

/**
 *
 * Gravar transação no WHMCS
 * @ggnb_add_trans
 *
 */
if(!function_exists('ggnb_add_trans')){
function ggnb_add_trans( $USERID, $INVOICEID, $CHARGEID, $admin, $api_mode, $system_url, $debug ){
	$addtransaction = "addtransaction";
 	$addtransvalues['userid'] = $USERID;
 	$addtransvalues['invoiceid'] = $INVOICEID;
 	$addtransvalues['description'] = "Boleto gerado aguardando pagamento.";
 	$addtransvalues['amountin'] = '0.00';
 	$addtransvalues['fees'] = '0.00';
 	$addtransvalues['paymentmethod'] = 'gofasgerencianetboleto';
 	$addtransvalues['transid'] = 'ggnb_'.$api_mode.'_waiting-'.$CHARGEID.'';
 	$addtransvalues['date'] = date('d/m/Y');
	$addtransresults = localAPI( $addtransaction, $addtransvalues, $admin );
	
		if($debug_or_log and $addtransresults['result'] === 'success'){
			$log_result = array();
			$log_result['addtransaction__']	= '<p class="ok">Transação gravada com sucesso - API WHMCS.</p>';
			$log_result['addtransaction_']	=  'ID da Transação: ' . $addtransvalues['transid'] . '<br>';
			$log_result['addtransaction']		= $addtransresults;
			
		} elseif($debug_or_log and $addtransresults['result'] !== 'success'){
			$log_result = array();
			$log_result['addtransaction_']	= '<p class="erro">Erro ao gravar a transação - API WHMCS.</p>';
			$log_result['addtransaction']	= $addtransresults;
		}
	if( $addtransresults['result'] === 'success'){
		//return $addtransresults;
		return array('result'=>$addtransresults, 'log_result'=> $log_result);
		
	} elseif($addtransresults['result'] !== 'success'){
		$error = '<b>3) Não foi possível gerar o boleto, por favor <a href="'.$system_url.'/submitticket.php" target="_blank">entre em contato</a> informando o ID da fatura.</b>';
		//return $error;
		return array('error'=>$error, 'log_result'=> $log_result);
	}
}
}


/**
 *
 * Grava Boleto no DB
 *
 */
if( !function_exists('ggnb_store_billet') ){
function ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$debug_or_log,$api_mode){
	 $date = str_replace('/', '-', $pay_charge['data']['charges']['0']['dueDate']) ;
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
	 try {
		$save_billet = Capsule::table('gofasgerencianetboleto') ->insert($data);
	}
	catch (\Exception $e){
		$error = "Não foi possível gravar o Boleto no banco de dados. {$e->getMessage()}";
	}
	
	if($error){
		if($debug_or_log){
			return array('error'=>$error, 'data'=>$data, 'date'=>$date,'duedate'=>$dueDate, 'save_billet'=>$save_billet);
		}
		else {
			return array('error'=>$error);
		}
	}
	elseif(!$error){
		if($debug_or_log){
			return array('success'=>true, 'data'=>$data, 'date'=>$date,'duedate'=>$dueDate, 'save_billet'=>$save_billet);
		}
		else {
			return array('success'=>true);
		}
		
	}
		
}}



/**
 *
 * Envia email ao admin em caso de erro
 * ggnb_send_error_email
 *
 */
if(!function_exists('ggnb_send_error_email')){
function ggnb_send_error_email( $INVOICEID, $USERID, $FNAME, $LNAME, $system_url, $ADMIN, $EOE, $ERROR, $debug_or_log ){
	$sendEmailonError = "sendadminemail";
 	$sendEOEvalues['customsubject'] = 'Erro ao gerar boleto - fatura #'.$INVOICEID;
	$sendEOEvalues['custommessage'] = '<br/>Olá administrador,<br/>
		Ocorreu uma falha ao gerar um Boleto para a <a href="'.$system_url.'/admin/invoices.php?action=edit&id='.$INVOICEID.'">Fatura #'.$INVOICEID.'</a>.<br/><br/>
		Detalhes do erro:<br/>
		<b>Cliente:</b> <a href="'.$system_url.'/admin/clientssummary.php?userid='.$USERID.'">'.$FNAME.' '.$LNAME.'</a><br/><br/>
		<b>Erro exibido na Fatura:</b><br/><i>"'.$ERROR.'"</i><br/><br/>
		Email gerado de acordo com às configurações do gateway <a title="Ir para as configurações do módulo ↗" href="'.$system_url.'/admin/configgateways.php?updated=gofasgerencianetboleto#m_gofasgerencianetboleto">Gofas Gerencianet Boleto</a>.<br/><br/>';
 	$sendEOEvalues['type'] = 'system';
 	$sendEOEvalues['deptid'] = $EOE;
 	$sendEOEresults = @localAPI($sendEmailonError,$sendEOEvalues,$ADMIN);
		
	if($debug_or_log and $sendEOEresults['result'] === 'success'){
		$log_result = array();
		$log_result['sendEmailonError_']	= '<p class="ok">Email envido ao admin notificando o erro - API WHMCS.</p>';
		$log_result['sendEmailonError']		= $sendEOEresults;
	}
	elseif($debug_or_log and $sendEOEresults['result'] !== 'success'){
		$log_result = array();
		$log_result['sendEmailonError_']	= '<p class="error">Falha ao enviar email notificando o erro - API WHMCS.</p>';
		$log_result['sendEmailonError']	= $sendEOEresults;
	}
	if($debug_or_log){
		return array('result'=>$sendEOEresults, 'log_result'=>$log_result,);
 	}
	else {
		return array('result'=>$sendEOEresults);
 	}
}
}