<?php
/**
 * Módulo EFÍ Boleto para WHMCS
 * @author		Gofas Software
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 -> 2023 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.9.1
 */
if((int)substr(preg_replace('/[^\da-z]/i','',phpversion()),0,2)>=(int)81){
    require __DIR__.'/gofasgerencianetboleto/index.php';
}
if((int)substr(preg_replace('/[^\da-z]/i','',phpversion()),0,2)<=(int)74){
    require __DIR__.'/gofasgerencianetboleto/indexd.php';
}