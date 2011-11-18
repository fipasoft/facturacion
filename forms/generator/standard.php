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

function insertMaster($form){
	
        //Adicciona
        if(array_key_exists('permissions', $form)) {		
          	if(array_key_exists('access', $form['permissions'])){
         		if(!getPermission($form, 'access')){
				   return;
				}
			}
        	if(array_key_exists('insert', $form['permissions'])){
         		if(!getPermission($form, 'insert')){
				   return;
				}
			}
        }
                
        $db = db::raw_connect();        

        //Before Event
        if($form['events']['beforeInsert'])
        if($form['events']['beforeInsert']()=='error'){			
			return;
        }

        $insertData = "(";
        $insertFields = "(";
        $valnue = "";
        foreach($form['components'] as $fkey => $rrow){
          		if($form['components'][$fkey]['type']=='image'){				  	
					move_uploaded_file($_FILES["fl_".$fkey]['tmp_name'], "public/img/upload/{$_FILES["fl_".$fkey]['name']}");
 					$_REQUEST["fl_".$fkey] = urlencode("upload/".$_FILES["fl_".$fkey]['name']);
				}
          	    if($_REQUEST['fl_'.$fkey]){
					$insertFields.= "$fkey,";    
				}                
				if($form['components'][$fkey]['primary']){
				  	$valnue.="$fkey={$_REQUEST['fl_'.$fkey]} ";
				}												
                if($form['components'][$fkey]['type']=='check'){
                        if($_REQUEST["fl_".$fkey]=='true')
                        $insertData.="'".$form['components'][$fkey]["checkedValue"]."',";
                        else $insertData.="'".$form['components'][$fkey]["unCheckedValue"]."',";
                } else {
                        if($form['components'][$fkey]["auto_numeric"]){
                                $q = $db->query("select max($fkey)+1 from ".$form['source']);
                                $numRow = $db->fetch_array($q);
                                if($numRow[0]==null) $numRow[0] = 1;
                                $insertData.="".$numRow[0].",";
                                $_REQUEST['fl_'.$fkey] = $numRow[0];
                        } else {
                            //Insert Value if have requested a Value for that field
                          	if($_REQUEST['fl_'.$fkey]){
                                if($form['components'][$fkey]['valueType']=='numeric'){
                                        if(!$_REQUEST["fl_".$fkey]){
                                                $insertData.="0,";
                                        } else $insertData.=$_REQUEST["fl_".$fkey].",";
                                } else $insertData.="'".$_REQUEST["fl_".$fkey]."',";
                            }
                        }
                }
                
                
        }
        $insertFields = substr($insertFields, 0, strlen($insertFields)-1).")";
        $insertData = substr($insertData, 0, strlen($insertData)-1).")";

        $db->query("begin work");
        
        $query = "Insert into ".$form['source']." $insertFields values $insertData";
        if(!$db->query($query)){
                Flash::error($db->no_error()." ".$db->error());
                $db->query("rollback");
                return;
        } 

        //After Event
        if($form['events']['afterInsert'])
        if($form['events']['afterInsert']()=='error') {
                $db->query("rollback");
                return;
        }

        Flash::success("Se insert&oacute; el Registro Correctamente");
        $db->query("commit");

        foreach($form['components'] as $fkey => $rrow){
        	if(!$form['components'][$fkey]['auto_numeric']);
        }
        if(!$form['components'][$fkey]['masterDetailRelation']){
        	unset($_REQUEST["fl_".$fkey]);
        }
                
}


//Esta funcion me consulta
function queryMaster($form){
  		if(array_key_exists('permissions', $form)) {		
          	if(array_key_exists('access', $form['permissions'])){
         		if(!getPermission($form, 'access')){
				   return;
				}
			}
        	if(array_key_exists('query', $form['permissions'])){
         		if(!getPermission($form, 'query')){
				   return;
				}
			}
        }
        if(isset($form['dataFilter'])) {
          	if($form['dataFilter'])
			  	$dataFilter = $form['dataFilter'];
			else $dataFilter = "1=1";
		} else $dataFilter = "1=1";
		

		if(!isset($form['joinTables'])) {
		  	$form['joinTables'] = "";
		  	$tables = "";
		}
		else if($form['joinTables']) $tables = ",".$form['joinTables'];
		if(!isset($form['joinConditions'])) {
		  	$form['joinConditions'] = "";
		  	$joinConditions = "";
		}
        if($form['joinConditions']) $joinConditions = " and ".$form['joinConditions'];

        $query =  "select * from ".$form['source']."$tables where $dataFilter $joinConditions ";
        $source = $form['source'];
        foreach($form['components'] as $fkey => $rrow){
          		if(!isset($_REQUEST["fl_".$fkey])) $_REQUEST["fl_".$fkey] = "";
                if(trim($_REQUEST["fl_".$fkey])&&$_REQUEST["fl_".$fkey]!='@'){
                        if($form['components'][$fkey]['valueType']=='numeric')
                        $query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
                        else
                        if($form['components'][$fkey]['type']=='hidden'){
                        	$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
                        } else {
                                if($form['components'][$fkey]['type']=='check'){
                                        if($_REQUEST["fl_".$fkey]==$form['components'][$fkey]['checkedValue'])
                                        $query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
                                }
                                else $query.=" and $source.$fkey like '%".$_REQUEST["fl_".$fkey]."%'";
                        }
                }
        }
        $_SESSION['QueryTemp'] = $query;

        $_REQUEST['queryStatus'] = true;
        $_REQUEST['id'] = 0;

        fetchQuery(0, $form);
}

