<?php

/** KumbiaForms - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
* Copyright (C) 2007-2007 Roger José Padilla Camacho	(rogerjose81 at gmail.com)
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
 * MySQL Database Support
 ****************************************************************************
 * Estas funciones le permiten acceder a servidores de bases de datos MySQL.
 * Puede encontrar más información sobre MySQL en http://www.mysql.com/.
 * La documentación de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @link http://www.php.net/manual/es/ref.mysql.php
 * @access Public
 *****************************************************************************
 */
class db extends dbBase implements DbBaseInterface  {

	public $Id_Connection;
	public $lastResultQuery;
	private $lastQuery;
	private $dbUser;
	private $dbHost;
	private $dbPass;
	private $dbName;
	private $dbPort = 3306;
	private $dbDSN;
	public $lastError;

	const DB_ASSOC = MYSQL_ASSOC;
	const DB_BOTH = MYSQL_BOTH;
	const DB_NUM = MYSQL_NUM;

	/**
	 * Hace una conexión a la base de datos de MySQL
	 *
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbname
	 * @return resource_connection
	 */
	function connect($dbhost='', $dbuser='', $dbpass='', $dbname='', $dbport='', $dbdsn=''){

		if(!extension_loaded('mysql')){
			throw new dbException('Debe cargar la extensión de PHP llamada php_mysql');
			return false;
		}

		if(!$dbhost) $dbhost = $this->dbHost; else $this->dbHost = $dbhost;
		if(!$dbuser) $dbuser = $this->dbUser; else $this->dbUser = $dbuser;
		if(!$dbpass) $dbpass = $this->dbPass; else $this->dbPass = $dbpass;
		if(!$dbport) $dbport = $this->dbPort; else $this->dbPort = $dbport;
		if(!$dbdsn) $dbdsn = $this->dbDSN; else $this->dbDSN = $dbdsn;

		if($this->Id_Connection = @mysql_connect("{$this->dbHost}:{$this->dbPort}", $this->dbUser, $this->dbPass)){
			if($dbname!=='') {
				if(!mysql_select_db($dbname, $this->Id_Connection)){
					$this->lastError = $this->error();
					$this->log($this->lastError, Logger::ERROR);
					if($this->display_errors){
						throw new dbException($this->error(), false, mysql_errno());
					}
					return false;
				} else {
					$this->dbName = $dbname;
				}
			}
			return true;
		} else {
			$this->lastError = $this->error();
			$this->log($this->lastError, Logger::ERROR);
			if($this->display_errors){
				throw new dbException($this->error(), false, mysql_errno());
			}
			return false;
		}

	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param string $sqlQuery
	 * @return resource or false
	 */
	function query($sqlQuery){
		$this->debug($sqlQuery);
		$this->log($sqlQuery, Logger::DEBUG);
		if(!$this->Id_Connection){
			$this->connect();
			if(!$this->Id_Connection){
				return false;
			}
		}
		$this->lastQuery = $sqlQuery;
		if($resultQuery = mysql_query($sqlQuery)){
			$this->lastResultQuery = $resultQuery;
			return $resultQuery;
		} else {
			$this->log($this->error()." al ejecutar '$sqlQuery'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error();
			if($this->display_errors){
				throw new dbException($this->error()." al ejecutar <i>'$sqlQuery'</i>", true, mysql_errno());
			}
			return false;
		}
	}

	/**
	 * Cierra la Conexión al Motor de Base de datos
	 */
	function close(){
		if($this->Id_Connection) {
			mysql_close();
		}
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param resource $resultQuery
	 * @param integer $opt
	 * @return array
	 */
	function fetch_array($resultQuery='', $opt=''){
		if($opt==='') $opt = db::DB_BOTH;
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		return mysql_fetch_array($resultQuery, $opt);
	}

	/**
	 * Constructor de la Clase
	 */
	function __construct($dbhost=null, $dbuser=null, $dbpass=null, $dbname='', $dbport='', $dbdns=''){
		$this->connect($dbhost, $dbuser, $dbpass, $dbname, $dbport, $dbdsn);
	}

	/**
	 * Devuelve el numero de filas de un select
	 */
	function num_rows($resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = mysql_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->log($this->error(), Logger::ERROR);
			$this->lastError = $this->error();
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return string
	 */
	function field_name($number, $resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = mysql_field_name($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}


	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return boolean
	 */
	function data_seek($number, $resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($success = mysql_data_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			if($this->display_errors){
				throw new dbException($this->error(), true, mysql_errno());
			}
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	function affected_rows($resultQuery=''){
		if(!$this->Id_Connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = mysql_affected_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de MySQL
	 *
	 * @return string
	 */
	function error($err=''){
		if(!$this->Id_Connection){
			return mysql_error() ? mysql_error() : "[Error Desconocido en MySQL: $err]";
		}
		return mysql_error();
	}

	/**
	 * Devuelve el no error de MySQL
	 *
	 * @return integer
	 */
	function no_error(){
		if(!$this->Id_Connection){
			return false;
		}
		return mysql_errno();
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	function table_exists($table){
		$q = $this->query("show tables like '$table'");
		return $this->num_rows($q);
	}

}

?>