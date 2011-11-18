<?php
/** Kumbia - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andr&eacute;s Felipe Guti&eacute;rrez (andresfelipe at vagoogle.net)
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
* bajo los terminos de la licencia p&uacute;blica general GNU tal y como fue publicada
* por la Fundaci&oacute;n del Software Libre; desde la versi&oacute;n 2.1 o cualquier
* versi&oacute;n superior.
*
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; sin dejar atr&aacute;s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m&aacute;s detalles.
*
* Debes recibir una copia de la Licencia P&uacute;blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci&oacute;n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*****************************************************************************
* Functions for Application Controllers
* ***************************************************************************
* Este archivo contiene las clases utilizadas para la generaci&oacute;n de los
* formularios.
* Define sus metodos principales y hace la interaccin con las funciones
* definidas en los archivos:
* - forms.functions.php
*****************************************************************************/

/**
 * La Clase StandardForm es la base principal para la generacin de formularios
 * de tipo Standard
 *
 * Notas de Version:
 * Desde Kumbia-0.4.7, StandardForm mantiene los valores de la entrada cuando los
 * metodos before_ o validation devuelven false;
 */
abstract class StandardForm extends ApplicationControllerBase  {

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
	public $keep_action = true;

	public $sucess_insert_message = "";
	public $failure_insert_message = "";
	public $success_update_message = "";
	public $failure_update_message = "";
	public $success_delete_message = "";
	public $failure_delete_message = "";

	/**
	 * Obtiene el Valor de Source cuando no esta disponible
	 */
	public function __get($property){
		if($property=="source"){
			if(!$this->source){
				ActiveRecord::sql_sanizite($_REQUEST["controller"]);
				return $this->source = $_REQUEST["controller"];
			}
		}
		return $this->$property;
	}

