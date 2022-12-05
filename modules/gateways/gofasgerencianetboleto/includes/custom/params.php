<?php
/**
 * Módulo Gerencinet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.5.0
 */
foreach($ItEm as $key => $value){
	$custom_items[] =
        array('name'=>substr(str_replace(array("\n", "\r","=>"),
        array(" ", " ","-"),
        $value['name']),0,255),
        'marketplace' =>array(
            'repasses'=>array(
                array(
                    'percentage'=> 2500, // porcentagem de repasse (2500 = 25%)
                    'payee_code'=>'4c640ca051ab239b194ed2609967a831', // Mauricio Gofas
                ),
                /* array(
                    'percentage'=> 2500, // porcentagem de repasse (2500 = 25%)
                    'payee_code'=>'4c640ca051ab239b194ed2609967a831', // Mauricio Gofas
                ),*/
            )
        ),
        'amount'=>1,'value' => (int)($value['value'])
    );
}
//$body = array('items' => $custom_items,'metadata' => $metadata);