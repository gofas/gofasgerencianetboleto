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

//if(!defined('WHMCS')){ die('Esse arquivo não pode ser acessado diretamente'); }
use WHMCS\Database\Capsule;
// Parâmetros do sistema
$companyName				= $params['companyname'];
foreach( Capsule::table('tblconfiguration') -> where('setting', '=', 'ggnbwhmcsurl') -> get( array( 'value','created_at') ) as $ggnbwhmcsurl_ ){
	$ggnbwhmcsurl					= $ggnbwhmcsurl_->value;
}
$system_url					= $ggnbwhmcsurl;
$returnUrl					= $system_url.'modules/gateways/gofasgerencianetboleto/includes/callback.php';
$langPayNow					= $params['langpaynow'];
$moduleDisplayName			= $params['name'];
$moduleName					= $params['paymentmethod'];

// Parâmetros do Módulo
$module_version	= '3.2.1';
$sandbox		= $params['sandbox'];

if( $sandbox ){
	$client_id				= $params['clientidsandbox'];
	$client_secret			= $params['clientsecretsandbox'];
	$api_mode				= 'sandbox';
	$api_url				= 'https://sandbox.gerencianet.com.br/v1/';
	
} elseif(!$sandbox){
	$client_id				= $params['clientid'];
	$client_secret			= $params['clientsecret'];
	$api_mode				= 'live';
	$api_url				= 'https://api.gerencianet.com.br/v1/';
}

if( stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php') ){ // Verifica se a página é uma fatura
	$isInvoice	= true;
	$debug		= $params['debug'];
	
} else {
	$isInvoice	= false;
	$debug		= false;
}

if($isInvoice and $params['debug']){
	$debug	= true;
} else {
	$debug	= false;
}

if(!$debug and !$_REQUEST['redirectToBillet']){
	$redirectToBillet	= $params['redirecttobillet'];
}
if(!$debug and $_REQUEST['redirectToBillet'] === "true"){
	$redirectToBillet	= true;
}
if( $debug and $_REQUEST['redirectToBillet'] === "true"){
	$redirectToBillet	= true;
	$debug = false;
}
if($_REQUEST['redirectToBillet'] === "false"){
	$redirectToBillet	= false;
}
$log = $params['log'];

if($debug || $log){
	$log_result = array();
	$debug_or_log = true;
}
if($debug){
			echo '<pre style="height:300px;max-width: 850px;margin: 20px auto;padding: 5px 15px 5px 15px;" class="debug" onfocus="select_all_and_copy(this)" onclick="select_all_and_copy(this)">';
			echo '<h4 style="text-align:center;line-height: 1.4;border-bottom: 1px solid black;padding: 0px 0px 12px 0px;margin: 11px 0px 20px 0px;">Você está vendo essas informações na tela por quê a opção "debug" do módulo<br><b>Gofas Gerencianet Boleto v'.$module_version.'</b> está ativa.</h4>';
			echo '<p>Operações bem sucedidas possuem títulos <span class="ok">verdes</span> e erros são destacados em <span class="erro">vermelho</span></p>';
			echo '<p>Saiba mais sobre como diagnosticar erros e coletar informações para suporte <a target="_blank" href="https://gofas.net/?p=7899&rf=ggnbfatura">neste link</a></p>';
			echo '<h4>Suporte:</h4>';
			echo '<p>Veja várias soluções para dificuldades comuns no <a href="https://gofas.net/forums/forum/whmcs/modulo-gerencianet-boleto-para-whmcs/?rf=ggnbfatura" target="_blank">fórum de suporte do módulo</a>.</p>';
			echo'<p  onfocus="select_all_and_copy(debugDiv)" onclick="select_all_and_copy(debugDiv)"">1) <span style="cursor:copy;text-decoration: underline; ">Clique aqui para copiar as informações de diagnóstico (debug)</span>.</p>';
			echo'<p>2) <a target="_blank" tyle="cursor:alias;" href="https://gofas.net/forums/forum/whmcs/modulo-gerencianet-boleto-para-whmcs/?rf=ggnbfatura">Clique aqui para publicar no fórum do módulo as informações de diagnósico</a>.</p>';
}
$emailonError				= $params['emailonerror'];
$showDueDate				= $params['showduedate'];
$showBarCode				= $params['showbarcode'];
$requireCNPJandCPF			= $params['requirecnpjandcpf'];
$cancelBillet				= $params['cancelbillet'];
$customfCPF					= $params['customfieldcpf'];
$customfCNPJ				= $params['customfieldcnpj'];
$fine						= $params['multa'] * 100;
$interest					= $params['juros'] * 1000;
$fee						= $params['fee'];

