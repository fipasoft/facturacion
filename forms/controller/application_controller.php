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
*************************************************************************/

/**
 * Es la clase principal para controladores de Kumbia
 *
 */
class ApplicationController extends ApplicationControllerBase  {

	private $cache_view = 0;
	private $cache_layout = 0;
	private $cache_template = 0;
	private $persistance = false;

	public $response = "";
	public $controller_name;
	public $action_name;
	public $id;
	static public $force = false;

	/**
	 * Visualiza una vista en el controlador actual
	 *
	 * @param string $view
	 */
	function render($view){		
		if(file_exists("views/{$_REQUEST['controller']}/$view.phtml")){
			if(is_array(kumbia::$models)){
				foreach(kumbia::$models as $model_name => $model){
				 	$$model_name = $model;
				}
			}
			foreach($this as $var => $value){
				$$var = $value;
			}			
			include "views/{$_REQUEST['controller']}/$view.phtml";
		} else {
			Flash::kumbia_error('<u>KumbiaError: No existe la Vista</u><br>
						  <span style="font-size:16px">Kumbia no puede encontrar la vista "'.$view.'"
						  </span>');
		}
	}

	/**
	 * Redirecciona la ejecución a otro controlador en un
	 * tiempo de ejecución determinado
	 *
	 * @param string $controller
	 * @param integer $seconds
	 */
	function redirect($controller, $seconds=0.5){
		$config = Config::read();
		$seconds*=1000;
		if(headers_sent()){
			print "
				<script type='text/javascript'> 					
					window.setTimeout(\"window.location='".KUMBIA_PATH."$controller'\", $seconds); 
				</script>\n";			    
		} else
		$xx = "Location: ".KUMBIA_PATH."$controller";
		header("Location: ".KUMBIA_PATH."$controller");
	}

	/**
	 * Visualiza un Texto en la Vista Actual
	 *
	 * @param string $text
	 */
	function render_text($text){
		print $text;
	}

	/**
	 * Visualiza una vista parcial en el controlador actual
	 *
	 * @param string $partial
	 */
	function render_partial($partial, $values = ''){
		render_partial($partial, $values);
	}

	/**
	 * Visualiza una acción ???
	 *
	 * @param string $action
	 */
	function render_action($action){

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
	 * una acción a otra.
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
	 * Devuelve true si el valor es alpha-numerico
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function is_alnum($value){
		return ctype_alnum($value);
	}

	/**
	 * Devuelve true si el valor es numerico
	 * false en lo contrario
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function is_numeric($value){
		return is_numeric($value);
	}

	/**
	 * Indica si un controlador va a ser persistente, en este
	 * caso los valores internos son automaticamente almacenados
	 * en sesion y disponibles cada vez que se ejecute una acción
	 * en el controlador
	 *
	 * @param boolean $value
	 */
	public function set_persistance($value){

		$this->persistance = $value;

	}


	/**
	 * Sube un archivo al directorio img/upload si esta en $_FILES
	 *
	 * @param string $name
	 * @return string 
	 */
	public function upload_image($name){
		if($_FILES[$name]){
			move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars("public/img/upload/{$_FILES[$name]['name']}"));
			return urlencode(htmlspecialchars("upload/".$_FILES[$name]['name']));
		} else return urlencode($this->request($name));
	}
	
	/**
	 * Sube un archivo al directorio $dir si esta en $_FILES
	 *
	 * @param string $name
	 * @return string 
	 */
	public function upload_file($name, $dir){
		if($_FILES[$name]){
			 return move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars("$dir/{$_FILES[$name]['name']}"));			
		} else return false;
	}

}


?>