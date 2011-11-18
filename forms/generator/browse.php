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
* Browse Functions for Standard and Master-Detail forms
*****************************************************************************/

function writeLocation(){
	$ret = KUMBIA_PATH.$_REQUEST['controller']."/";
	$first = true;
	foreach($_GET as $name => $getVar){
		if($name!='url'&&$name!='controller'&&$name!='action'&&$name!='typeOrd'&&$name!='orderBy'&&$name!='limBrowse'&&$name!='numBrowse'&&substr($name, 0, 1)!='/'){
			if($first){
				$ret.="$name=$getVar";
				$first = false;
			} else {
				$ret.="&amp;$name=$getVar";
			}
		}
	}	
	return $ret;
}

function doBrowseLocation($field, $source){
	$ret = "";
	$first = true;
	$oBy = false;
	foreach($_GET as $name => $getVar){
		if($name!='orderBy'&&$name!='url'&&$name!='controller'&&$name!='action'){
			if($first){
				$ret.="$name=$getVar";
				$first = false;
			} else {
				$ret.="&amp;$name=$getVar";
			}
		}
	}
	$ret.="&amp;orderBy=$source.$field";	
	return $ret;
}

function doTypeBrowseLocation($field, $source){
  
	$ret = "";
	$first = true;
	$oBy = false;
	$tOr = false;
	foreach($_GET as $name => $getVar){		
	  	if($name!='orderBy'&&$name!='typeOrd'&&$name!='url'&&$name!='controller'&&$name!='action')
		if($first){
			$ret.="$name=$getVar";
			$first = false;
		} else {
			$ret.="&amp;$name=$getVar";
		}
	}
	$ret.="&amp;orderBy=$source.$field";		
	if($_GET['typeOrd']=='desc'){
		$ret.="&amp;typeOrd=asc";
		$img = "f_up";
	} else {
		$ret.="&amp;typeOrd=desc";
		$img = "f_down";  
	}
	return "<a href='$ret'><img src='".KUMBIA_PATH."img/$img.gif' style='border:none; margin: 0 0 0 0;' alt=''></a>";
}

