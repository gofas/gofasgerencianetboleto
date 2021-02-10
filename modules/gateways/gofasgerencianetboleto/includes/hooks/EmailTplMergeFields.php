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
//Output additional merge fields in the list when editing an email template
add_hook('EmailTplMergeFields', 1, function($vars){
    $ggnb_merge_fields = array();
	$ggnb_merge_fields['ggnb_billet_info']	= 'Gerencianet: Informações do boleto';
    $ggnb_merge_fields['ggnb_link']			= 'Gerencianet: Link para o boleto';
	$ggnb_merge_fields['ggnb_pdf']			= 'Gerencianet: Link para o boleto em PDF';
	$ggnb_merge_fields['ggnb_barcode']		= 'Gerencianet: Linha digitável do boleto';
	$ggnb_merge_fields['ggnb_expire_at']	= 'Gerencianet: Vencimento do boleto';
	$ggnb_merge_fields['ggnb_total']		= 'Gerencianet: Total do boleto';
	$ggnb_merge_fields['ggnb_charge_id']	= 'Gerencianet: ID da transação';
	$ggnb_merge_fields['ggnb_api_mode']		= 'Gerencianet: API mode (sandbox ou live)';
	$ggnb_merge_fields['ggnb_debug']		= 'Gerencianet: Debug nos emails';
    return $ggnb_merge_fields;
});