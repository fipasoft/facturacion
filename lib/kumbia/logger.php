<?php

/** Kumbia - PHP Rapid Development Framework *****************************
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
*
*****************************************************************************
/**
 * Permite realizar logs en archivos de texto en la carpeta Logs
 * $fileLogger = Es el File Handle para escribir los logs
 * $transaction = Indica si hay o no transaccion
 * $quenue = array con lista de logs pendientes
 *
 * Ej:
 *
 * //Empieza un log en logs/logDDMMY.txt
 * $myLog = new Logger();
 *
 * $myLog->log("Loggear esto como un debug", Logger::DEBUG);
 *
 * //Esto se guarda al log inmediatamente
 * $myLog->log("Loggear esto como un error", Logger::ERROR);
 *
 * //Inicia una transacción
 * $myLog->begin();
 *
 * //Esto queda pendiente hasta que se llame a commit para guardar
 * //ó rollback para cancelar
 * $myLog->log("Esto es un log en la fila", Logger::WARNING)
 * $myLog->log("Esto es un otro log en la fila", Logger::WARNING)*
 *
 * //Se guarda al log
 * $myLog->commit();
 *
 * //Cierra el Log
 * $myLog->close();
 */
class Logger {

	private $fileLogger;
	private $transaction = false;
	private $quenue = array();

	const DEBUG = 1;
	const ERROR = 2;
	const WARNING = 3;
	const CUSTOM = 4;

	/**
 	 * Constructor del Logger
 	 */
	function Logger($name=''){
		if($name===''||$name===true){
			$name = 'log'.date('dmY').".txt";
		}
		$this->fileLogger = @fopen('logs/'.$name, "a");
		if(!$this->fileLogger){
			Flash :: error('KumbiaLogger: Cannot Open Log '.$name);
			return false;
		}
	}
	/**
 	 * Almacena un mensaje en el log
 	 *
 	 * @param string $msg
 	 */
	function log($msg, $type=self::DEBUG){
		if(!$this->fileLogger){
			error('KumbiaLogger: Cannot handle log on an invalid logger');
		}
		if(PHP_VERSION>=5.1) {
			$date = date(DATE_RFC1036);
		} else {
			$date = date("r");
		}
		switch($type){
			case self::DEBUG:
				$type = 'DEBUG';
				break;
			case self::ERROR:
				$type = 'ERROR';
				break;
			case self::WARNING:
				$type = 'WARNING';
				break;
			case self::CUSTOM :
				$type = 'CUSTOM';
				break;
			default:
				$type = 'CUSTOM';
		}
		if($this->transaction){
			$this->quenue[] = "[$date][$type] ".$msg."\n";
		} else {
			fputs($this->fileLogger, "[$date][$type] ".$msg."\n");
		}
	}

	/**
 	 * Inicia una transacción
 	 *
 	 */
	function begin(){
		$this->transaction = true;
	}

	/**
 	 * Deshace una transacción
 	 *
 	 */
	function rollback(){
		$this->transaction = false;
		$this->quenue = array();
	}

	/**
 	 * Commit a una transacción
 	 */
	function commit(){
		$this->transaction = false;
		foreach($this->quenue as $msg){
			$this->log($msg);
		}
	}

	/**
 	 * Cierra el Logger
 	 *
 	 */
	function close(){
		if(!$this->fileLogger){
			error('KumbiaLogger: Cannot handle log on an invalid logger');
		}
		return fclose($this->fileLogger);
	}

}

?>