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

function updateDetail($form){
	$data = array();
	foreach($_REQUEST as $nvar => $item){
		if(substr($nvar, 0, 3)=="fl_"){
			if(strpos($nvar, "__n")){
				$ffield = substr($nvar, 3, strpos($nvar, "__n")-3);
				$num = substr($nvar, strpos($nvar, "__n")+3);
				$data[$num][$ffield] = $item;
			}
		}
	}
	$db = db::raw_connect();
	$db->query("Begin");
	$selectedFields = "";
	for($i=0;$i<=count($form['components'])-1;$i++){
		if(!$form['components'][key($form['components'])]['formOnly']){
			$selectedFields.= key($form['components']).",";
		}
		next($form['components']);
	}
	$selectedFields = substr($selectedFields, 0, strlen($selectedFields)-1);
	$autoInc = 1; 
	$db->query("create temporary table ".$form['source']."_temp as select $selectedFields from ".$form['source']." limit 0");
	foreach($data as $item){
		$notInsert = false;
		$ins = "Insert into ".$form['source']."_temp (";
		$query = "";
		reset($form['components']);
		for($i=0;$i<=count($item)-1;$i++){
			$key = key($form['components']);

			if($form['components'][$key]["attributes"]["value"]){
				while(ereg("[@][a-zA-Z0-9_]+", $form['components'][$key]["attributes"]["value"], $regs)){
					$form['components'][$key]["attributes"]["value"] =
					str_replace($regs[0], $_REQUEST["fl_".str_replace("@", "", $regs[0])], $form['components'][$key]["attributes"]["value"]);
				}
			}
			
			if(!$form['components'][$key]['formOnly']){
				if($item[$key]=="undefined") $item[$key] = "";
				if($form['components'][$key]['type']=='hidden') $item[$key] = $form['components'][$key]['attributes']['value'];
				if($form['components'][$key]['type']=='auto') $item[$key] = $autoInc++;
				if($form['components'][$key]['defaultInsert']){
					if(!trim($item[$key])
					||($item[$key]=='0000-00-00')
					||($item[$key]=='0.00')
					||($item[$key]=='undefined')){
						$item[$key] = $form['components'][$key]['defaultInsert'];
					}
				}
				if($form['components'][$key]['valueType']=='numeric'){
					$item[$key] = $item[$key] ? $item[$key] : 0;
					//print "$key=>".$item[$key]."<br>";
				}
				if($form['components'][$key]['type']=='combo'){
					if($form['components'][$key]['primary'])
					if($item[$key]=='') $notInsert = true;
				}
				$query.="'".$item[$key]."',";
				$ins.=$key.",";
			}
			next($form['components']);
		}
		$query = substr($query, 0, strlen($query)-1);
		$ins = substr($ins, 0, strlen($ins)-1);
		$query = $ins.") Values (".$query.")";
		if(!$notInsert){
			//print $query."<br>";
			if(!$db->query($query)) {
				$err = $db->error();
				$db->query("Rollback");
				Flash::error($err." : 1");
				return;
			}
		}
	}

	$pk = array();
	foreach($form['components'] as $k => $com){
		if($com['primary']=='true') $pk[] = $k;
	}

	$Join = "";
	for($i=0;$i<=count($pk)-1;$i++){
		$Join.= $form['source'].".{$pk[$i]} = ".$form['source']."_temp.{$pk[$i]}";
		if($i!=(count($pk)-1)) $Join .=" and ";
	}

	$pkIsNull = "";
	$pkIsNull2 = "";
	for($i=0;$i<=count($pk)-1;$i++){
		$pkIsNull.= $form['source'].".{$pk[$i]} is null";
		$pkIsNull2.= $form['source']."_temp.{$pk[$i]} is null";
		if($i!=(count($pk)-1)) $pkIsNull .=" and ";
		if($i!=(count($pk)-1)) $pkIsNull2 .=" and ";
	}

	//$db->query("select * from ".$form['source']."_temp");
	//while($row = $db->fetch_array()){
	//print_r($row);
	//}

	//Inserta los Nuevos
	if(!$form['dataFilter']) $form['dataFilter'] = " 1 = 1 ";
	else {
		if($x = strpos($form['dataFilter'], '@')){
			while(ereg("[\@][A-Za-z0-9_]+", $form['dataFilter'], $regs)){
				$form['dataFilter'] = str_replace($regs[0], $_REQUEST["fl_".str_replace('@', '', $regs[0])], $form['dataFilter']);
			}
		}  	  
	}

	//Revisar DataFilter
	//print "select ".$form['source']."_temp.* from ".$form['source']." right join ".$form['source']."_temp on $Join where $pkIsNull";
	if(!($q = $db->query("select ".$form['source']."_temp.* from ".$form['source']."
    right join ".$form['source']."_temp on $Join where $pkIsNull"))){
		Flash::error($db->error()." : 2");
		$db->query("Rollback");
		return;
    }

    //print_r($form);

    if($db->num_rows($q)){
    	while($row = $db->fetch_array($q, MYSQL_ASSOC)){
    		$iData = "";
    		$iFields = "";
    		$numberValid = 0;
    		reset($row);
    		$c=0;
    		//print "<pre>";
    		//print_r($row);
    		for($i=0;$i<=count($row)-1;$i++){
    			$rrow = $row[key($row)];
    			$fkey = key($row);
    			
    			if($form['components'][$fkey]['defaultInsert']){
    				if(trim($rrow)
    				||($rrow=='0000-00-00')
    				||($rrow=='0.00')
    				||($rrow=='undefined'))
    				$rrow = $form['components'][$fkey]['defaultInsert'];
    			}
    			$iFields.=$fkey.",";
    			if(!$form['components'][$fkey]["formOnly"]){
    				if(!is_numeric($key)){
    					if(trim($rrow)
    					&&($rrow!='0000-00-00')
    					&&($rrow!='0.00')
    					&&(!$form['components'][$fkey]['primary'])
    					&&($rrow!='undefined')){
    						if($form['components'][$fkey]['defaultInsert']!=$rrow)
    						$numberValid++;
    					}
    					if($form['components'][$fkey]['type']=='text') {
    						if($form['components'][$fkey]['valueType']=='numeric'){    							
    							$rrow = ($rrow == "" ? 0 : $rrow);    							
    						} else $c++;
    					}
    					$iData.="'".$rrow."',";
    					$b++;
    				}
    			}
    			next($row);
    		}
    		if(!$c) $numberValid = 1;
    		$iData = substr($iData, 0, strlen($iData)-1);
    		$iFields = substr($iFields, 0, strlen($iFields)-1);
    		//print "insert into ".$form['source']."($iFields) values ($iData) [$numberValid]<br>\n\r";
    		if($numberValid){
    			if(!$db->query("insert into ".$form['source']." values ($iData)")){
    				Flash::error($db->error()." : 3");
    				$db->query("Rollback");    				
    				return;
    			}
    		}
    		//print "</pre>";
    	}
    }    

    //Borra los Que Faltan
    $selectedFields = "";
    for($i=0;$i<=count($pk)-1;$i++){
    	$selectedFields.= $form['source'].".{$pk[$i]}";
    	if($i!=(count($pk)-1)) $selectedFields .=", ";
    }
    
    $qjoin = "select $selectedFields From ".$form['source']." Left Join ".$form['source']."_temp On $Join where $pkIsNull2 and ".$form['dataFilter'];
    //print "<br>";
    if(!($q = $db->query($qjoin))){
    	Flash::error($db->error()." : 4");
    	$db->query("Rollback");  
    	return;
    }
    if($db->num_rows()){
    	while($row = $db->fetch_array($q)){
    		$whereDelete = "";
    		for($i=0;$i<=count($pk)-1;$i++){
    			$whereDelete.= "{$pk[$i]} = '".$row[$pk[$i]]."'";
    			if($i!=(count($pk)-1)) $whereDelete .=" and ";
    		}
    		//print "delete from ".$form['source']." where $whereDelete<br>";
    		if(!$db->query("delete from ".$form['source']." where $whereDelete")){
    			Flash::error($db->error());
    			$db->query("Rollback");    			
    			return;
    		}
    	}
    }

    //Actualiza los que estan Iguales
    if($form['dataFilter']){
    	if(ereg("[@][a-zA-Z0-9]+", $form['dataFilter'], $regs)){
    		$form['dataFilter'] = str_replace($regs[0], $_REQUEST['fl_'.str_replace("@", "", $regs[0])], $form['dataFilter']);
    	}
    }
    //print "select ".$form['source']."_temp.* from ".$form['source']." inner join ".$form['source']."_temp On $Join where ".$form['dataFilter']."<br>";

    if(!($q = $db->query("select ".$form['source']."_temp.* from ".$form['source']." inner join ".$form['source']."_temp On $Join where ".$form['dataFilter']))){
    	Flash::error($db->error());
    	$db->query("Rollback");    	
    	return;
    }
    if($db->num_rows()){    	
    	while($row = $db->fetch_array($q)){
    		$a = 1;
    		$updateData = "";
    		foreach($row as $fkey => $rrow){
    			if(!is_numeric($fkey)) {
    				if($form['components'][$fkey]['primary']!=true){
    					$updateData.="$fkey = '".$rrow."',";
    				}
    			}
    		}
    		$updateData = substr($updateData, 0, strlen($updateData)-1);
    		$whereUpdate = "";
    		for($i=0;$i<=count($pk)-1;$i++){
    			$whereUpdate.= "{$pk[$i]} = '".$row[$pk[$i]]."'";
    			if($i!=(count($pk)-1)) $whereUpdate .=" and ";
    		}
    		//print "update ".$form['source']." set ".$updateData." where $whereUpdate";
    		if(!$db->query("update ".$form['source']." set ".$updateData." where $whereUpdate")){
    			Flash::error($db->error());
    			$db->query("Rollback");    			
    			return;
    		}
    	}
    }

    if($form['events']['afterUpdate']) $form['events']['afterUpdate']();

    if($form['type']!='grid'){
    	foreach($form['components'] as $name => $component){
    		if($component['masterDetailRelation'])
    		unset($_REQUEST['fl_'.$name]);
    	}
    }

    $db->query("Commit");    
}

?>