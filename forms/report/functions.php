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


/******************************************************************************
* Generador de Reportes
*******************************************************************************/
class Report {

	function generate($form){

		$config = Config::read();

		$weightArray = array();
		$headerArray = array();
		$selectedFields = "";
		$tables = "";
		$whereCondition = "";
		$maxCondition = "";
		$n = 0;
		$db = db::raw_connect();

		if($form['dataFilter']){
			if(strpos($form['dataFilter'], '@')){
				ereg("[\@][A-Za-z0-9_]+", $form['dataFilter'], $regs);
				foreach($regs as $reg){
					$form['dataFilter'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['dataFilter']);
				}
			}
		}
		if($form['type']=='standard'){
			if($form['joinTables']) $tables = $form['joinTables'];
			if($form['joinConditions']) $whereCondition = " ".$form['joinConditions'];
			foreach($form['components'] as $name => $com){
				if($_REQUEST['fl_'.$name]==$com['attributes']['value']){
					$_REQUEST['fl_'.$name] = "";
				}
				if(trim($_REQUEST["fl_".$name])&&$_REQUEST["fl_".$name]!='@'){
					if($form['components'][$name]['valueType']=='date'){
						$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
					} else {
						if($form['components'][$name]['valueType']=='numeric'){
							$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
						} else {
							if($form['components'][$name]['type']=='hidden'){
								$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
							} else {
								if($com['type']=='check'){
									if($_REQUEST["fl_".$name]==$form['components'][$name]['checkedValue'])
									$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
								} else {
									if($com['type']=='time'){
										if($_REQUEST["fl_".$name]!='00:00'){
											$whereCondition.=" and {$form['source']}.$name = '".$_REQUEST["fl_".$name]."'";
										}
									} else {
										if($com['primary']||$com['type']=='combo'){
											$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
										} else {
											$whereCondition.=" and ".$form['source'].".$name like '%".$_REQUEST["fl_".$name]."%'";
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		//Modificaciones para seleccion de la ordenacion del report, si esta acabado en _id, quiere decir foreignkey
		//Cojeremos el texto sin el id, tendremos la tabla 
		ActiveRecord::sql_item_sanizite($_REQUEST['reportTypeField']);
		if (substr($_REQUEST['reportTypeField'],strlen($_REQUEST['reportTypeField']) -3,strlen($_REQUEST['reportTypeField'])) == "_id"){
			$OrderFields = substr($_REQUEST['reportTypeField'],0,strlen($_REQUEST['reportTypeField'])-3);
		}else{
			$OrderFields =$_REQUEST['reportTypeField'];
		} 
		$maxCondition = $whereCondition;
		$n = 0;
		foreach($form['components'] as $name => $com){
			if(!$com['notReport']){
				$headerArray[$n] = str_replace("&oacute;", "ó", $com['caption']);
				$headerArray[$n] = str_replace("&aacute;", "á", $headerArray[$n]);
				$headerArray[$n] = str_replace("&eacute;", "é", $headerArray[$n]);
				$headerArray[$n] = str_replace("&iacute;", "í", $headerArray[$n]);
				$headerArray[$n] = str_replace("&uacute;", "ú", $headerArray[$n]);
				$headerArray[$n] = str_replace("<br/>", " ", $headerArray[$n]);
				if($com['type']=='combo'&&$com['class']=='dynamic'){
					if($com['extraTables']){
						$tables.="{$com['extraTables']},";
					}
					if($com['whereConditionOnQuery']){
						$whereCondition.=" and {$com['whereConditionOnQuery']}";
					}
					if(strpos(" ".$com['detailField'], "concat(")){
						$selectedFields.=$com['detailField'].",";
					} else {
						$selectedFields.=$com['foreignTable'].".".$com['detailField'].",";
						//Comparamos la Tabla foranea que tenemos, y cuando sea igual, suponiendo no hay
						//mas de una clave foranea por tabla, sabremos a que tabla pertenece
						if ($com['foreignTable'] == $OrderFields){
							$OrderFields = $com['foreignTable'].".".$com['detailField'];
						}	 
					}
					$tables.=$com['foreignTable'].",";
					if($com['column_relation']){
						$whereCondition.=" and ".$com['foreignTable'].".".$com['column_relation']." = ".$form['source'].".".$name;
					} else {
						$whereCondition.=" and ".$com['foreignTable'].".".$name." = ".$form['source'].".".$name;
					}
					$weightArray[$n] = strlen($headerArray[$n])+2;
					$n++;
				} else {
					if($com['type']!='hidden'){
						if($com['class']=='static'){
							$weightArray[$n] = strlen($headerArray[$n])+2;
							if($config->database->type=='postgresql'){
								$selectedFields.="case ";
							}
							if($config->database->type=='mysql'){
								for($i=0;$i<=count($com['items'])-2;$i++){
									$selectedFields.="if(".$form['source'].".".$name."='".$com['items'][$i][0]."', '".$com['items'][$i][1]."', ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}

							if($config->database->type=='postgresql'){
								for($i=0;$i<=count($com['items'])-1;$i++){
									$selectedFields.=" when ".$form['source'].".".$name."='".$com['items'][$i][0]."' THEN '".$com['items'][$i][1]."' ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}


							$n++;
							if($config->database->type=='mysql'){
								$selectedFields.="'".$com['items'][$i][1]."')";
								for($j=0;$j<=$i-2;$j++) {
									$selectedFields.=")";
								}
							}
							if($config->database->type=='postgresql'){
								$selectedFields.=" end ";
							}
							$selectedFields.=",";
						} else {
							$selectedFields.=$form['source'].".".$name.",";
							//Aqui seguro que no es foranea, entonces tenemos que poner la tabla principal 							//
							//antes para evitar repeticiones
							if ($name == $OrderFields){
								$OrderFields = $form['source'].".".$OrderFields;
							}	 
							$weightArray[$n] = strlen($headerArray[$n])+2;
							$n++;
						}
					}
				}
			}
		}
		$tables.=$form['source'];
		$selectedFields = substr($selectedFields, 0, strlen($selectedFields)-1);

		if($form['dataRequisite']){
			$whereCondition.=" and {$form['dataFilter']}";
		}

		//Modificacion del order
		$OrderCondition = "Order By ".$OrderFields;

		$query = "select $selectedFields from $tables where 1 = 1 ".$whereCondition. " " .$OrderCondition;
		
		$q = $db->query($query);		
		if(!is_bool($q)){
			if(!$db->num_rows($q)){
				Flash::notice("No hay informaci&oacute;n para listar");
				return;
			}
		} else {
			Flash::error($db->error());
			return;
		}

		$result = array();
		$n = 0;
		while($row = $db->fetch_array($q, db::DB_NUM)){
			$result[$n++] = $row;
		}

		foreach($result as $row){
			for($i=0;$i<=count($row)-1;$i++){
				if($weightArray[$i]<strlen(trim($row[$i]))){
					$weightArray[$i] = strlen(trim($row[$i]));
				}
			}
		}

		for($i=0;$i<=count($weightArray)-1;$i++){
			$weightArray[$i]*= 1.8;
		}

		$sumArray = array_sum($weightArray);

		if(!$_REQUEST['reportType']){
			$_REQUEST['reportType'] = 'pdf';
		}

		if($format!='html'){
			$title = str_replace("&oacute;", "ó", $form['caption']);
			$title = str_replace("&aacute;", "á", $title);
			$title = str_replace("&eacute;", "é", $title);
			$title = str_replace("&iacute;", "í", $title);
			$title = str_replace("&uacute;", "ú", $title);
		} else {
			$title = $form['caption'];
		}

		switch($_REQUEST['reportType']){
			case 'pdf':
			require_once "forms/report/format/pdf.php";
			break;
			case 'xls':
			require_once "forms/report/format/xls.php";
			break;
			case 'html':
			require_once "forms/report/format/htm.php";
			break;
			case 'doc':
			require_once "forms/report/format/doc.php";
			break;
			default:
			require_once "forms/report/format/pdf.php";
			break;
		}

	}
}

?>
