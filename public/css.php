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
* TIPO DE GARANTIA; dejando atr�s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m�s detalles.
*
* Debes recibir una copia de la Licencia P�blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci�n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

/**
 * El objetivo de esta funci�n es reemplazar las variables @path, @img_path
 * @css_path en los archivos css para que busquen bien las rutas.
 *
 * Los archivos CSS son cacheados mientras no cambie la fecha de modificacion
 * de estos, en este caso vuelven a ser cacheados.
 *
 * Este archivo solo tiene funci�n cuando se envia el segundo parametro
 * a stylesheet_link_tag("ruta.css", true)
 */
if(isset($_GET['c'])){
	$css = $_GET['c'];
	if(file_exists("css/$css.css")){
		$cache_css = base64_encode($css);
		if(file_exists("temp/$cache_css")){
			if(filemtime("temp/$cache_css")>filemtime("css/$css.css")){
				header('Content-type: text/css');
				print file_get_contents("temp/$cache_css");
				exit;
			}
		}
		$css_content = file_get_contents("css/$css.css");
		$css_content = str_replace("@path", $_GET['p'], $css_content);
		$css_content = str_replace("@img_path", $_GET['p']."img", $css_content);
		$css_content = str_replace("@css_path", $_GET['p']."css", $css_content);
		header('Content-type: text/css');
		file_put_contents("temp/$cache_css", $css_content);
		print $css_content;
	}
}

?>