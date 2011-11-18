<?php

/** KumbiaForms - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
*
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
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
* Este framework es distribuido con la esperanza de ser útil pero SIN NINGUN
* TIPO DE GARANTIA; sin dejar atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
*
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

require_once "forms/db/main.php";
require_once "forms/db/active_record.php";
require_once "forms/errors/main.php";
require_once "forms/security/main.php";
require_once "forms/generator/builder.php";

class Generator {

	static public $outForm = "";

	static function get_index($field, $form){
		$n = 0;
		foreach($form['components'] as $name => $comp){
			if($name==$field) {
				return $n;
			}
			$n++;
		}
		return 0;
	}

	/**
	 * Obtiene el tipo de explorador usado por el cliente
	 * de la aplicación
	 *
	 * @return string
	 */
	static function get_browser(){
		if(strpos($_SERVER['HTTP_USER_AGENT'], "Firefox")){
			return "firefox";
		} else {
			return "msie";
		}
	}

	/**
	 * Genera una salida que es cacheada para luego hacer que
	 * salga toda junta
	 *
	 * @param mixed $val
	 */
	static function forms_print($val){
		self::$outForm.=$val;
	}

	/**
	 * Imprime la salida que estaba cacheada utilizando self::forms_print
	 *
	 */
	static function build_form_out(){
		print self::$outForm;
		self::$outForm = "";
	}

	function no_access_error(){
		print "<table class='no_access' align='center' cellpadding=10>
			<td><h1><u>No tiene Acceso a este Modulo</u><h1>
			<div align='left'>No est&aacute; autorizado para trabajar en este modulo.<br>
			Por favor consulte con el Administrador del Sistema cualquier inquietud<br><br>
			<input type='button' class='errorButton' value='Atrás'
			onclick='history.back()'><br><br></div></td>
			</table>";
	}

	/**
	 * Vuelca la información de la tabla para construir el array
	 * interno que luego sirve para construir el formulario
	 *
	 * @param array $form
	 * @return boolean
	 */
	static function dump_field_information($form){
		$form['force'] = eval("return {$_REQUEST['controller']}Controller::\$force;");
		if($_SESSION['dumps'][$_SESSION['KUMBIA_PATH']][$form['source'].$form['type']]&&!$form['force']){
			$form = unserialize($_SESSION['dumps'][$_SESSION['KUMBIA_PATH']][$form['source'].$form['type']]);
			return true;
		}
		$config = Config::read();
		include_once "lib/kumbia/utils.php";
		$db = db::raw_connect();
		if($config->database->type=='mysql'){
			$q = $db->query('desc '.$form['source']);
			if(!$q) {
				Flash::kumbia_error("No existe la tabla {$form['source']} en la base de datos {$config->database->name}");
				return false;
			}
			$cp = $form;
			$form = array();
			$n = 0;
			if(!$form['components']) {
				$form['components'] = array();
			}
			while($field = $db->fetch_array($q)){
				array_insert($form['components'], $n, array(), $field['Field']);
				if($field['Type']=='date'){
					if(!isset($form['components'][$field['Field']]['valueType'])){
						$form['components'][$field['Field']]['valueType'] = "date";
					}
				}
				if($field['Field']=='id'){
					$form['components'][$field['Field']]['auto_numeric'] = true;
					if($cp['type']=='grid'){
						$form['components'][$field['Field']]['type'] = "auto";
					}
				}
				if($field['Field']=='email'){
					if(!isset($form['components'][$field['Field']]['valueType'])){
						$form['components'][$field['Field']]['valueType'] = "email";
					}
				}
				if($field['Key']=='PRI'){
					if(!isset($form['components'][$field['Field']]['primary'])){
						$form['components'][$field['Field']]['primary'] = true;
					}
				}
				if($field['Null']=='NO'){
					if(!isset($form['components'][$field['Field']]['notNull'])){
						$form['components'][$field['Field']]['notNull'] = true;
					}
				}
				if(strpos(" ".$field['Type'], "int")||strpos(" ".$field['Type'], "decimal")){
					if(!isset($form['components'][$field['Field']]['valueType'])){
						$form['components'][$field['Field']]['valueType'] = "numeric";
					}
				}
				if($field['Type']=='text'){
					$form['components'][$field['Field']]['type'] = 'textarea';
				}
				if($field['Field']=='email'){
					if(!isset($form['components'][$field['Field']]['valueType'])){
						$form['components'][$field['Field']]['valueType'] = "email";
					}
				}
				if(ereg("[a-z_0-9A-Z]+_id$", $field['Field'])){
					$table = substr($field['Field'], 0, strpos($field['Field'], "_id"));
					ActiveRecord::sql_item_sanizite($table);
					$dq = $db->query("desc $table");
					if($dq){
						$y = 0;
						$p = 0;
						while($rowq = $db->fetch_array($dq)){
							if($rowq['Field']=='id'){
								$p = 1;
							}
							if(
							($rowq['Field']=='detalle')||
							($rowq['Field']=='nombre')||
							($rowq['Field']=='descripcion')||
							($rowq['Field']=='name')
							){
								$detail = $rowq['Field'];
							}
						}
						if($p&&$detail&&!isset($form['components'][$field['Field']]['type'])){
							$form['components'][$field['Field']]['type'] = 'combo';
							$form['components'][$field['Field']]['class'] = 'dynamic';
							$form['components'][$field['Field']]['foreignTable'] = $table;
							if(!isset($form['components'][$field['Field']]['detailField'])){
								$form['components'][$field['Field']]['detailField'] = $detail;
							}
							$form['components'][$field['Field']]['orderBy'] = "2";
							$form['components'][$field['Field']]['column_relation'] = "id";
							$form['components'][$field['Field']]['caption'] =
							ucwords(str_replace("_", " ", str_replace("_id", "", $field['Field'])));
						}
					}
				} else {
					if($x = strpos(" ".$field['Type'], "(")){
						$l = substr($field['Type'], $x);
						$l = substr($l, 0, strpos($l, ")"));
						if(!isset($form['components'][$field['Field']]['attributes']['size'])){
							$form['components'][$field['Field']]['attributes']['size'] = (int) $l;
						}
						if(!isset($form['components'][$field['Field']]['attributes']['maxlength'])){
							$form['components'][$field['Field']]['attributes']['maxlength'] = (int) $l;
						}
					}
				}
				if(!isset($form['components'][$field['Field']]['type'])){
					$form['components'][$field['Field']]['type'] = "text";
				}
				$n++;
			}
		}
		if($config->database->type=='postgresql'){
			$q = $db->query("
			SELECT a.attname AS Field, t.typname AS Type, CASE WHEN attnotnull=false
			THEN 'YES' ELSE 'NO' END as Null, CASE WHEN a.atttypmod > 0
			THEN a.atttypmod ELSE a.attlen*8 END AS Length,
			CASE WHEN (select cc.contype from pg_catalog.pg_constraint cc WHERE
			cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI' ELSE ''
			END AS Key FROM pg_catalog.pg_class c, pg_catalog.pg_attribute a,
			pg_catalog.pg_type t WHERE c.relname = '{$form['source']}'
			AND c.oid = a.attrelid AND a.attnum > 0 AND t.oid = a.atttypid
			ORDER BY a.attnum");
			if(!$q) {
				Flash::kumbia_error("No existe la tabla {$form['source']} en la base de datos {$config->database->name}");
				return false;
			}
			$cp = $form;
			$form = array();
			$n = 0;
			if(!$form['components']) {
				$form['components'] = array();
			}
			while($field = $db->fetch_array($q)){
				array_insert($form['components'], $n, array(), $field['field']);
				if($field['type']=='date'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "date";
					}
				}
				if($field['field']=='id'){
					$form['components'][$field['field']]['auto_numeric'] = true;
					if($cp['type']=='grid'){
						$form['components'][$field['field']]['type'] = "auto";
					}
				}
				if($field['field']=='email'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "email";
					}
				}
				if($field['key']=='PRI'){
					if(!isset($form['components'][$field['field']]['primary'])){
						$form['components'][$field['field']]['primary'] = true;
					}
				}
				if($field['null']=='NO'){
					if(!isset($form['components'][$field['field']]['notNull'])){
						$form['components'][$field['field']]['notNull'] = true;
					}
				}
				if(strpos(" ".$field['type'], "int")||strpos(" ".$field['type'], "decimal")){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "numeric";
					}
				}
				if($field['type']=='text'){
					$form['components'][$field['field']]['type'] = 'textarea';
				}
				if($field['field']=='email'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "email";
					}
				}
				if(ereg("[a-z_0-9A-Z]+_id$", $field['field'])){
					$table = substr($field['field'], 0, strpos($field['field'], "_id"));
					$dq = $db->query("
					SELECT a.attname AS Field, t.typname AS Type,
					CASE WHEN attnotnull=false
					THEN 'YES' ELSE 'NO' END as Null, CASE WHEN a.atttypmod > 0
					THEN a.atttypmod ELSE a.attlen*8 END AS Length,
					CASE WHEN (select cc.contype from pg_catalog.pg_constraint cc WHERE
					cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI'
					ELSE '' END AS Key FROM pg_catalog.pg_class c,
					pg_catalog.pg_attribute a, pg_catalog.pg_type t
					WHERE c.relname = '{$table}' AND c.oid = a.attrelid
					AND a.attnum > 0 AND t.oid = a.atttypid
					ORDER BY a.attnum");
					if($dq){
						$y = 0;
						$p = 0;
						while($rowq = $db->fetch_array($dq)){
							if($rowq['field']=='id'){
								$p = 1;
							}
							if(
							($rowq['field']=='detalle')||
							($rowq['field']=='nombre')||
							($rowq['field']=='descripcion')||
							($rowq['field']=='name')
							){
								$detail = $rowq['field'];
							}
						}
						if($p&&$detail&&!isset($form['components'][$field['field']]['type'])){
							$form['components'][$field['field']]['type'] = 'combo';
							$form['components'][$field['field']]['class'] = 'dynamic';
							$form['components'][$field['field']]['foreignTable'] = $table;
							if(!isset($form['components'][$field['field']]['detailField'])){
								$form['components'][$field['field']]['detailField'] = $detail;
							}
							$form['components'][$field['field']]['orderBy'] = "2";
							$form['components'][$field['field']]['column_relation'] = "id";
							$form['components'][$field['field']]['caption'] =
							ucwords(str_replace("_", " ", str_replace("_id", "", $field['field'])));
						}
					}
				} else {
					if(!isset($form['components'][$field['field']]['attributes']['size'])){
						if($field['length']>30){
							$form['components'][$field['field']]['attributes']['size'] = (int) 20;
						} else {
							$form['components'][$field['field']]['attributes']['size'] = (int) $field['length'];
						}
					}
					if(!isset($form['components'][$field['field']]['attributes']['maxlength'])){
						$form['components'][$field['field']]['attributes']['maxlength'] = (int) $field['length'];

					}
				}
				if(!isset($form['components'][$field['field']]['type'])){
					$form['components'][$field['field']]['type'] = "text";
				}
				$n++;
			}
		}
		if($config->database->type=='oracle'){
			$q = $db->query("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD, LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE, ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (SELECT COUNT(*) FROM ALL_CONS_COLUMNS WHERE TABLE_NAME = '".strtoupper($form['source'])."' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS WHERE ALL_TAB_COLUMNS.TABLE_NAME = '".strtoupper($form['source'])."'");
			if(!$q) {
				Flash::kumbia_error("No existe la tabla {$form['source']} en la base de datos {$config->database->name}");
				return false;
			}
			$cp = $form;
			$form = array();
			$n = 0;
			if(!$form['components']) {
				$form['components'] = array();
			}
			while($field = $db->fetch_array($q)){
				array_insert($form['components'], $n, array(), $field['field']);
				if($field['type']=='date'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "date";
					}
				}
				if($field['field']=='id'){
					$form['components'][$field['field']]['auto_numeric'] = true;
					if($cp['type']=='grid'){
						$form['components'][$field['field']]['type'] = "auto";
					}
				}
				if($field['field']=='email'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "email";
					}
				}
				if($field['key']){
					if(!isset($form['components'][$field['field']]['primary'])){
						$form['components'][$field['field']]['primary'] = true;
					}
				}
				if($field['isnull']=='N'){
					if(!isset($form['components'][$field['field']]['notNull'])){
						$form['components'][$field['field']]['notNull'] = true;
					}
				}
				if(strpos(" ".$field['type'], "int")||strpos(" ".$field['type'], "decimal")||strpos(" ".$field['type'], "number")){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "numeric";
					}
				}
				if($field['type']=='text'){
					$form['components'][$field['field']]['type'] = 'textarea';
				}
				if($field['field']=='email'){
					if(!isset($form['components'][$field['field']]['valueType'])){
						$form['components'][$field['field']]['valueType'] = "email";
					}
				}
				if(ereg("[a-z_0-9A-Z]+_id$", $field['field'])){
					$table = substr($field['field'], 0, strpos($field['field'], "_id"));
					$dq = $db->query("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD, LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE, ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (SELECT COUNT(*) FROM ALL_CONS_COLUMNS WHERE TABLE_NAME = '".strtoupper($table)."' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS WHERE ALL_TAB_COLUMNS.TABLE_NAME = '".strtoupper($table)."'");
					if($dq){
						$y = 0;
						$p = 0;
						while($rowq = $db->fetch_array($dq)){
							if($rowq['field']=='id'){
								$p = 1;
							}
							if(
							($rowq['field']=='detalle')||
							($rowq['field']=='nombre')||
							($rowq['field']=='descripcion')||
							($rowq['field']=='name')
							){
								$detail = $rowq['field'];
							}
						}
						if($p&&$detail&&!isset($form['components'][$field['field']]['type'])){
							$form['components'][$field['field']]['type'] = 'combo';
							$form['components'][$field['field']]['class'] = 'dynamic';
							$form['components'][$field['field']]['foreignTable'] = $table;
							if(!isset($form['components'][$field['field']]['detailField'])){
								$form['components'][$field['field']]['detailField'] = $detail;
							}
							$form['components'][$field['field']]['orderBy'] = "2";
							$form['components'][$field['field']]['column_relation'] = "id";
							$form['components'][$field['field']]['caption'] =
							ucwords(str_replace("_", " ", str_replace("_id", "", $field['field'])));
						}
					}
				} else {
					if(!isset($form['components'][$field['field']]['attributes']['size'])){
						if($field['length']>30){
							$form['components'][$field['field']]['attributes']['size'] = (int) 20;
						} else {
							$form['components'][$field['field']]['attributes']['size'] = (int) $field['length'];
						}
					}
					if(!isset($form['components'][$field['field']]['attributes']['maxlength'])){
						$form['components'][$field['field']]['attributes']['maxlength'] = (int) $field['length'];
					}
				}
				if(!isset($form['components'][$field['field']]['type'])){
					$form['components'][$field['field']]['type'] = "text";
				}
				$n++;
			}
		}
		if(!count($cp['components'])) {
			unset($cp['components']);
		}

		$form = array_merge_overwrite($form, $cp);
		foreach($form['components'] as $key => $value){
			if($value['ignore']) {
				unset($form['components'][$key]);
			}
		}
		$_SESSION['dumps'][$form['source'].$form['type']] = serialize($form);
		return true;
	}

	/**
	 * Genera información importante para la construcción del formulario
	 *
	 * @param mixed $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function scaffold($form, $scaffold = false){

		if(!is_array($form)){
			$form = array();
		}

		if(isset($form['source'])) {
			if(!$form['source']) {
				kumbia::$controller->source = $_REQUEST['controller'];
				$form['source'] = $_REQUEST['controller'];
			}
		} else {
			if(kumbia::$controller->source){
				$form['source'] = kumbia::$controller->source;
			} else {
				kumbia::$controller->source = $_REQUEST['controller'];
				$form['source'] = $_REQUEST['controller'];
			}
		}
		ActiveRecord::sql_item_sanizite($form['source']);
		if(isset($form['caption'])) {
			if(!$form['caption']) {
				$form['caption'] = ucwords(str_replace("_", " ", $_REQUEST['controller']));
			}
		} else {
			$form['caption'] = ucwords(str_replace("_", " ", $_REQUEST['controller']));
		}

		if(isset($form['type'])) {
			if(!$form['type']) {
				$form['type'] = 'standard';
			}
		}
		else $form['type'] = 'standard';

		//Dump Data Field Information if no components are loaded
		if(!isset($form['components']))	{
			$form['components'] = null;
		}
		if(!isset($form['scaffold'])) {
			$form['scaffold'] = false;
		}
		if((!$form['components'])||$form['scaffold']||$scaffold){
			if(!self::dump_field_information(&$form)){
				return false;
			}
			if($form['type']=='master-detail'){
				self::dump_field_information(&$form['detail']);
				$form['detail']['dataFilter'] = "{$form['detail']['source']}.{$form['source']}_id = '@id'";
				foreach($form["detail"]['components'] as $k => $f){
					if($k=='id'){
						$form["detail"]['components'][$k]['type'] = "auto";
						$form["detail"]['components'][$k]['caption'] = "";
						$f['caption'] = "";
						$f['type'] = "auto";
					}
					if($k==$form['source']."_id"){
						$form["detail"]['components'][$k]['type'] = "hidden";
						$form["detail"]['components'][$k]['caption'] = "";
						$form["detail"]['components'][$k]['attributes']['value'] = $_POST["fl_id"];
						$f['caption'] = "";
						$f['type'] = "hidden";
					}
					if(!isset($f["caption"])) {
						if($f['type']!='auto'&&$f['type']!='hidden'){
							$form["detail"]['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
						}
					}
				}
			}
		}

		if(!$form['components']){
			Flash::kumbia_error("No se pudo cargar la información de la relación '{$form['source']}'</span><br>Verifique que la entidad exista
		en la base de datos actual ó que los parámetros seán correctos");
			return;
		}

		//Creating Captions
		foreach($form['components'] as $k => $f){
			if(!isset($f["caption"])) {
				if($f['type']!='auto'&&$f['type']!='hidden'){
					$form['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
				}
			}
		}

	}

	/**
	 * BuildForm is the main function that builds all the forms
	 *
	 * @param array $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function build_form($form, $scaffold=false){

		require_once "forms/generator/components.php";

		//self::$outForm = "";

		Generator::scaffold(&$form, $scaffold);

		if(!$form['components']) return false;

		//Loading The JavaScript Functions
		self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.js'></script>\r\n");
		if($form['type']=='ajax'){
			self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.ajax.js'></script>\r\n");
		}
		if($form['type']=='grid'){
			self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.grid.js'></script>\r\n");
		}
		if($form['type']=='standard'||$form['type']=='insert-only'){
			self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.standard.js'></script>\r\n");
		}
		if($form['type']=='master-detail'){
			self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.master_detail.js'></script>\r\n");
			self::forms_print("<script type='text/javascript' language='JavaScript1.2' src='".KUMBIA_PATH."javascript/kumbia/load.grid.js'></script>\r\n");
		}	
		self::forms_print("<script language='JavaScript1.2' type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/utilities.calendarDateInput.js'></script>\r\n");
		self::forms_print("<script language='JavaScript1.2' type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/email.js'></SCRIPT>\r\n");		
		self::forms_print("<SCRIPT language='JavaScript1.2'  type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/webServices.js'></SCRIPT>\r\n");

		if(file_exists("public/javascript/{$_REQUEST["controller"]}.js")){
			self::forms_print("<SCRIPT language='JavaScript1.2'  type='text/javascript' src='".KUMBIA_PATH."javascript/{$_REQUEST["controller"]}.js'></SCRIPT>\r\n");
		}
		
		if(file_exists("public/css/{$_REQUEST["controller"]}.css")){
			self::forms_print("<link rel='stylesheet' href='".KUMBIA_PATH."css/{$_REQUEST["controller"]}.css' type='text/css'/>\n");			
		}

		self::forms_print("<div class='{$_REQUEST['controller']}'>
		<form method='post' name='fl' action='' onsubmit='return false'>");
		if(!isset($form["notShowTitle"])){
			if(isset($form['titleImage'])){
				if($form['titleHelp']){
					self::forms_print("<table><td><img src='".KUMBIA_PATH."img/{$form['titleImage']}' border=0></td>
				<td><h1 class='".$form['titleStyle']."' title='{$form['titleHelp']}'
				style='cursor:help'>&nbsp;<u>".$form["caption"]."</u></h1>
				</td></table>\r\n");
				} else {
					self::forms_print("<table><td><img src='".KUMBIA_PATH."img/{$form['titleImage']}' border=0></td>
				<td><h1 class='".$form['titleStyle']."'>&nbsp;".$form["caption"]."</h1>
				</td></table>\r\n");
				}
			} else {
				if(!isset($form['titleStyle'])) {
					$form['titleStyle'] = "";
				}
				self::forms_print("<h1 class='".$form['titleStyle']."'>&nbsp;".$form["caption"]."</h1>\r\n");
			}
		}
		self::forms_print("<input type='hidden' name='aaction' value='".$_REQUEST['controller']."'>\r\n");
		self::forms_print("<input type='hidden' id='kb_path' value='".KUMBIA_PATH."'>\r\n");
		if(isset($_REQUEST['value'])){
			self::forms_print("<input type='hidden' name='vvalue' value='".$_REQUEST['value']."'>\r\n");
		}
		self::forms_print("<input type='hidden' id='errStatus' value='0'>\r\n");
		self::forms_print("<input type='hidden' id='winHelper' value='0'>\r\n");
		if($_REQUEST['action']=='validation'){
			self::forms_print("<input type='hidden' id='validation' value='1'>\r\n");
		} else {
			self::forms_print("<input type='hidden' id='validation' value='0'>\r\n");
		}

		// Grid Style Forms
		if($form['type']=='grid'){
			include_once "forms/generator/grid.build.php";
			GridForm_Generator::build_form_grid($form);
		}

		//Standard Forms
		if($form['type']=='standard'){
			include_once "forms/generator/standard.build.php";
			Standard_Generator::build_form_standard($form);
		}

		//AJAX Forms
		if($form['type']=='ajax'){
			include_once "forms/generator/ajax.build.php";
			Standard_Generator::build_form_standard($form);
		}

		//MasterDetail Forms
		if($form['type']=='master-detail'){
			include_once "forms/generator/master_detail.build.php";
			include_once "forms/generator/grid.build.php";
			build_form_master_detail($form);
		}

		if($form['type']=='insert-only'){
			include_once "forms/generator/insertonly.build.php";
			buildInsertOnly($form);
		}

		self::forms_print("</div>");

		self::build_form_out();
	}

	static function get_max_auto($db, $table, $field){
		ActiveRecord::sql_item_sanizite($table);
		ActiveRecord::sql_item_sanizite($field);
		$db->query("select max($field)+1 from $table");
		$row = $db->fetch_array();
		if(!$row[0]) $row[0] = 1;
		return $row[0];
	}

}

?>
