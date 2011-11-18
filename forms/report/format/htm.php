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

$config = Config::read();
$file = md5(uniqid());

$content = "
<html>
 <head>
   <title>REPORTE DE ".strtoupper($title)."</title>
 </head>
 <body bgcolor='white'>
 <div style='font-size:20px;font-family:Verdana;color:#000000'>".strtoupper($config->project->name)."</div>\n
 <div style='font-size:18px;font-family:Verdana;color:#000000'>REPORTE DE ".strtoupper($title)."</div>\n 
 <div style='font-size:18px;font-family:Verdana;color:#000000'>".date("Y-m-d")."</div>\n  
 <br/>
 <table cellspacing='0' border=1 style='border:1px solid #969696'>
 ";
$content.= "<tr bgcolor='#F2F2F2'>\n";
for($i=0;$i<=count($headerArray)-1;$i++){
	$content.= "<th style='font-family:Verdana;font-size:12px'>".$headerArray[$i]."</th>\n";
}
$content.= "</tr>\n";

$l = 5;
foreach($result as $row){
	$content.= "<tr bgcolor='white'>\n";
	for($i=0;$i<=count($row)-1;$i++){		
		if(is_numeric($row[$i])){
			$content.= "<td style='font-family:Verdana;font-size:12px' align='center'>{$row[$i]}</td>";
		} else {
			$content.= "<td style='font-family:Verdana;font-size:12px'>{$row[$i]}&nbsp;</td>";
		}
	}
	$content.= "</tr>\n";
	$l++;
}

file_put_contents("public/temp/$file.html", $content);

if(isset($raw_output)){
	print "<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".html', null);  </script>";
} else {
	Generator::forms_print("<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".html', null);  </script>");
}


?>