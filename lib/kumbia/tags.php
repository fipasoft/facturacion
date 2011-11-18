<?php

/** Kumbia - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
* Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
* Copyright (C) 2007-2007 Deivinson Jose Tejeda Brito (deivinsontejeda at gmail.com)
* Copyright (C) 2007-2007 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
*
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this framework; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
* Este framework es software libre; puedes redistribuirlo y/o modificarlo
* bajo los terminos de la licencia pública general GNU tal y como fue publicada
* por la Fundación del Software Libre; desde la versión 2.1 o cualquier
* versión superior.
*
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; dejando atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
*
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

function get_params($params){
	$data = array();
	$i = 0;
	foreach ($params as $p) {
		if(ereg("([a-z_0-9]+[:][ ]).+", $p, $regs)){
			$p = str_replace($regs[1], "", $p);
			$n = str_replace(": ", "", $regs[1]);
			$data[$n] = $p;
		} else $data[$i] = $p;
		$i++;
	}
	return $data;
}

function is_param($param){
	return ereg("([a-z_0-9]+[:][ ]).+", $param);
}

function get_param($param){
	$data = array();
	if(ereg("([a-z_0-9]+[:][ ]).+", $p, $regs)){
		$p = str_replace($regs[1], "", $p);
		$n = str_replace(": ", "", $regs[1]);
		$data[$n] = $p;
		return array();
	} else return array($param);
}

function link_to($action, $text=''){
	if(func_num_args()>2){
		$action = get_params(func_get_args());
	}
	if(is_array($action)){
		if($action['confirm']){
			$action['onclick'] = "if(!confirm(\"{$action['confirm']}\")) { if(document.all) event.returnValue = false; else event.preventDefault(); }; ".$action['onclick'];
			unset($action['confirm']);
		}
		$code = "<a href='".KUMBIA_PATH."{$action[0]}' ";
		if(!$action['text']){
			$action['text'] = $action[1];
		}
		foreach($action as $key => $value){
			if(!is_numeric($key)&&$key!='text'){
				$code.=" $key='$value' ";
			}
		}
		$code.=">{$action['text']}</a>";
		return $code;
	} else {
		if(!$text) {
			$text = str_replace('_', ' ', $action);
			$text = str_replace('/', ' ', $text);
			$text = ucwords($text);
		}
		return "<a href='".KUMBIA_PATH."$action'>$text</a>";
	}
}

function link_to_action($action, $text=''){
	if(func_num_args()>2){
		$action = get_params(func_get_args());
	}
	if(is_array($action)){
		if(isset($action['confirm'])){
			$action['onclick'] = "if(!confirm(\"{$action['confirm']}\")) if(document.all) event.returnValue = false; else event.preventDefault(); ".$action['onclick'];
			unset($action['confirm']);
		}
		$code = "<a href='".KUMBIA_PATH."{$_REQUEST['action']}/{$action[0]}' ";
		foreach($action as $key => $value){
			if(!is_numeric($key)){
				$code.=" $key='$value' ";
			}
		}
		$code.=">{$action[1]}</a>";
		return $code;
	} else {
		if(!$text) {
			$text = str_replace('_', ' ', $action);
			$text = str_replace('/', ' ', $text);
			$text = ucwords($text);
		}
		return "<a href='".KUMBIA_PATH."{$_REQUEST['controller']}/$action'>$text</a>";
	}
}

/**
 * Permite ejecutar una acción en la vista actual dentro de un contenedor
 * HTML usando AJAX
 *
 * confirm: Texto de Confirmación
 * success: Codigo JavaScript a ejecutar cuando termine la petición AJAX
 * before: Codigo JavaScript a ejecutar antes de la petición AJAX
 * oncomplete: Codigo JavaScript que se ejecuta al terminar la petición AJAX
 * update: Que contenedor HTML será actualizado
 * action: Accion que ejecutará la petición AJAX
 * text: Texto del Enlace
 *
 * @return string
 */
function link_to_remote(){
	$data = get_params(func_get_args());
	if(!$data['update']){
		$update = $data[2];
	} else {
		$update = $data['update'];
	}
	if(!$data['text']){
		$text = $data[1];
	} else {
		$text = $data['text'];
	}
	if(!$text){
		$text = $data[0];
	}
	if(!$data['action']){
		$action = $data[0];
	} else {
		$action = $data['action'];
	}
	//$data['before'] = str_replace("'", "\"", $data['before']);
	if($data['loading']&&!$data['before']) $data['before'] = $data['loading'];
	if(isset($data['confirm'])){
		$code = "<a href='#' onclick='if(confirm(\"{$data['confirm']}\")) { new AJAX.viewRequest({action:\"$action\", container:\"$update\", callbacks: { before: function(){{$data['before']}}, oncomplete: function(){{$data['oncomplete']}}, success: function(){{$data['success']}} }}); } return false;'";
	} else {
		$code = "<a href='#' onclick=\"new AJAX.viewRequest({action: '$action', container: '$update', callbacks: { before: function(){".$data['before']."}, oncomplete: function(){{$data['oncomplete']}}, success: function(){{$data['success']}} }}); return false;\"";
	}
	unset($data['before']);
	unset($data['oncomplete']);
	unset($data['success']);
	unset($data['loading']);
	unset($data['update']);
	unset($data['confirm']);
	foreach($data as $key => $value){
		if(!is_numeric($key)){
			$code.=" $key='$value' ";
		}
	}
	return $code.">$text</a>";
}

