<?php

/** Kumbia - PHP Rapid Development Framework *****************************
*	
* Copyright (C) 2005-2007 Andr�s Felipe Guti�rrez (andresfelipe at vagoogle.net)
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
* bajo los terminos de la licencia p�blica general GNU tal y como fue publicada
* por la Fundaci�n del Software Libre; desde la versi�n 2.1 o cualquier
* versi�n superior.
* 
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN 
* TIPO DE GARANTIA; sin dejar atr�s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m�s detalles.
* 
* Debes recibir una copia de la Licencia P�blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci�n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*****************************************************************************/

if(isset($_SERVER['SERVER_SOFTWARE'])){
	header('Location: index.php');
	exit;
}

require_once "forms/config/main.php";
require_once "forms/generator/main.php";
require_once "forms/xml/main.php";
require_once "kumbia.php";

if(!dbBase::load_driver()){
	Flash::warning('No se pudo cargar el driver de la base de datos definido en forms/config/config.ini');
	return false;
}

if($kumbia_config = Config::read('core.ini')){
	$kumbia_config->modules->extensions = str_replace(" ", "", $kumbia_config->modules->extensions);
	$extensions = explode(",", $kumbia_config->modules->extensions);
	foreach($extensions as $extension){
		$ex = explode(".", $extension);
		require_once "lib/{$ex[0]}/{$ex[1]}.php";
	}
}

$models = array();
foreach(scandir('models/') as $model){
	if(strpos($model, ".php")){
		require_once "models/$model";
		$model = str_replace(".php", "", $model);
		$objModel = str_replace("_", " ", $model);
		$objModel = ucwords($objModel);
		$objModel = str_replace(" ", "", $objModel);
		if(!class_exists($objModel)){
			throw new kumbiaException(
			"No se encontr&oacute; la Clase \"$objModel\"",
			"Es necesario definir una clase en el modelo
			'$model' llamado '$objModel' para que esto 
			funcione correctamente.");
			return false;
		} else {
			$$objModel = new $objModel($model, false);
			$$objModel->source = $model;
			$$objModel->source = $model;
			$models[] = $objModel;
		}
	}
}

function create_controller($a=''){
	if(!$a){
		print "Error: Debe especificar el nombre del Controlador!\n";
		return false;
	} else {
		if(file_exists("controllers/$a"."_controller.php")){
			print "Error: El controlador '$a' ya existe\n";
			return false;
		} else {
			$file = "<?php
			
	class ".ucwords(strtolower($a))."Controller extends ApplicationController {
	
	}
	
?>\n";		file_put_contents("controllers/$a"."_controller.php", $file);
			print "El controlador '$a' se cre� correctamente\n";
		}
	}
	return true;
}

function create_standardform($a=''){
	if(!$a){
		print "Error: Debe especificar el nombre del Controlador!\n";
		return false;
	} else {
		if(file_exists("controllers/$a"."_controller.php")){
			print "Error: El controlador '$a' ya existe\n";
			return false;
		} else {
			$file = "<?php
			
	class ".ucwords(strtolower($a))."Controller extends StandardForm {
	 
	       public \$scaffold = true;
	       
	}
	
?>\n";		file_put_contents("controllers/$a"."_controller.php", $file);
			print "El controlador '$a' se cre� correctamente\n";
		}
	}
	return true;
}


function create_model($a=''){
	if(!$a){
		print "Error: Debe especificar el nombre del Modelo!\n";
		return false;
	} else {
		if(file_exists("models/$a.php")){
			print "Error: El modelo '$a' ya existe\n";
			return false;
		} else {
			$file = "<?php
			
	class ".ucwords(strtolower($a))." extends ActiveRecord {
	
	}
	
?>\n";		file_put_contents("models/$a.php", $file);
			print "El modelo '$a' se cre� correctamente\n";
		}
	}
	return true;
}

$fp = fopen("php://stdin", "r");
print "Bienvenido al Kumbia 0.4 Interactivo\n";
print "Escribe 'exit' para salir\n\n";
print "iphp> ";
while($c = fgets($fp)){
	if(rtrim($c)=="quit"){
		exit;
	}
	try {
		if(trim($c)){
			$a = eval("return ".trim($c).";");
			if($a===null){
				print "NULL";
			} else {
				if($a===false){
					print "FALSE";
				} else {
					if($a===true){
						print "TRUE";
					} else {
						if(!is_object($a)){
							print_r($a);
						} else {
							print "Object Instance Of ".get_class($a);
						}
					}
				}
			}
			print "\niphp> ";
		} else {
			print "iphp> ";
		}
	}
	catch(kumbiaException $e){				
		print $e->getMessage()."\n";
		$i = 1;
		foreach($e->getTrace() as $trace){
			if($trace['class']){
				print "#$i {$trace['class']}::{$trace['function']}(".join(",",$trace['args']).") en ".basename($trace['file'])."\n";				
			}
			$i++;
		}
	}
	catch(dbException $e){		
		print $e->getMessage()."\n";
		$i = 1;
		foreach($e->getTrace() as $trace){
			if($trace['class']){
				print "#$i {$trace['class']}::{$trace['function']}(".join(",",$trace['args']).") en ".basename($trace['file'])."\n";				
			}
			$i++;
		}
	}
	catch(ActiveRecordException $e){		
		print $e->getMessage()."\n";
		$i = 1;
		foreach($e->getTrace() as $trace){
			if($trace['class']){
				print "#$i {$trace['class']}::{$trace['function']}(".join(",",$trace['args']).") en ".basename($trace['file'])."\n";				
			}
			$i++;
		}	
	}
}
fclose($fp);

?>
