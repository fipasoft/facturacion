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
* Functions for Application Controllers
* ***************************************************************************
* Este archivo contiene las clases utilizadas para la generación de los
* formularios.
* Define sus metodos principales y hace la interacción con las funciones
* definidas en los archivos
*****************************************************************************/

/**
 * La Clase StandardForm es la base principal para la generación de formularios
 * de tipo Standard
 */
abstract class AjaxForm extends ApplicationControllerBase  {

	public $controller_name;
	public $action_name;
	public $id;
	public $ignore_list = array();
	public $form = array();
	static public $force = false;
	public $cache_layout;
	public $cache_view;
	public $scaffold = false;
	public $view;

	public $sucess_insert_message = "";
	public $failure_insert_message = "";
	public $success_update_message = "";
	public $failure_update_message = "";
	public $success_delete_message = "";
	public $failure_delete_message = "";

	/**
	 * Es el metodo principal de StandarForm y es llamado implicitamente
	 * para mostrar el formulario y su accion asociada.
	 * La propiedad $this->source indica la tabla con la que se va a generar
	 * el formulario
	 * La función buildForm es la encargada de crear el formulario
	 * esta se encuentra en forms.functions.php
	 */
	function index(){

		$this->form['type'] = "ajax";
		if($this->scaffold){
			if(isset($this->source)) {
				$this->form["source"] = $this->source;
			}
			foreach($this->ignore_list as $ignore){
				$this->form['components'][$ignore]['ignore'] = true;
			}
			Generator::build_form($this->form, true);
		} else {
			if(count($this->form)){
				if($this->source){
					$this->form["source"] = $this->source;
				}
				foreach($this->ignore_list as $ignore){
					$this->form['components'][$ignore]['ignore'] = true;
				}
				Generator::build_form($this->form);
			} else {
				Flash::kumbia_error('
					Debe especificar las propiedades del formulario a crear en $this->form
					ó coloque var $scaffold = true para generar dinámicamente el formulario.');
				$this->reset_form();
			}
		}
	}
	
	/**
	  * Obtiene el valor en minutos para el cache de la
	  * vista actual
	  *
	  */
	public function get_view_cache(){
		return $this->cache_view;
	}
	
	/**
	  * Obtiene el valor en minutos para el cache del
	  * layout actual
	  *
	  */
	public function get_layout_cache(){
		return $this->cache_layout;
	}
	
	/**
	 * Metodo Insert por defecto del Formulario
	 *
	 */
	public function insert(){

		$this->set_response("view");		

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de inserci&oacute;n');
			$this->_create_model();
			return;
		}

		$modelName = kumbia::get_model_name($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump();
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * validation, si este m&eacute;todo devuelve false termina la ejecuciï¿½n
		 * de la accin
		 */
		if(method_exists($this, "validation")){
			if($this->validation()===false){
				$this->keep_action = "insert";
				if(!Kumbia::$routed){
					return;
				}				
			}
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->validation)){
				if($this->{$this->validation}()===false){
					$this->keep_action = "insert";									
					if(!Kumbia::$routed){
						return;
					}
				}
				if(Kumbia::$routed){
					return;
				}				
			}
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_insert, si este m&eacute;todo devuelve false termina la ejecucion
		 * de la accion
		 */
		if(method_exists($this, "before_insert")){
			if($this->before_insert()===false){
				$this->keep_action = "insert";	
				if(!Kumbia::$routed){
					return;
				}			
			}
			if(Kumbia::$routed){
				return;
			}			
		} else {
			if(isset($this->before_insert)){
				if($this->{$this->before_insert}()===false){
					$this->keep_action = "insert";			
					if(!Kumbia::$routed){
						return;
					}
				}
				if(Kumbia::$routed){
					return;
				}				
			}
		}

		/**
		 * Subimos los archivos de Imagenes del Formulario
		 */
		foreach($this->form['components'] as $fkey => $rrow){
			if($this->form['components'][$fkey]['type']=='image'){
				if($_FILES["fl_".$fkey]){
					move_uploaded_file($_FILES["fl_".$fkey]['tmp_name'], htmlspecialchars("public/img/upload/{$_FILES["fl_".$fkey]['name']}"));
					$this->{$modelName}->$fkey = urlencode(htmlspecialchars("upload/".$_FILES["fl_".$fkey]['name']));
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para insertar el registro
		 * por lo tanto los
		 */
		$this->{$modelName}->id = null;
		if($this->{$modelName}->create()){
			if($this->success_insert_message){
				Flash::success($this->success_insert_message);
			} else {
				Flash::success("Se insert&oacute; correctamente el registro");
			}
		} else {
			if($this->failures_insert_message){
				Flash::error($this->failure_insert_message);
			} else {
				Flash::error("Hubo un error al insertar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_insert
		 */
		if(method_exists($this, "after_insert")){
			$this->after_insert();
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->after_insert)){
				$this->{$this->after_insert}();
			}
			if(Kumbia::$routed){
				return;
			}
		}		

	}
	
	/**
	 * Indica el tipo de Respuesta dada por el controlador
	 *
	 * @param string $type
	 */
	public function set_response($type){
		$this->response = $type;
	}	
	
}

?>
