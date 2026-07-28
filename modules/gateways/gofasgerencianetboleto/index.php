<?php
/**
 * Módulo Efí Boleto para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 -> 2025 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.14.2
 */
use WHMCS\Application;
use WHMCS\Database\Capsule;

if(!function_exists('ggnb_verifyInstall')){
function ggnb_verifyInstall(){
	// v3.14.2: a funcao passou a ser chamada tambem fora da tela de configuracoes
	// (gravacao de boleto e tarefa cron). O resultado positivo fica em cache estatico
	// para nao repetir as consultas de schema na mesma requisicao. Erro nao e cacheado.
	static $result = NULL;
	if( is_array($result) ){
		return $result;
	}
	$error = NULL;
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
	// v3.14.0: registra, por cobranca, se o modulo enviou o bloco configurations (multa e juros).
	// E o que permite descobrir depois, sem criar cobranca de sondagem, se a conta Efi tem encargos proprios.
	if( Capsule::schema()->hasTable('gofasgerencianetboleto') and !Capsule::schema()->hasColumn('gofasgerencianetboleto','sent_config') ){
		try {
			Capsule::schema()->table('gofasgerencianetboleto', function($table){
				$table->tinyInteger('sent_config')->default(0);
			});
		}
		catch (\Exception $e){
			$error = "Não foi possível adicionar a coluna sent_config na tabela do módulo: {$e->getMessage()}";
		}
	}
	if(!$error){
		$result = array('success'=>1);
		return $result;
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
			return array('error'=> $error);
		}
	}
}
/**
 *
 * v3.14.0
 * Configuracao de multa e juros aplicada pela Efi a uma cobranca ja existente.
 * A API Cobrancas nao tem endpoint para ler as configuracoes da conta ou da carteira:
 * o unico lugar onde esses valores aparecem e o detalhe da cobranca.
 * @ggnb_efi_charge_config
 *
 */
if(!function_exists('ggnb_efi_charge_config')){
	function ggnb_efi_charge_config($charge){
		$cfg = $charge['payment']['banking_billet']['configurations'];
		if(!is_array($cfg)){
			return array('fine'=>0,'interest'=>0,'interest_type'=>'daily');
		}
		$interest = $cfg['interest'];
		if(is_array($interest)){ // formato novo da Efi: interest => array('value'=>..,'type'=>..)
			$interest_type	= (string)$interest['type'] ?: 'daily';
			$interest		= (int)$interest['value'];
		}
		else {
			$interest_type	= (string)$cfg['interest_type'] ?: 'daily';
			$interest		= (int)$interest;
		}
		return array(
			'fine'			=> (int)$cfg['fine'],
			'interest'		=> $interest,
			'interest_type'	=> $interest_type,
		);
	}
}
/**
 *
 * v3.14.0
 * Cache da configuracao de multa e juros da conta Efi, por ambiente (live/sandbox).
 * Alimentado pela tarefa cron a partir de cobrancas em que o modulo NAO enviou configurations,
 * portanto sem nenhuma cobranca de sondagem e sem alterar o comportamento de cobrancas reais.
 * @ggnb_efi_account_config
 *
 */
