<?php

/** KumbiaForms - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
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
* bajo los terminos de la licencia pï¿½blica general GNU tal y como fue publicada
* por la Fundaci&oacute;n del Software Libre; desde la versi&oacute;n 2.1 o cualquier
* versi&oacute;n superior.
*
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; sin dejar atrï¿½s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para mï¿½s detalles.
*
* Debes recibir una copia de la Licencia Pï¿½blica General GNU junto con este
* framework, si no es asi, escribe a Fundaciï¿½n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

/******************************************************************************
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 *
 ******************************************************************************/
abstract class Kumbia {

	public $config;
	public $data = array();
	static private $persistence = array();
	static public $content = "";
	static public $smarty_object = "";
	static public $routed = true;
	static public $models = array();
	static public $controller;
	static public $db;

	/**
	 * Funci&oacute;n Principal donde se ejecutan los controladores
	 *
	 * @return boolean
	 */
	static function main(){
		require "controllers/application.php";
		require "forms/controller/application_controller.php";
		require "forms/controller/standard_form_controller.php";
		require "forms/controller/grid_form_controller.php";
		require "forms/controller/master_detail_form_controller.php";
		require "forms/controller/ajax_form_controller.php";
		require "forms/session/main.php";
		$_REQUEST['controller'] = str_replace("/", "", $_REQUEST['controller']);
		$_REQUEST['controller'] = str_replace("\\", "", $_REQUEST['controller']);
		$_REQUEST['controller'] = str_replace("..", "", $_REQUEST['controller']);
		if($_REQUEST['action']==''){
			$_REQUEST['action'] = "index";
		}
		try {
			if(!$_REQUEST['controller']){
				$app = new ApplicationController();
				if(method_exists($app, 'init')){
					$app->init();
				} else {
					if(method_exists($app, 'not_found')){
						$app->not_found($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['id']);
					} else {
						throw new kumbiaException("No se encontr&oacute; la Acci&oacute;n por defecto \"init\"",
						"Es necesario definir un m&eacute;todo en la clase controladora
						'ApplicationController' llamado 'init' para que esto funcione correctamente.");
						return false;
					}
				}
			}
			if($_REQUEST['controller']){
				self::if_routed();
				if($_SESSION['session_data']){
					if(!is_array($_SESSION['session_data'])){
						$_SESSION['session_data'] = unserialize($_SESSION['session_data']);
					}
				}
				self::$persistence = array();
				foreach(scandir('models/') as $model){
					if(ereg('^[[:alpha:]]+.php$', $model)){
						$model = escapeshellcmd($model);
						require_once "models/$model";
						$model = str_replace(".php", "", $model);
						$objModel = str_replace("_", " ", $model);
						$objModel = ucwords($objModel);
						$objModel = str_replace(" ", "", $objModel);
						if(!class_exists($objModel)){
							throw new kumbiaException("No se encontr&oacute; la Clase \"$objModel\"",
							"Es necesario definir una clase en el modelo
							'$model' llamado '$objModel' para que esto funcione correctamente.");
							return false;
						} else {
							self::$models[$objModel] = new $objModel($model, false);
							if(!is_subclass_of(self::$models[$objModel], "ActiveRecord")){
								throw new kumbiaException("Error inicializando modelo \"$objModel\"",
								"El modelo '$model' debe ser una clase o sub-clase de ActiveRecord");
								return false;
							}
							self::$models[$objModel]->source = $model;
							if(isset(self::$models[$objModel]->persistent)){
								if(self::$models[$objModel]->persistent){
									self::$persistence[] = $objModel;
								}
							}
							self::$models[$objModel]->source = $model;

						}
					}
				}
				foreach(self::$persistence as $p){
					if(Session::get_data($p)){
						self::$models[$p] = Session::get_data($p);
					}
				}
				ob_start();
				while(self::$routed){
					self::$routed = false;
					if(file_exists("controllers/".$_REQUEST['controller']."_controller.php")||
					   $_REQUEST['controller']=='builder'){
					   	/**
					   	 * El controlador builder es llamado automaticamente
					   	 * desde el core del framework
					   	 */
					   	if($_REQUEST['controller']=='builder'){
							require_once "forms/controller/builder_controller.php";
						}
						$appController = $_REQUEST['controller']."Controller";
						if(!class_exists($appController)){
							require "controllers/".$_REQUEST['controller']."_controller.php";
						}
						if(class_exists(ucwords($_REQUEST['controller'])."Controller")) {
							if(!isset($_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController])||eval("return $appController::\$force;")){
								self::$controller = new $appController();
							} else {
								self::$controller = unserialize($_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController]);
							}
							$_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController] = serialize(self::$controller);
							self::$controller->response = "";
							self::$controller->controller_name = $_REQUEST['controller'];
							self::$controller->action_name = $_REQUEST['action'];
							self::$controller->id = $_REQUEST['id'];
							self::$controller->all_parameters = $_REQUEST['all_parameters'];
							self::$controller->parameters = $_REQUEST['parameters'];
							foreach(self::$models as $model_name => $model){
								self::$controller->{$model_name} = $model;
							}
							/**
					 		 * El metodo before_filter es llamado antes de ejecutar una accion en un
					 		 * controlador, puede servir para realizar ciertas validaciones
					 		 */
							if(method_exists($appController, "before_filter")){
								if(self::$controller->before_filter($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['id'])===false){
									continue;
								}
							} else {
								if(isset(self::$controller->before_filter)){
									if(self::$controller->{self::$controller->before_filter}($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['id'])===false){
										continue;
									}
								}
							}
							if(!method_exists(self::$controller, $_REQUEST['action'])){
								if(method_exists(kumbia::$controller, 'not_found')){
									kumbia::$controller->route_to('action: not_found');
									continue;
								} else {
									throw new kumbiaException(
									"No se encontr&oacute; la Acci&oacute;n \"{$_REQUEST['action']}\"",
									"Es necesario definir un m&eacute;todo en la clase
										controladora '{$_REQUEST['controller']}' llamado
										'{$_REQUEST['action']}' para que esto funcione correctamente.");
									return false;
								}
							}
							/**
							 * Cuando una acci&oacute;n retorna un valor diferente de Nulo
						 	 * Kumbia autom&aacute;ticamente crea una salida XML
						 	 * para la acci&oacute;n utilizando un CDATA, es muy
						 	 * &uacute;til en conjunto a la funciï¿½n JavaScript
						 	 * AJAX.query
						 	 */
							$value_returned = call_user_func_array(
								array(self::$controller, $_REQUEST['action']),
								$_REQUEST['parameters']);
							if(!is_null($value_returned)){
								$xml = new simpleXMLResponse();
								$xml->addData($value_returned);
								$xml->outResponse();
								self::$controller->set_response('xml');
							}
							foreach(self::$models as $model_name => $model){
								unset(self::$controller->{$model_name});
							}
							if(isset($_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController])){
								$_SESSION['KUMBIA_CONTROLLERS'][$_SESSION['KUMBIA_PATH']][$appController] = serialize(self::$controller);
							}
							foreach(self::$models as $model_name => $model){
								$$model_name = $model;
							}
							if(is_subclass_of(self::$controller, "ApplicationController")){
								foreach(self::$controller as $var => $value) {
									$$var = $value;
								}
							}
							foreach(self::$persistence as $p){
								Session::set_data($p, kumbia::$models[$p]);
							}
							/**
					 		 * El metodo after_filter es llamado despues de ejecutar una accion en un
					 		 * controlador, puede servir para realizar ciertas validaciones
					 		 */
							if(method_exists($appController, "after_filter")){
								self::$controller->after_filter($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['id']);
							} else {
								if(isset(self::$controller->after_filter)){
									self::$controller->{self::$controller->after_filter}($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['id']);
								}
							}
						} else {
							throw new kumbiaException("
							No se encontr&oacute; el Clase Controladora \"{$_REQUEST['controller']}Controller\"",
							"Debe definir esta clase para poder trabajar este controlador");
							return false;
						}
					} else {
						ApplicationController :: redirect('sesion/restringir');
						$config = Config::read();
						if($config->project->interactive){
							InteractiveBuilder::create_controller($_REQUEST['controller'], $_REQUEST['action']);
						}
						/*throw new kumbiaException("
						No se encontr&oacute; el Controlador \"{$_REQUEST['controller']}\"",
						"Hubo un problema al cargar el controlador, probablemente
						el archivo no exista en el directorio de m&oacute;dulos &oacute; exista algun error
						de sintaxis. Si desconoce la naturaleza
						de este mensaje consulte con el administrador del sistema");*/
						return false;
					}
				}

				/**
				* Kumbia busca un los templates correspondientes al nombre de la accion y el layout
				* del controlador. Si el controlador tiene un atributo $template tambien va a
				* cargar la vista ubicada en layouts con el valor de esta
				*
				* en views/$controller/$action
				* en views/layouts/$controller
				* en views/layouts/$template
				*
				* Los archivos con extension .phtml son archivos template de kumbia que
				* tienen codigo html y php y son el estandar, tambien pueden tener
				* extensiï¿½n .tpl, en este caso, Kumbia hace la integracion con Smarty si
				* este esta disponible.
				*
				*/
				Kumbia::$content = ob_get_contents();
				/**
			 	* Verifica si existe cache para el layout, vista ï¿½ template
		 		* sino, crea un directorio en cache
		 		*/
				if($_REQUEST['controller']){
					if(self::$controller->get_view_cache()||
					self::$controller->get_layout_cache()){
						$view_cache_dir = "cache/".session_id()."/";
						if(!file_exists("cache/".session_id()."/")){
							mkdir($view_cache_dir);
						}
						$view_cache_dir.=$_REQUEST['controller'];
						if(!file_exists($view_cache_dir)){
							mkdir($view_cache_dir);
						}
					}
					if(self::$controller->response!='xml'){
						if(file_exists("views/{$_REQUEST['controller']}/{$_REQUEST['action']}.phtml")){
							ob_clean();
							/**
							 * Aqui verifica si existe un valor en minutos para el cache
				 			 */
							if(self::$controller->get_view_cache()){
								/**
					 			 * Busca el archivo en el directorio de cache que se crea
					 			 * a partir del valor $_SESSION['SID'] para que sea ï¿½nico
					 			 * para cada sesiï¿½n
					 			 */
								if(!file_exists($view_cache_dir."/{$_REQUEST['action']}")){
									include "views/{$_REQUEST['controller']}/{$_REQUEST['action']}.phtml";
									file_put_contents($view_cache_dir."/{$_REQUEST['action']}", ob_get_contents());
								} else {
									include $view_cache_dir."/{$_REQUEST['action']}";
								}
							} else {
								include "views/{$_REQUEST['controller']}/{$_REQUEST['action']}.phtml";
							}
							Kumbia::$content = ob_get_contents();
						} else {
							if(defined('USE_SMARTY')){
								if(file_exists("views/{$_REQUEST['controller']}/{$_REQUEST['action']}.tpl")){
									if(!Kumbia::$smarty_object){
										self::kumbia_smarty(self::$controller);
									}
									foreach(self::$controller as $_key => $_value){
										Kumbia::$smarty_object->assign($_key, $_value);
									}
									foreach(self::$models as $_model_name => $_model){
										Kumbia::$smarty_object->assign($_model_name, $_model);
									}
									Kumbia::$smarty_object->display($_REQUEST['action'].".tpl");
									Kumbia::$content = ob_get_contents();
								}

							}
						}
					}
					if(self::$controller->response!='xml'&&self::$controller->response!='view'){
						if(self::$controller->template){
							/**
				 			 * Aqui verifica si existe un valor en minutos para el cache
				 			 */
							if(file_exists("views/layouts/".self::$controller->template.".phtml")){
								ob_clean();
								if(self::$controller->get_layout_cache()){
									/**
					   				 * Busca el archivo en el directorio de cache que se crea
					 	 			 * a partir del valor session_id() para que sea ï¿½nico
					 	 			 * para cada sesiï¿½n
					 	 			 */
									if(!file_exists($view_cache_dir."/layout")){
										self::$controller->template = escapeshellcmd($controller->template);
										include "views/layouts/".self::$controller->template.".phtml";
										file_put_contents($view_cache_dir."/layout", ob_get_contents());
									} else {
										include $view_cache_dir."/{$_REQUEST['action']}";
									}
								} else {
									$controller->template = escapeshellcmd($controller->template);
									include "views/layouts/".self::$controller->template.".phtml";
								}
								Kumbia::$content = ob_get_contents();
							}
						}
					}
					if((self::$controller->response!='xml')&&(self::$controller->response!='view')){
						if(file_exists("views/layouts/{$_REQUEST['controller']}.phtml")){
							ob_clean();
							if(self::$controller->get_layout_cache()){
								/**
				 				 * Busca el archivo en el directorio de cache que se crea
				 	 			 * a partir del valor session_id() para que sea ï¿½nico
				 	 			 * para cada sesiï¿½n
				 	 			 */
								if(!file_exists($view_cache_dir."/layout")){
									include "views/layouts/{$_REQUEST['controller']}.phtml";
									file_put_contents($view_cache_dir."/layout", ob_get_contents());
								} else {
									include $view_cache_dir."/layout";
								}
							} else {
								include "views/layouts/{$_REQUEST['controller']}.phtml";
							}
							Kumbia::$content = ob_get_contents();
						}
					}
				}
				if((self::$controller->response!='view')&&(self::$controller->response!='xml')){
					if(file_exists("views/index.phtml")){
						ob_clean();
						include "views/index.phtml";
						Kumbia::$content = ob_get_contents();
					}
					if($_SESSION['session_data']){
						$_SESSION['session_data'] = serialize($_SESSION['session_data']);
					}
					foreach(self::$persistence as $p){
						if(Session::get_data($p)){
							Session::get_data($p, self::$models[$p]);
						}
					}
					ob_end_flush();
					self::$controller = null;
				}
			}
		}
		catch(kumbiaException $e){
			if($_SESSION['session_data']){
				$_SESSION['session_data'] = serialize($_SESSION['session_data']);
			}
			foreach(self::$persistence as $p){
				if(Session::get_data($p)){
					Session::get_data($p, self::$models[$p]);
				}
			}
			/**
			 * Si no es una Accion AJAX incluye index.phtml y muestra el contenido de las excepciones
			 * dentro de este.
			 */
			if((self::$controller->response!='view')&&(self::$controller->response!='xml')){
				$e->show_message();
				Kumbia::$content = ob_get_contents();
				if(file_exists("views/index.phtml")){
					ob_clean();
					include "views/index.phtml";
					Kumbia::$content = ob_get_contents();
				}
				ob_end_flush();
			} else {
				$e->show_message();
			}
			return;
		}
		catch(dbException $e){
			if($_SESSION['session_data']){
				$_SESSION['session_data'] = serialize($_SESSION['session_data']);
			}
			foreach(self::$persistence as $p){
				if(Session::get_data($p)){
					Session::get_data($p, self::$models[$p]);
				}
			}
			$e->show_message();
			return;
		}
		catch(ActiveRecordException $e){
			if($_SESSION['session_data']){
				$_SESSION['session_data'] = serialize($_SESSION['session_data']);
			}
			foreach(self::$persistence as $p){
				if(Session::get_data($p)){
					Session::get_data($p, self::$models[$p]);
				}
			}
			$e->show_message();
			return;
		}
	}

	/**
 	 * Busca en la tabla de entutamiento si hay una ruta en forms/config/routes.ini
 	 * para el controlador, accion, id actual
     *
     */
	static function if_routed(){
		unset($_SESSION['KUMBIA_STATIC_ROUTES']);
		if(!isset($_SESSION['KUMBIA_STATIC_ROUTES'])){
			$routes = Config::read('routes.ini');
			if(isset($routes->routes)){
				foreach($routes->routes as $source => $destination){
					if(count(explode("/", $source))!=3||count(explode("/", $destination))!=3){
						Flash::warning("Pol&iacute;tica de enrutamiento invalida '$source' a '$destination' en forms/config/routes.ini");
					} else {
						list($controller_source,
						$action_source,
						$id_source) = explode("/", $source);
						list($controller_destination,
						$action_destination,
						$id_destination) = explode("/", $destination);
						if(($controller_source==$controller_destination)&&
						($action_source==$action_destination)&&
						($id_source==$id_destination)){
							Flash::warning("Politica de enrutamiento ciclica de '$source' a '$destination' en forms/config/routes.ini");
						} else {
							$_SESSION['KUMBIA_STATIC_ROUTES'][$controller_source][$action_source][$id_source] =
							array("controller" => $controller_destination,
							"action" => $action_destination,
							"id" => $id_destination);
						}
					}
				}
			}
		}
		$new_route = array("controller" => '*', "action" => '*', "id" => '*');
		if(isset($_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['*']][$_REQUEST['action']]['*'])){
			$new_route = $_SESSION['KUMBIA_STATIC_ROUTES']['*'][$_REQUEST['action']]['*'];
		}
		if(isset($_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']]['*']['*'])){
			$new_route = $_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']]['*']['*'];
		}
		if(isset($_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']]['*'][$_REQUEST['id']])){
			$new_route = $_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']]['*'][$_REQUEST['id']];
		}
		if(isset($_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']][$_REQUEST['action']]['*'])){
			$new_route = $_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']][$_REQUEST['action']]['*'];
		}
		if(isset($_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']][$_REQUEST['action']][$_REQUEST['id']])){
			$new_route = $_SESSION['KUMBIA_STATIC_ROUTES'][$_REQUEST['controller']][$_REQUEST['action']][$_REQUEST['id']];
		}
		if($new_route['controller']!='*'){
			$_REQUEST['controller']	= $_POST['controller'] = $_GET['controller'] = $new_route['controller'];
		}
		if($new_route['action']!='*'){
			$_REQUEST['action']	= $_POST['action'] = $_GET['action'] = $new_route['action'];
		}
		if($new_route['id']!='*'){
			$_REQUEST['id']	= $_POST['id'] = $_GET['id'] = $new_route['id'];
		}
		return;
	}

	/**
	 * Esta funci&oacute;n realiza la integraci&oacute;n con Smarty, creando los
	 * directorios necesarios para que un controlador pueda
	 * utlizar templates Smarty
	 *
	 * @param $controllerObj
	 */
	static function kumbia_smarty($controllerObject){
		if(!Kumbia::$smarty_object){
			foreach($controllerObject as $property => $value){
				if(@get_class($value)=="Smarty"){
					Kumbia::$smarty_object = $value;
				}
			}
		}
		if(!Kumbia::$smarty_object){
			Kumbia::$smarty_object = new Smarty();
		}
		if(!file_exists("cache/{$_REQUEST['controller']}")){
			mkdir("cache/{$_REQUEST['controller']}");
			mkdir("cache/{$_REQUEST['controller']}/compile");
			mkdir("cache/{$_REQUEST['controller']}/cache");
			mkdir("cache/{$_REQUEST['controller']}/config");
		}
		Kumbia::$smarty_object->template_dir = "views/{$_REQUEST['controller']}/";
		Kumbia::$smarty_object->compile_dir = "cache/{$_REQUEST['controller']}/compile";
		Kumbia::$smarty_object->cache_dir = "cache/{$_REQUEST['controller']}/cache";
		Kumbia::$smarty_object->config_dir = "cache/{$_REQUEST['controller']}/config";
	}

	/**
	 * Verifica si $model es un modelo del Proyecto
	 *
	 * @param string $model
	 * @return boolean
	 */
	static function is_model($model){

		if($model==''){
			return false;
		}

		return isset(self::$models[self::get_model_name($model)]);

	}

	/**
	 * Devuelve el nombre de modelo de la entidad $model
	 *
	 * @param string $model
	 * @return string
	 */
	static function get_model_name($model){

		if($model==''){
			return false;
		}

		$objModel = str_replace("_", " ", $model);
		$objModel = ucwords($objModel);
		$objModel = str_replace(" ", "", $objModel);

		return $objModel;

	}

	/**
	 * Carga Librerias JavaScript Importantes en el Framework
	 *
	 */
	static function javascript_base(){
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/scriptaculous/prototype.js'></script>\r\n";
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/scriptaculous/effects.js'></script>\r\n";
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/base.js'></script>\r\n";
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/validations.js'></script>\r\n";
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/main.php?path=".urlencode(KUMBIA_PATH)."&controller={$_REQUEST['controller']}&action={$_REQUEST['action']}&id={$_REQUEST['id']}'></script>\r\n";
	}

	/**
	 * Carga Librerias JavaScript Windows
	 *
	 */
	static function javascript_windows(){
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/scriptaculous/window.js'></script>\r\n";
		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/scriptaculous/window_effects.js'></script>\r\n";

		print "<script type='text/javascript' src='".KUMBIA_PATH."javascript/scriptaculous/debug.js'></script>\r\n";

	}



	/**
	 * Carga otras librerias para realizar efectos en JavaScript
	 *
	 */
	static function javascript_use_drag(){
		print "\t<script src='".KUMBIA_PATH."javascript/scriptaculous/unittest.js' type='text/javascript'></script>\r\n";
		print "\t<script src='".KUMBIA_PATH."javascript/scriptaculous/scriptaculous.js' type='text/javascript'></script>\r\n";
	}

	/**
	 * Enruta el controlador actual a otro controlador,
	 * ï¿½ otra acci&oacute;n
	 * Ej:
	 * kumbia::route_to("controller: nombre", ["action: accion"], ["id: id"])
	 *
	 * @return null
	 */
	static function route_to(){
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
	 * Metodo que muestra informaci&oacute;n del Framework y la licencia
	 *
	 */
	static function info(){
		$config = Config::read();
		self::javascript_base();
		self::javascript_windows();

		print stylesheet_link_tag("../themes/default");
		print stylesheet_link_tag("../themes/mac_os_x");

		print "<body bgcolor='#6699CC'>
		<script type='text/javascript'>
		new Event.observe(window, \"load\", function(){
			var welcomeWindow = new Window(
				{
					className: \"mac_os_x\",
					width: 700,
					height: 500,
					zIndex: 100,
					resizable: true,
					title: \"Bienvenido a Kumbia\",
					showEffect: Effect.Appear,
					hideEffect: Effect.SwitchOff,
					draggable:true
				}
			)
			welcomeWindow.setHTMLContent($('content').innerHTML)
			welcomeWindow.showCenter()
		})
		</script>";
		print "
		<div style='display:none' id='content'>
	    <div style='font-family:Lucida Grande,Verdana;color:#2C2C2C;font-size:32px'>
	    Bienvenido a Kumbia</div>
	    <div style='font-family:\"Lucida Grande\",Verdana;font-size:14px; padding:10px'>
	    Ya puedes empezar a usar el mejor framework para desarrollar aplicaciones web con php.<br/><br/>
	    Kumbia es un web framework libre escrito en PHP5. Basado en las mejores pr&aacute;cticas
	    de desarrollo web, usado en software comercial y educativo, Kumbia fomenta la velocidad
	    y eficiencia en la creaci&oacute;n y mantenimiento de aplicaciones web, reemplazando tareas de
	    codificaci&oacute;n repetitivas por poder, control y placer. <br><br>
	    Si ha visto a Ruby-Rails/Python-Django encontrara a Kumbia una alternativa para proyectos en PHP con caracter&iacute;sticas como: <br>
<ul>
<li>Sistema de Plantillas sencillo
<li>Administración de Cache
<li>Scaffolding Avanzado
<li>Modelo de Objetos y Separación MVC
<li>Soporte para AJAX
<li>Generaci&oacute;n de Formularios
<li>Componentes Gráficos
<li>Seguridad
</ul>
y muchas cosas m&aacute;s. Kumbia puede ser la soluci&oacute;n que esta buscando. <br><br>

El n&uacute;mero de prerrequisitos para instalar y configurar es muy peque&ntilde;o, apenas Unix o Windows con un servidor web y PHP5 instalado. Kumbia es compatible con motores de base de datos como MySQL, PostgreSQL y Oracle. <br><br>
Usar Kumbia es f&aacute;cil para personas que han usado PHP y han trabajado patrones de dise&ntilde;o para aplicaciones de Internet cuya curva de aprendizaje está reducida a un d&iacute;a. El dise&ntilde;o limpio y la f&aacute;cil lectura del c&oacute;digo se facilitan con Kumbia. Desarrolladores pueden aplicar principios de desarrollo como DRY, KISS &oacute; XP, enfoc&aacute;ndose en la l&oacute;gica de aplicaci&oacute;n y dejando atr&aacute;s otros detalles que quitan tiempo.<br><br>
Kumbia intenta proporcionar facilidades para construir aplicaciones robustas para entornos comerciales. Esto significa que el framework es muy flexible y configurable. Al escoger Kumbia esta apoyando un proyecto libre publicado bajo licencia GNU/GPL.
	    <br/><br/>
        Para iniciar edite el archivo <b>forms/config/config.ini</b>
		</div>
		</div></body>";
	}

	static function scandir_recursive($package_dir, $files=array()){
		foreach(scandir($package_dir) as $file){
			if($file!='.'&&$file!='..'){
				if(is_dir($package_dir."/".$file)){
					$files = self::scandir_recursive($package_dir."/".$file, $files);
				} else {
					if(ereg("(.)+\.php$", $file)){
						$files[] = $package_dir."/".$file;
					}
				}
			}
		}
		return $files;
	}

	static function import($package){

		$package_array = explode(".", $package);
		$package_dir = "";
		$class = "";

		if($package_array[count($package_array)-1]=='*'){
			unset($package_array[count($package_array)-1]);
			$package_dir = join(".", $package_array);
			$class = '*';
		} else {
			$package_dir = $package;
		}

		if($class=='*'){
			$package_dir = str_replace('.', '/', $package_dir);
			if(!file_exists($package_dir)){
				throw new kumbiaException("No existe el directorio '$package_dir'\n");
			}
			$files = self::scandir_recursive($package_dir);
			foreach($files as $file){
				$file = escapeshellcmd($file);
				include_once $file;
			}
		} else {
			$package_dir = str_replace('.', '/', $package_dir);
			if(file_exists($package_dir.'.php')){
				$package_dir = escapeshellcmd($package_dir);
				include_once $package_dir.'.php';
			} else {
				throw new kumbiaException("No existe el directorio '$package_dir'\n");
			}
		}
	}

}

?>