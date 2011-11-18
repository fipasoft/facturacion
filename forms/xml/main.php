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

	class simpleXMLResponse {
	  	
	  	private $code;
	  	
	  	function simpleXMLResponse(){
		    $this->code.="<?xml version='1.0' encoding='iso8859-1'?>\r\n<response>\r\n";
		}
	  	  
	  	function addNode($arr){
		    $this->code.="\t<row ";
		    foreach($arr as $k => $v){
				$this->code.="$k='".$v."' ";	
			}
			$this->code.="/>\r\n";
			
		}
		
		function addData($val){
		   $this->code.="\t<data><![CDATA[$val]]></data>\n";	
		}
		
		function outResponse(){
		  	$this->code.="</response>";
		  	header('Content-Type: text/xml');
		  	header("Pragma: no-cache");
			header("Expires: 0");
		  	print $this->code;		  	
		}
	}

?>