/**
 * Genera una etiqueta script que apunta a un archivo JavaScript
 * respetando las rutas y convenciones de Kumbia
 *
 * @param string $src
 * @param string $cache
 * @return unknown
 */
function javascript_include_tag($src='', $cache=true){
	if(!$src) $src = $_REQUEST['controller'];
	$src.=".js";
	if(!$cache) {
		$cache = md5(uniqid());
		$src.="?nocache=".$cache;
	}
	return "<script type='text/javascript' src='".KUMBIA_PATH."javascript/$src'></script>\r\n";
}

function javascript_library_tag($src){
	return "<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/$src.js'></script>\r\n";
}

/**
 * Agrega una etiqueta link para incluir un archivo CSS respetando
 * las rutas y convenciones de Kumbia
 *
 * use_variables: utilizar variables de Kumbia en el css
 */
function stylesheet_link_tag(){
	$params = get_params(func_get_args());
	$use_variables = isset($params['use_variables']);
	unset($params['use_variables']);
	
	$atts = '';
	foreach($params as $at => $val){
		if(!is_numeric($at)){
			$atts.=" $at=\"".$val."\"";
		}
	}

	$kb = substr(KUMBIA_PATH, 0, strlen(KUMBIA_PATH)-1);
	$code = '';
	
	for($i=0; isset($params[$i]); $i++){
		$src = $params[$i];
		if($use_variables){
			$code.="<link rel=\"stylesheet\" type=\"text/css\" href=\"".KUMBIA_PATH."css.php?c=$src&p=$kb\"$atts/>\r\n";
		} else {
			$code.="<link rel=\"stylesheet\" type=\"text/css\" href=\"".KUMBIA_PATH."css/$src.css\"$atts/>\r\n";
		}	
	}

	if(!$i){ //$i=0 si no se especificaron hojas de estilo
		$src = $_REQUEST['action'];
		if($use_variables){
			$code.="<link rel=\"stylesheet\" type=\"text/css\" href=\"".KUMBIA_PATH."css.php?c=$src&p=$kb\"$atts/>\r\n";
		} else {
			$code.="<link rel=\"stylesheet\" type=\"text/css\" href=\"".KUMBIA_PATH."css/$src.css\"$atts/>\r\n";
		}	
	}

	return $code;
}

/**
 * Permite incluir una imagen dentro de una vista respetando
 * las convenciones de directorios y rutas en Kumbia
 *
 * @param string $img
 * @return string
 */
function img_tag($img){
	$atts = get_params(func_get_args());
	if(!$atts['src']){
		$code.="<img src='".KUMBIA_PATH."img/{$atts[0]}' ";
	} else {
		$code.="<img src='{$atts['src']}' ";
	}
	unset($atts['src']);
	if(!$atts['alt']) $atts['alt'] = "";
	if($atts['drag']) {
		$drag=true;
		unset($atts['drag']);
	}
	if($atts['reflect']) {
		$reflect=true;
		unset($atts['reflect']);
	}
	if(is_array($atts)){
		if(!$atts['alt']) $atts['alt'] = "";
		foreach($atts as $at => $val){
			if(!is_numeric($at)){
				$code.="$at=\"".$val."\" ";
			}
		}
	}
	$code.= "/>\r\n";
	if($drag){
		$code.="<script type=\"text/javascript\">new Draggable('{$atts['id']}', {revert:true})</script>\r\n";
	}
	if($reflect){
		$code.="<script type=\"text/javascript\">new Reflector.reflect('{$atts['id']}')</script>\r\n";
	}
	return $code;
}

/**
 * Permite generar un formulario remoto
 *
 * @param string $data
 * @return string
 */
function form_remote_tag($data){
	$data = get_params(func_get_args());
	if(!$data['action']) {
		$data['action'] = $data[0];
	}
	$data['callbacks']	= array();
	if($data['complete']){
		$data['callbacks'][] = " complete: function(){ ".$data['complete']." }";
	}
	if($data['before']){
		$data['callbacks'][] = " before: function(){ ".$data['before']." }";
	}
	if($data['success']){
		$data['callbacks'][] = " success: function(){ ".$data['success']." }";
	}
	if($data['required']){
		$requiredFields = encomillar_lista($data['required']);
		$code = "<form action='".KUMBIA_PATH."{$data['action']}/{$_REQUEST['id']}' method='post'
		onsubmit='if(validaForm(this,new Array({$requiredFields}))){ return ajaxRemoteForm(this,\"{$data['update']}\",{".join(",",$data['callbacks'])."}); } else{ return false; }'";
	} else{
		$code = "<form action='".KUMBIA_PATH."{$data['action']}/{$_REQUEST['id']}' method='post'
		onsubmit='return ajaxRemoteForm(this, \"{$data['update']}\", { ".join(",", $data['callbacks'])." });'";
	}
	foreach($data as $at => $val){
		if(!is_numeric($at)&&(!in_array($at, array("action", "complete", "before", "success", "callbacks")))){
			$code.="$at=\"".$val."\" ";
		}
	}
	return $code.=">\r\n";
}


