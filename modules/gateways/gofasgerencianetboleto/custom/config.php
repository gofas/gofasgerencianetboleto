<?php
/**
 * Módulo EFÍ Boleto para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 -> 2023 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.9.0
 */

// Use $opt_num++ para enumerar as opções
// Não renomear a variável $ggnb_custom_config

$ggnb_custom_config_ =[ 
	// Opção 1
	'optiontest' => [ // Utilize $params['optiontest'] no arquivo /custom/params.php para incluir o valor dessa opção na função gofasgerencianetboleto_link
		'FriendlyName' => $opt_num++.'- Configuração personalizada',
		'Type' => 'text',
		'Size' => '40',
		'Default' => 'Valor padrão',
		'Description' => 'Campo de configuração via arquivo /custom/config.php',
	],
	// Opção 2
	'optiontest2' => [
		'FriendlyName' => $opt_num++.'- Outra configuração personalizada',
		'Type' => 'text',
		'Size' => '40',
		'Default' => '',
		'Description' => 'Customização via arquivo /custom/config.php',
	],
];
// $ggnb_custom_config = $ggnb_custom_config_;