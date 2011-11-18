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
*****************************************************************************/


function buildInsertOnly($form){
	formsPrint("<table align='center'><tr>\r\n");
	$n = 1;
	//La parte de los Componentes
	formsPrint("<td align='right' valign='top'>\r\n");
	foreach($form['components'] as $name => $com){

		switch($com['type']){
			//Si es de Tipo TExt
			case 'text':
			buildMasterTextComponent($com, $name, $form);
			break;

			case 'userDefined':
			buildMasterUserDefinedComponent($com, $name, $form);
			break;

			case 'helpText':
			buildHelpContext($com, $name, $form);
			break;

			//Tipo password
			case 'password':
			buildStandardPassword($com, $name);
			break;

			//Cuando es de Tipo Combo
			case 'combo':
			buildStandardCombo($com, $name);
			break;

			//Este es el Check Chulito
			case 'check':
			if($com['first']) formsPrint("<b>".$com['groupcaption']."</b></td><td><table cellpadding=0>");
			formsPrint("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
			if($_REQUEST['fl_'.$name]==$com['checkedValue'])
			formsPrint(" checked ");
			if($com["attributes"])
			foreach($com["attributes"] as $nitem => $item) formsPrint(" $nitem='$item' ");
			formsPrint(">\r\n</td><td>".$com['caption']."</td></tr>");
			if($com["last"]) formsPrint("</table>");
			break;

			//Cajita Grande
			case 'textarea':
			formsPrint("<b>".$com['caption']." :</br></td><td><textarea disabled name='fl_$name' id='flid_$name' ");
			foreach($com['attributes'] as $natt => $vatt){
				formsPrint("$natt='$vatt' ");
			}
			formsPrint(">".$_REQUEST['fl_'.$name]."</textarea>");
			break;

			//Oculto
			case 'hidden':
			formsPrint("<input type='hidden' name='fl_$name' id='flid_$name' value='".$com['value']."'/>\r\n");
			break;

		}
		formsPrint("</tr><tr><td align='right'>");
	}
	formsPrint("</table>");
	formsPrint("<br><center><input type='button' class='controlButton' id='aceptar' value='Aceptar' onclick='FormAccept()'>&nbsp;</center>");
	//Todos los campos
	formsPrint("<script> var Fields = new Array(");
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
	$db = db::raw_connect();
	foreach($form['components'] as $key => $value){
		if($value['auto_numeric']){
			$q = $db->query("select max($key)+1 from ".$form['source']);
			$row = $db->fetch_array($q);
			$aFields.="'".($row[0] ? $row[0] : 1 )."',";
		}
	}	
	$aFields = substr($aFields, 0, strlen($aFields)-1);
	formsPrint("$aFields); </script>\r\n");
	if($_REQUEST['subaction']=='validation'){
		if($_REQUEST['param']){
			formsPrint("<script>function returnStatus() { document.all.flid_".$_REQUEST['param'].".select(); document.all.flid_".$_REQUEST['param'].".focus(); }</script>");
		}
	}
	formsPrint("<input type='hidden' id='actAction' value=''>");
	formsPrint("<input type='hidden' id='param' value='".$_REQUEST['param']."'>");
	formsPrint("<input type='hidden' id='oldAction' value=''></form>
   <form name='saveDataForm' method='post'></form>");
	formsPrint("<script>enableInsert('Adicionar', 1)</script>");
	
}
?>
