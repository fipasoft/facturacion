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

/**
 * Oracle Database Support
 * 
 * Estas funciones le permiten acceder a servidores de bases de datos Oracle. 
 * Puede encontrar más información sobre Oracle en http://www.oracle.com/.
 * La documentación de Oracle puede encontrarse en http://www.oracle.com/technology/documentation/index.html. 
 *
 * @link http://www.php.net/manual/es/ref.oci8.php 
 * @access Public
 * 
 */
class db extends dbBase implements DbBaseInterface  {

	public $Id_Connection;
	public $lastResultQuery;
	private $lastQuery;
	private $dbUser;
	private $dbHost;
	private $dbPass;
	private $dbName;
	private $dbPort;
	private $dbDSN;
	private $autocommit = false;
	public $lastError;
	private $num_rows = false;

	const DB_ASSOC = OCI_ASSOC;
	const DB_BOTH = OCI_BOTH;
	const DB_NUM = OCI_NUM;

	/**
	 * Hace una conexión a la base de datos de Oracle
	 *
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbname
	 * @return resource_connection
	 */
	function connect($dbhost='', $dbuser='', $dbpass='', $dbname='', $dbport='', $dbdsn=''){

		if(!extension_loaded('oci8')){
			throw new dbException('Debe cargar la extensión de PHP llamada php_oci8');
			return false;
		}

		if(!$dbhost) $dbhost = $this->dbHost; else $this->dbHost = $dbhost;
		if(!$dbuser) $dbuser = $this->dbUser; else $this->dbUser = $dbuser;
		if(!$dbpass) $dbpass = $this->dbPass; else $this->dbPass = $dbpass;
		if(!$dbname) $dbname = $this->dbName; else $this->dbName = $dbname;
		if(!$dbport) $dbport = $this->dbPort; else $this->dbPort = $dbport;
		if(!$dbdsn) $dbdsn = $this->dbDSN; else $this->dbDSN = $dbdsn;

		if($this->Id_Connection = @oci_pconnect($this->dbUser, $this->dbPass, $this->dbName)){
			/**
			 * Cambio el formato de fecha al estandar YYYY-MM-DD
			 */
			$this->query("alter session set nls_date_format = 'YYYY-MM-DD'");	
			return true;
		} else {
			if($this->display_errors){
				throw new dbException($this->error($php_errormsg), false);
			}
			$this->lastError = $this->error();
			$this->log($this->lastError, Logger::ERROR);
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
		$this->num_rows = false;
		$this->lastQuery = $sqlQuery;
		$resultQuery = @oci_parse($this->Id_Connection, $sqlQuery);
		if($resultQuery){
			$this->lastResultQuery = $resultQuery;			
		} else {
			if($this->display_errors){
				throw new dbException($this->error($php_errormsg)." al ejecutar <i>'$sqlQuery'</i>");
			}
			$this->log($this->error($php_errormsg)." al ejecutar '$sqlQuery'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error($php_errormsg);
			return false;
		}
		if($this->autocommit){
			$commit = OCI_COMMIT_ON_SUCCESS;
		} else {
			$commit = OCI_DEFAULT;
		}
		
		if(!@oci_execute($resultQuery, $commit)){
			if($this->display_errors){
				throw new dbException($this->error($php_errormsg)." al ejecutar <i>'".htmlentities($sqlQuery)."'</i>");
			}
			$this->log($this->error()." al ejecutar '$sqlQuery'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error($php_errormsg);
			return false;
		}		
		return $resultQuery;
	}

	/**
	 * Cierra la Conexión al Motor de Base de datos
	 */
	function close(){
		if($this->Id_Connection) {
			oci_close($this->Id_Connection);
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
		$result = oci_fetch_array($resultQuery, $opt);
		if(is_array($result)){
			$result_to_lower = array();
			foreach($result as $key => $value){
				$result_to_lower[strtolower($key)] = $value;
			}
			return $result_to_lower;
		} else {
			return false;
		}
		return false;
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
				throw new dbException('Resource invalido para db::num_rows');
				return false;
			}
		}
		/**
		 * El Adaptador cachea la ultima llamada a num_rows por razones de performance
		 */
		/*if($resultQuery==$this->lastResultQuery){		
			if($this->num_rows!==false){
				return $this->num_rows;	
			}
		}*/		
		if($this->autocommit){
			$commit = OCI_COMMIT_ON_SUCCESS;
		} else {
			$commit = OCI_DEFAULT;
		}
		if(!@oci_execute($resultQuery, $commit)){
			if($this->display_errors){
				throw new dbException($this->error($php_errormsg)." al ejecutar <i>'{$this->lastQuery}'</i>");
			}
			$this->log($this->error($php_errormsg)." al ejecutar '{$this->lastQuery}'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error($php_errormsg);
			return false;
		}		
		$this->num_rows = oci_fetch_all($resultQuery, $tmp = array());
		unset($tmp);
		@oci_execute($resultQuery, $commit);
		return $this->num_rows;
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
				throw new dbException('Resource invalido para db::field_name');
				return false;
			}
		}
		if(($fieldName = oci_field_name($resultQuery, $number))!==false){
			return strtolower($fieldName);
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
				throw new dbException('Resource invalido para db::data_seek');
				return false;
			}
		}
		if($this->autocommit){
			$commit = OCI_COMMIT_ON_SUCCESS;
		} else {
			$commit = OCI_DEFAULT;
		}
		if(!@oci_execute($resultQuery, $commit)){
			if($this->display_errors){
				throw new dbException($this->error($php_errormsg)." al ejecutar <i>'$sqlQuery'</i>");
			}
			$this->log($this->error($php_errormsg)." al ejecutar '$sqlQuery'", Logger::ERROR);
			$this->lastResultQuery = false;
			$this->lastError = $this->error($php_errormsg);
			return false;
		}
		if($number){
			for($i=0;$i<=$number-1;$i++){
				if(!oci_fetch_row($resultQuery)){
					return false;
				}
			}
		} else {
			return true;
		}
		return true;
	}

	/**
	 * Número de Filas afectadas en un insert, update ó delete
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
		if(($numberRows = oci_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de Oracle
	 *
	 * @return string
	 */
	function error($err=''){
		if(!$this->Id_Connection){
			$error = oci_error() ? oci_error() : "[Error Desconocido en Oracle]";			
			if(is_array($error)){
				$error['message'].=" > $err ";	
				return $error['message'];
			} else {
				$error.=" $php_errormsg ";
				return $error;
			}
		}
		$error = oci_error($this->Id_Connection);
		$error['message'].=" > $err ";	
		return $error['message'];
	}

	/**
	 * Devuelve el no error de Oracle
	 *
	 * @return integer
	 */
	function no_error(){
		if(!$this->Id_Connection){
			$error = oci_error() ? oci_error() : "0";						
			if(is_array($error)){
				return $error['code'];
			} else {
				return $error;
			}
		}
		$error = oci_error($this->Id_Connection);
		return $error['code'];
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	function table_exists($table){
		$num = $this->fetch_one("select count(*) from all_tables where table_name = '".strtoupper($table)."'");		
		return $num[0];
	}

}

?>