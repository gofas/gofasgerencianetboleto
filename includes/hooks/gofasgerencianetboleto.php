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
use WHMCS\Aplication;
$self=App::self();
if(!function_exists('ggnb_get_protected_property')){
	function ggnb_get_protected_property($object, $property){
	    $reflectedClass=new \ReflectionClass($object);
	    $reflection=$reflectedClass->getProperty($property);
	    $reflection->setAccessible(true);
	    return $reflection->getValue($object);
	}
}
if(!function_exists('ggnb_get_string_between')){
	function ggnb_get_string_between($string, $start, $end){
		$string=" ".$string;
		$ini=strpos($string,$start);
		if($ini==0)return"";
		$ini+=strlen($start);   
		$len=strpos($string,$end,$ini)-$ini;
		return substr($string,$ini,$len);
	}
}
$root_dir = '/'.ggnb_get_string_between(ggnb_get_protected_property(ggnb_get_protected_property(ggnb_get_protected_property(ggnb_get_protected_property($self,'clientTemplate'),'config'),'configFile'),'path'),'/','/templates/');
require_once $root_dir.'/modules/gateways/gofasgerencianetboleto.php';