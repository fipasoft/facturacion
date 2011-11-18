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
* TIPO DE GARANTIA; sin dejar atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
* 
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*
*****************************************************************************
* Functions for build Master-Detail forms
*****************************************************************************/

class MasterDetail_Generator {

	function build_form_master_detail($form){

		if(!array_key_exists('dataRequisite', $form)) $form['dataRequisite'] = 1;
		if(!$form['dataRequisite']) {
			Generator::forms_print("<font style='font-size:11px'><center><i><b>No hay datos en consulta</b></i></center></font>");
		} else {
			if($_REQUEST['subaction']!='browse'){
				Generator::forms_print("<center>");
				if(!array_key_exists('permissions', $form)) {
					$form['permissions'] = array();
				}
				if($_REQUEST['oldsubaction']=='Modificar') {
					$_REQUEST['queryStatus'] = true;
				}

				if($form['controlButtons']){
					foreach($form['controlButtons'] as $name => $cButton){
						Generator::forms_print("<input type='button' class='controlButton' id='$name' value='".$cButton['caption']."' onclick='".$cButton['action']."'>&nbsp;");
					}
				}

				if(!$_REQUEST['queryStatus']){
					if(!$form['unableInsert']){
						/*foreach($form['components'] as $key => $com){
						$pkey = "";
						if($com["primary"]){
						$pkey $key
						}
						}*/
						if(array_key_exists('insert', $form['permissions'])){
							if(getPermission($form, 'insert'))
							Generator::forms_print("<input type='button' class='controlButton' id='adiciona' value='Adicionar' onclick='enableInsertMD(this)'>&nbsp;");
						} else Generator::forms_print("<input type='button' class='controlButton' id='adiciona' value='Adicionar' onclick='enableInsertMD(this)'>&nbsp;");
					}
					if(array_key_exists('query', $form['permissions'])){
						if(getPermission($form, 'query'))
						Generator::forms_print("<input type='button' class='controlButton' id='consulta' value='Consultar' onclick='enableQueryMD(this)'>&nbsp;");
					} else Generator::forms_print("<input type='button' class='controlButton' id='consulta' value='Consultar' onclick='enableQueryMD(this)'>&nbsp;");
					$ds = "disabled";
				} else {
					$query_string = "action=".$_REQUEST['action']."&value=".$_REQUEST['value']."&option=".$_REQUEST['option'];
					Generator::forms_print("<button id='primero' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=0\"'>&nbsp;Primero</button>&nbsp;");
					Generator::forms_print("<button id='anterior' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=".($_REQUEST['number']-1)."\"'>&nbsp;&nbsp;Anterior</button>&nbsp;");
					Generator::forms_print("<button id='siguiente' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=".($_REQUEST['number']+1)."\"'>Siguiente&nbsp;</button>&nbsp;");
					Generator::forms_print("<button id='ultimo' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=last\"'>Ultimo&nbsp;&nbsp;</button>&nbsp;");
				}

				//El Boton de Actualizar
				if($_REQUEST['queryStatus']){
					if(!$form['unableUpdate']){
						if($form['updateCondition']){
							if(strpos($form['updateCondition'], '@')){
								ereg("[\@][A-Za-z0-9_]+", $form['updateCondition'], $regs);
								foreach($regs as $reg){
									$form['updateCondition'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['updateCondition']);
								}
							}
							$form['updateCondition'] = " \$val = (".$form['updateCondition'].");";
							eval($form['updateCondition']);
							if($val){
								if(array_key_exists('update', $form['permissions'])){
									if(getPermission($form, 'update'))
									Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
								} else Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
							}
						} else {
							if(array_key_exists('update', $form['permissions'])){
								if(getPermission($form, 'update'))
								Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
							} else Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
						}
					}
				}

				//El de Borrar
				if($_REQUEST['queryStatus']){
					if(!$form['unableDelete']){
						if(array_key_exists('delete', $form['permissions'])){
							if(getPermission($form, 'delete'))
							Generator::forms_print("<input type='button' class='controlButton' id='borra' value='Borrar' $ds onclick=\"enableDeleteMD()\">&nbsp;");
						} else Generator::forms_print("<input type='button' class='controlButton' id='borra' value='Borrar' $ds onclick=\"enableDeleteMD()\">&nbsp;");
					}
				}

				if(!$_REQUEST['queryStatus']) {
					if(array_key_exists('browse', $form['permissions'])){
						if(getPermission($form, 'browse'))
						Generator::forms_print("<input type='button' class='controlButton' id='visualiza' value='Visualizar' onclick='enableBrowse(this, \"{$_REQUEST['action']}\")'>&nbsp;");
					} else Generator::forms_print("<input type='button' class='controlButton' id='visualiza' value='Visualizar' onclick='enableBrowse(this, \"{$_REQUEST['action']}\")'>&nbsp;");
				}

				//Boton de Reporte
				if(!$_REQUEST['queryStatus']) {
					if(array_key_exists('report', $form['permissions'])){
						if(getPermission($form, 'report'))
						Generator::forms_print("<input type='button' class='controlButton' id='reporte' value='Reporte' onclick='enableReportMD(this)'>&nbsp;");
					} else Generator::forms_print("<input type='button' class='controlButton' id='reporte' value='Reporte' onclick='enableReportMD(this)'>&nbsp;");
				} else Generator::forms_print("<br><br><button class='controlButton' id='volver' onclick='javascript:window.location=\"?$query_string&subaction=back\"'>Atras&nbsp;&nbsp;</button>&nbsp;");


				Generator::forms_print("\r\n</center><br/><br/>");

				if($form['useCompactStyle']){
					Generator::forms_print("<table align='center' style='position:xabsolute;left:".$form['left'].";top:".$form['top']."' cellspacing=0>
				  <tr><td width=100%><table cellpadding=3 cellspacing=0>
				  <td class='activeForm' id='tab1'><a href='#'
				  onclick=\"if(document.getElementById('tab1').className=='activeForm'){ document.getElementById('tab1').className='inactiveForm'; document.getElementById('tab2').className='activeForm'; document.getElementById('tabForm2').style.visibility='visible'; document.getElementById('tabForm1').style.visibility='hidden'; } else { document.getElementById('tab1').className='activeForm'; document.getElementById('tab2').className='inactiveForm'; document.getElementById('tabForm2').style.visibility='hidden'; document.getElementById('tabForm1').style.visibility='visible';}\"
				  >".str_replace(" ", "&nbsp;", $form['caption'])."</a></td>
				  <td id='tab2' class='inactiveForm' width='100%'				  				  
				  ><a href='#' onclick=\"if(document.getElementById('tab2').className=='inactiveForm'){ document.getElementById('tab1').className='inactiveForm'; document.getElementById('tab2').className='activeForm'; document.getElementById('tabForm2').style.visibility='visible'; document.getElementById('tabForm1').style.visibility='hidden'; } else { document.getElementById('tab1').className='activeForm'; document.getElementById('tab2').className='inactiveForm'; document.getElementById('tabForm2').style.visibility='hidden'; document.getElementById('tabForm1').style.visibility='visible';}\">".str_replace(" ", "&nbsp;", $form['detail']['caption'])."</a></td>
				  </table></td></tr>
				  <tr><td align='center' id='tabForm1' 
				  style='visibility:visible;'><br>");
				}

				Generator::forms_print("<table align='center'><tr>");
				$n = 1;


				//La parte de los Componentes
				Generator::forms_print("<td valign='top' align='right'>");
				foreach($form['components'] as $name => $com){

					//SI esa de Tipo TExt
					if($com['type']=='text') buildMasterTextComponent($com, $name, $form);
					if($com['type']=='helpText') buildHelpContext($com, $name, $form);

					//Tipo password
					if($com['type']=='password'){
						Generator::forms_print("<b>".$com['caption']." : </b></td><td id='tp$i' valign='top'><input type='password' name='fl_$name' id='flid_$name' disabled ");
						if($_REQUEST['fl_'.$name])
						Generator::forms_print("value = '".$_REQUEST['fl_'.$name]."'");
						foreach($com["attributes"] as $nitem => $item)
						Generator::forms_print(" $nitem='$item' ");
						Generator::forms_print(" onkeydown='capturePassword(tp$i, flid_$name)' onblur='nextRetype()'");
						Generator::forms_print("/>\r\n");
					}

					//Cuando es de Tipo Combo
					if($com['type']=='combo') buildStandardCombo($com, $name);

					//Este es el Check Chulito
					if($com['type']=='check'){
						if($com['first']) Generator::forms_print("<b>".$com['groupcaption']."</b></td><td><table cellpadding=0>");
						Generator::forms_print("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
						if($_REQUEST['fl_'.$name]==$com['checkedValue'])
						Generator::forms_print(" checked ");
						if($com["attributes"])
						foreach($com["attributes"] as $nitem => $item) Generator::forms_print(" $nitem='$item' ");
						Generator::forms_print(">\r\n</td><td>".$com['caption']."</td></tr>");
						if($com["last"]) Generator::forms_print("</table>");
					}

					//Cajita Grande
					if($com['type']=='textarea'){
						Generator::forms_print("<b>".$com['caption']." :</br></td><td><textarea disabled name='fl_$name' id='flid_$name' ");
						foreach($com['attributes'] as $natt => $vatt){
							Generator::forms_print("$natt='$vatt' ");
						}
						Generator::forms_print(">".$_REQUEST['fl_'.$name]."</textarea>");
					}

					//Oculto
					if($com['type']=='hidden')
					Generator::forms_print("<input type='hidden' name='fl_$name' id='flid_$name' value='".$com['value']."'/>\r\n");
					else {
						Generator::forms_print("</td>");
						if($com['type']=='check'){
							if($com['last']) {
								if(!($n%$form['fieldsPerRow'])) Generator::forms_print("</tr><tr>\r\n");
								$n++;
								Generator::forms_print("<td valign='top' align='right'>");
							}
						}
						else {
							if(!($n%$form['fieldsPerRow'])) Generator::forms_print("</tr><tr>\r\n");
							$n++;
							Generator::forms_print("<td valign='top' align='right'>");
						}
					}
				}
				Generator::forms_print("</tr></table></form>\r\n");

				if($form['useCompactStyle']){
					Generator::forms_print("</td></tr><tr>");
				}

				//Todos los campos
				Generator::forms_print("<script> var FieldsMaster = new Array(");
				reset($form['components']);
				for($i=0;$i<=count($form['components'])-1;$i++){
					Generator::forms_print("'".key($form['components'])."'");
					if($i!=(count($form['components'])-1)) Generator::forms_print(",");
					next($form['components']);
				}
				Generator::forms_print("); </script>\r\n");

				//Campos que no pueden ser nulos
				Generator::forms_print("<script> var NotNullFields = new Array(");
				reset($form['components']);
				$NotNullFields = "";
				for($i=0;$i<=count($form['components'])-1;$i++){
					if($form['components'][key($form['components'])]['notNull']||$form['components'][key($form['components'])]['primary'])
					$NotNullFields.="'".key($form['components'])."',";
					next($form['components']);
				}
				$NotNullFields = substr($NotNullFields, 0, strlen($NotNullFields)-1);
				Generator::forms_print("$NotNullFields); </script>\r\n");

				Generator::forms_print("<script> var DateFields = new Array(");
				$dFields = "";
				foreach($form['components'] as $key => $value){
					if($value['valueType']=='date')
					$dFields.="'".$key."',";
				}
				$dFields = substr($dFields, 0, strlen($dFields)-1);
				Generator::forms_print("$dFields); </script>\r\n");


				//Campos que no son llave
				Generator::forms_print("<script> var UFields = new Array(");
				$uFields = "";
				foreach($form['components'] as $key => $value){
					if(!$value['primary'])
					$uFields.="'".$key."',";
				}
				$uFields = substr($uFields, 0, strlen($uFields)-1);
				Generator::forms_print("$uFields); </script>\r\n");


				//Campos que son llave
				Generator::forms_print("<script> var PFields = new Array(");
				$pFields = "";
				foreach($form['components'] as $key => $value){
					if($value['primary'])
					$pFields.="'".$key."',";
				}
				$pFields = substr($pFields, 0, strlen($pFields)-1);
				Generator::forms_print("$pFields); </script>\r\n");


				//Campos que son Auto Numericos
				Generator::forms_print("<script> var AutoFields = new Array(");
				$aFields = "";
				foreach($form['components'] as $key => $value){
					if($value['auto_numeric'])
					$aFields.="'".$key."',";
				}
				$aFields = substr($aFields, 0, strlen($aFields)-1);
				Generator::forms_print("$aFields); </script>\r\n");


				Generator::forms_print("<script> var queryOnlyFields = new Array(");
				$rFields = "";
				foreach($form['components'] as $key => $value){
					if($value['valueType']!='date')
					if($value['queryOnly'])
					$rFields.="'".$key."',";
				}
				$rFields = substr($rFields, 0, strlen($rFields)-1);
				Generator::forms_print("$rFields); </script>\r\n");

				Generator::forms_print("<script> var queryOnlyDateFields = new Array(");
				$rdFields = "";
				foreach($form['components'] as $key => $value){
					if($value['valueType']=='date')
					if($value['queryOnly']){
						$rdFields.="'".$key."',";
					}
				}
				$rdFields = substr($rdFields, 0, strlen($rdFields)-1);
				Generator::forms_print("$rdFields); </script>\r\n");


				Generator::forms_print("<script> var AddFields = new Array(");
				$aFields = "";
				foreach($form['components'] as $key => $value){
					if((!$value['auto_numeric'])&&(!$value['attributes']['value']))
					$aFields.="'".$key."',";
				}
				$aFields = substr($aFields, 0, strlen($aFields)-1);
				Generator::forms_print("$aFields); </script>\r\n");

				//Campos E-Mail
				Generator::forms_print("<script> var emailFields = new Array(");
				$uFields = "";
				foreach($form['components'] as $key => $value){
					if($value['valueType']=='email')
					$uFields.="'".$key."',";
				}
				$uFields = substr($uFields, 0, strlen($uFields)-1);
				Generator::forms_print("$uFields); </script>\r\n");

				//??????????
				Generator::forms_print("<script> var comboQueryFields = new Array(");
				$uFields = "";
				foreach($form['components'] as $key => $value){
					if($value['whereConditionOnQuery'])
					$uFields.="'".$key."',";
				}
				$uFields = substr($uFields, 0, strlen($uFields)-1);
				Generator::forms_print("$uFields); </script>\r\n");

				//??????????
				Generator::forms_print("<script> var comboAddFields = new Array(");
				$uFields = "";
				foreach($form['components'] as $key => $value){
					if($value['whereConditionOnAdd'])
					$uFields.="'".$key."',";
				}
				$uFields = substr($uFields, 0, strlen($uFields)-1);
				Generator::forms_print("$uFields); </script>\r\n");

				Generator::forms_print("<script> var AutoValuesFields = new Array(");
				$aFields = "";
				foreach($form['components'] as $key => $value){
					if($value['auto_numeric'])
					$aFields.="'".$key."',";
				}
				$aFields = substr($aFields, 0, strlen($aFields)-1);
				Generator::forms_print("$aFields); </script>\r\n");

				Generator::forms_print("<script> var AutoValuesFFields = new Array(");
				$aFields = "";
				if(!$db) {
					$db = db::raw_connect();
					kumbia::$db = $db;
				}
				foreach($form['components'] as $key => $value){
					if($value['auto_numeric']){
						ActiveRecord::sql_item_sanizite($key);
						ActiveRecord::sql_item_sanizite($form['source']);
						$q = $db->query("select max($key)+1 from ".$form['source']);
						$row = $db->fetch_array($q);
						$aFields.="'".($row[0] ? $row[0] : 1 )."',";
					}
				}
				$aFields = substr($aFields, 0, strlen($aFields)-1);
				Generator::forms_print("$aFields); </script>\r\n");

				Generator::forms_print("<center>");
				if($form['useCompactStyle']){
					Generator::forms_print("<td align='center' id='tabForm2' style='visibility:hidden;position:absolute;left:".($form['left']).";top:".$form['top']."'>");
				}
				buildFormGrid($form['detail']);
				if($form['useCompactStyle']){
					Generator::forms_print("</td><tr><td align='center'>");
				}
				Generator::forms_print("<br><input type='button' class='controlButton' id='aceptar' value='Aceptar' disabled onclick='FormAcceptMD()'>&nbsp;");
				Generator::forms_print("<input type='button' class='controlButton' id='cancelar' value='Cancelar' disabled onclick='cancelFormMD()'>&nbsp;</center>");
				if($form['useCompactStyle']){
					Generator::forms_print("</td></tr></table>");
				}

				if($_REQUEST['subaction']=='validation')
				if($_REQUEST['oldsubaction']=='Adicionar')
				Generator::forms_print("<input type='hidden' id='actAction' value='".$_REQUEST['oldsubaction']."'><script>enableInsertMD(document.all.adiciona, 1); </script>");
				else Generator::forms_print("<input type='hidden' id='actAction' value='".$_REQUEST['oldsubaction']."'><script>enableUpdateMD(document.all.modifica); </script>");
				else
				Generator::forms_print("<input type='hidden' id='actAction' value=''>");
				Generator::forms_print("<input type='hidden' id='param' value='".$_REQUEST['param']."'>");
				Generator::forms_print("<input type='hidden' id='oldAction' value=''></form>
                <form name='saveDataForm' method='post'></form>");
				//print_r($form['components'][$_REQUEST['param']]);
				if($_REQUEST['subaction']=='validation'){
					if($form['components'][$_REQUEST['param']]['validation']['action']['type']=='javascript'){
						if($form['components'][$_REQUEST['param']]['validation']['function']($form['components'][$_REQUEST['param']]['validation']['action']['parameters']))
						Generator::forms_print("<script> documen.body.onload = ".$form['components'][$_REQUEST['param']]['validation']['action']['value']."</script>");
					} else {
						if($form['components'][$_REQUEST['param']]['validation']['action']['nextField']){
							$nextField = $form['components'][$_REQUEST['param']]['validation']['action']['nextField'];
							Generator::forms_print("<script> function retField() { document.all.fl_$nextField.select(); document.all.fl_$nextField.focus(); } documen.body.onload = retField; </script>");
						}
					}
				}
				if($_REQUEST['subaction']=='validation')
				Generator::forms_print("<script>function returnStatus() { document.all.flid_".$_REQUEST['param'].".select(); document.all.flid_".$_REQUEST['param'].".focus(); }</script>");
			} else {
				require_once "forms.functions.browse.php";
				formsBrowse($form);
			}
		}
	}

}

?>