function formsBrowse($form){
	
	$config = Config::read();
	
	Generator::forms_print("&nbsp;</center><br><table cellspacing=0 align='center' cellpadding=5>
	<tr bgcolor='#D0D0D0' style='border-top:1px solid #FFFFFF'>");

	$browseSelect = "select ";
	$browseFrom = " from ".$form['source'];
	$browseWhere = " Where 1 = 1";
	$broseLike = "";
	$source = $form['source'];
	$nalias = 1;
	foreach($form['components'] as $name => $component){
		if(($component['type']!='hidden')&&(!$component['notBrowse'])){
			if($component['browseCaption']){
				Generator::forms_print("<td align='center' valign='bottom' class='browseHead'>
                <table><tr><td align='center'><a href='".doBrowseLocation($name, $form['source'])."'>".$component['browseCaption']."</a></td>
                <td>".doTypeBrowseLocation($name, $form['source'])."</td></tr></table></td>\r\n");
			} else {
				Generator::forms_print("<td align='center' valign='bottom'>
                <table><tr><td align='center'><a href='".doBrowseLocation($name, $form['source'])."'>".$component['caption']."</a></td>
                <td>".doTypeBrowseLocation($name, $form['source'])."</td></tr></table></td>\r\n");
			}
		}
		if(($component['type']=='combo')&&($component['class']=='dynamic')){
		  if(!$component['notPrepare']){
			if($first) {
				$browseSelect.=",";
			} else $first = true;
			
			if(strpos(" ".$browseFrom, $component['foreignTable'])){
			  	$alias = "t".$nalias;
			  	$nalias++;
				$browseFrom.=",".$component['foreignTable']." ".$alias;				
			} else {
			  	$browseFrom.=",".$component['foreignTable'];				
			  	$alias = "";
			}			
			if(strpos($component['detailField'], "(")){			  	
				$browseSelect.=$component['detailField']." as $name, $source.$name as pk_$name";				
				$browseLike.=" or {$component['detailField']} like '%{$_GET['q']}%'";
			} else {
			  	if($alias)
				    $browseLike.=" or $alias.{$component['detailField']} like '%{$_GET['q']}%'"
				;
				else
					$browseLike.=" or {$component['foreignTable']}.{$component['detailField']} like '%{$_GET['q']}%'"
				;	
			    if(!$alias)
					$browseSelect.=$component['foreignTable'].".".$component['detailField']." as $name, $source.$name as pk_$name";
				else
					$browseSelect.=$alias.".".$component['detailField']." as $name, $source.$name as pk_$name";	
			}
			if($component["extraTables"]){
				$browseFrom.=",".$component["extraTables"];
			}
			if($component['column_relation']){
			  	if($alias){
					$browseWhere.=" and ".$alias.".".$component['column_relation']." = ".$form['source'].".".$name;
				} else {
					$browseWhere.=" and ".$component['foreignTable'].".".$component['column_relation']." = ".$form['source'].".".$name;  				  
				}
			} else {
				$browseWhere.=" and ".$component['foreignTable'].".".$name." = ".$form['source'].".".$name;
			}
			if($component['whereCondition'])
				$browseWhere.=" and ".$component['whereCondition'];
		  }	
		} else {
			if(($component['class']=='static')&&($component['type']=='combo')){
				if($first) {
					$browseSelect.=",";
				} else {
					$first = true;
				}
				$weightArray[$n] = strlen($headerArray[$n])+2;
				if($config->database->type=='postgresql'){
					$browseSelect.="case ";
				}
				if($config->database->type=='mysql'){
					for($i=0;$i<=count($component['items'])-2;$i++){					
						$browseSelect.="if(".$form['source'].".".$name."='".$component['items'][$i][0]."', '".$component['items'][$i][1]."', ";
					} 
				}
				if($config->database->type=='postgresql'){
					for($i=0;$i<=count($component['items'])-1;$i++){
						$browseSelect.=" when ".$form['source'].".".$name."='".$component['items'][$i][0]."' THEN '".$component['items'][$i][1]."' ";
					}
				}
				$n++;
				if($config->database->type=='mysql'){
					$browseSelect.="'".$component['items'][$i][1]."')";				
					for($j=0;$j<=$i-2;$j++) { 
						$browseSelect.=")";
					}
				} 
				if($config->database->type=='postgresql'){
					$browseSelect.=" end ";
				}
				$browseSelect.=" as $name";
				$_GET['q'] = addslashes($_GET['q']);
				$browseLike.=" or $name like '%{$_GET['q']}%'";
			} else {
				if($component['type']!='hidden'){
					if($first) {
						$browseSelect.=",";
					} else $first = true;
					$browseSelect.=$form['source'].".$name";
					$_GET['q'] = addslashes($_GET['q']);
					$browseLike.=" or {$form['source']}.$name like '%{$_GET['q']}%' ";
				}
			}
		}
	}	
	$brw = $browseWhere;
	if($_REQUEST['q']){
	 	$browseWhere.=" and (1<>1 $browseLike)";
	}	
	if(!isset($_REQUEST['typeOrd'])) $_REQUEST['typeOrd'] = "asc";	
	if(!isset($_REQUEST['orderBy'])){
		ActiveRecord::sql_item_sanizite($_REQUEST['typeOrd']);
		$browseSelect.= $browseFrom.$browseWhere." Order By 1 ".$_REQUEST['typeOrd'];
	} else {
		ActiveRecord::sql_item_sanizite($_REQUEST['typeOrd']);
		ActiveRecord::sql_sanizite($_REQUEST['orderBy']);
		$browseSelect.= $browseFrom.$browseWhere." Order By ".$_REQUEST['orderBy']." ".$_REQUEST['typeOrd'];
	}
	
	if(!isset($_REQUEST['limBrowse'])){
	 	$_REQUEST['limBrowse'] = 0;
	} else {
		$_REQUEST['limBrowse'] = intval($_REQUEST['limBrowse']);
	}
	
	if(!isset($_REQUEST['numBrowse'])){
	 	$_REQUEST['numBrowse'] = 10;
	} else {
	 	$_REQUEST['numBrowse'] = intval($_REQUEST['numBrowse']);
	}
	
	if(isset($_REQUEST['limBrowse'])&&isset($_REQUEST['numBrowse'])){
		if($config->database->type=='mysql'){			
			$browseSelect.=" limit {$_REQUEST['limBrowse']},{$_REQUEST['numBrowse']}";	
		} 
		if($config->database->type=='postgresql'){			
			$browseSelect.=" offset {$_REQUEST['limBrowse']} limit {$_REQUEST['numBrowse']}";	
		}
	}
	
	if($db = db::raw_connect()){			
		$q = $db->query($browseSelect);
		if($q===false) {
			Flash::error($db->error());
			return;
		}
		$color1 = "browse_primary";
		$hoverColor1 = "browse_primary_active";
		$color2 = "browse_secondary"; 
		$hoverColor2 = "browse_secondary_active"; 
			
		$color = $color1;
		$hoverColor = $hoverColor1;
				
		if($db->num_rows($q)){
			$nTr = 0;
			$queryBrowse = "select count(*) $browseFrom $brw";
			$qq = $db->query($queryBrowse);
			$num = $db->fetch_array($qq);
			$num = $num[0];
			while($row = $db->fetch_array($q)){
				Generator::forms_print("</tr>\r\n<tr id='nTr$nTr' class='$color'>");
				foreach ($form['components'] as $name => $component) {
					if(($component['type']!='hidden')&&(!$component['notBrowse'])){
						if($component['format']=='money'){
							$row[$name] = "\$&nbsp;".number_format($row[$name], 0, '.', ',');
						}
						if($component['type']!='image'){
							Generator::forms_print("<td align='center' style='border-left:1px solid #D1D1D1'
                            onmouseover='$(\"nTr$nTr\").className=\"$hoverColor\"'
                            onmouseout='$(\"nTr$nTr\").className=\"$color\"'
                            >".$row[$name]."</td>");
                        } else {
						    Generator::forms_print("<td align='center' style='border-left:1px solid #D1D1D1'
                            onmouseover='$(\"nTr$nTr\").className=\"$hoverColor\"'
                            onmouseout='$(\"nTr$nTr\").className=\"$color\"'
                            ><img src='".KUMBIA_PATH."img/".urldecode($row[$name])."' style='border:1px solid black;width:128;height:128' alt=''></td>");						  	
						}
					}
				}
				$nTr++;
				$pk=doPrimaryKey($form, $row);
				if(!$form['unableUpdate']){
					Generator::forms_print("<td style='border-left:1px solid #D1D1D1'><img src='".KUMBIA_PATH."img/edit.gif' title='Editar este Registro' style='cursor:pointer' onclick='window.location=\"".KUMBIA_PATH."{$_REQUEST["controller"]}/query/&amp;$pk\"' alt=''/></td>");
				}
				if(!$form['unableDelete']){
					Generator::forms_print("<td style='border-left:1px solid #D1D1D1'><img src='".KUMBIA_PATH."img/delete.gif' title='Borrar este Registro' style='cursor:pointer' onclick='if(confirm(\"Seguro desea borrar este Registro?\")) window.location=\"".KUMBIA_PATH."{$_REQUEST["controller"]}/delete/&amp;$pk\"' alt=''/></td>");
				}
				if($color==$color2) $color = $color1; else $color = $color2;
				if($hoverColor==$hoverColor2) $hoverColor = $hoverColor1; else $hoverColor = $hoverColor2;
			}			
			$m = $_REQUEST['limBrowse'] ? $_REQUEST['limBrowse']: 1;
			if($_GET['orderBy']) $oBy = "&amp;orderBy=".$_GET['orderBy'];
			Generator::forms_print("</tr><tr><td bgcolor='#D0D0D0'
			 style='border-left:1px solid #D1D1D1;border-right:1px solid #D1D1D1' align='center'>
			<table>
			 <tr>
			  <td>
			   $m&nbsp;de&nbsp;$num:
			  </td>
			  <td><a href='".KUMBIA_PATH."{$_REQUEST['controller']}/browse/&amp;limBrowse=0&amp;numBrowse=10$oBy' 
			  title='Ir al Principio'
			  ><img border='0' width=6 height=9 src='".KUMBIA_PATH."img/first.gif' alt=''/></a></td>
			  <td><a 
			  href='".KUMBIA_PATH."{$_REQUEST['controller']}/browse/&amp;limBrowse=".($_REQUEST['limBrowse']-10<0 ? $_REQUEST['limBrowse'] : $_REQUEST['limBrowse']-10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy'
			  title='Ir al Anterior'
			  ><img border=0 width=5 height=9 src='".KUMBIA_PATH."img/prev.gif' alt=''/></a></td>
			  <td>
			  <a href='".KUMBIA_PATH."{$_REQUEST['controller']}/browse/&amp;limBrowse=".($_REQUEST['limBrowse']+10>$num ? $num-10<0 ? 0 : $num-10 : $_REQUEST['limBrowse']+10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy'
			  title='Ir al Siguiente $num'
			  ><img border=0 width=5 height=9 src='".KUMBIA_PATH."img/next.gif' alt=''/></a></td>
			  <td>
			  <a href='".KUMBIA_PATH."{$_REQUEST['controller']}/browse/&amp;limBrowse=".($num-10<0 ? 0 : $num - 10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy' 
			  title='Ir al Ultimo'
			  ><img border=0 width=6 height=9 
			  src='".KUMBIA_PATH."img/last.gif' alt=''/></a></td>
			 </tr>
			</table>
			</td></tr>");
			Generator::forms_print("</table>");			
		} else {
			Generator::forms_print("</table>");
			Generator::forms_print("<center><br><br>No Hay Registros Para Visualizar</center>");
		}
		Generator::forms_print("</form>");
		/*if($num>=20){
			  Generator::forms_print("<center><br><table><td><span><b>B&uacute;squeda:</b></span></td>
			  <td><input style='border:1px solid #808080' type='text' id='q'
			  ></td><td><input type=button class='controlButton'
			  value='Buscar'
			  onclick=\"window.location='?action={$_REQUEST['action']}&subaction=browse&option={$_REQUEST['option']}&q='+document.getElementById('q').value\"
			  ></td></table>
			  <br></center>");
			}*/
		Generator::forms_print("\r\n<br><center><input type='button' class='controlButton' 
		value='Volver' onclick='window.location = \"".KUMBIA_PATH."{$_REQUEST['controller']}/back\"'></center>"); 
	}
}

function doPrimaryKey($form, $row){
	$str = "";
	foreach($form['components']as $name => $com){
		if($com['primary']){
		  	if($row["pk_".$name])
				$str.="fl_$name=".$row["pk_".$name]."&amp;";
			else
				$str.="fl_$name=".$row[$name]."&amp;";
		}
	}
	return $str;
}

