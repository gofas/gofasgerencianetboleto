<?php
/**
 * Módulo Gerencianet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.3.1
 */
foreach(glob(__DIR__.'/includes/hooks/*.php') as $hooks){
	if(file_exists($hooks) ){
		require $hooks;
	}
}
require_once __DIR__.'/includes/config.php';
if(!function_exists('gofasgerencianetboleto_link')){
function gofasgerencianetboleto_link($params){
	require __DIR__.'/includes/callback.php';
	if(!$license_error and $license_results['status'] === "Active" and $setting['license_key'] and $local_key_value){
		$devFee = 0;
	}
	else {
		$devFee = 25;
	}
	require __DIR__.'/includes/params.php';
	require __DIR__.'/includes/functions.php';
	
	//logModuleCall("gofasgerencianetboleto","genarate_billet_start",array('module_version'=>'3.3.0','params'=>$params,'user_id'=>$user_id),$params,'');
	// Verify if generate billet
	if((stripos($_SERVER['REQUEST_URI'],'viewinvoice')) or (!stripos($_SERVER['REQUEST_URI'], 'viewinvoice') and ($params['billetonemail']))){
		$generate_billet = true;
	}
	if($generate_billet){
		// Verify Database
		 $ggnb_verifyInstall = ggnb_verifyInstall();
		if($ggnb_verifyInstall['error']){
			$error .= $ggnb_verifyInstall['error'];
		}
		if(!$error){
			$access_token_ = ggnb_get_token($api_url,$client_id,$client_secret);
			if($access_token_['access_token']){
				$access_token = $access_token_['access_token'];
			}
			if($access_token_['error']){
				$error .= $access_token_['error'];
			}
		}
		/**
		*
		* Transactions verifycation
		*
		*/
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
			if(!$error and $chargeExistID === $trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate >= date('Y-m-d') and $chargeExistTotal === $invoice_amount){
				$link		= $chargeExist['data']['payment']['banking_billet']['link'];
				$expire_at	= $chargeExistDuedate;
				$barcode	= $chargeExist['data']['payment']['banking_billet']['barcode'];
			}
			elseif(!$error and  ($chargeExistID === $trans_id and $chargeExistStatus !== 'canceled' and $chargeExistDuedate < date('Y-m-d') and $chargeExistDuedate > date('Y-m-d',strtotime('-90 days'))) and !$configurations and !$cancelBillet ){
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
			elseif(!$error and ($chargeExistID === $trans_id and ($chargeExistStatus === 'canceled' or $chargeExistStatus === 'unpaid'))){
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
						// Cria transação no whmcs "Boleto gerado aguardando pagamento."
						$addtrans = ggnb_add_trans($user_id,$invoice_id,$charge_id,(int)$params['admin'], $api_mode, $system_url);
						if($addtrans['error']){
							$error	.= $addtrans['error'];
						}
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
			elseif(!$error and $chargeExistID === $trans_id and $chargeExistDuedate >= date('Y-m-d') and $chargeExistTotal !== $invoice_amount ){
				if($cancelBillet){
					$cancelCharge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
					 if($cancelCharge['error']){
						$error .= $cancelCharge['error'];
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
						// Cria transação no whmcs "Boleto gerado aguardando pagamento."
						$addtrans = ggnb_add_trans($user_id,$invoice_id,$charge_id,(int)$params['admin'], $api_mode, $system_url);
						if($addtrans['error']){
							$error	.= $addtrans['error'];
						}						
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
			elseif(!$error and ($chargeExistID === $trans_id and $chargeExistDuedate < date('Y-m-d') ) and ($configurations or $cancelBillet) ){
				// cancela transação gerada anteriormente
				if( $chargeExistStatus === 'new' || $chargeExistStatus === 'waiting' || $chargeExistStatus === 'unpaid'){
					$cancelCharge = ggnb_cancel_charge($api_url,$access_token,$trans_id);
					 if($cancelCharge['error']){
						 $error .= $cancelCharge['error'];
					 }
					// segunda via do boleto com multa e juros
					if( $cancelCharge['result'] === 'success'){
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
						// Cria transação no whmcs "Boleto gerado aguardando pagamento."
						$addtrans = ggnb_add_trans($user_id,$invoice_id,$charge_id,(int)$params['admin'], $api_mode, $system_url);
						if($addtrans['error']){
							$error	.= $addtrans['error'];
						}
					} elseif( is_string($pay_charge) ){
						$error .= $pay_charge['error'];
					}
				} else {
					$error .= $create_charge['error'];
				}
			}			
		} // End of if( $trans_id and !$error)
		/// The firt billet for the invoice
		elseif( !$trans_id ){
			if( (float)$invoiceTotal >= (float)$minimunAmount){
				// Criar transação
				$create_charge = ggnb_create_charge($api_url,$access_token,$body); // body
				$charge_id = $create_charge['result'];
				if($create_charge['error']){
						$error	.= $create_charge['error'];
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
						// Cria transação no whmcs "Boleto gerado aguardando pagamento."
						$addtrans = ggnb_add_trans($user_id,$invoice_id,$charge_id,(int)$params['admin'], $api_mode, $system_url);
						if($addtrans['error']){
							$error	.= $addtrans['error'];
						}
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
		if( $error ){
			// Email enviado ao admin em caso de erro
			if( $emailonError ){
				$sendEmailonError = ggnb_send_error_email( $invoice_id, $user_id, $firstname, $lastname, $system_url, (int)$params['admin'], $emailonError, $error);
			}
			// Debug
			include __DIR__.'/includes/debug.php';
			//logModuleCall("gofasgerencianetboleto","genarate_billet",array('module_version'=>'3.3.0','invoice_id'=>$invoice_id,'user_id'=>$user_id),"", $log_result);
			logModuleCall("gofasgerencianetboleto","genarate_billet",get_defined_vars(),"", $error);
			return $error . $css;
		}
		elseif( !$error and $redirectToBillet and stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php') ){
			header_remove();
			header("Location: $link",TRUE,303);
			exit;
		}
		elseif( !$error and !$redirectToBillet and stripos($_SERVER['REQUEST_URI'], 'viewinvoice.php')){
			
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
			// Finalize
			if($debug){
				$define_version = '?v='.time();
			}
			elseif(!$debug){
				$define_version = '';
			}
			$result .= '<script type="text/javascript" src="'.$whmcs_url.'modules/gateways/gofasgerencianetboleto/assets/js/copy.js'.$define_version.'" charset="UTF-8">
</script>';
			// Debug
			include __DIR__.'/includes/debug.php';
			return $result.$css;
		}
	} // End of if( $generate_billet )
	else {
		$log_result['generate_billet_false_']	= 'Boleto não foi gerado ao gerar a fatura';
		$log_result['params']	= $params;
		if($log){
			//logModuleCall("gofasgerencianetboleto","generate_billet_false",array('module_version'=>'3.2.0','invoice_id'=>$invoice_id,'user_id'=>$user_id),"", $log_result);
			logModuleCall("gofasgerencianetboleto","genarate_billet",get_defined_vars(),"", $result);
		}
	}
}}