if(!function_exists('ggnb_efi_account_config')){
	function ggnb_efi_account_config($api_mode){
		$empty = array('fine'=>0,'interest'=>0,'interest_type'=>'daily','checked_at'=>0,'known'=>false);
		try {
			$row = Capsule::table('tbltransientdata')->where('name','=','EFI.Boleto.Account.Config.'.$api_mode)->first();
		}
		catch (\Exception $e){
			return $empty;
		}
		if(!$row or !$row->data){
			return $empty;
		}
		$data = json_decode($row->data,true);
		if(!is_array($data)){
			return $empty;
		}
		$data['known'] = true;
		return $data;
	}
}
if(!function_exists('ggnb_efi_account_config_store')){
	function ggnb_efi_account_config_store($api_mode,$cfg){
		$data = array(
			'fine'			=> (int)$cfg['fine'],
			'interest'		=> (int)$cfg['interest'],
			'interest_type'	=> (string)$cfg['interest_type'] ?: 'daily',
			'checked_at'	=> (int)date('YmdHi'),
		);
		try {
			$exists = Capsule::table('tbltransientdata')->where('name','=','EFI.Boleto.Account.Config.'.$api_mode)->first();
			if($exists){
				Capsule::table('tbltransientdata')->where('name','=','EFI.Boleto.Account.Config.'.$api_mode)->update(array('data'=>json_encode($data)));
			}
			else {
				Capsule::table('tbltransientdata')->insert(array('name'=>'EFI.Boleto.Account.Config.'.$api_mode,'data'=>json_encode($data),'expires'=>date('Y-m-d H:i:s',strtotime('+1 year'))));
			}
		}
		catch (\Exception $e){
			return array('error'=>$e->getMessage());
		}
		return array('success'=>true,'config'=>$data);
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
			return array('error'=> $error);
		}
		// Resposta inesperada da Efí: tratada como falha, nunca como sucesso
		return array('error'=>'Erro: resposta inesperada da Efí ao cancelar a cobrança '.$trans_id.'. '.json_encode($json));
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
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
			$error = 'Erro: '.$json['error'].($json['error_description'] ? ' - '.$json['error_description'] : '');
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
/**
 *
 * Ultimo boleto ativo da fatura (nao pago, nao cancelado)
 * @ggnb_active_charge
 *
 */
if(!function_exists('ggnb_active_charge')){
	function ggnb_active_charge($invoice_id,$api_mode){
		$row = Capsule::table('gofasgerencianetboleto')
			->where('invoice_id','=',(string)$invoice_id)
			->where('api_mode','=',$api_mode)
			->whereNotIn('status',array('paid','canceled'))
			->orderBy('id','desc')
			->first(array('charge_id'));
		if($row and $row->charge_id){
			return (string)$row->charge_id;
		}
		return false;
	}
}
/**
 *
 * Status de cobranca que representam pagamento: paid (pago no banco) e settled (baixa manual no painel da Efi)
 * @ggnb_is_paid_status
 *
 */
if(!function_exists('ggnb_is_paid_status')){
	function ggnb_is_paid_status($status){
		return in_array((string)$status, array('paid','settled'), true);
	}
}
/**
 *
 * Status atual da cobranca na Efi: a Efi e a fonte da verdade, nunca o retorno do cancelamento
 * @ggnb_charge_status
 *
 */
if(!function_exists('ggnb_charge_status')){
	function ggnb_charge_status($api_url,$access_token,$charge_id){
		$detail = ggnb_detail_charge($api_url,$access_token,$charge_id);
		$status = (string)$detail['result']['data']['status'];
		if(!$status){
			return array('error'=>'Não foi possível consultar a cobrança '.$charge_id.' na Efí: '.$detail['error']);
		}
		return array('status'=>$status,'charge'=>$detail['result']['data']);
	}
}
/**
 *
 * Cancela a cobranca e so confirma o cancelamento depois de reconsultar o status na Efi
 * @ggnb_cancel_charge_confirmed
 *
 */
if(!function_exists('ggnb_cancel_charge_confirmed')){
	function ggnb_cancel_charge_confirmed($api_url,$access_token,$charge_id,$api_mode){
		$before = ggnb_charge_status($api_url,$access_token,$charge_id);
		if($before['error']){
			return array('error'=>$before['error']);
		}
		if(ggnb_is_paid_status($before['status'])){
			ggnb_set_charge_status($charge_id,$api_mode,'paid');
			return array('paid'=>true,'error'=>'A cobrança '.$charge_id.' está paga: não pode ser cancelada.');
		}
		if((string)$before['status'] === 'canceled'){
			ggnb_set_charge_status($charge_id,$api_mode,'canceled');
			return array('canceled'=>true);
		}
		$cancel = ggnb_cancel_charge($api_url,$access_token,$charge_id);
		// A Efi e reconsultada: o cancelamento so vale se o status virou canceled
		$after = ggnb_charge_status($api_url,$access_token,$charge_id);
		if($after['error']){
			return array('error'=>$after['error']);
		}
		if(ggnb_is_paid_status($after['status'])){
			ggnb_set_charge_status($charge_id,$api_mode,'paid');
			return array('paid'=>true,'error'=>'A cobrança '.$charge_id.' foi paga: não pode ser cancelada.');
		}
		if((string)$after['status'] !== 'canceled'){
			return array('error'=>'A cobrança '.$charge_id.' continua com status "'.$after['status'].'" na Efí após o pedido de cancelamento. '.$cancel['error']);
		}
		ggnb_set_charge_status($charge_id,$api_mode,'canceled');
		return array('canceled'=>true);
	}
}
/**
 *
 * Atualiza o status local do boleto, mantendo o historico da fatura
 * @ggnb_set_charge_status
 *
 */
if(!function_exists('ggnb_set_charge_status')){
	function ggnb_set_charge_status($charge_id,$api_mode,$status){
		return Capsule::table('gofasgerencianetboleto')
			->where('charge_id','=',(string)$charge_id)
			->where('api_mode','=',$api_mode)
			->update(array('status'=>$status));
	}
}
/**
 *
 * Verifica se a transacao ja foi lancada na fatura
 * @ggnb_transaction_exists
 *
 */
if(!function_exists('ggnb_transaction_exists')){
	function ggnb_transaction_exists($trans_id){
		return (int)Capsule::table('tblaccounts')->where('transid','=',(string)$trans_id)->count() > 0;
	}
}
/**
 *
 * Confere se a cobranca paga pertence a fatura e ao cliente
 * @ggnb_charge_belongs_to_invoice
 *
 */
if(!function_exists('ggnb_charge_belongs_to_invoice')){
	function ggnb_charge_belongs_to_invoice($charge,$api_mode){
		$charge_id	= (string)$charge['charge_id'];
		$invoice_id	= (int)$charge['custom_id']; // custom_id e gravado por este modulo na criacao da cobranca
		if(!$charge_id){
			return array('error'=>'Cobranca sem charge_id: nada a confirmar.');
		}
		if($invoice_id < 1){
			return array('error'=>'Cobranca '.$charge_id.' sem custom_id: nao e possivel identificar a fatura.');
		}
		$invoice = Capsule::table('tblinvoices')->where('id','=',$invoice_id)->first(array('id','userid','status','total','credit','paymentmethod'));
		if(!$invoice){
			return array('error'=>'Fatura '.$invoice_id.' (custom_id da cobranca '.$charge_id.') nao existe.');
		}
		if((int)$invoice->userid < 1){
			return array('error'=>'Fatura '.$invoice_id.' nao tem cliente associado.');
		}
		// Se a cobranca esta registrada localmente, o vinculo com a fatura tem de bater
		$local = Capsule::table('gofasgerencianetboleto')
			->where('charge_id','=',$charge_id)
			->where('api_mode','=',$api_mode)
			->first(array('invoice_id'));
		if($local and (int)$local->invoice_id !== $invoice_id){
			return array('error'=>'Cobranca '.$charge_id.' esta registrada para a fatura '.$local->invoice_id.', mas informou a fatura '.$invoice_id.'.');
		}
		// Cobranca sem registro local (boleto antigo, base restaurada): so confirma se a fatura ainda for do modulo
		if(!$local and (string)$invoice->paymentmethod !== 'gofasgerencianetboleto'){
			return array('error'=>'Cobranca '.$charge_id.' nao esta registrada localmente e a fatura '.$invoice_id.' nao usa o metodo de pagamento gofasgerencianetboleto.');
		}
		// Saldo devedor da fatura: o total do WHMCS ja vem liquido do credito aplicado (tblinvoices.total = subtotal - credit),
		// entao o saldo e o total menos o que ja foi pago na fatura. E com este saldo, nao com o total,
		// que o valor pago tem de ser comparado: cobre pagamento parcial ja lancado e boleto pago com multa e juros.
		$paid_in	= (float)Capsule::table('tblaccounts')->where('invoiceid','=',$invoice_id)->sum('amountin');
		$paid_out	= (float)Capsule::table('tblaccounts')->where('invoiceid','=',$invoice_id)->sum('amountout');
		$balance	= (float)number_format((float)$invoice->total - ($paid_in - $paid_out), 2,'.','');
		return array(
			'invoice_id'	=> $invoice_id,
			'user_id'		=> (int)$invoice->userid,
			'invoice_status'=> (string)$invoice->status,
			'invoice_total'	=> (float)$invoice->total,
			'invoice_credit'=> (float)$invoice->credit,
			'invoice_balance'=> $balance,
			'registered'	=> $local ? true : false,
		);
	}
}
/**
 *
 * Valor pago da cobranca, em centavos (cobre pagamento a maior ou a menor no banco)
 * @ggnb_charge_paid_value
 *
 */
if(!function_exists('ggnb_charge_paid_value')){
	function ggnb_charge_paid_value($charge){
		$paid = (int)$charge['paid_value'];
		if($paid > 0){
			return $paid;
		}
		$total		= (int)$charge['total'];
		$discount	= (int)$charge['payment']['discount'];
		if($discount > 0){
			$total = $total - $discount;
		}
		return (int)$total;
	}
}
/**
 *
 * Cancela os demais boletos em aberto da fatura, garantindo um unico boleto pagavel
 * @ggnb_cancel_other_charges
 *
 */
if(!function_exists('ggnb_cancel_other_charges')){
	function ggnb_cancel_other_charges($api_url,$access_token,$invoice_id,$api_mode,$keep_charge_id){
		$result = array();
		foreach( Capsule::table('gofasgerencianetboleto')
			->where('invoice_id','=',(string)$invoice_id)
			->where('api_mode','=',$api_mode)
			->where('charge_id','<>',(string)$keep_charge_id)
			->whereNotIn('status',array('paid','canceled'))
			->orderBy('id','desc')
			->get(array('charge_id')) as $row ){
				$cancel = ggnb_cancel_charge_confirmed($api_url,$access_token,$row->charge_id,$api_mode);
				if($cancel['canceled']){
					$result[$row->charge_id] = 'cancelado';
				}
				elseif($cancel['paid']){ // ja pago: nao cancela, registra para o admin
					$result[$row->charge_id] = 'pago';
				}
				else{
					$result[$row->charge_id] = 'erro ao cancelar: '.$cancel['error'];
				}
		}
		return $result;
	}
}
/**
 *
 * Confirmacao de pagamento: rotina unica do callback, do acesso a fatura e da tarefa cron
 * @ggnb_confirm_payment
 *
 */
if(!function_exists('ggnb_confirm_payment')){
	function ggnb_confirm_payment($api_url,$access_token,$charge,$api_mode,$params,$origin){
		$charge_id	= (string)$charge['charge_id'];
		$status		= (string)$charge['status'];
		if(!ggnb_is_paid_status($status)){
			return array('error'=>'Cobranca '.$charge_id.' com status "'.$status.'": nada a confirmar.');
		}
		// A cobranca paga tem de pertencer a fatura e a fatura tem de ter cliente
		$owner = ggnb_charge_belongs_to_invoice($charge,$api_mode);
		if($owner['error']){
			return array('error'=>$owner['error']);
		}
		$invoice_id	= $owner['invoice_id'];
		$user_id	= $owner['user_id'];
		$trans_id	= 'ggnb-'.$api_mode.'-'.$charge_id;
		// Nao lanca a mesma cobranca duas vezes (callback, acesso a fatura e cron podem chegar juntos)
		if(ggnb_transaction_exists($trans_id)){
			ggnb_set_charge_status($charge_id,$api_mode,'paid');
			return array('skipped'=>'Transacao '.$trans_id.' ja lancada na fatura '.$invoice_id.'.');
		}
		if($owner['invoice_status'] !== 'Unpaid'){
			ggnb_set_charge_status($charge_id,$api_mode,'paid');
			return array('skipped'=>'Fatura '.$invoice_id.' esta como "'.$owner['invoice_status'].'": pagamento nao lancado.');
		}
		$paid_amount = (float)number_format(ggnb_charge_paid_value($charge)/100, 2,'.','');
		if($paid_amount <= 0){
			return array('error'=>'Cobranca '.$charge_id.' paga sem valor identificado.');
		}
		// Multa, juros ou desconto do boleto: acerta a fatura pela diferenca entre o valor pago e o saldo devedor.
		// Comparar com o saldo (e nao com o total) evita lancar "Descontos" indevidos em fatura com credito aplicado
		// ou com pagamento parcial ja lancado, e cobre o boleto pago com multa e juros (diferenca positiva).
		$invoice_total	= (float)$owner['invoice_total'];
		$invoice_balance= (float)$owner['invoice_balance'];
		$adjustment		= (float)number_format($paid_amount - $invoice_balance, 2,'.','');
		$update_invoice	= false;
		if($adjustment > 0){
			$update_invoice = localAPI('updateinvoice', array(
				'invoiceid'				=> $invoice_id,
				'newitemdescription'	=> array('Acréscimos calculados na emissão do Boleto'),
				'newitemamount'			=> array($adjustment),
			), ggnb_setup_admin('id'));
		}
		if($adjustment < 0){
			$update_invoice = localAPI('updateinvoice', array(
				'invoiceid'				=> $invoice_id,
				'newitemdescription'	=> array('Descontos calculados na emissão do Boleto'),
				'newitemamount'			=> array($adjustment),
			), ggnb_setup_admin('id'));
		}
		$fee		= (float)number_format((float)($params['fee'] ?: 0), 2,'.','');
		$add_trans	= ggnb_add_trans($user_id,$invoice_id,$paid_amount,$fee,$trans_id,'Boleto pago - confirmação via '.$origin);
		ggnb_set_charge_status($charge_id,$api_mode,'paid');
		// Um unico boleto pagavel por fatura: cancela os demais boletos em aberto
		$canceled = ggnb_cancel_other_charges($api_url,$access_token,$invoice_id,$api_mode,$charge_id);
		return array(
			'success'			=> true,
			'invoice_id'		=> $invoice_id,
			'user_id'			=> $user_id,
			'charge_id'			=> $charge_id,
			'paid_amount'		=> $paid_amount,
			'invoice_total'		=> $invoice_total,
			'invoice_balance'	=> $invoice_balance,
			'adjustment'		=> $adjustment,
			'update_invoice'	=> $update_invoice,
			'add_trans'			=> $add_trans,
			'other_charges'		=> $canceled,
			'origin'			=> $origin,
		);
	}
}
/**
 *
 * Cria a cobranca, emite o boleto e grava o registro local
 * @ggnb_new_charge
 *
 */
if(!function_exists('ggnb_new_charge')){
	function ggnb_new_charge($api_url,$access_token,$body,$body2,$invoice_amount,$invoice_id,$api_mode,$sent_config=0){
		$create_charge	= ggnb_create_charge($api_url,$access_token,$body);
		$charge_id		= $create_charge['result'];
		if(!is_int($charge_id)){
			return array('error'=>$create_charge['error']);
		}
		$pay_charge_	= ggnb_pay_charge($api_url,$access_token,$charge_id,$body2);
		$pay_charge		= $pay_charge_['result'];
		if($pay_charge_['error'] or !is_array($pay_charge)){
			return array('error'=>$pay_charge_['error']);
		}
		$store = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode,$sent_config);
		if($store['error']){
			return array('error'=>$store['error']);
		}
		return array(
			'charge_id'	=> $charge_id,
			'link'		=> $pay_charge['data']['link'],
			'expire_at'	=> $pay_charge['data']['expire_at'],
			'barcode'	=> $pay_charge['data']['barcode'],
		);
	}
}
/**
 *
 * Substitui o boleto da fatura: o novo so e criado depois do anterior estar cancelado na Efi
 * @ggnb_replace_charge
 *
 */
