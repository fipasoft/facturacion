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

session_register("rsa_key");
	
function generateRSAKey($kumbia){	;
  	$h = date("G")>12 ? 1 : 0;
 	$time = uniqid().mktime($h, 0, 0, date("m"), date("d"), date("Y"));
  	$key = sha1($time);
  	$_SESSION['rsa_key'] = $key;  
  	$xCode = "<input type='hidden' id='rsa32_key' value='$key' />\r\n";
  	if($kumbia)
  		formsPrint($xCode);
  	else
  		return $xCode;
  	return "";
}

function createSecureRSAKey($kumbia=true){  
 $config = Config::read('core.ini');
 if($config->kumbia->secure_ajax){
  if($_SESSION['rsa_key']){
   if((time()%8)==0){    
     return generateRSAKey($kumbia);
   } else {
   	if($kumbia)  	
   		formsPrint("<input type='hidden' id='rsa32_key' value=\"{$_SESSION['rsa_key']}\"/> \r\n");
   	else
 		print "<input type='hidden' id='rsa32_key' value=\"{$_SESSION['rsa_key']}\"/> \r\n";  		
   }
  } else {
    return generateRSAKey($kumbia);
  }
 }
 return null;
}

?>