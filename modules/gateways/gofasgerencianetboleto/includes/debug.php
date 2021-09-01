<?php
/**
 * Módulo Gerencianet Boleto para WHMCS
 * @author		Mauricio Gofas
 * @see			https://gofas.net/?p=7893
 * @copyright	2016 / 2020 Gofas Software
 * @license		https://gofas.net?p=9340
 * @support		https://gofas.net/?p=7856
 * @version		3.3.0
 */
use WHMCS\Database\Capsule;
if(!defined("WHMCS")){ die("Esse arquivo não pode ser acessado diretamente"); }
if($debug){
	echo '<pre style="height:300px;max-width: 850px;margin: 20px auto;padding: 5px 15px 5px 15px;" class="debug" onfocus="select_all_and_copy(this)" onclick="select_all_and_copy(this)">';
	echo '<h4 style="text-align:center;line-height: 1.4;border-bottom: 1px solid black;padding: 0px 0px 12px 0px;margin: 11px 0px 20px 0px;">
	Você está vendo essas informações na tela por quê a opção "debug" do módulo<br><b>Gofas Gerencianet Boleto v'.$module_version.'</b> está ativa.</h4>';
	echo '<h4>Suporte:</h4>';
	echo '<p>Saiba mais sobre como diagnosticar erros e coletar informações para suporte <a target="_blank" href="https://gofas.net/?p=7899&rf=ggnbfatura">neste link</a></p>';
	echo '<p>Veja várias soluções para dificuldades comuns no <a href="https://gofas.net/forums/forum/whmcs/modulo-gerencianet-boleto-para-whmcs/?rf=ggnbfatura" target="_blank">fórum de suporte do módulo</a>.</p>';
	echo'<p  onfocus="select_all_and_copy(debugDiv)" onclick="select_all_and_copy(debugDiv)">1) <span style="cursor:copy;text-decoration: underline; ">Clique aqui para copiar as informações de depuração (debug)</span>.</p>';
	echo'<p>2) <a target="_blank" style="cursor:alias;" href="https://gofas.net/contato?rf=ggnbfatura">Clique aqui e preencha o formulário de Ajuda / Suporte nos enviado as informações de depuração que você acabou de copiar.</a>.</p>';
	echo '<div id="debugDiv"  onfocus="select_all_and_copy(this)" onclick="select_all_and_copy(this)">',print_r(get_defined_vars()), "</div></pre>";
}
if($log){
	logModuleCall("gofasgerencianetboleto","genarate_billet",array(get_defined_vars()),"", get_defined_functions());
}