function fetchQuery($num, $form){
		if(array_key_exists('permissions', $form)) {		
          	if(array_key_exists('access', $form['permissions'])){
         		if(!getPermission($form, 'access')){
				   return;
				}
			}
        	if(array_key_exists('query', $form['permissions'])){
         		if(!getPermission($form, 'query')){
				   return;
				}
			}
        }
        
        $db = db::raw_connect();		
        if(!($q = $db->query($_SESSION['QueryTemp']))) print Flash::error($db->error());
        if(!isset($_REQUEST['id'])) {
        	$_REQUEST['id'] = 0;
        } else $num = $_REQUEST['id'];

        //Hubo resultados en el select?
        if(!$db->num_rows($q)){
                Flash::notice("No se encontraron resultados en la b&uacute;squeda");
                foreach($form['components'] as $fkey => $rrow){
                        unset($_REQUEST["fl_".$fkey]);
                }                
                unset($_REQUEST['queryStatus']);
                return;
        }

        if($_REQUEST['id']>=$db->num_rows($q)) $num = $db->num_rows($q)-1;
        if($num<0) $num = 0;
        if($_REQUEST['id']==='last') $num = $db->num_rows($q)-1;

        Flash::notice("Visualizando ".($num+1)." de ".$db->num_rows()." registros");

        //especifica el registro que quiero mostrar
        $success = $db->data_seek($num, $q);        
        if($success){        	
       		$row = $db->fetch_array($q);
        }
                
        //Mete en $row la fila en la que me paro el seek
        foreach($row as $key => $value){
        	if(!is_numeric($key)){
            	$_REQUEST['fl_'.$key] = $value;
            }
        }
        
        $_REQUEST['id'] = $num;
                        
        //After Fetch
        if(isset($form['events']['afterFetch']))
	        if($form['events']['afterFetch']) $form['events']['afterFetch']();

}


//This function modify 
function updateMaster($form){
		
		if(array_key_exists('permissions', $form)) {		
          	if(array_key_exists('access', $form['permissions'])){
         		if(!getPermission($form, 'access')){
				   return;
				}
			}
        	if(array_key_exists('update', $form['permissions'])){
         		if(!getPermission($form, 'update')){
				   return;
				}
			}
        }
        $upValues = array();
        $updateData = "";
        $pkUpdate = "";
        foreach($form['components'] as $fkey => $com){
            if(!$com['primary']){
            	if($form['components'][$fkey]['type']=='check'){
                	if($_REQUEST["fl_".$fkey]=='true'){
                    	$updateData.="$fkey = '".$form['components'][$fkey]["checkedValue"]."',";
                        $upValues[$fkey] = $form['components'][$fkey]["checkedValue"];
                    } else {
                        $updateData.=" $fkey = '".$form['components'][$fkey]["unCheckedValue"]."',";
                        $upValues[$fkey] = $form['components'][$fkey]["unCheckedValue"];
                    }
            	} else {
            		$updateData.="$fkey = '".$_REQUEST["fl_".$fkey]."',";
                	$upValues[$fkey] = $_REQUEST["fl_".$fkey];
            	}
            } else $pkUpdate.=" $fkey = '".$_REQUEST["fl_".$fkey]."' and ";
        }
        $updateData = substr($updateData, 0, strlen($updateData)-1);
        $pkUpdate = substr($pkUpdate, 0, strlen($pkUpdate)-5);
        $db = db::raw_connect();       
        $valant = "";
        $valnue = "";
        $q = $db->query("select * from ".$form['source']." where $pkUpdate");
        if($row = $db->fetch_array($q, db::DB_ASSOC)){
			foreach($row as $field => $value){
				if(!$form['components'][$field]['primary']){
					if($upValues[$field]<>$value){
						$valant.="$field=$value ";
						$valnue.="$field=".$upValues[$field]." ";
                   }
				} else {
					$valant.="$field=$value ";
					$valnue.="$field=$value ";
				}
			}
        }
        if($form['events']['beforeUpdate']) {
			if($form['events']['beforeUpdate']()!='error'){
				if(!$db->query("Update ".$form['source']." set ".$updateData." where ".$pkUpdate)){
					Flash::error($db->error());
				} else {
					Flash::success("Se actualiz&oacute; el registro correctamente");              
				}
			}
        } else {
			if(!$db->query("update ".$form['source']." set ".$updateData." where ".$pkUpdate)){
               	Flash::error($db->error());
			} else {
				Flash::success("Se actualiz&oacute; el registro correctamente");                        
			}
        }
        if($form['events']['afterUpdate']) $form['events']['afterUpdate']();
        
}


//Delete a Standard Source Row
function deleteMaster($form){
		
	
		if(array_key_exists('permissions', $form)) {		
          	if(array_key_exists('access', $form['permissions'])){
         		if(!getPermission($form, 'access')){
				   return;
				}
			}
        	if(array_key_exists('delete', $form['permissions'])){
         		if(!getPermission($form, 'delete')){
				   return;
				}
			}
        }
        $pkDelete = "";
        $valant = "";
        foreach($form['components'] as $fkey => $com){
                if($com['primary']){
                        $pkDelete.=" $fkey = '".$_REQUEST["fl_".$fkey]."' and ";
                        $valant.="$fkey=".$_REQUEST["fl_".$fkey]." ";
                }
        }
        $pkDelete = substr($pkDelete, 0, strlen($pkDelete)-5);
        $db = db::raw_connect();

        //print "Delete From ".$form['source']." where ".$pkDelete;

        if(!$db->query("Delete From ".$form['source']." where ".$pkDelete)){
        	Flash::error($db->error());
        } else {
			Flash::success("Se borr&oacute; el registro correctamente");                
        }
        foreach($form['components'] as $fkey => $rrow){
                unset($_REQUEST["fl_".$fkey]);
        }        
}

?>