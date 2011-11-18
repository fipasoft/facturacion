<?php

/** Kumbia - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
* Revised by César Caballero Gállego (phillipo at ccaballero.com)
* Revised by Roger Jose Padilla Camacho (rogerjose81 at gmail.com)
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
********************************************************************************/

/**
 * Clase standard para enviar advertencias, informacion y errores a la pantalla
 *
 * @access public
 */
class Flash {

	/**
	 * Visualiza un mensaje de error
	 *
	 * @param string $err
	 */
	public static function error($err, $include_style=false){
		if(isset($_SERVER['SERVER_SOFTWARE'])){
			if($include_style){
				stylesheet_link_tag('style');
			}
    		print '<div id="kumbiaDisplay" class="error_message">'.$err.'</div>'."\n";    		
	    } else {
			print strip_tags($err)."\n";
		}
	}

	/**
	 * Visualiza una alerta de Error JavaScript
	 *
	 * @param string $err
	 */
	public static function jerror($err){
        	formsPrint("\r\nalert(\"$err\")\r\n");
	}

	/**
	 * Visualiza informacion en pantalla
	 *
	 * @param string $msg
	 */
	public static function notice($msg){
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			print '<div id="kumbiaDisplay" class="notice_message">'.$msg.'</div>'."\n";    		
		} else {
			print strip_tags($msg)."\n";
		}
	}

	/**
	 * Visualiza informacion de Suceso en pantalla
	 *
	 * @param string $msg
	 */
	public static function success($msg){
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			print '<div id="kumbiaDisplay" class="sucess_message">'.$msg.'</div>'."\n";    		
		} else {
			print strip_tags($msg)."\n";
		}
	}

	/**
	 * Visualiza un mensaje de advertencia en pantalla
	 *
	 * @param string $msg
	 */
	public static function warning($msg){
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			print '<div id="kumbiaDisplay" class="warning_message">'.$msg.'</div>'."\n";    		
		} else {
			print strip_tags($msg)."\n";
		}
	}

	public static function interactive($msg){
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			print '<div id="kumbiaDisplay" class="interactive_message">'.$msg.'</div>'."\n";    		
		} else {
			print strip_tags($msg)."\n";
		}
	}

	public static function kumbia_error($what){
		self::error('<u>KumbiaError:</u> '.$what);
	}
}

class kumbiaException extends Exception {

	public $message;
	public $extended_message;

	/**
	 * Constructor de la clase;
	 *
	 */
	public function __construct($message, $extended_message=''){
		$this->message = $message;
		$this->extended_message = $extended_message;
		parent::__construct($message);
	}

	public function show_message(){
		Flash::error("
		<span style='font-size:24px'>KumbiaException: $this->message</span><br/>
		<div>$this->extended_message<br>
		<span style='font-size:12px'>En el archivo <i>{$this->getFile()}</i> en la l&iacute;nea: <i>{$this->getLine()}</i></span></div>", true);
		print "<pre style='border:1px solid #969696; background: #FFFFE8'>";
		print $this->getTraceAsString()."\n";
		print "</pre>";
	}

}


?>
