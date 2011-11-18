<?php

class BuilderController extends ApplicationController {

	public function before_filter(){
		$config = Config::read();
		if(!$config->project->interactive){
			$this->redirect("");
			return false;
		}
	}

	/**
	 * Crea un modelo base en models/
	 *
	 * @param string $name
	 * @param string $controller
	 * @param string $action
	 */
	public function create_model($name, $controller, $action){

		if(file_exists("models/$name.php")){
			Flash::error("Error: El modelo '$name' ya existe\n");
		} else {
			$model_name = str_replace(" ", "", ucwords(strtolower(str_replace("_", "", $name))));
			$file = "<?php\n			\n	class $model_name extends ActiveRecord {\n
	}\n	\n?>\n";
			file_put_contents("models/$name.php", $file);
			Flash::success("Se cre&oacute; correctamente el modelo '$name' en models/$name.php\n");
			$model = $name;
			require_once "models/$model.php";
			$objModel = str_replace("_", " ", $model);
			$objModel = ucwords($objModel);
			$objModel = str_replace(" ", "", $objModel);
			if(!class_exists($objModel)){
				throw new kumbiaException("No se encontr&oacute; la Clase \"$objModel\"",
				"Es necesario definir una clase en el modelo
							'$model' llamado '$objModel' para que esto funcione correctamente.");
				return false;
			} else {
				Kumbia::$models[$objModel] = new $objModel($model, false);
				Kumbia::$models[$objModel]->source = $model;
			}
			$this->route_to("controller: $controller", "action: $action");
		}
	}

	public function create_controller($controller, $action){
		$config = Config::read("core.ini");
		$file = strtolower($controller)."_controller.php";
		if(file_exists("{$config->kumbia->controller_dir}/$file")){
			Flash::error("Error: El controlador '$controller' ya existe\n");
		} else {
			if($this->post("kind")=="applicationcontroller"){
				$filec = "<?php\n			\n	class ".ucfirst($controller)."Controller extends ApplicationController {\n\n\t\tfunction $action(){\n\n\t\t}\n\n	}\n	\n?>\n";
				file_put_contents("{$config->kumbia->controller_dir}/$file", $filec);
			} else {
				$filec = "<?php\n			\n	class ".ucfirst($controller)."Controller extends StandardForm {\n\n\t\tpublic \$scaffold = true;\n\n\t\tpublic function __construct(){\n\n\t\t}\n\n	}\n	\n?>\n";
				file_put_contents("{$config->kumbia->controller_dir}/$file", $filec);
				$this->create_model($controller, $controller, "index");
			}
			Flash::success("Se cre&oacute; correctamente el controlador '$controller' en '{$config->kumbia->controller_dir}/$file'");
		}
		$this->route_to("controller: $controller", "action: $action");
	}

	public function index(){
		$this->redirect("");
	}

}


?>