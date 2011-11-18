<?php 

/** Kumbia - PHP Rapid Development Framework *****************************
*	
* Copyright (C) 2005-2007 Andr�s Felipe Guti�rrez (andresfelipe at vagoogle.net)
* Copyright (C) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
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
* TIPO DE GARANTIA; dejando atr�s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m�s detalles.
* 
* Debes recibir una copia de la Licencia P�blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci�n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*****************************************************************************/
//Load Session
$config = parse_ini_file( '../forms/config/config.ini' );
session_name( sha1( $config[ 'name' ] ) );
session_register('queryTemp', 'dumps', 'session_data');
date_default_timezone_set('America/Mexico_City');

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
 
//Dispatch url from QueryString
$url_items = explode("/", $_REQUEST['url']);
$_REQUEST['controller'] = $url_items[0]; 
$_REQUEST['action'] = $url_items[1]; 
$_REQUEST['id'] = $url_items[2];
$_REQUEST['all_parameters'] = $url_items;

//En data_params quedan los valores de parametros por URL 
unset($url_items[0], $url_items[1]);
$_REQUEST['parameters'] = array_values($url_items);

$_REQUEST['controller'] = escapeshellcmd($_REQUEST['controller']);
$_REQUEST['action'] = escapeshellcmd($_REQUEST['action']);

$_POST['action'] = $_GET['action'] = $_REQUEST['action'];
$_POST['controller'] = $_GET['controller'] = $_REQUEST['controller'];

/**
 * Kumbia reinicia las variables de aplicaci�n cuando cambiamos 
 * entre una aplicaci�n y otra
 */
chdir('..');
$delete_session_cache = false;
$path = join(array_slice(split( "/" ,dirname($_SERVER['PHP_SELF'])),1,-1),"/");
if($path!=$_SESSION['KUMBIA_PATH']){
	$delete_session_cache = true;
}
$_SESSION['KUMBIA_PATH'] = $path;
if($_SESSION['KUMBIA_PATH']){
	define('KUMBIA_PATH', "/".$_SESSION['KUMBIA_PATH']."/"); 
} else {
	define('KUMBIA_PATH', "/"); 
}
require "forms.php";
if($delete_session_cache){
	Kumbia::$models = array();
	Kumbia::$controller = null;
}
Kumbia::main(); 

?>