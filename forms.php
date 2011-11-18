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

/****************************************************************************
* Main Forms Module
****************************************************************************/
require_once "forms/config/main.php";
require_once "forms/generator/main.php";
require_once "forms/xml/main.php";
require_once "kumbia.php";


/**
 * El driver de Kumbia es cargado segun lo que diga en config.ini
 */
if(!dbBase::load_driver()){
	Flash::warning('No se pudo cargar el driver de la base de datos definido en forms/config/config.ini');
	return false;
}

/**
 * La lista de modulos en core.ini son cacheados en la variable de sesion
 * $_SESSION['KUMBIA_MODULES'] para no leer este archivo muchas veces
 * 
 * La variable extensiones en el apartado modules en forms/config/core.ini 
 * tiene valores estilo kumbia.tags,... esto hace q Kumbia cargue 
 * automaticamente en el directorio lib/kumbia el archivo tags.php.
 * 
 * Esta variable tambien puede ser utilizada para cargar modulos de
 * usuario y clases personalizadas
 * 
 * Chequee la función import() en este mismo archivo para encontrar una forma
 * alternativa para cargar modulos y clases de usuario en Kumbia
 * 
 */
if(!isset($_SESSION['KUMBIA_MODULES'])){
	$_SESSION['KUMBIA_MODULES'] = array();
}
if(!isset($_SESSION['KUMBIA_MODULES'][$_SESSION['KUMBIA_PATH']])){
	$_SESSION['KUMBIA_MODULES'][$_SESSION['KUMBIA_PATH']] = array();
	if($kumbia_config = Config::read('core.ini')){
		$kumbia_config->modules->extensions = str_replace(" ", "", $kumbia_config->modules->extensions);
		$extensions = explode(",", $kumbia_config->modules->extensions);
		foreach($extensions as $extension){
			$ex = explode(".", $extension);
			$ex[0] = escapeshellcmd($ex[0]);
			$ex[1] = escapeshellcmd($ex[1]);			
			$_SESSION['KUMBIA_MODULES'][$_SESSION['KUMBIA_PATH']][] = "lib/{$ex[0]}/{$ex[1]}.php";
		}
	}
}
foreach($_SESSION['KUMBIA_MODULES'][$_SESSION['KUMBIA_PATH']] as $kbmodule){
	require_once $kbmodule;
}

/**
 * Muestra el contenido acumulado en llamados anteriores
 * en las vistas
 *
 */
function content(){
	print Kumbia::$content;
}


?>