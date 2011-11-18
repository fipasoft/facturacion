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
* TIPO DE GARANTIA; dejando atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
* 
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*****************************************************************************/

/**
 * Clase para la carga de Archivos .INI y de configuración
 *
 * Aplica el patrón Singleton que utiliza un array 
 * indexado por el nombre del archivo para evitar que 
 * un .ini de configuración sea leido más de una
 * vez en runtime con lo que aumentamos la velocidad.
 * 
 */
class Config {

	static private $instance = array();

	/**
	 * El constructor privado impide q la clase sea 
	 * instanciada y obliga a usar el metodo read
	 * para obtener la instancia del objeto
	 *
	 */
	private function __construct(){

	}

	/**
	 * Constructor de la Clase Config
	 *
	 * @return Config
	 */
	static public function read($file="config.ini"){

		if(isset(self::$instance[$file])){
			return self::$instance[$file];
		}

		$config = new Config();
		$file = escapeshellcmd($file);
		foreach(parse_ini_file('forms/config/'.$file, true) as $conf => $value){
			$config->$conf = new stdClass();
			foreach($value as $cf => $val){				
				$config->$conf->$cf = $val;
			}
		}

		if($file=="config.ini"){
			if(!isset($config->project->mode)){
				if(!isset($config->project)){
					$config->project = new stdClass();
				}
				$config->project->mode = "production";
			}

			//Carga las variables db del modo indicado
			if(isset($config->{$config->project->mode})){
				foreach($config->{$config->project->mode} as $conf => $value){
					if(ereg("([a-z0-9A-Z]+)\.([a-z0-9A-Z]+)", $conf, $registers)){
						if(!isset($config->{$registers[1]})){
							$config->{$registers[1]} = new stdClass();
						}
						$config->{$registers[1]}->{$registers[2]} = $value;
					} else {
						$config->$conf = $value;
					}
				}
			}

			//Carga las variables de [project]
			if(isset($config->project)){
				foreach($config->project as $conf => $value){
					if(ereg("([a-z0-9A-Z]+)\.([a-z0-9A-Z]+)", $conf, $registers)){
						if(!isset($config->{$registers[1]})){
							$config->{$registers[1]} = new stdClass();
						}
						$config->{$registers[1]}->{$registers[2]} = $value;
					} else {
						$config->$conf = $value;
					}
				}
			}
		}

		self::$instance[$file] = $config;
		return $config;
	}

}

?>