	/**
	 * Emula la accin Report llamando a show
	 */
	public public function report(){

		$this->view = 'index';

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de reporte');
			$this->_create_model();
			return $this->route_to('action: index');
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
		 * before_report, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "before_report")){
			if($this->before_report()===false){
				return null;
			}
			if(Kumbia::$routed){
				return null;
			}
		} else {
			if(isset($this->before_report)){
				if($this->{$this->before_report}()===false){
					return null;
				}
				if(Kumbia::$routed){
					return null;
				}
			}
		}

		require_once "forms/report/functions.php";
		Generator::scaffold(&$this->form, $this->scaffold);
		Report::generate($this->form);

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_insert, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "after_report")){
			if($this->after_report()===false){
				return null;
			}
			if(Kumbia::$routed){
				return null;
			}
		} else {
			if(isset($this->after_report)){
				if($this->{$this->after_report}()===false){
					return null;
				}
				if(Kumbia::$routed){
					return null;
				}
			}
		}

		return $this->route_to('action: index');
	}

	/**
	 * Invoca al Kumbia Builder a crear un modelo en caso de que no exista
	 */
	private function _create_model(){
		$config = Config::read();
		if($config->project->interactive){
			InteractiveBuilder::create_model($this->source, $this->controller_name, $this->action_name);
		}
	}

	/**
	 * Metodo Insert por defecto del Formulario
	 *
	 */
	public function insert(){

		$this->view = 'index';
		$this->keep_action = "";

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de inserci&oacute;n');
			$this->_create_model();
			return $this->route_to('action: index');
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
		 * validation, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "validation")){
			if($this->validation()===false){
				$this->keep_action = "insert";
				if(!Kumbia::$routed){
					return $this->route_to('action: index');
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
						return $this->route_to('action: index');
					}
				}
				if(Kumbia::$routed){
					return;
				}
			}
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_insert, si este m&eacute;todo devuelve false termina la ejecucin
		 * de la accin
		 */
		if(method_exists($this, "before_insert")){
			if($this->before_insert()===false){
				$this->keep_action = "insert";
				if(!Kumbia::$routed){
					return $this->route_to('action: index');
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
						return $this->route_to('action: index');
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

		// Muestra el Formulario en la accion show
		return $this->route_to('action: index');

	}

	/**
	 * Emula la accin Update llamando a show
	 *
	 */
	public function update(){

		$this->view = 'index';
		$this->keep_action = "";

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operacin de actualizaci&oacute;n');
			$this->_create_model();
			return $this->route_to('action: index');
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
		 * validation, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "validation")){
			if($this->validation()===false){
				$this->keep_action = "update";
				if(!Kumbia::$routed){
					return $this->route_to('action: index');
				}
			}
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->validation)){
				if($this->{$this->validation}()===false){
					$this->keep_action = "update";
					if(!Kumbia::$routed){
						return $this->route_to('action: index');
					}
				}
				if(Kumbia::$routed){
					return;
				}
			}
		}

		/**
		 * Busca si existe un metodo o un llamado variable al metodo
		 * before_update, si este metodo devuelve false termina la ejecucion
		 * de la accion
		 */
		if(method_exists($this, "before_update")){
			if($this->before_update()===false){
				$this->keep_action = "update";
				if(!Kumbia::$routed){
					return $this->route_to('action: index');
				}
			}
			if(Kumbia::$routed){
				return null;
			}
		} else {
			if(isset($this->before_update)){
				if($this->{$this->before_update}()===false){
					$this->keep_action = "update";
					if(!Kumbia::$routed){
						return $this->route_to('action: index');
					}
				}
				if(Kumbia::$routed){
					return null;
				}
			}
		}

		/**
		 * Subimos los archivos de Im&aacute;genes del Formulario
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
		 * Utilizamos el modelo ActiveRecord para actualizar el registro
		 */
		if($this->{$modelName}->update()){
			if($this->success_update_message){
				Flash::success($this->success_update_message);
			} else {
				Flash::success("Se actualiz&oacute; correctamente el registro");
			}
		} else {
			if($this->failures_update_message){
				Flash::error($this->failure_update_message);
			} else {
				Flash::error("Hubo un error al actualizar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_update
		 */
		if(method_exists($this, "after_update")){
			$this->after_update();
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->after_update)){
				$this->{$this->after_update}();
				if(Kumbia::$routed){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return $this->route_to('action: index');

	}

	public function show_not_nulls($option = true){
		$this->form['show_not_nulls'] = $option;
	}

	/**
	 * Emula la accin Delete llamando a show
	 *
	 */
	public function delete(){

		$this->view = 'index';

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operacin de eliminacin');
			$this->_create_model();
			return $this->route_to('action: index');
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
		 * before_delete, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "before_delete")){
			if($this->before_delete()===false){
				if(!Kumbia::$routed){
					return $this->route_to('action: index');
				}
			}
			if(Kumbia::$routed){
				return null;
			}
		} else {
			if(isset($this->before_delete)){
				if($this->{$this->before_delete}()===false){
					if(!Kumbia::$routed){
						return $this->route_to('action: index');
					}
				}
				if(Kumbia::$routed){
					return null;
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para eliminar el registro
		 */
		if($this->{$modelName}->delete()){
			if($this->success_delete_message){
				Flash::success($this->success_delete_message);
			} else {
				Flash::success("Se elimin&oacute; correctamente el registro");
			}
		} else {
			if($this->failures_delete_message){
				Flash::error($this->failure_delete_message);
			} else {
				Flash::error("Hubo un error al eliminar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_delete
		 */
		if(method_exists($this, "after_delete")){
			$this->after_delete();
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->after_delete)){
				$this->{$this->after_delete}();
				if(Kumbia::$routed){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return $this->route_to('action: index');
	}

	/**
	 * Emula la accin Query llamando a show
	 */
	public function query(){

		$this->view = 'index';
		$config = Config::read();

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			Flash::error('No hay un modelo "'.$this->source.'" para hacer la operacin de consulta');
			$this->_create_model();
			return $this->route_to('action: index');
		}

		if(isset($this->form['dataFilter'])) {
			if($this->form['dataFilter'])
			$dataFilter = $form['dataFilter'];
			else $dataFilter = "1=1";
		} else $dataFilter = "1=1";


		if(!isset($this->form['joinTables'])) {
			$this->form['joinTables'] = "";
			$tables = "";
		}
		else if($this->form['joinTables']) $tables = ",".$this->form['joinTables'];
		if(!isset($this->form['joinConditions'])) {
			$this->form['joinConditions'] = "";
			$joinConditions = "";
		}
		if($this->form['joinConditions']) $joinConditions = " and ".$this->form['joinConditions'];

		$modelName = kumbia::get_model_name($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump();
		}

		$query =  "select * from ".$this->form['source']."$tables where $dataFilter $joinConditions ";
		$source = $this->form['source'];

		$form = $this->form;
		foreach($this->{$modelName}->attributes_names as $fkey){
			if(trim($_REQUEST["fl_".$fkey])&&$_REQUEST["fl_".$fkey]!='@'){
				if($form['components'][$fkey]['valueType']=='numeric'||$form['components'][$fkey]['valueType']=='date'){
					if($config->database->type!='oracle'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['valueType']=='date'){
							$query.=" and $source.$fkey = TO_DATE('".$_REQUEST["fl_".$fkey]."', 'YYYY-MM-DD')";
						} else {
							$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
						}
					}
				} else {
					if($form['components'][$fkey]['type']=='hidden'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['type']=='check'){
							if($_REQUEST["fl_".$fkey]==$form['components'][$fkey]['checkedValue']){
								$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
							}
						} else {
							if($form['components'][$fkey]['type']=='time'){
								if($_REQUEST["fl_".$fkey]!='00:00'){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								}
							} else {
								if($form['components'][$fkey]['primary']){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								} else {
									$query.=" and $source.$fkey like '%".$_REQUEST["fl_".$fkey]."%'";
								}
							}
						}
					}
				}
			}
		}

		$this->query = $query;

		$_REQUEST['queryStatus'] = true;
		$_REQUEST['id'] = 0;

		$this->fetch(0);

	}

	/**
	 * Metodo de ayuda para el componente helpText
	 *
	 */
	public function __autocomplete(){

	}

	/**
	 * Metodo de ayuda para el componente helpText
	 */
	public function __check_value_in(){
		$this->set_response('xml');
		$db = db::raw_connect();
		$_REQUEST['condition'] = str_replace(";", "", urldecode($_REQUEST['condition']));
		ActiveRecord::sql_item_sanizite($_REQUEST['ftable']);
		ActiveRecord::sql_item_sanizite($_REQUEST['dfield']);
		ActiveRecord::sql_item_sanizite($_REQUEST['name']);
		ActiveRecord::sql_item_sanizite($_REQUEST['crelation']);
		$_REQUEST['ftable'] = str_replace(";", "", $_REQUEST['ftable']);
		$_REQUEST['dfield'] = str_replace(";", "", $_REQUEST['dfield']);
		$_REQUEST['name'] = str_replace(";", "", $_REQUEST['name']);
		if($_REQUEST["crelation"]){
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['crelation']." = '".$_REQUEST['value']."'");
		} else {
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['name']." = '".$_REQUEST['value']."'");
		}
		print "<?xml version='1.0' encoding='iso8859-1'?>\r\n<response>\r\n";
		$row = $db->fetch_array();
		print "\t<row num='".$db->num_rows()."' detail='".htmlspecialchars($row[0])."'/>\r\n";
		$db->close();
		print "</response>";
	}

	/**
	 * Emula la accin Fetch llamando a show
	 */
	public function fetch($id){

		$this->view = 'index';

		$db = db::raw_connect();

		if(!$this->query){
			return $this->route_to("action: index");
		}

		if(!($q = $db->query($this->query))) {
			Flash::error($db->error());
		}
		if(!isset($id)) {
			$id = 0;
		} else {
			$num = $id;
		}

		//Hubo resultados en el select?
		if(!$db->num_rows($q)){
			Flash::notice("No se encontraron resultados en la b&uacute;squeda");
			foreach($this->form['components'] as $fkey => $rrow){
				unset($_REQUEST["fl_".$fkey]);
			}
			unset($_REQUEST['queryStatus']);
			return $this->route_to('action: index');
		}

		if($id>=$db->num_rows($q)){
			$num = $db->num_rows($q)-1;
		}
		if($num<0) $num = 0;
		if($id==='last') {
			$num = $db->num_rows($q)-1;
		}


		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_fetch, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la accin
		 */
		if(method_exists($this, "before_fetch")){
			if($this->before_fetch()===false){
				return null;
			}
			if(Kumbia::$routed){
				return null;
			}
		} else {
			if(isset($this->before_fetch)){
				if($this->{$this->before_fetch}()===false){
					return null;
				}
				if(Kumbia::$routed){
					return null;
				}
			}
		}

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

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_delete
		 */
		if(method_exists($this, "after_fetch")){
			$this->after_fetch();
			if(Kumbia::$routed){
				return;
			}
		} else {
			if(isset($this->after_fetch)){
				$this->{$this->after_fetch}();
			}
			if(Kumbia::$routed){
				return;
			}
		}

		return $this->route_to('action: index');

	}

	/**
	 * Cambia la vista de browse a la vista index
	 *
	 * @return boolean
	 */
	public function back(){

		$this->view = 'index';
		return $this->route_to('action: index');

	}

	/**
	 * Emula la accin Browse llamando a show
	 */
	public function browse(){

		$this->view = 'browse';
		return $this->route_to('action: index');
	}

	/**
	* Es el metodo principal de StandarForm y es llamado implicitamente
	* para mostrar el formulario y su accion asociada.
	* La propiedad $this->source indica la tabla con la que se va a generar
	* el formulario
	* La funci�n buildForm es la encargada de crear el formulario
	* esta se encuentra en forms.functions.php
	*/
	function index(){

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
					� coloque var $scaffold = true para generar din�micamente el formulario.');
				$this->reset_form();
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

	/**
	 * Elimina los meta-datos del formulario
	 *
	 */
	function reset_form(){
		print $appController = $_REQUEST['controller']."Controller";
		unset($_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController]);
		print_r($_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']]);
	}

	/**
	 * Guarda un nuevo valor para una relacion detalle del
	 * controlador actual
	 *
	 */
	public function _save_helper(){
		$this->set_response('view');
		$db = db::raw_connect();
		Generator::scaffold(&$this->form, $this->scaffold);

		$field = $this->form['components'][$this->request('name')];
		ActiveRecord::sql_item_sanizite($field['foreignTable']);
		ActiveRecord::sql_item_sanizite($field['detailField']);
		$db->query("insert into {$field['foreignTable']} ({$field['detailField']})
		values ('{$this->request('valor')}')");
	}

	/**
	 * Devuelve los valores actualizados de
	 *
	 */
	public function _get_detail(){

		$this->set_response('xml');
		$db = db::raw_connect();
		Generator::scaffold(&$this->form, $this->scaffold);

		$name = $this->request('name');
		$com = $this->form['components'][$this->request('name')];

		if($com['extraTables']){
			ActiveRecord::sql_item_sanizite($com['extraTables']);
			$com['extraTables']=",".$com['extraTables'];
		}

		ActiveRecord::sql_sanizite($com['orderBy']);

		if(!$com["orderBy"]) $ordb = $name; else $ordb = $com["orderBy"];
		if($com['whereCondition']) $where = "where ".$com['whereCondition']; else $where = "";

		ActiveRecord::sql_item_sanizite($name);
		ActiveRecord::sql_item_sanizite($com['detailField']);
		ActiveRecord::sql_item_sanizite($com['foreignTable']);

		if($com['column_relation']){
			$com['column_relation'] = str_replace(";", "", $com['column_relation']);
			$query = "select ".$com['foreignTable'].".".$com['column_relation']." as $name,
					".$com['detailField']." from
					".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		} else {
			$query = "select ".$com['foreignTable'].".$name,
					  ".$com['detailField']." from ".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		}
		$xml = new simpleXMLResponse();
		while($row = $db->fetch_array()){
			if($this->request('valor')==$row[1]){
				$xml->addNode(array("value" => $row[0], "text" => $row[1], "selected" => "1"));
			} else {
				$xml->addNode(array("value" => $row[0], "text" => $row[1], "selected" => "0"));
			}
		}
		$xml->outResponse();
	}

	/**
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @param string $param_name
	 * @return mixed
	 */
	public function post($param_name){
		return $_POST[$param_name];
	}

	/**
	 * Obtiene un valor del arreglo $_GET
	 *
	 * @param string $param_name
	 * @return mixed
	 */
	public function get($param_name){
		return $_GET[$param_name];
	}

	/**
	 * Obtiene un valor del arreglo $_REQUEST
 	 *
	 * @param string $param_name
	 * @return mixed
	 */
	public function request($param_name){
		return $_REQUEST[$param_name];
	}

	/**
	 * Indica que un campo tendr� un helper de ayuda
	 *
	 * @param string $field
	 * @param string $helper
	 */
	function use_helper($field,$helper=''){
		if(!$helper) $helper = $field;
		$this->form['components'][$field."_id"]['use_helper'] = $helper;
	}

	/**
	 * Establece el Titulo del Formulario
	 *
	 * @param string $caption
	 */
	function set_form_caption($caption){
		$this->form['caption'] = $caption;
	}

	/**
	 * Indica que un campo ser� de tipo imagen
	 *
	 * @param string $what
	 */
	function set_type_image($what){
		$this->form['components'][$what]['type'] = 'image';
	}

	/**
	 * Indica que un campo ser� de tipo numerico
	 *
	 * @param string $what
	 */
	function set_type_numeric($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'numeric';
	}

	/**
	 * Indica que un campo ser� de tipo Time
	 *
	 * @param string $what
	 */
	function set_type_time($what){
		$this->form['components'][$what]['type'] = 'time';
	}

	/**
	 * Indica que un campo ser� de tipo fecha
	 *
	 * @param string $what
	 */
	function set_type_date($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'date';
	}

	/**
	 * Indica que un campo ser� de tipo password
	 *
	 * @param string $what
	 */
	function set_type_password($what){
		$this->form['components'][$what]['type'] = 'password';
	}

	/**
	 * Indica que un campo ser� de tipo textarea
	 *
	 * @param string $what
	 */
	function set_type_textarea($what){
		$this->form['components'][$what]['type'] = 'textarea';
	}

	/**
	 * Indica una lista de campos recibir�n entrada solo en may�sculas
	 *
	 */
	function set_text_upper(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['type'] = 'text';
				$this->form['components'][$what]['valueType'] = 'textUpper';
			}
		}
	}

	/**
	 * Crea un combo est�tico
	 *
	 * @param string $what
	 * @param string $arr
	 */
	function set_combo_static($what, $arr){
		$this->form['components'][$what]['type'] = 'combo';
		$this->form['components'][$what]['class'] = 'static';
		$this->form['components'][$what]['items'] = $arr;
	}

	/**
	 * Crea un combo Dinamico
	 *
	 * @param string $what
	 */
	function set_combo_dynamic($what){
		$opt = get_params(func_get_args());
		$opt['field'] = $opt['field'] ? $opt['field'] : $opt[0];
		$opt['relation'] = $opt['relation'] ? $opt['relation'] : $opt[1];
		$opt['detail_field'] = $opt['detail_field'] ? $opt['detail_field'] : $opt[2];
		$this->form['components'][$opt['field']]['type'] = 'combo';
		$this->form['components'][$opt['field']]['class'] = 'dynamic';
		$this->form['components'][$opt['field']]['foreignTable'] = $opt['relation'];
		$this->form['components'][$opt['field']]['detailField'] = $opt['detail_field'];
		if($opt['conditions']){
			$this->form['components'][$opt['field']]['whereCondition'] = $opt['conditions'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		} else {
			$this->form['components'][$opt['field']]['column_relation'] = $opt['id'];
		}
	}

	/**
	 * Crea un Texto de Ayuda de Contexto
	 *
	 * @param string $what
	 */
	function set_help_context($what){
		$opt = get_params(func_get_args());
		$opt['field'] = $opt['field'] ? $opt['field'] : $opt[0];
		$opt['relation'] = $opt['relation'] ? $opt['relation'] : $opt[1];
		$opt['detail_field'] = $opt['detail_field'] ? $opt['detail_field'] : $opt[2];
		$this->form['components'][$opt['field']]['type'] = 'helpText';
		$this->form['components'][$opt['field']]['class'] = 'dynamic';
		$this->form['components'][$opt['field']]['foreignTable'] = $opt['relation'];
		$this->form['components'][$opt['field']]['detailField'] = $opt['detail_field'];
		if($opt['conditions']){
			$this->form['components'][$opt['field']]['whereCondition'] = $opt['conditions'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		} else {
			$this->form['components'][$opt['field']]['column_relation'] = $opt['id'];
		}
		if($opt['message_error']){
			$this->form['components'][$opt['field']]['messageError'] = $opt['message_error'];
		} else {
			$this->form['components'][$opt['field']]['messageError'] = "NO EXISTE EL REGISTRO DIGITADO";
		}
	}

	/**
	 * Especifica que un campo es de tipo E-Mail
	 * @param $fields
	 */
	function set_type_email($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'text';
				$this->form['components'][$field]['valueType'] = "email";
			}
		}
	}

	/**
	 * Recibe una lista de campos que no van a ser incluidos en
	 * la generaci�n del formulario
	 */
	function ignore(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['ignore'] = true;
				if(!in_array($what, $this->ignore_list)){
					$this->ignore_list[] = $what;
				}
			}
		}
	}

	/**
	 * Permite cambiar el tama�o (size) de un campo $what a $size
	 *
	 * @param string $what
	 * @param integer $size
	 */
	function set_size($what, $size){
		$this->form['components'][$what]['attributes']['size'] = $size;
	}

	/**
	 * Permite cambiar el tama�o m�ximo de caracteres que se puedan
	 * digitar en un campo texto
	 *
	 * @param unknown_type $what
	 * @param unknown_type $size
	 */
	function set_maxlength($what, $size){
		$this->form['components'][$what]['attributes']['maxlength'] = $size;
	}

	/**
	 * Hace que un campo aparezca en la pantalla de visualizaci&oacute;n
	 *
	 */
	function not_browse(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notBrowse'] = true;
			}
		}
	}

	/**
	 * Hace que un campo no aparezca en el reporte PDF
	 *
	 * @param string $what
	 */
	function not_report($what){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notReport'] = true;
			}
		}
	}

	/**
	 * Cambia la imagen del Formulario. $im es una imagen en img/
	 *
	 * @param string $im
	 */
	function set_title_image($im){
		$this->form['titleImage'] = $im;
	}

	/**
	 * Cambia el numero de campos que aparezcan por fila
	 * cuando se genere el formulario
	 *
	 * @param unknown_type $number
	 */
	function fields_per_row($number){
		$this->form['fieldsPerRow'] = $number;
	}

	/**
	 * Inhabilita el formulario para insertar
	 *
	 */
	function unable_insert(){
		$this->form['unableInsert'] = true;
	}

	/**
	 * Inhabilita el formulario para borrar
	 *
	 */
	function unable_delete(){
		$this->form['unableDelete'] = true;
	}

	/**
	 * Inhabilita el formulario para actualizar
	 *
	 */
	function unable_update(){
		$this->form['unableUpdate'] = true;
	}

	/**
	 * Inhabilita el formulario para consultar
	 *
	 */
	function unable_query(){
		$this->form['unableQuery'] = true;
	}

	/**
	 * Inhabilita el formulario para visualizar
	 *
	 */
	function unable_browse(){
		$this->form['unableBrowse'] = true;
	}

	/**
	 * Inhabilita el formulario para generar reporte
	 *
	 */
	function unable_report(){
		$this->form['unableReport'] = true;
	}

	/**
	 * Indica que un campo ser� de tipo Hidden
	 *
	 */
	function set_hidden($what){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'hidden';
			}
		}
	}

	/**
	 * Cambia el Texto Caption de un campo en especial
	 *
	 */
	function set_caption($what, $caption){
		$this->form['components'][$what]['caption'] = $caption;
	}

	/**
	 * Hace que un campo sea de solo lectura
	 *
	 * @param string $what
	 */
	function set_query_only($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['queryOnly'] = true;
			}
		}
	}

	/**
	 * Cambia el texto de los botones para los formularios
	 * estandar
	 * set_action_caption('insert', 'Agregar')
	 */
	function set_action_caption($action, $caption){
		$this->form['buttons'][$action] = $caption;
	}

	/**
	 * Asigna un atributo a un campo del formulario
	 * set_attribute('campo', 'rows', 'valor')
	 * @param $field
	 * @param $name
	 * @param $value
	 */
	function set_attribute($field, $name, $value){
		$this->form['components'][$field]['attributes'][$name] = $value;
	}


	/**
	 * Asigna un atributo a un campo del formulario
	 * set_attribute('campo', 'rows', 'valor')
	 * @param $field
	 * @param $event
	 * @param $value
	 */
	function set_event($field, $event, $value){
		$this->form['components'][$field]['attributes']["on".$event] = $value;
	}

	/**
	 * Cache la vista correspondiente a la accion durante $minutes
	 *
	 * @param $minutes
	 */
	public function cache_view($minutes){
		$this->cache_view = $minutes;
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
	 * Cache la vista en views/layouts/
	 * correspondiente al controlador durante $minutes
	 *
	 * @param $minutes
	 */
	public function cache_layout($minutes){
		$this->cache_layout = $minutes;
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
	  * Hace el enrutamiento desde un controlador a otro, o desde
	  * una accin a otra.
	  *
	  * Ej:
	  *
	  * return $this->route_to("controller: clientes", "action: consultar", "id: 1");
	  *
	  */
	public function route_to(){
		Kumbia::$routed = false;
		$url = get_params(func_get_args());
		if($url['controller']){
			$_REQUEST['controller'] = $url['controller'];
			$_REQUEST['action'] = "index";
			Kumbia::$routed = true;
		}
		if($url['action']){
			$_REQUEST['action'] = $url['action'];
			Kumbia::$routed = true;
		}
		if($url['id']){
			$_REQUEST['id'] = $url['id'];
			Kumbia::$routed = true;
		}
		return null;
	}

}

?>
