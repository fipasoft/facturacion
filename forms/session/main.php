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
/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones
 *
 */
class Session {

	/**
	 * Crear ó especificar el valor para un indice de la sesión
	 * actual
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	static function set_data($index, $value){
	  	$_SESSION['session_data'][$index] = $value;  	  	
	}

	/**
	 * Obtener el valor para un indice de la sesión
	 *
	 * @param string $index
	 * @return mixed
	 */
	static function get_data($index){
	  	return $_SESSION['session_data'][$index];   
	}
	
	/**
	 * Crear ó especificar el valor para un indice de la sesión
	 * actual
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	static function set($index, $value){
	  	$_SESSION['session_data'][$index] = $value;  	  	
	}

	/**
	 * Obtener el valor para un indice de la sesión
	 *
	 * @param string $index
	 * @return mixed
	 */
	static function get($index){
	  	return $_SESSION['session_data'][$index];   
	}

	/**
	 * Unset una variable de indice 
	 *
	 */
	static function unset_data(){
	  	$lista_args = func_get_args();
	  	if($lista_args){
  	  		foreach($lista_args as $arg){
			  	unset($_SESSION['session_data'][$arg]);
			}
		}
	}
	
	static function isset_data($index){	  	
		return isset($_SESSION['session_data'][$index]);		
	}
}

class SessionRecord {
  
  	private $records;
  	public $id;
  	public $persistent = true;
  	
  	function create($arr=''){
  		if(!is_array($arr)) $arr = get_params(func_get_args());
  	  	if(!is_array($arr)&&!$this->id){
  	  	  	Flash::kumbia_error("Cannot save constant value on Session Record '$this->source'");
			return;	  	
		}
		if(!is_array($arr)){
			$arr = array();
			foreach($this->get_attributes() as $at){
				$arr[$at] = $this->$at;
			}		
		}
		$max = 0;
		if(is_array($this->records)){
			foreach($this->records as $r){
			  	if(!$max) $max = $r['id'];
			  	if($r['id']==$arr['id']){
			  	  	Flash::kumbia_error("Cannot insert duplicate id on Session Record '$this->source'");
			  	  	return;
			  	}
				if($r['id']>$max) $max = $r['id'];
			}
		}
		if(!$arr['id']) $arr['id'] = ++$max;
  	  	$rec = array();
		foreach($arr as $key => $value){
		  	if($this->is_attribute($key))
			 	$rec[$key] = $value;
			else {
			  	Flash::kumbia_error("Field $key is not defined on Session Record '$this->source' when creating");
			  	return;
			}
		}
		$this->records[] = $rec;
	}
	
	function save(){
	  	$record = false;
  	  	$n = 0;
		if(is_array($this->records)){
			foreach($this->records as $r){
		  		if(!$max) $max = $r['id'];
		  		if(!is_null($this->id)){
				  	if($r['id']==$this->id){
				  	  	$record = $n;				  	  	
				  	}
				}
				if($r['id']>$max) $max = $r['id'];
				$n++;
			}
		}    
		if(!$this->id) $this->id = ++$max;
		if($record===false){
		  	$rec = array();
		  	foreach($this->get_attributes() as $at){
				$rec[$at] = $this->$at;
			}			
			$this->create($rec);
		} else {
		  	$this->update($id, $record);
		}
		
  	}
  	
  	function update($id='', $n=''){
  	  	if($id==='') $id = $this->id;
  	  	if($n===''){
  	  	  	$n = 0;
			if(is_array($this->records)){
				foreach($this->records as $r){			  		
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->records[$n][$at] = $this->$at;
						}				  	  	
				  	  	return;
				  	}					
					$n++;
				}
			}		    
		} else {
		  	if(isset($this->records[$n])){
			  	foreach($this->get_attributes() as $at){
					$this->records[$n][$at] = $this->$at;
				}				  	  	
				return;
			}
		}
	}
	
	function delete($id=''){
	  	if($id!==''){
	  		$n = 0;
			foreach($this->records as $r){			  		
			  	if($r['id']==$id){
					unset($this->records[$n]); 			  	  	
			  	}					
				$n++;
			}
		} else {
		  	foreach($this->get_attributes() as $at){
			    $this->$at = '';
			}
			unset($this->records);
			$this->records = array();
		}
	  
  	}
  	
  	function is_attribute($att){
	    return in_array($att, $this->get_attributes());
	}
	
	function show(){ }
	
	function show_records(){
	  	print_r($this->records);
	}
	
  	function get_attributes(){
  	  	$atts = array();
	    foreach($this as $key => $t){
		  	if(!is_callable($this, $key)
			  &&($key!="source")
			  &&($key!="persistent")
			  &&!is_array($this->$key)) $atts[] = $key;
		}	    
		return $atts;
	}
	
	function find($id=''){
		if($id!=''){
		  	$n = 0;
		  	foreach($this->get_attributes() as $at){
			    $this->$at = '';
			}
		  	if(is_array($this->records)){		  	  	
				foreach($this->records as $r){			  		
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->$at = $this->records[$n][$at];
						}
						return $this->dump($this->records[$n][$at]);									  	  			  	  	
				  	}					
					$n++;
				}
			}
			return false;
		} else {
		  	$results = array();
		  	if(is_array($this->records)){
				foreach($this->records as $r){						
					$results[] = $this->dump($r);					  						
				} 
			}
			return count($results) ? $results : array();
		}			
	}
	
	function find_first($id=''){
		if($id!==''){
		  	$n = 0;
		  	if($this->records){
				foreach($this->records as $r){			  		
				  	if($r['id']==$id){
				  	  	foreach($this->get_attributes() as $at){
							$this->$at = $this->records[$n][$at];
						}
						return $this->dump($this->records[$n][$at]);									  	  			  	  	
				  	}					
					$n++;
				}				
			} 
			return false;			  	
		} else {
		  	if(is_array($this->records)){
				foreach($this->records as $r){						
					return $this->dump($r);					
				} 
			}  
		}
	}
	
	function dump($rec){
	  	$obj = clone $this;
	  	foreach($this->get_attributes() as $at){
		    $obj->$at = $rec[$at];
		}
		return $obj;
	}
  
}