/*
 * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
 * @param string $lista Cadena con Items separados por comas (,).
 * @return string $listaEncomillada Cadena con Items encerrados en doblecomillas y separados por comas (,).
 */
function encomillar_lista($lista){
	$arrItems = split(",", $lista);
	$n = count($arrItems);
	$listaEncomillada = "";
	for ($i=0; $i<$n-1; $i++) {
		$listaEncomillada.= "\"".$arrItems[$i]."\",";
	}
	$listaEncomillada.= "\"".$arrItems[$n-1]."\"";
	return $listaEncomillada;
}


function form_tag($action){
	if(func_num_args()>1){
		$atts = get_params(func_get_args());
	}
	if(!$action){
		$action = $atts[0] ? $atts[0] : $atts['action'];
	}
	if(!$atts['method']){
		$atts['method'] = "post";
	}
	if($atts['confirm']){
		$atts['onsubmit'].= ";if(!confirm(\"{$atts['confirm']}\")) { return false; }";
		unset($atts['confirm']);
	}
	if($atts['required']){
		$requiredFields = encomillar_lista($atts['required']);
		$atts['onsubmit'].= ";if(!validaForm(this,new Array({$requiredFields}))){ return false; }";
		unset($atts['required']);
	}
	$code = "<form action='".KUMBIA_PATH."$action/{$_REQUEST['id']}' ";
	foreach($atts as $key => $value){
		if(!is_numeric($key)){
			$code.= "{$key} = '{$value}' ";
		}
	}
	$code.= ">\r\n";
	return $code;
}

/**
 * Devuelve un string encerrado en comillas
 *
 * @param string $word
 * @return string
 */

function comillas($word){
	return "'$word'";
}

/**
 * Etiqueta para cerrar un formulario
 *
 * @return $string_code
 */
function end_form_tag(){
	$str = "</form>\r\n";
	return $str;
}

/**
 * Crea un boton de submit para el formulario actual
 *
 * @param string $caption
 * @return html code
 */
