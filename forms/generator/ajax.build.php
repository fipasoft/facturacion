<?php

/** KumbiaForms - PHP Rapid Development Framework *****************************
*	
* Copyright (C) 2005-2007 Andr&eacute;s Felipe Guti&eacute;rrez (andresfelipe at vagoogle.net)
* Copyright (C) 2007-2007 Julian Cortes (jucorant at gmail.com)
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
* bajo los terminos de la licencia publica general GNU tal y como fue publicada
* por la Fundacion del Software Libre; desde la version 2.1 o cualquier
* version superior.
* 
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN 
* TIPO DE GARANTIA; sin dejar atras su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para mas detalles.
* 
* Debes recibir una copia de la Licencia Publica General GNU junto con este
* framework, si no es asi, escribe a Fundacion del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*
*****************************************************************************
* Functions for build Standard forms
*****************************************************************************/

class Standard_Generator {

	static function build_form_standard($form){

		if(!isset($_REQUEST['value'])) {
			$_REQUEST['value'] = "";
		}
		if(!isset($_REQUEST['option'])) {
			$_REQUEST['option'] = "";
		}
		if(!isset($_REQUEST['queryStatus'])) {
			$_REQUEST['queryStatus'] = false;
		}
		if(!isset($_REQUEST['oldsubaction'])) {
			$_REQUEST['oldsubaction'] = "";
		}
		if(!isset($form['unableInsert'])) {
			$form['unableInsert'] = false;
		}
		if(!isset($form['unableQuery'])) {
			$form['unableQuery'] = false;
		}
		if(!isset($form['unableUpdate'])){
			$form['unableUpdate'] = false;
		}
		if(!isset($form['unableDelete'])) {
			$form['unableDelete'] = false;
		}
		if(!isset($form['unableBrowse'])) {
			$form['unableBrowse'] = false;
		}
		if(!isset($form['unableReport'])) {
			$form['unableReport'] = false;
		}
		if(!isset($form['fieldsPerRow'])) {
			$form['fieldsPerRow'] = 1;
		}
		if(!array_key_exists('dataRequisite', $form)) $form['dataRequisite'] = 1;
		if(!$form['dataRequisite']) {
			Generator::forms_print("<font style='font-size:11px'><div align='center'><i><b>No hay datos en consulta</b></i></div></font>");
		} else {
			Generator::forms_print("<center>");			
			if($_REQUEST['oldsubaction']=='Modificar') {
				$_REQUEST['queryStatus'] = true;
			}			
			Generator::forms_print("<div id='ajax_messages' align='left'></div>\n");
			Generator::forms_print("<div id='spinner' style='display:none'>
			<table><tr><td><img src='".KUMBIA_PATH."img/spinner.gif'></td><td>Cargando...</td></tr></table>
			</div>\n");
			if(kumbia::$controller->view!='browse'){
				if(!$_REQUEST['queryStatus']){					
					if(!$form['unableInsert']){
						$caption = $form['buttons']['insert'] ? $form['buttons']['insert'] : "Adicionar";
						Generator::forms_print("<input type='button' class='controlButton' id='adiciona' value='$caption' lang='Adicionar' onclick='enable_insert(this)'>&nbsp;");						
					}
					if(!$form['unableQuery']){					
						$caption = $form['buttons']['query'] ? $form['buttons']['query'] : "Consultar";
						Generator::forms_print("<input type='button' class='controlButton' id='consulta' value='$caption' lang='Consultar' onclick='enable_query(this)'>&nbsp;\r\n");
					}
					$ds = "disabled";
				} else {
					$query_string = KUMBIA_PATH.$_REQUEST['controller']."/fetch/";
					Generator::forms_print("<input type='button' id='primero' class='controlButton' onclick='window.location=\"{$query_string}0/&amp;queryStatus=1\"' value='Primero'>&nbsp;");
					Generator::forms_print("<input type='button' id='anterior' class='controlButton' onclick='window.location=\"{$query_string}".($_REQUEST['id']-1)."/&amp;queryStatus=1\"' value='Anterior'>&nbsp;");
					Generator::forms_print("<input type='button' id='siguiente' class='controlButton' onclick='window.location=\"{$query_string}".($_REQUEST['id']+1)."/&amp;queryStatus=1\"' value='Siguiente'>&nbsp;");
					Generator::forms_print("<input type='button' id='ultimo' class='controlButton' onclick='window.location=\"{$query_string}last/&amp;queryStatus=1\"' value='Ultimo'>&nbsp;");
					$ds = "";
				}

				//El Boton de Actualizar
				if($_REQUEST['queryStatus']){
					if(!$form['unableUpdate']){
						$caption = $form['buttons']['update'] ? $form['buttons']['update'] : "Modificar";
						if(isset($form['updateCondition'])){
							if(strpos($form['updateCondition'], '@')){
								ereg("[\@][A-Za-z0-9_]+", $form['updateCondition'], $regs);
								foreach($regs as $reg){
									$form['updateCondition'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['updateCondition']);
								}
							}
							$form['updateCondition'] = " \$val = (".$form['updateCondition'].");";
							eval($form['updateCondition']);
							if($val){								
								Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");								
							}
						} else {
							Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");				
						}
					}
					//El de Borrar
					if(!$form['unableDelete']){	
						$caption = $form['buttons']['delete'] ? $form['buttons']['delete'] : "Borrar";
						Generator::forms_print("<input type='button' class='controlButton' id='borra' value='$caption' lang='Borrar' $ds onclick=\"enable_delete()\">\r\n&nbsp;");
					}
				}

				if(!$_REQUEST['queryStatus']) {
					if(!$form['unableBrowse']){	
						$caption = $form['buttons']['browse'] ? $form['buttons']['browse'] : "Visualizar";
						Generator::forms_print("<input type='button' class='controlButton' id='visualiza' value='$caption' lang='Visualizar' onclick='enable_browse(this, \"{$_REQUEST['controller']}\")'>&nbsp;\r\n");
					}
				}

				//Boton de Reporte
				if(!$_REQUEST['queryStatus']) {
					if(!$form['unableReport']){
						$caption = $form['buttons']['report'] ? $form['buttons']['report'] : "Reporte";
						Generator::forms_print("<input type='button' class='controlButton' id='reporte' value='$caption' lang='Reporte' onclick='enable_report(this)'>&nbsp;\r\n");
					}
				} else {
					Generator::forms_print("<br/><br/>\n<input type='button' class='controlButton' id='volver' onclick='window.location=\"".KUMBIA_PATH."{$_REQUEST['controller']}/back\"' value='Atr&aacute;s'>&nbsp;\r\n");
				}

				Generator::forms_print("</center><br>\r\n");
				Generator::forms_print("<table align='center'><tr>\r\n");
				$n = 1;
				//La parte de los Componentes
				Generator::forms_print("<td align='right' valign='top'>\r\n");
				foreach($form['components'] as $name => $com){

					switch($com['type']){
						case 'text':
						Component::build_text_component($com, $name, $form);
						break;

						case 'combo':
						Component::build_standard_combo($com, $name);
						break;

						case 'helpText':
						Component::build_help_context($com, $name, $form);
						break;

						case 'userDefined':
						Component::build_userdefined_component($com, $name, $form);
						break;

						case 'time':
						Component::build_time_component($com, $name, $form);
						break;

						case 'password':
						Component::build_standard_password($com, $name);
						break;

						case 'textarea':
						Component::build_text_area($com, $name);
						break;

						case 'image':
						Component::build_standard_image($com, $name);
						break;

						//Este es el Check Chulito
						case 'check':
						if($com['first']) Generator::forms_print("<b>".$com['groupcaption']."</b></td><td><table cellpadding=0>");
						Generator::forms_print("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
						if($_REQUEST['fl_'.$name]==$com['checkedValue'])
						Generator::forms_print(" checked ");
						if($com["attributes"])
						foreach($com["attributes"] as $nitem => $item) Generator::forms_print(" $nitem='$item' ");
						Generator::forms_print(">\r\n</td><td>".$com['caption']."</td></tr>");
						if($com["last"]) Generator::forms_print("</table>");
						break;

						//Cajita Grande
						case 'textarea':
						Generator::forms_print("<b>".$com['caption']." :</br></td><td><textarea disabled name='fl_$name' id='flid_$name' ");
						foreach($com['attributes'] as $natt => $vatt)
						Generator::forms_print("$natt='$vatt' ");
						Generator::forms_print(">".$_REQUEST['fl_'.$name]."</textarea>");
						break;

						//Oculto
						case 'hidden':
						Generator::forms_print("<input type='hidden' name='fl_$name' id='flid_$name' value='".($com['value'] ? $com['value'] : $_REQUEST['fl_'.$name])."'/>\r\n");
						break;

					}
					if($form['show_not_nulls']){
						if($com['notNull']&&$com['valueType']!='date'){
							Generator::forms_print("*\n");
						} 
					} 
					if($com['type']!='hidden'){
						Generator::forms_print("</td>");
						if($com['type']=='check'){
							if($com['last']) {
								if(!($n%$form['fieldsPerRow'])) Generator::forms_print("</tr><tr>\r\n");
								$n++;
								Generator::forms_print("<td align='right' valign='top'>");
							}
						}
						else {
							if(!($n%$form['fieldsPerRow'])) Generator::forms_print("</tr><tr>\r\n");
							$n++;
							Generator::forms_print("<td align='right' valign='top'>");
						}
					}
				}				
				Generator::forms_print("<br></td></tr><tr>				
				<td colspan='2' align='center'>				
				<div id='reportOptions' style='display:none' class='report_options'>
				<table>
				<td align='right'>
				<b>Formato Reporte:</b>
					<select name='reportType' id='reportType'>
						<option value='pdf'>PDF
						<option value='xls'>EXCEL
						<option value='doc'>WORD
						<option value='html'>HTML
					</select>
				</td>
				<td align='center'>
				<b>Ordenar Por:</b>
					<select name='reportTypeField' id='reportTypeField'>");
						reset($form['components']);
						for($i=0;$i<=count($form['components'])-1;$i++){
							if (!$form['components'][key($form['components'])]['notReport']){
								Generator::forms_print("<option value ='" .key($form['components']) ."'>".$form['components'][key($form['components'])]['caption']);
							}
							next($form['components']);
						}
				Generator::forms_print("</select>
				</td>
				</table>
				</div>
				<br>
				</td>
				</tr>");
				Generator::forms_print("</tr><br></table>\r\n");
			} else {
				require_once "forms/generator/browse.php";
				formsBrowse($form);
			}

			//Todos los Labels
			Generator::forms_print("<script type='text/javascript'>\nvar Labels = {");
			$aLabels = "";
			foreach($form['components'] as $key => $com){				
				$aLabels.=$key.": '".$com['caption']."',";				
			}
			$aLabels = substr($aLabels, 0, strlen($aLabels)-1);
			Generator::forms_print("$aLabels};\r\n");

			//Todos los campos
			Generator::forms_print("var Fields = [");
			reset($form['components']);
			for($i=0;$i<=count($form['components'])-1;$i++){
				Generator::forms_print("'".key($form['components'])."'");
				if($i!=(count($form['components'])-1)) Generator::forms_print(",");
				next($form['components']);
			}
			Generator::forms_print("];\r\n");

			//Campos que no pueden ser nulos
			Generator::forms_print("var NotNullFields = [");
			reset($form['components']);
			$NotNullFields = "";
			for($i=0;$i<=count($form['components'])-1;$i++){
				if(!isset($form['components'][key($form['components'])]['notNull'])){
					$form['components'][key($form['components'])]['notNull'] = false;
				}
				if(!isset($form['components'][key($form['components'])]['primary'])){
					$form['components'][key($form['components'])]['primary'] = false;
				}
				if($form['components'][key($form['components'])]['notNull']||$form['components'][key($form['components'])]['primary']){
					$NotNullFields.="'".key($form['components'])."',";
				}
				next($form['components']);
			}
			$NotNullFields = substr($NotNullFields, 0, strlen($NotNullFields)-1);
			Generator::forms_print("$NotNullFields];\r\n");

			Generator::forms_print("var DateFields = [");
			$dFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['valueType'])){
					if($value['valueType']=='date')
					$dFields.="'".$key."',";
				}
			}
			$dFields = substr($dFields, 0, strlen($dFields)-1);
			Generator::forms_print("$dFields];\r\n");

			//Campos que no son llave
			Generator::forms_print("var UFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if(!$value['primary']){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::forms_print("$uFields];\r\n");

			//Campos E-Mail
			Generator::forms_print("var emailFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['valueType'])){
					if($value['valueType']=='email'){
						$uFields.="'".$key."',";
					}
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::forms_print("$uFields];\r\n");

			//Campos Time
			Generator::forms_print("var timeFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if($value['type']=='time'){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::forms_print("$uFields];\r\n");

			//Campos Time
			Generator::forms_print("var imageFields = [");
			$uFields = "";
			foreach($form['components'] as $key => $value){
				if($value['type']=='image'){
					$uFields.="'".$key."',";
				}
			}
			$uFields = substr($uFields, 0, strlen($uFields)-1);
			Generator::forms_print("$uFields];\r\n");

			//Campos que son llave
			Generator::forms_print("var PFields = [");
			$pFields = "";
			foreach($form['components'] as $key => $value){
				if($value['primary']){
					$pFields.="'".$key."',";
				}
			}
			$pFields = substr($pFields, 0, strlen($pFields)-1);
			Generator::forms_print("$pFields];\r\n");

			//Campos que son Auto Numericos
			Generator::forms_print("var AutoFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(isset($value['auto_numeric'])){
					if($value['auto_numeric']){
						$aFields.="'".$key."',";
					}
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::forms_print("$aFields];\r\n");

			Generator::forms_print("var queryOnlyFields = [");
			$rFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['valueType'])) {
					$value['valueType'] = "";
				}
				if(!isset($value['queryOnly'])) {
					$value['queryOnly'] = false;
				}
				if($value['valueType']!='date'){
					if($value['queryOnly']){
						$rFields.="'".$key."',";
					}
				}
			}
			$rFields = substr($rFields, 0, strlen($rFields)-1);
			Generator::forms_print("$rFields];\r\n");

			Generator::forms_print("var queryOnlyDateFields = [");
			$rdFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['valueType'])) $value['valueType'] = "";
				if(!isset($value['queryOnly'])) $value['queryOnly'] = false;
				if($value['valueType']=='date'){
					if($value['queryOnly']){
						$rdFields.="'".$key."',";
					}
				}
			}
			$rdFields = substr($rdFields, 0, strlen($rdFields)-1);
			Generator::forms_print("$rdFields];\r\n");

			Generator::forms_print("var AddFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])) {
					$value['auto_numeric'] = false;
				}
				if(!isset($value['attributes']['value'])) {
					$value['attributes']['value'] = false;
				}
				if((!$value['auto_numeric'])&&(!$value['attributes']['value'])){
					$aFields.="'".$key."',";	
				}
				
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::forms_print("$aFields];\r\n");

			Generator::forms_print("var AutoValuesFields = [");
			$aFields = "";
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])) {
					$value['auto_numeric'] = false;
				}
				if($value['auto_numeric']){
					$aFields.="'".$key."',";
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::forms_print("$aFields];\r\n");

			Generator::forms_print("var AutoValuesFFields = [");
			$aFields = "";
			if(!isset($db)) {
				$db = db::raw_connect();
			}
			foreach($form['components'] as $key => $value){
				if(!isset($value['auto_numeric'])) $value['auto_numeric'] = false;
				if($value['auto_numeric']){
					ActiveRecord::sql_item_sanizite($key);
					ActiveRecord::sql_item_sanizite($form['source']);
					$q = $db->query("select max($key)+1 from ".$form['source']);
					$row = $db->fetch_array($q);
					$aFields.="'".($row[0] ? $row[0] : 1 )."',";
				}
			}
			$aFields = substr($aFields, 0, strlen($aFields)-1);
			Generator::forms_print("$aFields];\r\n");

			if(!isset($_REQUEST['param'])) {
				$_REQUEST['param'] = "";
			}
			
			Generator::forms_print("\nnew Event.observe(window, \"load\", function(){\n");
			if(kumbia::$controller->keep_action){
				Generator::forms_print("\tkeep_action('".kumbia::$controller->keep_action."');\n");
			}
			Generator::forms_print("\tregister_form_events()\n})\n</script>\n");
									
			if(kumbia::$controller->view!='browse'){
				Generator::forms_print("<center><input type='button' class='controlButton' id='aceptar' value='Aceptar' disabled onclick='form_accept()'>&nbsp;");
				Generator::forms_print("<input type='button' class='controlButton' id='cancelar' value='Cancelar' disabled onclick='cancel_form()'>&nbsp;</center>");				
				Generator::forms_print("<input type='hidden' id='actAction' value=''>\n
				</form>
                <form id='saveDataForm' method='post' action='' style='display:none' enctype=\"multipart/form-data\"></form>");								
			}
						
		}		

	}

}
?>