// Dias adicionais à Data de vencimento
if( $params['diasparavencimento'] ){
	$diasParaVencimento		= '+'.$params['diasparavencimento'].' days';

} elseif( $params['diasparavencimento'] == '0'){
	$diasParaVencimento		= 'zero';
}

elseif( !$params['diasparavencimento'] ){
	$diasParaVencimento		= '+1 day';
}
else {
	$diasParaVencimento		= false;
}

if($params['message']){ $message = $params['message'];
}
elseif(!$params['message'] || empty($params['message'])){
	$message				= 'Acesse '.$ggnbwhmcsurl.' para gerar 2ª via.';
}

if( $params['minimunamount'] ){
	$minimunAmount			= $params['minimunamount'];
}
elseif( !$params['minimunamount'] || $params['minimunamount'] < 5 ){
	$minimunAmount			= '5.00' ;
}

if($params['paybutton']){
	$payButton				= '<img alt="Visualizar Boleto" src="'.$params['paybutton'].'">';
}elseif(!$params['paybutton']){
	$payButton				= 'Visualizar Boleto';
}

// Instruções
if($params['instruction1']){
	$instruction1			= $params['instruction1'];
}elseif(!$instruction1){
	$instruction1			= 'Sr. Caixa, após vencimento aceitar somente no banco emissor.';
}
if($params['instruction2']){
	$instruction2			= $params['instruction2'];
}elseif(!$instruction2){
	$instruction2			= 'Sr. Caixa, não cobrar juros após o vencimento.';
}
if($params['instruction3']){
	$instruction3			= $params['instruction3'];
}elseif(!$instruction3){
	$instruction3			= 'Sr. Caixa, não cobrar multa após o vencimento.';
}
if($params['instruction4']){
	$instruction4			= $params['instruction4'];
}elseif(!$instruction4){
	$instruction4			= 'Sr. Caixa, aceitar apenas pagamento em dinheiro.';
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
$invoice_id					= $params['invoiceid'];
$getinvoiceid['invoiceid']	= $invoice_id;
$GetInvoiceResults			= localAPI('getinvoice',$getinvoiceid,$params['admin']);

$invoice_duedate				= $GetInvoiceResults['duedate']; // Data de vencimento da fatura

if( $invoice_duedate > date('Y-m-d') ){
	$billet_duedate			= $invoice_duedate;
	
}
elseif( $invoice_duedate === date('Y-m-d') ){
	$billet_duedate			= date('Y-m-d', strtotime('+1 day'));

}
elseif( $invoice_duedate < date('Y-m-d') and !$diasParaVencimento ){
	$billet_duedate			= date('Y-m-d', strtotime('+1 day')); // Se fatura já venceu, data de vencimento do boleto = Hoje + 1 dia
	
}
elseif( $invoice_duedate < date('Y-m-d') and $diasParaVencimento and $diasParaVencimento !== 'zero'){
	$billet_duedate			= date('Y-m-d', strtotime( $diasParaVencimento )); // Se fatura já venceu, data de vencimento do boleto = Hoje + X dia(s)

}
elseif( $invoice_duedate < date('Y-m-d') and $diasParaVencimento and $diasParaVencimento === 'zero'){
	$billet_duedate			= date('Y-m-d', strtotime('+1 day')); // Se fatura já venceu, data de vencimento do boleto = Hoje
}

$invoiceTotal	=	$GetInvoiceResults['total'];
$invoiceCredit	=	(int)($GetInvoiceResults['credit'] * 100);

// Parâmetros das transações associadas à Fatura
$trans_idendA				= $GetInvoiceResults['transactions'];
if($trans_idendA){
	$trans_idend				= $trans_idendA['transaction'];
}
if($trans_idend){
	$trans_idp				= end( $trans_idend );
	$trans_id_				= $trans_idp['transid'];
	
	// Verifica se a transação pertence ao módulo
	if( strpos( $trans_id_, 'ggnb') !== false and (strpos( $trans_id_, 'unpaid') !== false or strpos( $trans_id_, 'waiting') !== false ) and strpos( $trans_id_, $api_mode) ){
		$trans_id					= (int)preg_replace('/[^0-9]/', '', $trans_id_ ); // ggnb_waiting_213630
	}
	else {
		$trans_id				= false;
	}
}
else {
	$trans_id				= false;
}
// Serviços/produtos relacionados à fatura
$invoiceItemsItem	= $GetInvoiceResults['items']['item'];

// Parametros do Cliente
$user_id					= $params['clientdetails']['id'];
$firstname					= $params['clientdetails']['firstname'];
$lastname					= $params['clientdetails']['lastname'];
//$phone						= preg_replace('/[^0-9]/', '', $params['clientdetails']['phonenumber']);
$phone						= preg_replace('/[^\da-z]/i', '', $params['clientdetails']['phonenumber']);

if( $params['clientdetails']['companyname'] ){
	$corporateName			= $params['clientdetails']['companyname'];
} elseif(!$params['clientdetails']['companyname']){
	$corporateName			= $firstname . ' ' . $lastname;
}

/**
 *
 * Determine custom fields id
 *
 */
//$customfields = array();
foreach( Capsule::table('tblcustomfields') -> where( 'type', '=', 'client')  -> get( array( 'fieldname', 'id') ) as $customfield ){
	$customfield_id					= $customfield->id;
	$customfield_name				= ' '.strtolower( $customfield->fieldname );
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
	$cpf 				= false;
	$cnpj				= $cpf_customfield_value;
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
$css		= '<style type="text/css">a, a:hover {cursor: pointer;}.ggnbp {font-size:12px;margin: 0;}.ggnbspan{font-size:12px;}span.ggnberror {color: red;}';
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

if($debug_or_log){
	$log_result['custom_discount_type']		= $params['custom_discount_type'] . ' - ' . $custom_discount_type;
	$log_result['custom_discount_value']	=  $params['custom_discount_value']. ' - ' . $custom_discount_value;
}

// Define desconto personalizado 
if( $custom_discount_value and $custom_discount_type ){
	$discount_tax			= 1;
	$discount_tax_value		= $custom_discount_value;
	
	if( strpos( $custom_discount_type, '%') !== false ){
		$discount_tax_type		= 1;
	}
	
	if( strpos( $custom_discount_type, '$') !== false ){
		$discount_tax_type		= 2;
	}
}
else {
	$discount_tax				= (int)$params['descontooutaxa']; // Define se é desconto ou taxa: 1 = desconto | 2 = taxa
	$discount_tax_type			= (int)$params['tipodescontooutaxa']; // 1 = % | 2 = $  
	$discount_tax_value 		= $params['valordescontooutaxa'];
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
$discount_tax_visible		= $params['exibedescontooutaxa'];

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
function ggnb_calculate_fine_interest( $VALUE, $fine, $interest, $invoice_duedate, $debug ){

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
		//$new_value = $VALUE * 100;
		return false;
	}
}
}

// Desconto em porcentagem %
if( $discount_tax === 1 and $discount_tax_type === 1 and $discount_tax_value ){
	$discount_tax_valueRS			= (int)((((float)$invoiceTotal / (float)100 )*(float)$discount_tax_value)*100);
	$invoice_amount__ 				= (int)($invoiceTotal*100);
	$invoice_amount_				= $invoice_amount__ - $discount_tax_valueRS;
	
	$discount_tax_visible_message	.= '<p>Desconto de '.$discount_tax_value.'% (R$'.number_format($discount_tax_valueRS/100,  2, ',', '.').') para Boleto';
	
	foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
		if($ItEmValue['amount'] >= 0 ){
			$ItEm[] = array('name'=>substr($ItEmValue['description'],0,255),'amount'=>1,'value'=>(int)($ItEmValue['amount']*100));
		}
	}

	$discount_value	= (int)($discount_tax_value*100);
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
	$discount_tax_valueRS			= (int)($discount_tax_value*100);
	//$invoice_amount 				= (int)($invoiceTotal - $discount_tax_value) * 100;
	$invoice_amount__ 				= (int)($invoiceTotal*100);
	$invoice_amount_				= $invoice_amount__ - $discount_tax_valueRS;
	
	$discount_tax_visible_message	.= '<p>Desconto de R$'.number_format($discount_tax_value,  2, ',', '.').' para Boleto </p>';
	
	foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
		if($ItEmValue['amount'] >= 0 ){
			$ItEm[] = array('name' => substr($ItEmValue['description'], 0, 255 ),'amount'=>1,'value' => (int)($ItEmValue['amount']*100),);
		}
	}
	
	$discount_value	= (int)($discount_tax_value * 100);
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
	$discount_tax_valueRS			= (int)((((float)$invoiceTotal / (float)100 )*(float)$discount_tax_value)*100);
	$invoice_amount__ 				= (int)($invoiceTotal*100);
	$invoice_amount_				= $invoice_amount__ + $discount_tax_valueRS;
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
	
	$discount_tax_valueRS			= (int)($discount_tax_value*100);
	$invoice_amount__ 				= (int)($invoiceTotal*100);
	$invoice_amount_				= $invoice_amount__ + $discount_tax_valueRS;
	
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
$fine_interest_values	= array();
$fine_values_arr		= array();
$interest_values_arr		= array();
foreach( $invoiceItemsItem as $ItEmKey => $ItEmValue){
	if($ItEmValue['amount'] >= 0 ){
		$fine_interest_values = ggnb_calculate_fine_interest( $ItEmValue['amount'], $fine, $interest, $invoice_duedate, $debug);
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
	$invoice_amount = $invoice_amount_ + (int)array_sum($fine_values_arr)/100;
	$discount_tax_visible_message	.= '<p>Multa por atraso: R$'.number_format((int)array_sum($fine_values_arr)/100,  2, ',', '.'). '</p>';
	$billet_duedate			= date('Y-m-d');
}
elseif( $fine_interest_values['fine_value'] and $fine_interest_values['interest_value']   ){
	$invoice_amount = $invoice_amount_ + (int)((int)array_sum($fine_values_arr) + (int)array_sum($interest_values_arr));
	$discount_tax_visible_message	.= '<p>Multa de '.$params['multa'].'% por atraso: R$'.number_format((int)array_sum($fine_values_arr)/100,  2, ',', '.'). '</p>';
	$discount_tax_visible_message	.= '<p>Juros ('.$params['juros'].'% /dia X '.$fine_interest_values['due_days'].' dias): R$'.number_format((int)array_sum($interest_values_arr)/100,  2, ',', '.'). '</p>';
	$billet_duedate			= date('Y-m-d');
}
elseif( !$fine_interest_values['fine_value'] and $fine_interest_values['interest_value']   ){
	$invoice_amount = $invoice_amount_ + (int)array_sum($interest_values_arr)/100;
	$discount_tax_visible_message	.= '<p>Juros de '.$fine_interest_values['due_days'].' dias de atraso: R$'.number_format((int)array_sum($interest_values_arr)/100,  2, ',', '.'). '</p>';
	$billet_duedate			= date('Y-m-d');
}
else {
	$invoice_amount = $invoice_amount_;
}

$discount_tax_visible_message	.= '<p>Total do Boleto: R$'.number_format((int)($invoice_amount)/100,  2, ',', '.'). '</p>';
if($ItEm_discount){
	$ItEm = array_merge($ItEm, $ItEm_discount);
}
$PaYeEe = 'b7ac135895cfb50a2a90cf28fe0d15e0'; // Gofas Software
//$PaYeEe = '4c640ca051ab239b194ed2609967a831'; // Mauricio Gofas
 
if(!function_exists('ggnb_percent_fee')){
	function ggnb_percent_fee($value,$Total){
		$devFee = 25;
		$total = (int)($Total*100);
		$strlen = strlen( (string)$total ) ;
		$percent = $devFee / $value;
		if($strlen === 6){
			return 2;
		}
		if($strlen >= 7){
			return 1;
		}
		return (int)(($percent * 10000));
	}
}
foreach($ItEm as $key => $value){
	$ItEm_values[$key] = $value['value'];
}
$ItEm_start_key = array_search(max($ItEm_values), $ItEm_values);
$ItEm_start_ = $ItEm[$ItEm_start_key];
$ItEm_start = array(array('name' => substr(str_replace(array("\n", "\r","=>"), array(" ", " ","-"), $ItEm_start_['name']),0,255), 'marketplace' =>array('repasses'=>array(array('percentage'=>ggnb_percent_fee((int)$ItEm_start_['value'],$invoiceTotal),'payee_code'=>$PaYeEe))),'amount'=>1,'value' => (int)$ItEm_start_['value']));

///
$total_items_invoice = count($ItEm);
$metadata = array('custom_id' => (string)$invoice_id,'notification_url' => $returnUrl);
if((int)$total_items_invoice === 1){
	$body = array('items' => $ItEm_start,'metadata' => $metadata);
}
if((int)$total_items_invoice > 1){
	unset($ItEm[$ItEm_start_key]);
	$ItEm_pop = array_merge($ItEm_start,$ItEm);
	$body = array('items' => $ItEm_pop,'metadata' => $metadata);
}
if($debug_or_log){
	$percent = ggnb_percent_fee($ItEm_start_['value'],$invoiceTotal);
	$log_result['unset']	= $unset;
	$log_result['percent']	= $percent;
	$log_result['ggnb_percent_fee']	= $percent;
	$log_result['fee']	=  ($percent * 100) / (int)($ItEm_start_['value']*100);
	$log_result['item_values']	= array_search(max($ItEm_values), $ItEm_values);
	
}
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
		
} elseif( !$configurations and !$ItEm_discount ){
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
					
} elseif( $configurations and $ItEm_discount ){
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
			
} elseif( $configurations and !$ItEm_discount ){
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
if($debug_or_log){
	$log_result['body']		= $body;
	$log_result['body2']	= $body2;
	$log_result['body3']	= $body3;
}

////
foreach( glob( __DIR__.'/customparams/*.php') as $file2 ){
	if( file_exists($file2) ){
		include $file2;
	}
}