function submit_tag($caption){
	$data = get_params(func_get_args());
	if(!$data['caption']) {
		$data['caption'] = $data[0];
	}
	$code = "<input type='submit' value='{$data['caption']}' ";
	foreach($data as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}

/**
 * Crea un boton de submit para el formulario remoto actual
 *
 * @param string $caption
 * @return html code
 */
function submit_remote_tag($caption){
	$data = get_params(func_get_args());
	if(!$data['caption']) {
		$data['caption'] = $data[0];
	}
	$data['callbacks']	= array();
	if($data['complete']){
		$data['callbacks'][] = " complete: function(){ ".$data['complete']." }";
	}
	if($data['before']){
		$data['callbacks'][] = " before: function(){ ".$data['before']." }";
	}
	if($data['success']){
		$data['callbacks'][] = " success: function(){ ".$data['success']." }";
	}
	$code = "<input type='submit' value='{$data['caption']}' ";
	foreach($data as $at => $value){
		if(!is_numeric($at)&&(!in_array($at, array("action", "complete", "before", "success", "callbacks", "caption", "update")))){
			$code.="$at='$value' ";
		}
	}
	//{ ".join(",", $data['callbacks'])."}
	$code.=" onclick='return ajaxRemoteForm(this.form, \"{$data['update']}\")'>\r\n";
	return $code;
}

/**
 * Crea un boton de submit tipo imagen para el formulario actual
 *
 * @param string $caption
 * @return html code
 */
function submit_image_tag($caption, $src){
	$data = get_params(func_get_args());
	if(!$data['caption']) {
		$data['caption'] = $data[0];
	}
	if(!$data['src']) {
		$data['src'] = $data[1];
	}
	$code = "<input type='image' src='{$data['src']}' value='{$data['caption']}' ";
	foreach($data as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}

function button_tag(){
	$data = get_params(func_get_args());
	if(!isset($data['value'])) $data['value'] = $data[0];
	if($data['id']&&!$data['name']) $data['name'] = $data['id'];
	if(!isset($data['id'])) $data['id'] = $data['name'];
	$code = "<input type='button' ";
	foreach($data as $key => $value){
		if(!is_numeric($key)&&$key!=$data){
			$code.="$key=\"$value\" ";
		}
	}
	return $code.">\r\n";
}

function get_value_from_action($name){
	if(!is_array($name)){
		if(!$name) return;
		$action = ucwords($_REQUEST['action']);
		$model = kumbia::$models[$action];
		if($model){
			$x = $name;
			$value = $model->$x;
		}
	} else {
		if($name['value']) return $name['value'];
		$action = ucwords($_REQUEST['action']);
		$model = kumbia::$models[$action];
		if($model){
			$x = $name['name'] ? $name['name'] : $name[0];
			$value = $model->$x;
		}
	}
	return $value ? $value : $_REQUEST[$x];
}

# Helpers
function text_field_tag($name){
	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	if(!$value) $value = $name['value'];
	$code.="<input type='text' id='{$name[0]}' value='$value' ";
	foreach($name as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}

function checkbox_field_tag($name){
	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	if(!$value) $value = $name['value'];
	$code.="<input type='checkbox' id='{$name[0]}' value='$value' ";
	foreach($name as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}

/**
 * Permite agregar
 *
 * @param unknown_type $name
 * @return unknown
 */
function numeric_field_tag($name){

   $value = get_value_from_action($name);
   $name = get_params(func_get_args());
   
   if(!$name[0]){
       $name[0] = $name['id'];
   }
   
   if(!$name['name']){
       $name['name'] = $name[0];
   }
   
   if(!$value){
       $value = $name['value'];
   }
   
   if(!isset($name['onKeyPress'])) {
      $name['onKeyPress'] = " return valNumeric(event)";
      
   }else {
      $name['onKeyPress'].="; return valNumeric(event)";
   }
   
   $code.="<input type='text' id='{$name[0]}' value='$value' ";
   
   foreach($name as $key => $value){
      if(!is_numeric($key)){
         $code.="$key='$value' ";
      }
   }
   $code.=">\r\n";
   return $code;
}

function textupper_field_tag($name){

	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	if(!$value) $value = $name['value'];
	if(!isset($name['onblur'])) {
		$name['onblur'] = "keyUpper2(this)";
	} else {
		$name['onblur'].=";keyUpper2(this)";
	}
	$code.="<input type='text' id='{$name[0]}' value='$value' ";
	foreach($name as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}


function date_field_tag($name){
	$config = Config::read('core.ini');
	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	if(!$value) $value = $name['value'];

	if($value){
		$ano = substr($value, 0, 4);
		$mes = substr($value, 5, 2);
		$dia = substr($value, 8, 2);
	} else {
		$ano = date('Y');
		$mes = date('m');
		$dia = date('d');
	}
	
	$start_year = (isset($name['start_year'])) ? (int)$name['start_year'] : 1900;
	$end_year = (isset($name['end_year'])) ? (int)$name['end_year'] : date('Y');
	
	if($end_year < $start_year){
		$end_year = $start_year;
	}
	
	$meses = array(
	"01" => "Ene",
	"02" => "Feb",
	"03" => "Mar",
	"04" => "Abr",
	"05" => "May",
	"06" => "Jun",
	"07" => "Jul",
	"08" => "Ago",
	"09" => "Sep",
	"10" => "Oct",
	"11" => "Nov",
	"12" => "Dic",
	);
	
	$select_month = "<select name='{$name[0]}_month' id='{$name[0]}_month'
	onchange=\"$('{$name[0]}').value = $('{$name[0]}_year').options[$('{$name[0]}_year').selectedIndex].value+'-'+$('{$name[0]}_month').options[$('{$name[0]}_month').selectedIndex].value+'-'+$('{$name[0]}_day').options[$('{$name[0]}_day').selectedIndex].value\"
	>";
	
	if(isset($name['use_month_num'])){
		foreach($meses as $numero_mes => $nombre_mes){
			if($numero_mes==$mes){
				$select_month.="<option value='$numero_mes' selected='selected'>$numero_mes</option>\n";
			} else {
				$select_month.="<option value='$numero_mes'>$numero_mes</option>\n";
			}
		}
	} else {
		foreach($meses as $numero_mes => $nombre_mes){
			if($numero_mes==$mes){
				$select_month.="<option value='$numero_mes' selected='selected'>$nombre_mes</option>\n";
			} else {
				$select_month.="<option value='$numero_mes'>$nombre_mes</option>\n";
			}
		}	
	}
	$select_month.="</select>&nbsp;";

	$select_day ="<select name='{$name[0]}_day' id='{$name[0]}_day'
	onchange=\"$('{$name[0]}').value = $('{$name[0]}_year').options[$('{$name[0]}_year').selectedIndex].value+'-'+$('{$name[0]}_month').options[$('{$name[0]}_month').selectedIndex].value+'-'+$('{$name[0]}_day').options[$('{$name[0]}_day').selectedIndex].value\">";
	for($i=1;$i<=31;$i++){
		$n = sprintf("%02s", $i);
		if($n==$dia){
			$select_day.="<option value='$n' selected='selected'>$n</option>\n";
		} else {
			$select_day.="<option value='$n'>$n\n";
		}
	}
	$select_day.='</select>&nbsp;';

	$select_year ="<select name='{$name[0]}_year' id='{$name[0]}_year'
	onchange=\"$('{$name[0]}').value = $('{$name[0]}_year').options[$('{$name[0]}_year').selectedIndex].value+'-'+$('{$name[0]}_month').options[$('{$name[0]}_month').selectedIndex].value+'-'+$('{$name[0]}_day').options[$('{$name[0]}_day').selectedIndex].value\"
	>";
	for($i=$end_year;$i>=$start_year;$i--){
		if($i==$ano){
			$select_year.="<option value='$i' selected='selected'>$i\n";
		} else {
			$select_year.="<option value='$i'>$i</option>\n";
		}
	}
	$select_year.='</select>';

	if(isset($name['order'])){
		if($name['order']=='Y-m-d'){
			$code = $select_year.$select_month.$select_day;
		} elseif($name['order']=='Y-d-m'){
			$code = $select_year.$select_day.$select_month;
		} elseif($name['order']=='d-m-Y'){
			$code = $select_day.$select_month.$select_year;
		} elseif($name['order']=='d-Y-m'){
			$code = $select_day.$select_year.$select_month;
		} elseif($name['order']=='m-Y-d'){
			$code = $select_month.$select_year.$select_day;
		} else {
			$code = $select_month.$select_day.$select_year;
		}
	} else {
		$code = $select_month.$select_day.$select_year;
	}

	$code.="<input type='hidden' id='{$name[0]}' name='{$name[0]}' value='$value'>";

	return $code;
}


function file_field_tag($name){
	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	$code.="<input type='file' id='{$name[0]}' ";
	foreach($name as $key => $value){
		if(!is_numeric($key)){
			$code.="$key='$value' ";
		}
	}
	$code.=">\r\n";
	return $code;
}

function radio_field_tag($name){
	$value = get_value_from_action($name[0]);
	$name = get_params(func_get_args());
	if(!$name[0]) $name[0] = $name['id'];
	if(!$name['name']) $name['name'] = $name[0];
	if(!$value) $value = $name['value'];
	print "<table><tr>";
	foreach($name[1] as $key=>$text){
		if($value==$key){
			print "<td><input type='radio' name='{$name[0]}' id='{$name[0]}' value='$key' checked></td><td>$text</td>\r\n";
		} else {
			print "<td><input type='radio' name='{$name[0]}' id='{$name[0]}' value='$key'></td><td>$text</td>\r\n";
		}
	}
	print "</tr></table>";

	return $code;
}

function textarea_tag($configuration){
	if(func_num_args()==1){
		$value = get_value_from_action($configuration);
	} else{
		$value = get_value_from_action(get_params(func_get_args()));
	}
	if(func_num_args()==1){
		$configuration = func_get_args();
		return "<textarea id='{$configuration[0]}' name='{$configuration[0]}' cols=40 rows=25>$value</textarea>\r\n";
	} else {
		$configuration = get_params(func_get_args());
		if(!$configuration['name']) $configuration['name'] = $configuration[0];
		if(!$configuration['cols']) $configuration['cols'] = 40;
		if(!$configuration['rows']) $configuration['rows'] = 25;
		if($value===null) $value = $configuration['value'];
		return "<textarea id='{$configuration['name']}' name='{$configuration['name']}' cols={$configuration['cols']} rows={$configuration['rows']}>$value</textarea>\r\n";
	}
}

function password_field_tag($name){
	$value = get_value_from_action($name);
	if(func_num_args()>1){
		$name = get_params(func_get_args());
	}
	if(!is_array($name)){
		return "<input type='password' id='$name' name='$name' value='$value'>\r\n";
	} else {
		if(!$name[0]) $name[0] = $name['id'];
		if(!$name['name']) $name['name'] = $name[0];
		$code.="<input type='password' id='{$name[0]}'";
		foreach($name as $key => $value){
			if(!is_numeric($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=">\r\n";
		return $code;
	}
}

function hidden_field_tag($name){
	$value = get_value_from_action($name);
	if(func_num_args()>1){
		$name = get_params(func_get_args());
	}
	if(!is_array($name)){
		return "<input type='hidden' id='$name' name='$name' value='$value'>\r\n";
	} else {
		if(!$name[0]) $name[0] = $name['id'];
		if(!$name['name']) $name['name'] = $name[0];
		$code.="<input type='hidden' id='{$name[0]}'";
		foreach($name as $key => $value){
			if(!is_numeric($key)){
				$code.="$key='$value' ";
			}
		}
		$code.=">\r\n";
		return $code;
	}
}

function select_tag($name='', $data=''){
	if(func_num_args()>1){
		$opts = get_params(func_get_args());
		$code = "<select id='{$opts[0]}' name='{$opts[0]}' ";     
      
		foreach($opts as $at => $val){
			if(!is_numeric($at)){
				$code.= "$at = '".$val."' ";
			}
		}
      
		$code.=">\r\n";
      
		if(is_array($opts[1])){
			if(!is_object($opts[1][0])){
				foreach($opts[1] as $key => $value){
					$code.= "\t<option value='{$key}'>{$value}</option>\r\n";
				}
			}
			$code.= "</select>\r\n";
		}
	} else {
		$code = "<select id='$name' name='$name'>\r\n";
	}
   
	return $code;
} 

function option_tag($value, $text){
	if(func_num_args()>1){
		$opts = get_params(func_get_args());
		$value = $opts[0];
		$text = $opts[1];
	}
	$code.="<option value='$value' ";
	if(is_array($opts)){
		foreach($opts as $at => $val){
			if(!is_numeric($at)){
				$code.="$at = '".$val."' ";
			}
		}
	}
	$code.= " >$text</option>\r\n";
	print $code;
}

function upload_image_tag(){
	$opts = get_params(func_get_args());
	if(!$opts['name']){
		$opts['name'] = $opts[0];
	}

	$code.="<span id='{$opts['name']}_span_pre'>
	<select name='{$opts[0]}' id='{$opts[0]}' onchange='show_upload_image(this)'>";
	$code.="<option value='@'>Seleccione...\n";
	foreach(scandir("public/img/upload") as $file){
		if($file!='index.html'&&$file!='.'&&$file!='..'&&$file!='Thumbs.db'&&$file!='desktop.ini'&&$file!='CVS'){
			$nfile = str_replace('.gif', '', $file);
			$nfile = str_replace('.jpg', '', $nfile);
			$nfile = str_replace('.png', '', $nfile);
			$nfile = str_replace('.bmp', '', $nfile);
			$nfile = str_replace('_', ' ', $nfile);
			$nfile = ucfirst($nfile);
			if(urlencode("upload/$file")==$opts['value']){
				$code.="<option selected value='upload/$file' style='background: #EAEAEA'>$nfile</option>\n";
			} else {
				$code.="<option value='upload/$file'>$nfile</option>\n";
			}
		}
	}
	$code.="</select> <a href='#{$opts['name']}_up' name='{$opts['name']}_up' id='{$opts['name']}_up' onclick='enable_upload_file(\"{$opts['name']}\")'>Subir Imagen</a></span>
	<span style='display:none' id='{$opts['name']}_span'>
	<input type='file' name='{$opts['name']}_file' id='{$opts['name']}_file' onchange='upload_file(\"{$opts['name']}\")' >
	<a href='#{$opts['name']}_can' name='{$opts['name']}_can' id='{$opts['name']}_can' style='color:red' onclick='cancel_upload_file(\"{$opts['name']}\")'>Cancelar</a></span>
	";
	if(!$opts['width']) $opts['width'] = 128;
	if($opts['value']){
		$opts['style']="border: 1px solid black;margin: 5px;".$opts['value'];
	} else {
		$opts['style']="border: 1px solid black;display: none;margin: 5px;".$opts['value'];
	}
	$code.="<div>".img_tag(urldecode($opts['value']), 'width: '.$opts['width'], 'style: '.$opts['style'], 'id: '.$opts['name']."_im")."</div>";
	$code.="<div>".img_tag(urldecode($opts['value']), 'width: '.$opts['width'], 'style: '.$opts['style'], 'id: '.$opts['name']."_in")."</div>";
	return $code;
}

class table_tag {

	var $row_colors;
	var $table_headers;
	var $tableHtmlAttributes;
	var $cellHtmlAttributes;
	var $rows;

	function table_tag ($opts = ''){
		$this->tableHtmlAttributes["cellspacing"] = $cellspacing;
		$this->tableHtmlAttributes["cellpadding"] = $cellpadding;
		if(is_array($opts)){
			if(is_array($opts['table_attributes'])){
				$this->tableHtmlAttributes = array_merge($this->tableHtmlAttributes, $opts['table_attributes']);
			}
			if(is_array($opts['row_colors'])){
				$this->row_colors = $opts['row_colors'];
			}
			if(is_array($opts['table_headers'])){
				$this->table_headers = $opts['table_headers'];
			}
			if(is_array($opts['styles'])){
				$this->styles = $opts['styles'];
			}
			if($opts['style_default']){
				$this->style_default = $opts['style_default'];
			}
		}

	}

	function add_row($cells){
		if(is_array($cells))
		$this->rows[] = $cells;
	}

	function add_rows($cells){
		if(is_array($cells))
		$this->rows = array_merge($this->rows, $cells);
	}

	function render(){
		$str = "<table ";
		if(count($this->tableHtmlAttributes)){
			foreach($this->tableHtmlAttributes as $keyName => $value){
				$str.=" $keyName=\"$value\"";
			}
		}
		$str.=">\r\n";

		if(count($this->table_headers)){
			$str.="<thead>\r\n\t<tr>\r\n";
			foreach($this->table_headers as $th){
				$str.="\t\t<th>$th</th>\r\n";
			}
			$str.="\t</tr>\r\n</thead>\r\n";
		}
		$c = 0;

		$str.="<tbody>\r\n";
		if(count($this->rows)){

			foreach($this->rows as $row){
				$str.="\t<tr bgcolor='{$this->row_colors[$c]}'>\r\n";
				$n = 0;
				foreach($row as $field){
					if(!$this->styles[$n]) $this->styles[$n] = $this->style_default;
					$str.="\t\t<td class='{$this->styles[$n]}'>$field</td>\r\n";
					$n++;
				}
				$str.="\t</tr>\r\n";
				if($c==count($this->row_colors)-1) $c = 0; else $c++;
			}
		}
		$str.="</tbody>\r\n</table>\r\n";
		print $str;
		return $str;
	}

	function add_table_att($att, $value=''){
		if(is_array($att)){
			$this->tableHtmlAttributes = array_merge($this->tableHtmlAttributes, $att);
		} else
		$this->tableHtmlAttributes[$att] = $value;
	}

	function add_cell_att(){
	}

	function set_data($data, $column_order = ''){
		if(is_array($data)){
			if(is_object($data[0])){
				foreach($data as $ar){
					$d = array();
					if(is_array($column_order)){
						foreach($column_order as $f){
							$d[]=$ar->$f;
						}
					} else {
						foreach($ar->fields as $f){
							$d[]=$ar->$f;
						}
					}
					$this->add_row($d);
				}
			} else {
				foreach($data as $ar){
					$d = array();
					if(is_array($column_order)){
						foreach($column_order as $f){
							$d[]=$ar["$f"];
						}
					} else {
						$d = array_merge($d, $ar);
					}
					$this->add_row($d);
				}
			}
		}
	}

}

function set_droppable($obj, $action=''){
	$opts = get_params(func_get_args());
	if(!$opts['name']){
		$opts['name'] = $opts[0];
	}
	print "<script type=\"text/javascript\">Droppables.add('{$opts['name']}', {hoverclass: '{$opts['hover_class']}',onDrop:{$opts['action']}})</script>";
}

function redirect_to($action, $seconds = 0.01){
	$seconds*=1000;
	print "<script type=\"text/javascript\">setTimeout('window.location=\"?/$action\"', $seconds)</script>";
}

function render_partial($partial_view, $value='', $_vars = array() ){
	if(file_exists("views/{$_REQUEST['controller']}/_$partial_view.phtml")){
		if(is_array(Kumbia::$models)){
			foreach(Kumbia::$models as $model_name => $model){
				$$model_name = $model;
			}
		}
		if(is_subclass_of(kumbia::$controller, "ApplicationController")){
			foreach(kumbia::$controller as $var => $value) {
				$$var = $value;
			}
		}
		// pass values to the partial
		foreach( $_vars as $_var => $_value ){
			$$_var = $_value;
		}
		
		$$partial_view = $value;
		include "views/{$_REQUEST['controller']}/_$partial_view.phtml";
	} else {
		Flash::kumbia_error('No existe la Vista<br>
			  <span style="font-size:16px">Kumbia no puede encontrar la vista "'.$partial_view.'"
			  </span>');
	}
}

function tab_tag($tabs, $color='green', $width=800){

	switch($color){
		case 'blue':
		$col1 = '#E8E8E8'; $col2 = '#C0c0c0'; $col3 = '#000000';
		break;

		case 'pink':
		$col1 = '#FFE6F2'; $col2 = '#FFCCE4'; $col3 = '#FE1B59';
		break;

		case 'orange':
		$col1 = '#FCE6BC'; $col2 = '#FDF1DB'; $col3 = '#DE950C';
		break;

		case 'green':
		$col2 = '#EAFFD7'; $col1 = '#DAFFB9'; $col3 = '#008000';
		break;
	}


	print "
			<table cellspacing=0 cellpadding=0 width=$width>
			<tr>";
	$p = 1;
	$w = $width;
	foreach($tabs as $tab){
		if($p==1) $color = $col1;
		else $color = $col2;
		$ww = (int) ($width * 0.22);
		$www = (int) ($width * 0.21);
		print "<td align='center'
				  width=$ww style='padding-top:5px;padding-left:5px;padding-right:5px;padding-bottom:-5px'>
				  <div style='width:$www"."px;border-top:1px solid $col3;border-left:1px solid $col3;border-right:1px solid $col3;background:$color;padding:2px;color:$col3;cursor:pointer' id='spanm_$p'
				  onclick='showTab($p, this)'
				  >".$tab['caption']."</div></td>";
		$p++;
		$w-=$ww;
	}
	print "
			<script>
				function showTab(p, obj){
				  	for(i=1;i<=$p-1;i++){
					    $('tab_'+i).hide();
					    $('spanm_'+i).style.background = '$col2';
					}
					$('tab_'+p).show();
					obj.style.background = '$col1'
				}
			</script>
			";
	$p = $p + 1;
	//$w = $width/2;
	print "<td width=$w></td><tr>";
	print "<td colspan=$p style='border:1px solid $col3;background:$col1;padding:10px'>";
	$p = 1;
	foreach($tabs as $tab){
		if($p!=1){
			print "<div id='tab_$p' style='display:none'>";
		} else {
			print "<div id='tab_$p'>";
		}
		render_partial($tab['partial']);
		print "</div>";
		$p++;
	}
	print "<br></td><td width=30></td>";
	print "</table>";
}

function tr_break($x=''){
	static $l;
	if($x=='') {
		$l = 0;
		return;
	}
	if(!$l) $l = 1;
	else $l++;
	if(($l%$x)==0) {
		print "</tr><tr>";
	}
}

function br_break($x=''){
	static $l;
	if($x=='') {
		$l = 0;
		return;
	}
	if(!$l) $l = 1;
	else $l++;
	if(($l%$x)==0) {
		print "<br/>\n";
	}
}

function tr_color($colors){
	static $i;
	if(func_num_args()>1){
		$params = get_params(func_get_args());
	}
	if(!$i) {
		$i = 1;
	}
	print "<tr bgcolor=\"{$colors[$i-1]}\"";
	if(count($colors)==$i) {
		$i = 1;
	} else {
		$i++;
	}
	if(is_array($params)){
		foreach($params as $key => $value){
			if(!is_numeric($key)){
				print " $key = '$value'";
			}
		}
	}
	print ">";
}

/**
 * Crea un Button que al hacer click carga
 * un controlador y una acción determinada
 *
 * @param string $caption
 * @param string $action
 * @param string $classCSS
 * @return HTML del Botón
 */
function button_to_action($caption, $action, $classCSS=''){
	return "<button class='$classCSS' onclick='window.location=\"".KUMBIA_PATH."$action\"'>$caption</button>";
}

/**
 * Crea un Button que al hacer click carga
 * con AJAX un controlador y una acción determinada
 *
 * @param string $caption
 * @param string $action
 * @param string $classCSS
 * @return HTML del Botón
 */
function button_to_remote_action($caption, $action, $classCSS=''){
	$opts = get_params(func_get_args());
	if(func_num_args()==2){
		$opts['action'] = $opts[1];
		$opts['caption'] = $opts[0];
	} else {
		if(!$opts['action']) $opts['action'] = $opts[1];
		if(!$opts['caption']) $opts['caption'] = $opts[0];
	}
	$code = "<button onclick='AJAX.execute({action:\"{$opts['action']}\", container:\"{$opts['update']}\", callbacks: { success: function(){{$opts['success']}}, before: function(){{$opts['before']}} } })'";
	unset($opts['action']);
	unset($opts['success']);
	unset($opts['before']);
	unset($opts['complete']);
	foreach($opts as $k => $v){
		if(!is_numeric($k)&&$k!='caption'){
			$code.=" $k='$v' ";
		}
	}
	$code.=">{$opts['caption']}</button>";
	return $code;
}

/**
 * Crea un select multiple que actualiza un container
 * usando una accion ajax que cambia dependiendo del id
 * selecionado en el select
 * @param string $id
 * @return code
 */
function updater_select($id){
	$opts = get_params(func_get_args());
	if(func_num_args()==1){
		$opts['id'] = $id;
	}
	if(!$opts['id']) $opts['id'] = $opts[0];
	if(!$opts['container']) $opts['container'] = $opts['update'];
	$code = "
	<select onchange='AJAX.viewRequest({
	action: \"{$opts['action']}/\"+$(\"{$opts['id']}\").value,
	container: \"{$opts['container']}\"
	})' ";
	unset($opts['container']);
	unset($opts['update']);
	unset($opts['action']);
	foreach($opts as $k => $v){
		if(!is_numeric($k)){
			$code.=" $k='$v' ";
		}
	}
	$code.=">\n";
	return $code;
} 

function text_field_with_autocomplete(){
	$value = get_value_from_action($name);
	$name = get_params(func_get_args());
	$hash = md5(uniqid());
	if(!$name['name']) $name['name'] = $name[0];
	if(!$name['after_update']) $name['after_update'] = "function(){}";
	if(!$name['id']) $name['id'] = $name['name'] ? $name['name'] : $name[0];
	if(!$name['message']) $name['message'] = "Consultando..";
	$code = "<input type='text' id='{$name[0]}' name='{$name['name']}'";
	foreach($name as $key => $value){
		if(!is_numeric($key)&&$key!="action"&&$key!="after_update"){
			$code.="$key='$value' ";
		}
	}
	$code.= "/>
	<span id='indicator$hash' style='display: none'><img src='".KUMBIA_PATH."img/spinner.gif' alt='{$name['message']}'/></span>
	<div id='{$name[0]}_choices' class='autocomplete'></div>
	<script type='text/javascript'>
	// <![CDATA[
	new Ajax.Autocompleter(\"{$name[0]}\", \"{$name[0]}_choices\", \$Kumbia.path+\"{$name['action']}\", { minChars: 2, indicator: 'indicator$hash', afterUpdateElement : {$name['after_update']}});
	// ]]>
	</script>
	";
	return $code;
}


#Other functions
function truncate($word, $number=0){
	if($number){
		return substr($word, 0, $number);
	} else {
		return rtrim($word);
	}
}

function highlight($sentence, $what){
	return str_replace($what, '<strong class="highlight">'.$what.'</strong>', $sentence);
}

function money($number){
	$number = my_round($number);
	return "$ ".number_format($number, 2, ",", ".");
}

function roundnumber($n, $d = 0) {
	$n = $n - 0;
	if ($d === NULL) $d = 2;

	$f = pow(10, $d);
	$n += pow(10, - ($d + 1));
	$n = round($n * $f) / $f;
	$n += pow(10, - ($d + 1));
	$n += '';

	if ( $d == 0 ):
		return substr($n, 0, strpos($n, '.'));
	else:
		return substr($n, 0, strpos($n, '.') + $d + 1);
	endif;
}

function my_round($number, $n=2){
	return ActiveRecord::static_select_one("round($number, $n)");
}

/**
 * Helper de Paginación
 *
 * @param array $items
 * @param integer $page_number
 * @param integer $show
 * @return object
 */
function paginate($items, $page_number=1, $show=10){

   $n = count($items);
   $start = $show*($page_number-1);

   $page = new stdClass();
   $page->items = array_slice($items, $start, $show);
   $page->next = ($start + $show)<$n ? ($page_number+1) : false ;
   $page->before = ($page_number>1) ? ($page_number-1) : false ;
   $page->current = $page_number;
   $page->total_pages = ($n % $show) ? ((int)($n/$show) + 1):($n/$show) ;

   return $page;
}

?>
