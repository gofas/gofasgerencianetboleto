<?php
/**
 * Módulo Efí Boleto para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 -> 2023 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.9.5
 */

/**
 * Variáveis definidas nesse arquivo são carregadas antes da execução do módulo
 * Variáveis previamente definidas pelo módulo terão os valores substituídos pelo
 * valor das variáveis com o mesmo nome definidas nesse arquivo  
*/

// Exemplo de divisão dos pagamentos via marketplace:
foreach($ItEm as $key => $value){
	$custom_items[] =
        array('name'=>substr(str_replace(array("\n", "\r","=>"),
        array(" ", " ","-"),
        $value['name']),0,255),
        'marketplace' =>array(
            'repasses'=>array(
                array(
                    'percentage'=> 2500, // porcentagem de repasse (2500 = 25%)
                    'payee_code'=>'b7ac135895cfb50a2a90cf28fe0d15e0', // Gofas Software
                ),
                /* array(
                    'percentage'=> 2500, // porcentagem de repasse (2500 = 25%)
                    'payee_code'=>'b7ac135895cfb50a2a90cf28fe0d15e0', // Gofas Software
                ),*/
            )
        ),
        'amount'=>1,'value' => (int)($value['value'])
    );
}
//$body = array('items' => $custom_items,'metadata' => $metadata);