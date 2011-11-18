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

function buildFormMasterDetail($form){

	if(!array_key_exists('dataRequisite', $form)) $form['dataRequisite'] = 1;
	if(!$form['dataRequisite']) {
		formsPrint("<font style='font-size:11px'><center><i><b>No hay datos en consulta</b></i></center></font>");
	} else {
	  if($_REQUEST['subaction']!='browse'){
		formsPrint("<center>");
		if(!array_key_exists('permissions', $form)) {
			$form['permissions'] = array();
		}
		if($_REQUEST['oldsubaction']=='Modificar') {
			$_REQUEST['queryStatus'] = true;
		}
		
		if($form['controlButtons']){
			foreach($form['controlButtons'] as $name => $cButton){
				formsPrint("<input type='button' class='controlButton' id='$name' value='".$cButton['caption']."' onclick='".$cButton['action']."'>&nbsp;");	
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
					formsPrint("<input type='button' class='controlButton' id='adiciona' value='Adicionar' onclick='enableInsertMD(this)'>&nbsp;");
				} else formsPrint("<input type='button' class='controlButton' id='adiciona' value='Adicionar' onclick='enableInsertMD(this)'>&nbsp;");
			}
			if(array_key_exists('query', $form['permissions'])){
				if(getPermission($form, 'query'))
				formsPrint("<input type='button' class='controlButton' id='consulta' value='Consultar' onclick='enableQueryMD(this)'>&nbsp;");
			} else formsPrint("<input type='button' class='controlButton' id='consulta' value='Consultar' onclick='enableQueryMD(this)'>&nbsp;");
			$ds = "disabled";
		} else {
			$query_string = "action=".$_REQUEST['action']."&value=".$_REQUEST['value']."&option=".$_REQUEST['option'];
			formsPrint("<button id='primero' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=0\"'>&nbsp;Primero</button>&nbsp;");
			formsPrint("<button id='anterior' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=".($_REQUEST['number']-1)."\"'>&nbsp;&nbsp;Anterior</button>&nbsp;");
			formsPrint("<button id='siguiente' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=".($_REQUEST['number']+1)."\"'>Siguiente&nbsp;</button>&nbsp;");
			formsPrint("<button id='ultimo' class='controlButton' onclick='javascript:window.location=\"?$query_string&queryStatus=1&subaction=fetch&number=last\"'>Ultimo&nbsp;&nbsp;</button>&nbsp;");
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
								formsPrint("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
						} else formsPrint("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");	
					}
				} else {				    	
					if(array_key_exists('update', $form['permissions'])){
						if(getPermission($form, 'update'))
							formsPrint("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
					} else formsPrint("<input type='button' class='controlButton' id='modifica' value='Modificar' $ds onclick=\"enableUpdateMD(this)\">&nbsp;");
				}
			}
		}		
				
		//El de Borrar
		if($_REQUEST['queryStatus']){
			if(!$form['unableDelete']){
				if(array_key_exists('delete', $form['permissions'])){
					if(getPermission($form, 'delete'))
					formsPrint("<input type='button' class='controlButton' id='borra' value='Borrar' $ds onclick=\"enableDeleteMD()\">&nbsp;");
				} else formsPrint("<input type='button' class='controlButton' id='borra' value='Borrar' $ds onclick=\"enableDeleteMD()\">&nbsp;");
			}
		}
		
		if(!$_REQUEST['queryStatus']) {
			if(array_key_exists('browse', $form['permissions'])){
				if(getPermission($form, 'browse'))
					formsPrint("<input type='button' class='controlButton' id='visualiza' value='Visualizar' onclick='enableBrowse(this)'>&nbsp;");
			} else formsPrint("<input type='button' class='controlButton' id='visualiza' value='Visualizar' onclick='enableBrowse(this)'>&nbsp;");
		}

		//Boton de Reporte
		if(!$_REQUEST['queryStatus']) {
			if(array_key_exists('report', $form['permissions'])){
				if(getPermission($form, 'report'))
				formsPrint("<input type='button' class='controlButton' id='reporte' value='Reporte' onclick='enableReportMD(this)'>&nbsp;");
			} else formsPrint("<input type='button' class='controlButton' id='reporte' value='Reporte' onclick='enableReportMD(this)'>&nbsp;");			
		} else formsPrint("<br><br><button class='controlButton' id='volver' onclick='javascript:window.location=\"?$query_string&subaction=back\"'>Atras&nbsp;&nbsp;</button>&nbsp;");


		formsPrint("\r\n</center><br/><br/>");

		if($form['useCompactStyle']){
			formsPrint("<table align='center' style='position:xabsolute;left:".$form['left'].";top:".$form['top']."' cellspacing=0>
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

		formsPrint("<table align='center'><tr>");
		$n = 1;


		//La parte de los Componentes
		formsPrint("<td valign='top' align='right'>");
		foreach($form['components'] as $name => $com){

			//SI esa de Tipo TExt
			if($com['type']=='text') buildMasterTextComponent($com, $name, $form);
			if($com['type']=='helpText') buildHelpContext($com, $name, $form);

			//Tipo password
			if($com['type']=='password'){
				formsPrint("<b>".$com['caption']." : </b></td><td id='tp$i' valign='top'><input type='password' name='fl_$name' id='flid_$name' disabled ");
				if($_REQUEST['fl_'.$name])
				formsPrint("value = '".$_REQUEST['fl_'.$name]."'");
				foreach($com["attributes"] as $nitem => $item)
				formsPrint(" $nitem='$item' ");
				formsPrint(" onkeydown='capturePassword(tp$i, flid_$name)' onblur='nextRetype()'");
				formsPrint("/>\r\n");
			}

			//Cuando es de Tipo Combo
			if($com['type']=='combo') buildStandardCombo($com, $name);

			//Este es el Check Chulito
			if($com['type']=='check'){
				if($com['first']) formsPrint("<b>".$com['groupcaption']."</b></td><td><table cellpadding=0>");
				formsPrint("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
				if($_REQUEST['fl_'.$name]==$com['checkedValue'])
				formsPrint(" checked ");
				if($com["attributes"])
				foreach($com["attributes"] as $nitem => $item) formsPrint(" $nitem='$item' ");
				formsPrint(">\r\n</td><td>".$com['caption']."</td></tr>");
				if($com["last"]) formsPrint("</table>");
			}

			//Cajita Grande
			if($com['type']=='textarea'){
				formsPrint("<b>".$com['caption']." :</br></td><td><textarea disabled name='fl_$name' id='flid_$name' ");
				foreach($com['attributes'] as $natt => $vatt){
					formsPrint("$natt='$vatt' ");
				}
				formsPrint(">".$_REQUEST['fl_'.$name]."</textarea>");
			}

			//Oculto
			if($com['type']=='hidden')
			formsPrint("<input type='hidden' name='fl_$name' id='flid_$name' value='".$com['value']."'/>\r\n");
			else {
				formsPrint("</td>");
				if($com['type']=='check'){
					if($com['last']) {
						if(!($n%$form['fieldsPerRow'])) formsPrint("</tr><tr>\r\n");
						$n++;
						formsPrint("<td valign='top' align='right'>");
					}
				}
				else {
					if(!($n%$form['fieldsPerRow'])) formsPrint("</tr><tr>\r\n");
					$n++;
					formsPrint("<td valign='top' align='right'>");
				}
			}
		}
		formsPrint("</tr></table></form>\r\n");

		if($form['useCompactStyle']){
			formsPrint("</td></tr><tr>");
		}

		//Todos los campos
		formsPrint("<script> var FieldsMaster = new Array(");
		reset($form['components']);
		for($i=0;$i<=count($form['components'])-1;$i++){
			formsPrint("'".key($form['components'])."'");
			if($i!=(count($form['components'])-1)) formsPrint(",");
			next($form['components']);
		}
		formsPrint("); </script>\r\n");

		//Campos que no pueden ser nulos
		formsPrint("<script> var NotNullFields = new Array(");
		reset($form['components']);
		$NotNullFields = "";
		for($i=0;$i<=count($form['components'])-1;$i++){
			if($form['components'][key($form['components'])]['notNull']||$form['components'][key($form['components'])]['primary'])
			$NotNullFields.="'".key($form['components'])."',";
			next($form['components']);
		}
		$NotNullFields = substr($NotNullFields, 0, strlen($NotNullFields)-1);
		formsPrint("$NotNullFields); </script>\r\n");

		formsPrint("<script> var DateFields = new Array(");
		$dFields = "";
		foreach($form['components'] as $key => $value){
			if($value['valueType']=='date')
			$dFields.="'".$key."',";
		}
		$dFields = substr($dFields, 0, strlen($dFields)-1);
		formsPrint("$dFields); </script>\r\n");


		//Campos que no son llave
		formsPrint("<script> var UFields = new Array(");
		$uFields = "";
		foreach($form['components'] as $key => $value){
			if(!$value['primary'])
			$uFields.="'".$key."',";
		}
		$uFields = substr($uFields, 0, strlen($uFields)-1);
		formsPrint("$uFields); </script>\r\n");


		//Campos que son llave
		formsPrint("<script> var PFields = new Array(");
		$pFields = "";
		foreach($form['components'] as $key => $value){
			if($value['primary'])
			$pFields.="'".$key."',";
		}
		$pFields = substr($pFields, 0, strlen($pFields)-1);
		formsPrint("$pFields); </script>\r\n");


		//Campos que son Auto Numericos
		formsPrint("<script> var AutoFields = new Array(");
		$aFields = "";
		foreach($form['components'] as $key => $value){
			if($value['auto_numeric'])
			$aFields.="'".$key."',";
		}
		$aFields = substr($aFields, 0, strlen($aFields)-1);
		formsPrint("$aFields); </script>\r\n");


		formsPrint("<script> var queryOnlyFields = new Array(");
		$rFields = "";
		foreach($form['components'] as $key => $value){
			if($value['valueType']!='date')
			if($value['queryOnly'])
			$rFields.="'".$key."',";
		}
		$rFields = substr($rFields, 0, strlen($rFields)-1);
		formsPrint("$rFields); </script>\r\n");

		formsPrint("<script> var queryOnlyDateFields = new Array(");
		$rdFields = "";
		foreach($form['components'] as $key => $value){
			if($value['valueType']=='date')
			if($value['queryOnly']){
				$rdFields.="'".$key."',";
			}
		}
		$rdFields = substr($rdFields, 0, strlen($rdFields)-1);
		formsPrint("$rdFields); </script>\r\n");


		formsPrint("<script> var AddFields = new Array(");
		$aFields = "";
		foreach($form['components'] as $key => $value){
			if((!$value['auto_numeric'])&&(!$value['attributes']['value']))
			$aFields.="'".$key."',";
		}
		$aFields = substr($aFields, 0, strlen($aFields)-1);
		formsPrint("$aFields); </script>\r\n");
		
		//Campos E-Mail
		formsPrint("<script> var emailFields = new Array(");
		$uFields = "";
		foreach($form['components'] as $key => $value){
			if($value['valueType']=='email')
				$uFields.="'".$key."',";
		}
		$uFields = substr($uFields, 0, strlen($uFields)-1);
		formsPrint("$uFields); </script>\r\n");

		//??????????
		formsPrint("<script> var comboQueryFields = new Array(");
		$uFields = "";
		foreach($form['components'] as $key => $value){
			if($value['whereConditionOnQuery'])
				$uFields.="'".$key."',";
		}
		$uFields = substr($uFields, 0, strlen($uFields)-1);
		formsPrint("$uFields); </script>\r\n");

		//??????????
		formsPrint("<script> var comboAddFields = new Array(");
		$uFields = "";
		foreach($form['components'] as $key => $value){
			if($value['whereConditionOnAdd'])
				$uFields.="'".$key."',";
		}
		$uFields = substr($uFields, 0, strlen($uFields)-1);
		formsPrint("$uFields); </script>\r\n");		

		formsPrint("<script> var AutoValuesFields = new Array(");
		$aFields = "";
		foreach($form['components'] as $key => $value){
			if($value['auto_numeric'])
			$aFields.="'".$key."',";
		}
		$aFields = substr($aFields, 0, strlen($aFields)-1);
		formsPrint("$aFields); </script>\r\n");

		formsPrint("<script> var AutoValuesFFields = new Array(");
		$aFields = "";
		if(!$db) {
		  	$db = db::raw_connect();
		  	kumbia::$db = $db;
		}
		foreach($form['components'] as $key => $value){
			if($value['auto_numeric']){
				$q = $db->query("select max($key)+1 from ".$form['source']);
				$row = $db->fetch_array($q);
				$aFields.="'".($row[0] ? $row[0] : 1 )."',";
			}
		}		
		$aFields = substr($aFields, 0, strlen($aFields)-1);
		formsPrint("$aFields); </script>\r\n");

		formsPrint("<center>");
		if($form['useCompactStyle']){
			formsPrint("<td align='center' id='tabForm2' style='visibility:hidden;position:absolute;left:".($form['left']).";top:".$form['top']."'>");
		}
		buildFormGrid($form['detail']);
		if($form['useCompactStyle']){
			formsPrint("</td><tr><td align='center'>");
		}
		formsPrint("<br><input type='button' class='controlButton' id='aceptar' value='Aceptar' disabled onclick='FormAcceptMD()'>&nbsp;");
		formsPrint("<input type='button' class='controlButton' id='cancelar' value='Cancelar' disabled onclick='cancelFormMD()'>&nbsp;</center>");
		if($form['useCompactStyle']){
			formsPrint("</td></tr></table>");
		}

		if($_REQUEST['subaction']=='validation')
		if($_REQUEST['oldsubaction']=='Adicionar')
		formsPrint("<input type='hidden' id='actAction' value='".$_REQUEST['oldsubaction']."'><script>enableInsertMD(document.all.adiciona, 1); </script>");
		else formsPrint("<input type='hidden' id='actAction' value='".$_REQUEST['oldsubaction']."'><script>enableUpdateMD(document.all.modifica); </script>");
		else
		formsPrint("<input type='hidden' id='actAction' value=''>");
		formsPrint("<input type='hidden' id='param' value='".$_REQUEST['param']."'>");
		formsPrint("<input type='hidden' id='oldAction' value=''></form>
                <form name='saveDataForm' method='post'></form>");
		//print_r($form['components'][$_REQUEST['param']]);
		if($_REQUEST['subaction']=='validation'){
			if($form['components'][$_REQUEST['param']]['validation']['action']['type']=='javascript'){
				if($form['components'][$_REQUEST['param']]['validation']['function']($form['components'][$_REQUEST['param']]['validation']['action']['parameters']))
				formsPrint("<script> iBody.onload = ".$form['components'][$_REQUEST['param']]['validation']['action']['value']."</script>");
			} else {
				if($form['components'][$_REQUEST['param']]['validation']['action']['nextField']){
					$nextField = $form['components'][$_REQUEST['param']]['validation']['action']['nextField'];
					formsPrint("<script> function retField() { document.all.fl_$nextField.select(); document.all.fl_$nextField.focus(); } iBody.onload = retField; </script>");
				}
			}
		}
		if($_REQUEST['subaction']=='validation')
	formsPrint("<script>function returnStatus() { document.all.flid_".$_REQUEST['param'].".select(); document.all.flid_".$_REQUEST['param'].".focus(); }</script>");
		} else {
    			formsBrowse($form);
    		}
	}
}

?>