if(!function_exists('ggnb_replace_charge')){
	function ggnb_replace_charge($api_url,$access_token,$trans_id,$charge_status,$invoice_id,$api_mode,$body,$body2,$invoice_amount,$sent_config=0){
		// O boleto anterior tem de estar cancelado na Efí antes de existir um novo: nunca dois boletos pagáveis na mesma fatura
		$cancel = ggnb_cancel_charge_confirmed($api_url,$access_token,$trans_id,$api_mode);
		if($cancel['paid']){
			return array('error'=>$cancel['error'].' Nenhum boleto novo foi gerado.','keep_previous'=>true,'paid'=>true);
		}
		if(!$cancel['canceled']){
			return array('error'=>'Não foi possível cancelar o boleto anterior ('.$trans_id.'): '.$cancel['error'].' O novo boleto não foi gerado, para não deixar dois boletos pagáveis na mesma fatura.','keep_previous'=>true);
		}
		return ggnb_new_charge($api_url,$access_token,$body,$body2,$invoice_amount,$invoice_id,$api_mode,$sent_config);
	}
}
if( !function_exists('ggnb_store_billet') ){
	function ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode,$sent_config=0){
		ggnb_verifyInstall(); // v3.14.2: garante o schema antes do insert, sem depender da tela de configuracoes
		$error = NULL;
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
			'sent_config'=>(int)$sent_config, // v3.14.0: 1 = o modulo enviou multa e juros nesta cobranca
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
			Email gerado de acordo com às configurações do gateway <a title="Ir para as configurações do módulo ↗" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?updated=gofasgerencianetboleto#m_gofasgerencianetboleto">Gofas Efí Boleto</a>.<br/><br/>';
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
			$message .= '<p>Última verificação '.date('d/m/Y à\s H:i', strtotime($updated_at)).' - <a style="text-decoration:underline;" href="'.ggnb_whmcs_url('admin_url').'/configgateways.php?manage=gofasgerencianetboleto&resetversion=gofasgerencianetboleto#m_gofasgerencianetboleto">verificar agora</a>.</p>';
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
		if($opt===1){ // local_version string
			$version = json_decode($ggnb_version, true);
			return $version['local_version'];
		}
		if($opt===2){ // local_version integer
			$version = json_decode($ggnb_version, true);
			return (int)preg_replace("/[^0-9]/", "", $version['local_version']);
		}
		if($opt===3){ // full
			return $ggnb_version;
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
	function ggnb_module_version(){
		return '3.14.0';
	}
	function ggnb_update_stats(){
		$params = getGatewayVariables('gofasgerencianetboleto');
		if($params['sandbox']){
			return;
		}
		// Sem consentimento: contabiliza a confirmacao de pagamento de forma anonima (sem URL nem identificacao do admin)
		if(empty($params['consent_stats'])){
			$anon_version = ggnb_module_version();
			$anon_id = 'gefib-v'.$anon_version;
			$install_url = $anon_id;
			$installer_email = $anon_id.'@gofas.net';
			$installer_firstname = 'gefib';
			$installer_lastname = 'v'.$anon_version;
		}
		else{
			$whmcs_url = ggnb_whmcs_url();
			$setup_admin = ggnb_setup_admin();
			$install_url = $whmcs_url['admin_url'];
			$installer_email = $setup_admin['email'];
			$installer_firstname = $setup_admin['firstname'];
			$installer_lastname = $setup_admin['lastname'];
		}
		$query = '?software_id=7893&install_url='.$install_url.'&current_version='.ggnb_get_local_version().'&installer_email='.$installer_email.'&installer_firstname='.$installer_firstname.'&installer_lastname='.$installer_lastname.'&action=charge'.ggnb_sysinfo();
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
		// v3.14.0: aviso de precedencia. Se a conta Efi ja cobra multa ou juros, os campos do modulo
		// sao ignorados: a Efi aplica a configuracao da conta e o modulo nao envia nada.
		$cfg_params		= getGatewayVariables('gofasgerencianetboleto');
		$cfg_api_mode	= $cfg_params['sandbox'] ? 'sandbox' : 'live';
		$efi_account	= ggnb_efi_account_config($cfg_api_mode);
		$efi_notice		= '';
		if($efi_account['fine'] or $efi_account['interest']){
			$efi_notice .= '<p style="color:#b35c00;border-left:3px solid #b35c00;padding:8px 12px;margin-top:8px;">';
			$efi_notice .= '<b>Este campo está sendo ignorado.</b><br>';
			$efi_notice .= 'Sua conta Efí já está configurada para cobrar ';
			$notice_parts = array();
			if($efi_account['fine']){
				$notice_parts[] = 'multa de '.number_format($efi_account['fine']/100, 2, ',', '.').'%';
			}
			if($efi_account['interest']){
				$notice_parts[] = 'juros de '.number_format($efi_account['interest']/1000, 3, ',', '.').'% ao dia';
			}
			$efi_notice .= implode(' e ', $notice_parts).'.<br>';
			$efi_notice .= 'A configuração da conta tem prioridade, e o módulo não envia multa nem juros nas cobranças. ';
			$efi_notice .= 'Para usar os valores definidos aqui, remova multa e juros na sua conta Efí, em Configurações de cobranças &gt; Boletos bancários e carnês.';
			$efi_notice .= '</p>';
		}
		elseif($efi_account['known']){
			$efi_notice .= '<p style="color:#31708f;border-left:3px solid #31708f;padding:8px 12px;margin-top:8px;">';
			$efi_notice .= 'Sua conta Efí não tem multa nem juros configurados, então valem os valores definidos aqui.';
			$efi_notice .= '</p>';
		}
		else {
			$efi_notice .= '<p style="color:#31708f;border-left:3px solid #31708f;padding:8px 12px;margin-top:8px;">';
			$efi_notice .= 'Ainda não foi possível verificar se sua conta Efí tem multa e juros configurados. ';
			$efi_notice .= 'A verificação acontece automaticamente na tarefa cron. Enquanto este campo estiver preenchido, ';
			$efi_notice .= 'o valor definido aqui substitui o da sua conta Efí.';
			$efi_notice .= '</p>';
		}
		$module_version = ggnb_module_version();
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
					'Value' => 'Gofas Efí Boleto',
				],
				'separator_1' => [
					'Description' => '
					<div class="ggnb_separator" style="padding: 1px 15px 9px;">
					'.(string)ggnb_decrypt($check_updates['check']).'
						<div style="margin-left: 10px;">
							<h4 style="padding-top: 5px; color: red;">Gofas Efí Boleto para WHMCS v'.$module_version.' | requer WHMCS versão 8.6.1 ou superior</h4>
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
				'Value' => 'Gofas Efí Boleto',
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
						<h4 style="padding-top: 5px;">Módulo Efí Boleto para WHMCS v'.$module_version.'</h4>
						'.$check_updates['message'].'
						'.ggnb_file_exists_check('/includes/hooks/gofasgerencianetboleto.php').'
						<h5>Antes de iniciar a configuração, lembre-se de:</h5>
						<p>- Criar um <a style="text-decoration:underline;" target="_blank" href="'.$whmcs_url['admin_url'].'configcustomfields.php">campo personalizado de cliente</a> para CPF e/ou CNPJ, ou se preferir, criar dois campos distintos, um campo apenas para CPF e outro campo para CNPJ. O módulo identifica os campos do perfil do cliente automaticamente.</p>
						<p>- Criar uma Aplicação e obter as credencians <i>Client_ID</i> e <i>Client_Secret</i> da <a style="text-decoration: underline;" target="_blank" href="https://app.sejaefi.com.br/api/introducao">API Efí</a>. Veja <a style="text-decoration: underline;" target="_blank" href="https://s3.amazonaws.com/uploads.gofas.me/wp-content/uploads/2021/02/07004154/Gerencianet_api.png">aqui</a> onde encontrar.</p>
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
				'Description' => 'Marque essa opção para utilizar a API Efí em modo "Desenvolvimento" (modo de testes). <a style="text-decoration: underline;" href="https://app.sejaefi.com.br/api/introducao" target="_blank">Painel da API</a>.',
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
				'Description'       => '<span class="ggnb_optional_txt">(Opcional)</span> Insira o valor da comissão paga à Efí a cada Boleto com pagamento confirmado. Essa informação servirá para calcular e preencher o campo "Taxas" (fee) da lista de transações do WHMCS. Use ponto(.) para separar casas decimais, ex.: 1.5',
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
			'attached_billet' => array(
					'FriendlyName' => $opt_num++.'- Anexar PDF do Boleto no email',
					'Type' => 'yesno',
					'Default' => 'yes',
					'Description' => 'Adiciona o boleto em PDF como anexo aos emais de faturas do WHMCS.',
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
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Multa cobrada após o vencimento (máximo 10%). Use ponto(.) para separar casas decimais, ex.: 1.5.<br>
				A multa é registrada na cobrança e calculada pela Efí no momento do pagamento. O valor do Boleto não muda depois do vencimento.'.$efi_notice,
			),
			// Multa por atraso
			'juros' => array(
				'FriendlyName' => $opt_num++.'- Juros após o vencimento',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Juros por dia cobrados após o vencimento (Mínimo de 0.001 e máximo de 0.33). Use ponto(.) para separar casas decimais.<br>
				Os juros são registrados na cobrança e calculados pela Efí no momento do pagamento. O valor do Boleto não muda depois do vencimento.'.$efi_notice,
			),
			// Dias para baixa automatica do boleto vencido
			'diasparabaixa' => array(
				'FriendlyName' => $opt_num++.'- Dias para baixa do Boleto vencido',
				'Type' => 'text',
				'Size' => '10',
				'Default' => '',
				'Description' => '<span class="ggnb_optional_txt">(Opcional)</span> Número de dias, após o vencimento, em que o Boleto vencido continua podendo ser pago (de 0 a 120). Deixe em branco para usar o padrão da Efí, que é 90 dias. Insira 0 para impedir o pagamento a partir do dia seguinte ao vencimento.<br>
				Aplica-se apenas quando multa ou juros estão definidos neste módulo.',
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
					<p>As instruções do Boleto configuradas abaixo serão ignoradas e substituídas pelas instruções padrão da API Efí, quando multa e/ou juros estiverem ativos.</p>
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
			// Consentimento opt-in para envio de estatisticas de uso (action=charge)
			'consent_stats' => array(
				'FriendlyName' => $opt_num++.'- Enviar estatísticas de uso (opcional)',
				'Type' => 'yesno',
				'Default' => 'no',
				'Description' => 'Opcional. Controla o envio identificado das estatísticas de confirmação de pagamento. Marcado: as confirmações são enviadas à Gofas identificadas pela URL do WHMCS, versão do módulo, versão do WHMCS, versão do PHP, email e nome do administrador. Desmarcado: as confirmações de pagamento continuam sendo contabilizadas, porém de forma anônima, sem URL nem identificação do administrador. Em ambos os casos, a verificação de novas versões do módulo envia a URL do WHMCS e o contato do administrador para notificar atualizações e contabilizar a instalação como ativa.',
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
	}
}
if(!function_exists('gofasgerencianetboleto_link')){
function gofasgerencianetboleto_link($params){
	$devFee = 0;
	$system_url = ggnb_whmcs_url('whmcs_url');
	$returnUrl = $system_url.'/modules/gateways/gofasgerencianetboleto.php';

	$langPayNow = $params['langpaynow'];
	$moduleDisplayName = $params['name'];
	$moduleName = $params['paymentmethod'];
	if($params['sandbox'] ){
		$client_id = $params['clientidsandbox'];
		$client_secret = $params['clientsecretsandbox'];
		$api_mode = 'sandbox';
		$api_url = 'https://cobrancas-h.api.efipay.com.br/v1/';

	}
	elseif(!$params['sandbox']){
		$client_id = $params['clientid'];
		$client_secret = $params['clientsecret'];
		$api_mode = 'live';
		$api_url = 'https://cobrancas.api.efipay.com.br/v1/';
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
	// v3.14.0: precedencia entre a configuracao da conta Efi e a do modulo.
	// A Efi trata configurations como bloco unico: enviar um campo apaga o outro que estiver na conta,
	// e enviar os dois zerados apaga a configuracao da conta inteira. Por isso a regra e tudo ou nada.
	// Se a conta ja cobra multa ou juros, o modulo nao envia nada e nao calcula nada: a Efi aplica.
	$efi_account		= ggnb_efi_account_config($api_mode);
	$efi_account_has	= ($efi_account['fine'] or $efi_account['interest']) ? true : false;
	if($efi_account_has){
		$fine		= 0;
		$interest	= 0;
	}

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

	// v3.14.0: configurations e enviado sempre com os dois campos, ou nao e enviado.
	// Envio parcial removia da cobranca o encargo configurado na conta Efi, sem nenhum aviso ao cliente.
	if( $fine or $interest ){
		$configurations = array(
				'fine' => (int)$fine,
				'interest' => (int)$interest,
			);
		if( (int)$params['diasparabaixa'] or $params['diasparabaixa'] === '0' ){
			$configurations['days_to_write_off'] = (int)$params['diasparabaixa'];
		}
	}
	else {
		$configurations = false;
	}
	$sent_config = $configurations ? 1 : 0;

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
	$invoiceCredit =	(int)round($GetInvoiceResults['credit'] * 100);

	// Parâmetros das transações associadas à Fatura
	$trans_id = ggnb_active_charge($invoice_id,$api_mode);
	// Serviços/produtos relacionados à fatura
	$invoiceItemsItem = $GetInvoiceResults['items']['item'];

	// Parametros do Cliente
	$user_id = $params['clientdetails']['id'];
	// #204: a fatura identificada por invoiceid tem que pertencer ao cliente do contexto.
	// Bloqueia criar, substituir ou alterar cobranca com custom_id de fatura de outro cliente.
	$ggnb_invoice_owner = (int) Capsule::table('tblinvoices')->where('id','=',(int)$invoice_id)->value('userid');
	$ggnb_owner_ok = ( $ggnb_invoice_owner > 0 and (int)$user_id > 0 and $ggnb_invoice_owner === (int)$user_id );
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
	if(!$params['paybutton'] ){
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
				$ItEm_discount[] = array('name'=>$Value['description'],'amount'=>1,'value' => (int)round($Value['amount'] * 100) );
			}
	}
	if( $invoiceCredit>0and is_array($ItEm_discount)){
		$ItEm_discount = array_merge($ItEm_discount, array(array('name' => 'Crédito aplicado à fatura','amount'=>1,'value' => -($invoiceCredit))));
	}
	if(($invoiceCredit>0 and !is_array($ItEm_discount)) || ($invoiceCredit>0 and!$ItEm_discount)){
		$ItEm_discount = array(array('name' => 'Crédito aplicado à fatura','amount'=>1,'value' => -($invoiceCredit)));
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
		$discount_tax_valueRS = (int)round((($invoiceTotal / 100) * $discount_tax_value) * 100);
		$invoice_amount__  = (int)round($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ - $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Desconto de '.$discount_tax_value.'% (R$'.number_format($discount_tax_valueRS/100,  2, ',', '.').') para Boleto';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name'=>substr($ItEmValue['description'],0,255),'amount'=>1,'value'=>(int)round($ItEmValue['amount']*100));
			}
		}
		$discount_value = (int)round($discount_tax_value*100);
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
		$discount_tax_valueRS = (int)round($discount_tax_value*100);
		$invoice_amount__  = (int)round($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ - $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Desconto de R$'.number_format($discount_tax_value,  2, ',', '.').' para Boleto </p>';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ),'amount'=>1,'value' => (int)round($ItEmValue['amount']*100),);
			}
		}
		$discount_value = (int)round($discount_tax_value * 100);
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
		$discount_tax_valueRS = (int)round((($invoiceTotal / 100) * $discount_tax_value) * 100);
		$invoice_amount__  = (int)round($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ + $discount_tax_valueRS;
		$discount_tax_visible_message	.= '<p>Tarifa de '.$discount_tax_value.'% (R$'.number_format($discount_tax_valueRS/100,  2, ',', '.') . ') para Boleto</p>';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ), 'amount'=>1,'value' => (int)round($ItEmValue['amount']*100),);
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

		$discount_tax_valueRS = (int)round($discount_tax_value*100);
		$invoice_amount__  = (int)round($invoiceTotal*100);
		$invoice_amount_ = $invoice_amount__ + $discount_tax_valueRS;

		$discount_tax_visible_message	.= '<p>Tarifa de R$'.number_format($discount_tax_value,  2, ',', '.').' para Boleto</p>';

		foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
			if($ItEmValue['amount'] >= 0 ){
				$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ), 'amount'=>1,
				'value' => (int)round($ItEmValue['amount']*100),);
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
				'value' => (int)round($ItEmValue['amount']*100),);
			}
		}
		$invoice_amount_ = (int)round($invoiceTotal * 100);
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
	// v3.14.0: multa e juros NAO entram mais como item da cobranca e nao alteram o vencimento do boleto.
	// Quem calcula os encargos e a Efi, no momento do pagamento, a partir do bloco configurations
	// registrado na cobranca ou da configuracao da conta. Somar os encargos ao valor fazia o total do
	// boleto crescer a cada dia de atraso, e cada crescimento disparava o cancelamento e a recriacao
	// da cobranca, trocando charge_id, vencimento e valor principal a cada execucao da cron (#203).
	// Os valores abaixo sao apenas informativos, exibidos na fatura.
	$invoice_amount = (int)$invoice_amount_;
	$fine_show		= $efi_account_has ? (int)$efi_account['fine'] : (int)$fine;
	$interest_show	= $efi_account_has ? (int)$efi_account['interest'] : (int)$interest;
	if( $fine_show and $fine_interest_values['due_days'] ){
		$discount_tax_visible_message	.= '<p>Multa de '.number_format($fine_show/100, 2, ',', '.').'% por atraso, calculada pelo banco no pagamento.</p>';
	}
	if( $interest_show and $fine_interest_values['due_days'] ){
		$discount_tax_visible_message	.= '<p>Juros de '.number_format($interest_show/1000, 3, ',', '.').'% ao dia, calculados pelo banco no pagamento.</p>';
	}
	$discount_tax_visible_message	.= '<p>Total do Boleto: R$'.number_format((int)($invoice_amount)/100,  2, ',', '.').($fine_show or $interest_show ? ' (sem multa e juros)' : ''). '</p>';
	if($ItEm_discount){
		$ItEm = array_merge($ItEm, $ItEm_discount);
	}
	//$PaYeEe = 'b7ac135895cfb50a2a90cf28fe0d15e0'; // Gofas Software
	//$PaYeEe = '4c640ca051ab239b194ed2609967a831'; // Mauricio Gofas


	foreach($ItEm as $key => $value){
		$ItEm_values[$key] = $value['value'];
	}
	// Valor efetivo do boleto: soma dos itens enviados à Efí. Inclui multa e juros por atraso,
	// crédito aplicado à fatura e desconto/taxa do módulo. É este valor, e não o total da fatura,
	// que a Efí grava como total da cobrança, então é ele que serve para comparar com o boleto existente.
	$billet_amount = (int)array_sum($ItEm_values);
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
	if( !$ggnb_owner_ok ){
		$error .= '#204: fatura '.$invoice_id.' nao pertence ao cliente '.$user_id.' (dono real: '.$ggnb_invoice_owner.'). Cobranca nao gerada.';
		if($params['log']){
			logModuleCall('gofasgerencianetboleto','owner_guard_204',array('invoice_id'=>$invoice_id,'context_user'=>$user_id,'invoice_owner'=>$ggnb_invoice_owner),'',$error);
		}
	}
	if( $ggnb_owner_ok and ((stripos($_SERVER['REQUEST_URI'],'viewinvoice')) or (!stripos($_SERVER['REQUEST_URI'], 'viewinvoice') and ($params['billetonemail'])))){
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
			// 1) Boleto pago: confirma o pagamento (valida fatura, cliente e valor pago) e recarrega a fatura
			if(!$error and ggnb_is_paid_status($chargeExistStatus)){
				$confirm = ggnb_confirm_payment($api_url,$access_token,$chargeExist['data'],$api_mode,$params,'acesso à fatura');
				if($params['log']){
					logModuleCall('gofasgerencianetboleto','confirm_payment',array('origem'=>'acesso à fatura','invoice_id'=>$invoice_id,'charge_id'=>$trans_id),'',$confirm);
				}
				header_remove();
				header("Location: ".$system_url.'/viewinvoice.php?id='.$params['invoiceid'],true,303);
				exit;
			}
			// 2) Boleto em aberto, com o mesmo valor e dentro do vencimento: reaproveita, sem nenhuma alteração na Efí
			elseif(!$error and $chargeExistID === (int)$trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate >= date('Y-m-d') and (int)$chargeExistTotal === (int)$billet_amount){
				$link		= $chargeExist['data']['payment']['banking_billet']['link'];
				$expire_at	= $chargeExistDuedate;
				$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
			}
			// 3) Boleto vencido, mesmo valor, com multa ou juros ativos (no modulo ou na conta Efi):
			//    reaproveita a cobranca intacta. Mesmo charge_id, mesmo vencimento, mesmo valor principal.
			//    Multa e juros sao calculados pela Efi e pela rede bancaria no momento do pagamento,
			//    conforme o bloco configurations registrado na cobranca. Enquanto o boleto nao for baixado
			//    (days_to_write_off, padrao 90 dias) ele continua pagavel, entao nao ha nada a alterar.
			elseif(!$error and $chargeExistID === (int)$trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate < date('Y-m-d') and (int)$chargeExistTotal === (int)$billet_amount and ($configurations or $efi_account_has) and !$cancelBillet){
				$link		= $chargeExist['data']['payment']['banking_billet']['link'];
				$expire_at	= $chargeExistDuedate;
				$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
			}
			// 4) Boleto vencido, mesmo valor, sem multa nem juros em lugar nenhum: altera o vencimento do próprio boleto
			elseif(!$error and $chargeExistID === (int)$trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate < date('Y-m-d') and $chargeExistDuedate > date('Y-m-d',strtotime('-29 days')) and (int)$chargeExistTotal === (int)$billet_amount and !$cancelBillet){
				$updateBillet = ggnb_update_billet($api_url,$access_token,$trans_id,$billet_duedate);
				if((string)$updateBillet['result'] === 'success'){
					$link		= $chargeExist['data']['payment']['banking_billet']['link'];
					$expire_at	= $billet_duedate;
					$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
					Capsule::table('gofasgerencianetboleto')->where('charge_id','=',(string)$trans_id)->where('api_mode','=',$api_mode)->update(array('expire_at'=>$billet_duedate));
				}
				else {
					$error .= $updateBillet['error'];
				}
			}
			// 5) Valor da fatura mudou, boleto cancelado, fora do prazo de alteração, ou "Cancelar Boleto Vencido" marcado:
			//    substitui o boleto. O novo só é criado depois do anterior ser cancelado na Efí.
			elseif(!$error){
				$replace = ggnb_replace_charge($api_url,$access_token,$trans_id,$chargeExistStatus,$invoice_id,$api_mode,$body,$body2,$invoice_amount,$sent_config);
				if($replace['error'] and $replace['keep_previous'] and $chargeExist['data']['payment']['banking_billet']['barcode']){
					// Boleto anterior não pôde ser cancelado: mantém o boleto existente para não deixar dois boletos pagáveis
					$link		= $chargeExist['data']['payment']['banking_billet']['link'];
					$expire_at	= $chargeExistDuedate;
					$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
					if($emailonError){
						$sendEmailonError = ggnb_send_error_email($invoice_id,$user_id,$firstname,$lastname,$emailonError,$replace['error']);
					}
					if($params['log']){
						logModuleCall('gofasgerencianetboleto','replace_charge',array('invoice_id'=>$invoice_id,'charge_id'=>$trans_id,'status'=>$chargeExistStatus),'',$replace);
					}
				}
				elseif($replace['error']){
					$error .= $replace['error'];
				}
				else {
					$link		= $replace['link'];
					$expire_at	= $replace['expire_at'];
					$barcode	= $replace['barcode'];
				}
			}
		} // End of if( $trans_id and !$error)
		/// The first billet for the invoice
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
						$ggnb_store_billet = ggnb_store_billet($pay_charge,$invoice_amount,$invoice_id,$api_mode,$sent_config);
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
				$sendEmailonError = ggnb_send_error_email( $invoice_id, $user_id, $firstname, $lastname, $emailonError, $error);
			}
			if($params['log']){
				logModuleCall("gofasgerencianetboleto","genarate_billet",array('invoice_id'=>$invoice_id,'charge_id'=>$trans_id,'invoice_amount'=>$invoice_amount),"", $error);
			}
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
			if($params['log']){
				logModuleCall("gofasgerencianetboleto","genarate_billet_result",array('invoice_id'=>$invoice_id,'charge_id'=>$trans_id,'invoice_amount'=>$invoice_amount),"", array('link'=>$link,'expire_at'=>$expire_at,'barcode'=>$barcode));
			}
			return $result.$css;
		}
	} // End of if( $generate_billet )
	else {
		if($params['log']){
			logModuleCall("gofasgerencianetboleto","genarate_billet",array('invoice_id'=>$invoice_id,'request_uri'=>$_SERVER['REQUEST_URI']),"", array('billet_not_generated'=>true));
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
		$api_url		= 'https://cobrancas-h.api.efipay.com.br/v1/';
	}
	elseif( !$params['sandbox'] ){
		$sandbox		= false;
		$client_id		= $params['clientid'];
		$client_secret	= $params['clientsecret'];
		$api_mode		= 'live';
		$api_url		= 'https://cobrancas.api.efipay.com.br/v1/';
	}
	$callback_log	= array();
	$access_token_	= ggnb_get_token($api_url,$client_id,$client_secret);
	if($access_token_['access_token']){
		$access_token = $access_token_['access_token'];
	}
	if($access_token_['error']){
		$callback_log['error'] = $access_token_['error'];
	}
	try {
		if($access_token){
			$notification		= ggnb_get_notification($api_url,$access_token,$_REQUEST['notification']);
			$notificationData	= is_array($notification['data']) ? end($notification['data']) : array();
			$charge_id			= (string)$notificationData['identifiers']['charge_id'];
			if(!$charge_id){
				$callback_log['error'] = 'Notificação sem charge_id: '.json_encode($notification);
			}
			else {
				// A cobranca e consultada na Efi: a notificacao so informa qual cobranca mudou de status
				$chargeExist_	= ggnb_detail_charge($api_url,$access_token,$charge_id);
				$charge			= $chargeExist_['result']['data'];
				if($chargeExist_['error'] or !$charge['charge_id']){
					$callback_log['error'] = 'Não foi possível consultar a cobrança '.$charge_id.': '.$chargeExist_['error'];
				}
				else {
					$callback_log['charge']		= array('charge_id'=>$charge['charge_id'],'status'=>$charge['status'],'custom_id'=>$charge['custom_id'],'total'=>$charge['total'],'paid_value'=>$charge['paid_value']);
					$callback_log['confirm']	= ggnb_confirm_payment($api_url,$access_token,$charge,$api_mode,$params,'notificação/callback');
				}
			}
		}
	}
	catch (Exception $e){
		$callback_log['error'] = $e->getMessage();
	}
	if($params['log']){
		logModuleCall('gofasgerencianetboleto','receive_callback',array('notification'=>$_REQUEST['notification'],'api_mode'=>$api_mode),'',$callback_log);
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
		ggnb_verifyInstall(); // v3.14.2: garante o schema antes de ler sent_config na tarefa cron
		$params = getGatewayVariables('gofasgerencianetboleto');
		if(!$params['maxinvoicespercheck'] || $params['maxinvoicespercheck'] < 1 || empty($params['maxinvoicespercheck'])){
        	return;
    	}
		if( $params['sandbox'] ){
			$sandbox		= true;
			$client_id		= $params['clientidsandbox'];
			$client_secret	= $params['clientsecretsandbox'];
			$api_mode		= 'sandbox';
			$api_url		= 'https://cobrancas-h.api.efipay.com.br/v1/';
		}
		elseif( !$params['sandbox'] ){
			$sandbox		= false;
			$client_id		= $params['clientid'];
			$client_secret	= $params['clientsecret'];
			$api_mode		= 'live';
			$api_url		= 'https://cobrancas.api.efipay.com.br/v1/';
		}
		$check_schedule = ggnb_check_schedule();
    	if(!is_array($check_schedule)){
        	return;
    	}
		if(is_array($check_schedule)){
		  // Get Billets
		  try {
			  $log		= array();
			  $boletos	= array();
			  $invoices	= array();
			  $access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
			  if($access_token_['access_token']){
				  $access_token = $access_token_['access_token'];
			  }
			  if($access_token_['error']){
				  $error .= $access_token_['error'];
			  }
			  if($access_token){
				  $account_config_read	= false; // v3.14.0: uma leitura da configuracao da conta por execucao
				  $account_config		= false;
				  // Faturas em aberto pagas por boleto
				  foreach( Capsule::table('tblinvoices') -> where( 'status','=','Unpaid' )->where('paymentmethod','=','gofasgerencianetboleto')->get() as $tblinvoices){
					  // Todos os boletos ainda nao confirmados da fatura, do mais novo para o mais antigo
					  foreach( Capsule::table('gofasgerencianetboleto')
						  ->where('invoice_id','=',(string)$tblinvoices->id)
						  ->where('api_mode','=',$api_mode)
						  ->whereNotIn('status',array('paid','canceled'))
						  ->orderBy('id','desc')
						  ->get(array('charge_id','sent_config')) as $local_boleto ){
							  $boleto			= ggnb_detail_charge($api_url,$access_token,$local_boleto->charge_id);
							  $charge			= $boleto['result']['data'];
							  $charge_status	= (string)$charge['status'];
							  $boletos[$local_boleto->charge_id] = $charge_status ?: $boleto['error'];
							  if($boleto['error'] or !$charge['charge_id']){
								  $error .= 'Erro ao verificar o Boleto '.$local_boleto->charge_id.': '.$boleto['error'];
								  continue;
							  }
							  // v3.14.0: cobranca criada sem o bloco configurations do modulo. O que a Efi
							  // aplicou nela veio da conta, entao serve para descobrir a configuracao da conta
							  // sem criar nenhuma cobranca de sondagem.
							  if(!(int)$local_boleto->sent_config and !$account_config_read){
								  $account_config_read = true;
								  $account_config = ggnb_efi_account_config_store($api_mode,ggnb_efi_charge_config($charge));
							  }
							  if($charge_status === 'canceled'){
								  ggnb_set_charge_status($local_boleto->charge_id,$api_mode,'canceled');
								  continue;
							  }
							  if(ggnb_is_paid_status($charge_status)){
								  // Confirma o pagamento e cancela os demais boletos em aberto da fatura
								  $invoices[$tblinvoices->id] = ggnb_confirm_payment($api_url,$access_token,$charge,$api_mode,$params,'tarefa cron');
								  break;
							  }
					  } // End Foreach
				  } // End Foreach
			  }
		 	}
			catch (Exception $e) {
			  	$error	.= 'Erro ao listar boletos pagos: ' . $e->getMessage();
			  	$log['error'] = $error;
		  	}
		}
		$log['boletos'] = $boletos;
		$log['invoices'] = $invoices;
		$log['error'] = $error;
		if($params['log']){
			logModuleCall('gofasgerencianetboleto','AfterCronJob',array('module_version'=>ggnb_module_version()),'',array($log) );
		}
		return;  
	}
}
add_hook("AfterCronJob",1,"ggnb_check_status_updates");
add_hook('EmailPreSend',1, function($vars){
	if(
		  $vars['messagename'] === 'Invoice Created' ||
		  $vars['messagename'] === 'Invoice Payment Reminder' ||
		  $vars['messagename'] === 'First Invoice Overdue Notice' ||
		  $vars['messagename'] === 'Second Invoice Overdue Notice' ||
		  $vars['messagename'] === 'Third Invoice Overdue Notice'
	){
		$params = getGatewayVariables('gofasgerencianetboleto');
		if($params['billetonemail']){
		  	$ggnb_merge_fields	= array();
		  	$invoice			= localAPI( 'GetInvoice', array('invoiceid' => $vars['relid']), ggnb_setup_admin('id'));
			if($params['log']){
		  		logModuleCall('gofasgerencianetboleto', 'EmailPreSend',['messagename'=>$vars['messagename'],'relid'=>$vars['relid']],'',['invoice_id'=>$invoice['invoiceid'],'status'=>$invoice['status']]);
		  	}
			if( (float)$invoice['total'] > (float)'0.00' and $invoice['paymentmethod'] === 'gofasgerencianetboleto'){
				// Saved Billets
				$billet_saved = array();
				foreach( Capsule::table('gofasgerencianetboleto') -> where('invoice_id','=',$vars['relid'])->whereNotIn('status',array('paid','canceled'))->orderBy('id','desc')->get(
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
					if($params['log']){
						logModuleCall('gofasgerencianetboleto', 'EmailPreSend2', array('invoice'=>$invoice,'billet_saved'=>$billet_saved),'',array('ggnb_merge_fields'=>$ggnb_merge_fields));
					}
					return $ggnb_merge_fields;
				}
				return;
		  	}
		  	return;
	  	}
	}
	return;
});
//Output additional merge fields in the list when editing an email template
add_hook('EmailTplMergeFields', 1, function($vars){
	$ggnb_merge_fields = array();
	$ggnb_merge_fields['ggnb_billet_info']	= 'Efí: Informações do boleto';
	$ggnb_merge_fields['ggnb_link']			= 'Efí: Link para o boleto';
	$ggnb_merge_fields['ggnb_pdf']			= 'Efí: Link para o boleto em PDF';
	$ggnb_merge_fields['ggnb_barcode']		= 'Efí: Linha digitável do boleto';
	$ggnb_merge_fields['ggnb_expire_at']	= 'Efí: Vencimento do boleto';
	$ggnb_merge_fields['ggnb_total']		= 'Efí: Total do boleto';
	$ggnb_merge_fields['ggnb_charge_id']	= 'Efí: ID da transação';
	$ggnb_merge_fields['ggnb_api_mode']		= 'Efí: API mode (sandbox ou live)';
	$ggnb_merge_fields['ggnb_debug']		= 'Efí: Debug nos emails';
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
			$api_url		= 'https://cobrancas-h.api.efipay.com.br/v1/';
		}
		elseif(!$sandbox){
			$sandbox		= false;
			$client_id		= $params['clientid'];
			$client_secret	= $params['clientsecret'];
			$api_mode		= 'live';
			$api_url		= 'https://cobrancas.api.efipay.com.br/v1/';
		}
	}
	$invoice	= localAPI('GetInvoice',array( 'invoiceid' => $vars['invoiceid'], ), ggnb_setup_admin('id'));	
	// Parâmetros das transações associadas à Fatura
	$trans_id = ggnb_active_charge($vars['invoiceid'],$api_mode);
	if($trans_id){
	$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
	if($access_token_['access_token']){
	  $access_token = $access_token_['access_token'];
	}
	if($access_token_['error']){
	  $error = $access_token_['error'];
	}
	if($params['log']){
		logModuleCall('gofasgerencianetboleto','access_token',array('api_url'=>$api_url),array('token_ok'=>(bool)$access_token_['access_token'],'error'=>$access_token_['error']) );
	}
	try {
		//$id = array('id' => (int)$trans_id);
		$cancel_charge = ggnb_cancel_charge($api_url,$access_token,$trans_id);	  
		if((string)$cancel_charge['result'] === (string)'success'){
			ggnb_set_charge_status($trans_id,$api_mode,'canceled');
			if($params['log']){
			  	logModuleCall('gofasgerencianetboleto','cancel_transaction',array('Sucesso:'=>$cancel_charge), $access_token_ );
		  	}
		}
		else {
			$error	= 'Erro ao cancelar Transação: ' . $cancel_charge['error'];
			if($params['log']){
				logModuleCall('gofasgerencianetboleto','cancel_transaction_1',array('Error:'=>$cancel_charge), $access_token_);
			}
		}
	}
	catch (Exception $e){
		$error	= 'Erro ao cancelar Transação: ' . $e->getMessage();
		logModuleCall('gofasgerencianetboleto','cancel_transaction_2',array('Error:'=>$error), '');
	}
	}
	if($params['log']){
		logModuleCall('gofasgerencianetboleto', 'InvoiceCancelled', array('invoice_id'=>$vars['invoiceid'],'charge_id'=>$trans_id),array('cancel_charge'=>$cancel_charge),'');
	}
});