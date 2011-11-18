<?php

/** Kumbia - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
* Copyright (C) 2007-2007 Roger Jose Padilla Camacho (rogerjose81 at gmail.com)
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
* bajo los terminos de la licencia p?blica general GNU tal y como fue publicada
* por la Fundaci?n del Software Libre; desde la versi?n 2.1 o cualquier
* versi?n superior.
*
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; sin dejar atr?s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m?s detalles.
*
* Debes recibir una copia de la Licencia P?blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci?n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*****************************************************************************/

/**
 * ActiveRecord Class maps relational to object
 *
 * Active Record es un enfoque al problema de acceder a los datos de una
 * base de datos. Una fila en la tabla de la base de datos (o vista)
 * se envuelve en una clase, de manera que se asocian filas ?nicas de
 * la base de datos con objetos del lenguaje de programaci?n usado.
 * Cuando se crea uno de estos objetos, se a?ade una fila a
 * la tabla de la base de datos. Cuando se modifican los atributos del
 * objeto, se actualiza la fila de la base de datos.
 *
 * Propiedades Soportadas:
 * $db = Conexion al Motor de Base de datos
 * $source = Tabla que contiene la tabla que esta siendo mapeada
 * $fields = Listado de Campos de la tabla que han sido mapeados
 * $count = Conteo del ultimo Resultado de un Select
 * $primary_key = Listado de columnas que conforman la llave primaria
 * $non_primary = Listado de columnas que no son llave primaria
 * $not_null = Listado de campos que son not_null
 * $attributes_names = array con los nombres de todos los campos que han sido mapeados
 * $attributes = String con los nombres de todos los campos separados por comas (,) que han sido mapeados
 * $debug = Indica si se deben mostrar los SQL enviados al RDBM en pantalla
 * $logger = Si es diferente de false crea un log utilizando la clase Logger
 * en lib/kumbia/logger.php, esta crea un archivo .txt en logs/ con todas las
 * operaciones realizadas en ActiveRecord, si $logger = "nombre", crea un
 * archivo con ese nombre
 *
 * Propiedades sin Soportar:
 * $dynamic_update : La idea es que en un futuro ActiveRecord solo
 * actualize los campos que han cambiado.  (En Desarrollo)
 * $dynamic_insert : Indica si los valores del insert son solo aquellos
 * que sean no nulos. (En Desarrollo)
 * $select_before_update: Exige realizar una sentencia SELECT anterior
 * a la actualizaci?n UPDATE para comprobar que los datos no hayan sido
 * cambiados (En Desarrollo)
 * $subselect : Permitira crear una entidad ActiveRecord de solo lectura que
 * mapearia los resultados de un select directamente a un Objeto (En Desarrollo)
 *
 */
class ActiveRecord {

	//Soportados
	public $db;
	public $source;
	public $count;
	public $fields = array();
	public $primary_key = array();
	public $non_primary = array();
	public $not_null = array();
	public $attributes_names = array();
	public $attributes;
	public $is_view;
	public $schema;

	public $debug = false;
	public $logger = false;
	public $persistent = false;

	//Sin Soportar
	public $dynamic_update = false;
	public $dynamic_insert = false;
	public $select_before_update = false;
	public $subselect = null;

	//:Privados
	private $validates_length = array();
	private $validates_numericality = array();
	private $validates_email = array();
	private $validates_date = array();
	private $validates_uniqueness = array();
	private $validates_inclusion = array();
	private $validates_exclusion = array();
	private $validates_format = array();
	private $_in = array();
	private $_at = array();
	private $where_pk;
	private $dumped = false;
	private $dump_lock = false;
	private $data_type = array();
	private $_has_one = array();
	private $_has_many = array();
	private $_belongs_to = array();


	// Persistance Models Meta-data
	static public $models;

	function ActiveRecord($table='', $dump=true){
		if(!$table){
			$this->model_name();
		} else {
			$this->source = strtolower($table);
		}
		if($dump && !$this->is_dumped()){
			$this->dump($table);
		}
	}

	/**
	 * Obtiene el nombre de la relacion en el RDBM a partir del nombre de la clase
	 *
	 */
	private function model_name(){
		$this->source = get_class($this);
		if(ereg("([a-z])([A-Z])", $this->source, $reg)){
			$this->source = str_replace($reg[0], $reg[1]."_".strtolower($reg[2]), $this->source);
		}
		$this->source = strtolower($this->source);
	}

	/**
	 * Pregunta si el ActiveRecord ya ha consultado la informacion de metadatos
	 * de la base de datos o del registro persistente
	 *
	 * @return boolean
	 */
	public function is_dumped(){
		return $this->dumped;
	}

	/**
	 * Valida que los valores que sean leidos del objeto ActiveRecord esten definidos
	 * previamente o sean atributos de la entidad
	 *
	 * @param string $property
	 */
	function __get($property){
		if(!$this->is_dumped()){
			$this->dump();
		}
		if(!$this->dump_lock && !isset($this->$property)){
			if(isset(kumbia::$models[$property])){
				return kumbia::$models[$property];
			} else {
				ActiveRecordException::display_warning("Propiedad no definida", "Propiedad indefinida '$property' leida de el modelo '$this->source'", $this->source);
			}
		}
		return $this->$property;
	}

	/**
	 * Valida que los valores que sean asignados al objeto ActiveRecord esten definidos
	 * o sean atributos de la entidad
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	function __set($property, $value){
		if(!$this->is_dumped()){
			$this->dump();
		}
		if(!$this->dump_lock){
			if(!isset($this->$property)){
				ActiveRecordException::display_warning("Propiedad no definida", "Propiedad indefinida '$property' asignada en el modelo '$this->source' ($value)", $this->source);
			}
			if($property=="source"){
				$value = ActiveRecord::sql_item_sanizite($value);
			}
		}
		$this->$property = $value;
	}

	/**
	 * Devuelve un valor o un listado dependiendo del tipo de Relaci&oacute;n
	 *
	 */
	public function __call($method, $args=array()){
		$has_relation = false;
		if(substr($method, 0, 8)=="find_by_"){
			$field = substr($method, 8);
			ActiveRecord::sql_item_sanizite($field);
			$arg = array("conditions: $field = '{$args[0]}'");
			return call_user_func_array(array($this, "find_first"), array_merge($arg, $args));
		}
		if(substr($method, 0, 9)=="count_by_"){
			$field = substr($method, 9);
			ActiveRecord::sql_item_sanizite($field);
			$arg = array("conditions: $field = '{$args[0]}'");
			return call_user_func_array(array($this, "count"), array_merge($arg, $args));
		}
		if(substr($method, 0, 12)=="find_all_by_"){
			$field = substr($method, 12);
			ActiveRecord::sql_item_sanizite($field);
			$arg = array("conditions: $field = '{$args[0]}'");
			return call_user_func_array(array($this, "find"), array_merge($arg, $args));
		}
		$model = ereg_replace("^get", "", $method);
		$mmodel = strtolower($model);
		if(in_array($mmodel, $this->_belongs_to)){
			$has_relation = true;
			if(kumbia::$models[$model]){
				$named_args = get_params($args);
				if(isset($named_args['conditions'])){
					//$arg = array("$this->{$mmodel."_id"} and ");
				}
				return kumbia::$models[$model]->find_first($this->{$mmodel."_id"});
			}
		}
		if(in_array($mmodel, $this->_has_many)){
			$has_relation = true;
			if(kumbia::$models[$model]){
				if($this->id){
					return kumbia::$models[$model]->find($this->source."_id={$this->id}");
				} else {
					return array();
				}
			}
		}
		if(in_array($mmodel, $this->_has_one)){
			$has_relation = true;
			if(kumbia::$models[$model]){
				if($this->id){
					return kumbia::$models[$model]->find_first($this->source."_id={$this->id}");
				} else {
					return array();
				}
			}
		}
		if(method_exists($this, $method)){
			call_user_func_array(array($this, $method), $args);
		} elseif($has_relation){
			throw new ActiveRecordException("No existe el modelo '$model' para relacionar con ActiveRecord::{$this->source}");
		} else {
			throw new ActiveRecordException("No existe el m?todo '$method' en ActiveRecord::{$this->source}");
		}
		return $this->$method($args);

	}

	/**
	 * Elimina la informaci?n de cache del objeto y hace que sea cargada en la proxima operaci?n
	 *
	 */
	public function reset_cache_information(){
		unset($_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']][$this->source]);
		$this->dumped = false;
		if(!$this->is_dumped()){
			$this->dump();
		}
	}

	/**
	 * Verifica si la tabla definida en $this->source existe
	 * en la base de datos y la vuelca en dump_info
	 *
	 * @return boolean
	 */
	public function dump(){
		if($this->source) {
			$this->source = str_replace(";", "", strtolower($this->source));
		} else {
			$this->model_name();
			if(!$this->source){
				return false;
			}
		}
		$table = $this->source;
		if(!count(ActiveRecord::get_meta_data($this->source))){
			if(!$this->db){
				$this->db = db::raw_connect();
			}
			$this->db->debug = $this->debug;
			$this->db->logger = $this->logger;
			$this->dumped = true;
			if($this->db->table_exists($table)){
				$this->dump_info($table);
			} else {
				$config = Config::read();
				throw new ActiveRecordException("No existe la tabla '$table' en la base de datos '{$config->database->name}'");
				return false;
			}
			if(!count($this->primary_key) && !$this->is_view){
				throw new ActiveRecordException("No se ha definido una llave primaria para la tabla '$table' esto imposibilita crear el ActiveRecord para esta entidad");
				return false;
			}
		} elseif(!$this->is_dumped()){
			$this->dumped = true;
			$this->dump_info($table);
		}
		return true;
	}

	/**
	 * Vuelca la informacion de la tabla $table en la base de datos
	 * para armar los atributos y meta-data del ActiveRecord,
	 * actualmente solo esta soportado MySQL
	 *
	 * @param string $table
	 */
	private function dump_info($table){
		$config = Config::read();
		$this->dump_lock = true;
		if($config->database->type == 'mysql' && !count(ActiveRecord::get_meta_data($table))){
			$meta_data = $this->db->in_query("DESC $table");
		} elseif($config->database->type=='postgresql' && !count(ActiveRecord::get_meta_data($table))){
			$meta_data = $this->db->in_query("SELECT a.attname AS Field, t.typname AS Type,
		 	CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS null,
		 	CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
		 	cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI' ELSE ''
		 	END AS Key FROM pg_catalog.pg_class c, pg_catalog.pg_attribute a,
		 	pg_catalog.pg_type t WHERE c.relname = '{$table}' AND c.oid = a.attrelid
		 	AND a.attnum > 0 AND t.oid = a.atttypid");
		} elseif($config->database->type=='oracle' && !count(ActiveRecord::get_meta_data($table))){
			$meta_data = $this->db->in_query("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD, LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE, ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (SELECT COUNT(*) FROM ALL_CONS_COLUMNS WHERE TABLE_NAME = '".strtoupper($table)."' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS WHERE ALL_TAB_COLUMNS.TABLE_NAME = '".strtoupper($table)."'");
	    }
	    if($meta_data){
			ActiveRecord::set_meta_data($table, $meta_data);
		}
		foreach(ActiveRecord::get_meta_data($table) as $field){
			if(!isset($this->$field['Field'])){
				$this->$field['Field'] = "";
			}
			$this->fields[] = $field['Field'];
			if($field['Key'] == 'PRI'){
				$this->primary_key[] = $field['Field'];
			} else{
				$this->non_primary[] = $field['Field'];
			}
			if($field['Null'] == 'NO'){
				$this->not_null[] = $field['Field'];
			}
			if($field['type']){
				$this->data_type[$field['Field']] = strtolower($field['Type']);
			}
			if(substr($field['Field'], strlen($field['Field'])-3, 3) == '_at'){
				$this->_at[] = $field['Field'];
			}
			if(substr($field['Field'], strlen($field['Field'])-3, 3) == '_in'){
				$this->_in[] = $field['Field'];
			}
		}
		$this->attributes_names = $this->fields;
		$this->attributes = join(',', $this->fields);
		$this->dump_lock = false;
		return true;
	}

	/**
	 * Commit a Transaction
	 *
	 * @return success
	 */
	public function commit(){
		if(!$this->dumped){
			$this->dump();
		}
		return $this->sql("commit");
	}

	/**
	 * Rollback a Transaction
	 *
	 * @return success
	 */
	public function rollback(){
		if(!$this->dumped){
			$this->dump();
		}
		return $this->sql("rollback");
	}

	/**
	 * Start a transaction in RDBM
	 *
	 * @return success
	 */
	public function begin(){
		if(!$this->dumped){
			$this->dump();
		}
		return $this->sql("begin");
	}

	/**
	 * Find all records in this table using a SQL Statement
	 *
	 * @param string $sqlQuery
	 * @return ActiveRecord Cursor
	 */
	public function find_all_by_sql($sqlQuery){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped){
			$this->dump();
		}
		$results = array();
		foreach($this->db->in_query($sqlQuery) as $result){
			$results[] = $this->dump_result($result);
		}
		return $results;
	}
	/**
	 * Find a record in this table using a SQL Statement
	 *
	 * @param string $sqlQuery
	 * @return ActiveRecord Cursor
	 */
	public function find_by_sql($sqlQuery){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped){
			$this->dump();
		}
		return $this->db->fetch_one($sqlQuery);
	}

	/**
	 * Execute a SQL Statement directly
	 *
	 * @param string $sqlQuery
	 * @return int affected
	 */
	public function sql($sqlQuery){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->logger = $this->logger;
		$this->db->debug = $this->debug;
		return $this->db->query($sqlQuery, $this->debug);
	}

	/**
	 * Return Fist Record
	 *
	 * @param mixed $what
	 * @param boolean $debug
	 * @return ActiveRecord Cursor
	 */
	public function find_first($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$params = get_params(func_get_args());
		$select = "SELECT ";
		if(isset($params['columns'])){
			$select.= ActiveRecord::sql_sanizite($params['columns']);
		} else {
			$select.= join(",", $this->fields);
		}
		if($this->schema){
			$select.= " FROM {$this->schema}.{$this->source}";
		} else {
			$select.= " FROM {$this->source}";
		}
		$select.= $this->convert_params_to_sql($params);
		$resp = false;
		if(!isset($params['limit'])){
			$select.=$this->limit(1);
		}
		$result = $this->db->fetch_one($select);
		if($result){
			$this->dump_result_self($result);
			$resp = $this->dump_result($result);
		}
		return $resp;
	}

	/**
	 * Find data on Relational Map table
	 *
	 * @param string $what
	 * @return ActiveRecord Cursor
	 */
	public function find($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$what = get_params(func_get_args());
		$select = "SELECT ";
		if(isset($what['columns'])){
			$select.= $what['columns'] ? ActiveRecord::sql_sanizite($what['columns']) : join(",", $this->fields);
		} else {
			$select.= join(",", $this->fields);
		}
		if($this->schema){
			$select.= " FROM {$this->schema}.{$this->source}";
		} else {
			$select.= " FROM {$this->source}";
		}
		$return = "n";
		if(isset($what['conditions'])&&$what['conditions']) {
			$select.= " WHERE {$what['conditions']} ";
		} else {
			if(!isset($this->primary_key[0])&&$this->is_view){
				$this->primary_key[0] = "id";
				ActiveRecord::sql_item_sanizite($this->primary_key[0]);
			}
			if(isset($what[0])){
				if(is_numeric($what[0])){
					$what['conditions'] = "{$this->primary_key[0]} = '{$what[0]}'";
					$return = "1";
				} else {
					if($what[0]==''){
						$what['conditions'] = "{$this->primary_key[0]} = ''";
					} else {
						$what['conditions'] = $what[0];
					}
					$return = "n";
				}
			}
			if(isset($what['conditions'])){
				$select.= " WHERE {$what['conditions']}";
			}
		}
		if(isset($what['group'])&&$what['group']) {
			ActiveRecord::sql_sanizite($what['group']);
			$select.= " GROUP BY ".$what['group'];
		}
		if(isset($what['order'])&&$what['order']) {
			ActiveRecord::sql_sanizite($what['order']);
			$select.= " ORDER BY ".$what['order'];
		}
		if(isset($what['limit'])&&$what['limit']) {
			$select.= $this->limit($what['limit']);
		}
		$results = array();
		$all_results = $this->db->in_query($select);
		foreach($all_results AS $result){
			$results[] = $this->dump_result($result);
		}
		$this->count = count($results);
		if($return=="1"){
			if(!isset($results[0])){
				$this->count = 0;
				return false;
			} else {
				$this->dump_result_self($all_results[0]);
				$this->count = 1;
				return $results[0];
			}
		} else {
			$this->count = count($results);
			return $results;
		}
	}

	/*
	* Arma una consulta SQL con el parametro $what, así:
	* 	$what = get_params(func_get_args());
	* 	$select = "SELECT * FROM Clientes";
	*	$select.= $this->convert_params_to_sql($what);
	* @param string $what
	* @return string
	*/
	public function convert_params_to_sql($what = ''){
		$select = "";
		if(is_array($what)){
			if(isset($what['conditions'])&&$what['conditions']){
				$select.= " WHERE {$what["conditions"]} ";
			} else {
				if(!isset($this->primary_key[0]) && (isset($this->id) || $this->is_view)){
					$this->primary_key[0] = "id";
				}
				ActiveRecord::sql_item_sanizite($this->primary_key[0]);
				if(isset($what[0])){
					if(is_numeric($what[0])){
						$what['conditions'] = "{$this->primary_key[0]} = '{$what[0]}'";
					} else {
						if($what[0]==''){
							$what['conditions'] = "{$this->primary_key[0]} = ''";
						} else {
							$what['conditions'] = $what[0];
						}
					}
				}
				if(isset($what['conditions'])){
					$select.= " WHERE {$what['conditions']}";
				}
			}
			if(isset($what['limit'])&&$what['limit']) {
				$select.= $this->limit($what['limit']);
			}
			if(isset($what['order'])&&$what['order']) {
				ActiveRecord::sql_sanizite($what['order']);
				$select.=" ORDER BY {$what['order']}";
			} else {
				$select.=" ORDER BY 1";
			}
		} else {
			if(strlen($what)){
				if(is_numeric($what)){
					$select.= "WHERE {$this->primary_key[0]} = '$what'";
				} else {
					$select.= "WHERE $what";
				}
			}
		}
		return $select;
	}

	/*
	* Devuelve una clausula LIMIT adecuada al RDBMS empleado, es conveniente 
	* utilizarla para no perder la portabilidad del codigo.
	* Ej.: "SELECT * FROM Clientes {$this->limit(15)}";
	* @param $num cantidad de registros limite
	* @return String clausula LIMIT adecuada al RDBMS empleado
	*/
	public function limit($num = 1){
		$config = Config::read();
		return $config->database->type == 'oracle' ? " AND ROWNUM <= {$num}" : " LIMIT {$num}";
	}


	public function distinct($what=''){
		if(!$this->dumped) $this->dump();
		if(func_num_args() > 1){
			$what = get_params(func_get_args());
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(is_array($what)){
			if(!$what['column']){
				$what['column'] = $what['0'];
			}
			$select = "SELECT DISTINCT {$what['column']} FROM {$this->source}";
			if($what["conditions"]) {
				$select.= " WHERE {$what["conditions"]}";
			}
			if($what["order"]) {
				$select.= " ORDER BY {$what["order"]}";
			}
			if($what["limit"]) {
				$select.= $this->limit($what["limit"]);
			}
		} elseif($what !== ''){
			$select = "SELECT DISTINCT {$what} FROM {$this->source}";
		}
		$results = array();
		foreach($this->db->in_query($select) as $result){
			$results[] = $result[0];
		}
		return $results;
	}

	/**
	 * Ejecuta una consulta en el RDBM directamente
	 *
	 * @param string $sql
	 * @return resource
	 */
	public function select_one($sql){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(substr(ltrim($sql), 0, 7) != "select"){
			$sql = "SELECT ".$sql;
		}
		$num = $this->db->fetch_one($sql);
		return $num[0];
	}

	static public function static_select_one($sql){
		$db = db::raw_connect();
		if(substr(ltrim($sql), 0, 7) != "select"){
			$sql = "SELECT ".$sql;
		}
		$num = $db->fetch_one($sql);
		return $num[0];
	}

	/**
	 * Realiza un conteo de filas
	 *
	 * @param string $what
	 * @return integer
	 */
	public function count($what=''){
		if(!$this->dumped) $this->dump();
		if(func_num_args()>1){
			$what = get_params(func_get_args());
		}
		if(is_array($what)){
			if(!$what['distinct']) {
				$select = "SELECT COUNT(*) FROM {$this->source}";
			} else {
				$select = "SELECT COUNT(DISTINCT {$what['distinct']}) FROM {$this->source}";
			}
			if($what["conditions"]){
				$select.= " WHERE {$what['conditions']}";
			}
			if($what["order"]){
				$select.= " ORDER BY {$what['order']}";
			}
			if($what["limit"]){
				$select.= $this->limit($what["limit"]);
			}
		} elseif($what !== ''){
			if(is_numeric($what)){
				if($this->is_view && !$this->primary_key[0]){
					$this->primary_key[0] = 'id';
				}
				ActiveRecord::sql_item_sanizite($this->primary_key[0]);
				$select = "SELECT COUNT(*) FROM {$this->source} WHERE {$this->primary_key[0]} = '{$what}'";
			} else {
				$select = "SELECT COUNT(*) FROM {$this->source} WHERE {$what}";
			}
		} else {
			$select = "SELECT COUNT(*) FROM {$this->source}";
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($select);
		return $num[0];
	}

	/**
	 * Realiza un promedio sobre el campo $what
	 *
	 * @param string $what
	 * @return array
	 */
	public function average($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		$what = get_params(func_get_args());
		if(!$what['column']){
			$what['column'] = $what[0];
		}
		ActiveRecord::sql_item_sanizite($what['column']);
		$select = "SELECT AVERAGE({$what['column']}) FROM {$this->source}" ;
		if($what["conditions"]) {
			$select.= " WHERE {$what['conditions']}";
		}
		if($what["order"]) {
			ActiveRecord::sql_item_sanizite($what["order"]);
			$select.= " ORDER BY {$what['order']}";
		}
		if($what["limit"]) {
			$select.= $this->limit($what['limit']);
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($select);
		return $num[0];
	}

	public function sum($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		$what = get_params(func_get_args());
		ActiveRecord::sql_item_sanizite($what['column']);
		if(!$what['column']) $what['column'] = $what[0];
		$select = "SELECT SUM({$what['column']}) FROM {$this->source}" ;
		if($what["conditions"]) {
			$select.= " WHERE {$what["conditions"]}";
		}
		if($what["order"]) {
			ActiveRecord::sql_item_sanizite($what['order']);
			$select.= " ORDER BY {$what["order"]} ";
		}
		if($what["limit"]) {
			$select.= $this->limit($what["limit"]);
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($select);
		return $num[0];
	}

	/**
	 * Busca el valor maximo para el campo $what
	 *
	 * @param string $what
	 * @return mixed
	 */
	public function maximum($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		$what = get_params(func_get_args());
		if(!$what['column']){
			$what['column'] = $what[0];
		}
		ActiveRecord::sql_item_sanizite($what['column']);
		$select = "SELECT MAX({$what['column']}) FROM {$this->source}";
		if($what["conditions"]) {
			$select.= " WHERE {$what["conditions"]}";
		}
		if($what["order"]) {
			ActiveRecord::sql_item_sanizite($what['order']);
			$select.= " ORDER BY {$what["order"]} ";
		}
		if($what["limit"]) {
			$select.= $this->limit($what["limit"]);
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($select);
		return $num[0];
	}

	/**
	 * Busca el valor minimo para el campo $what
	 *
	 * @param string $what
	 * @return mixed
	 */
	public function minimum($what=''){
		if(!$this->dumped) {
			$this->dump();
		}
		$what = get_params(func_get_args());
		if(!$what['column']){
			$what['column'] = $what[0];
		}
		$select = "SELECT MIN({$what['column']}) FROM {$this->source}";
		if($what["conditions"]) {
			$select.= " WHERE {$what["conditions"]} ";
		}
		if($what["order"]) {
			ActiveRecord::sql_item_sanizite($what['order']);
			$select.= " ORDER BY {$what["order"]}";
		}
		if($what["limit"]) {
			$select.= $this->limit($what["limit"]);
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($select);
		return $num[0];
	}

	/**
	 * Realiza un conteo directo mediante $sql
	 *
	 * @param string $sqlQuery
	 * @return mixed
	 */
	public function count_by_sql($sqlQuery){
		if(!$this->dumped) $this->dump();
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		$num = $this->db->fetch_one($sqlQuery);
		return $num[0];
	}

	/**
	 * Iguala los valores de un resultado de la base de datos
	 * en un nuevo objeto con sus correspondientes
	 * atributos de la clase
	 *
	 * @param array $result
	 * @return ActiveRecord
	 */
	function dump_result($result){
		if(!$this->dumped) $this->dump();
		$obj = clone $this;
		$this->dump_lock = true;
		if(is_array($result)){
			foreach($result as $k => $r){
				if(!is_numeric($k)){
					$obj->$k = stripslashes($r);
				}
			}
		}
		$this->dump_lock = false;
		return $obj;
	}

	/**
	 * Iguala los valores de un resultado de la base de datos
	 * con sus correspondientes atributos de la clase
	 *
	 * @param array $result
	 * @return ActiveRecord
	 */
	public function dump_result_self($result){
		if(!$this->dumped){
			$this->dump();
		}
		$this->dump_lock = true;
		if(is_array($result)){
			foreach($result as $k => $r){
				if(!is_numeric($k)){
					$this->$k = stripslashes($r);
				}
			}
		}
		$this->dump_lock = false;
	}

	/**
	 * Create a new Row using values from $_REQUEST
	 *
	 * @return boolean success
	 */
	public function create_from_request(){
		if(!$this->dumped){
			$this->dump();
		}
		$values = array();
		foreach($_REQUEST as $k => $r){
			if(isset($this->$k)) {
				$values[$k] = $r;
			}
		}
		return count($values) ? $this->create($values) : false;
	}

	/**
	 * Saves a new Row using values from $_REQUEST
	 *
	 * @return boolean success
	 */
	public function save_from_request(){
		if(!$this->dumped){
			$this->dump();
		}
		foreach($_REQUEST as $k => $r){
			if(isset($this->$k)) {
				$this->$k = $r;
			}
		}
		return $this->save();
	}

	/**
	 * Creates a new Row in map table
	 *
	 * @param mixed $values
	 * @return success boolean
	 */
	public function create($values=''){
		if(!$this->dumped) $this->dump();
		if(func_num_args()>1){
			$values = get_params(func_get_args());
		}
		if(is_array($values)){
			if(is_array($values[0])){
				foreach($values as $v){
					foreach($this->fields as $f){
						$this->$f = "";
					}
					foreach($v as $k => $r){
						if(!is_numeric($k)){
							if(isset($this->$k)){
								$this->$k = $r;
							} else {
								throw new ActiveRecordException("No existe el Atributo '$k' en la entidad '{$this->source}' al ejecutar la inserci&oacute;n");
								return false;
							}
						}
					}
					if(!$this->exists()){
						return $this->save();
					} else {
						Flash::error('No se puede crear el registro ya existe');
						return false;
					}
				}
			} else {
				foreach($this->fields as $f){
					$this->$f = "";
				}
				foreach($values as $k => $r){
					if(!is_numeric($k)){
						if(isset($this->$k)){
							$this->$k = $r;
						} else {
							throw new ActiveRecordException("No existe el Atributo '$k' en la entidad '{$this->source}' al ejecutar la inserci?n");
							return false;
						}
					}
				}
				if(!$this->exists()){
					return $this->save();
				} else{
					Flash::error('No se puede crear el registro ya existe');
				}
			}
		} elseif($values!==''){
			Flash::warning("Par&aacute;metro incompatible en acci&oacute;n 'create'. No se pudo crear ningun registro");
			return false;
		} elseif(!$this->exists()){
			return $this->save();
		}
		return true;
	}

	/**
	 * Consulta si un determinado registro existe o no
	 * en la entidad de la base de datos
	 *
	 * @return boolean
	 */
	function exists($where_pk=''){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped){
			$this->dump();
		}
		$query = "SELECT COUNT(*) FROM {$this->source}";
		if(!$where_pk){
			$this->where_pk = "";
			foreach($this->primary_key as $key){
				if($this->$key){
					$this->where_pk.= " {$key} = '{$this->$key}' AND ";
				} else {
					return false;
				}
			}			
			if($this->where_pk){
				$this->where_pk = substr($this->where_pk, 0, strlen($this->where_pk)-4);
				$query.= " WHERE {$this->where_pk}";
			}			
		} elseif(is_numeric($where_pk)){
			$query.= " WHERE id = '{$where_pk}'";
		} else {
			$query.= " WHERE {$where_pk}";
		}
		$query.= $this->limit(1);
		$num = $this->db->fetch_one($query);
		return $num[0];
	}

	/**
	 * Saves Information on the ActiveRecord Properties
	 *
	 * @return boolean success
	 */
	public function save(){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped) {
			$this->dump();
		}
		#Run Validation Callbacks Before
		if(method_exists($this, 'before_validation') && $this->before_validation() == 'cancel'){
			return false;
		} elseif(isset($this->before_validation)){
			$method = $this->before_validation;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		$ex = $this->exists();
		if(!$ex && method_exists($this, "before_validation_on_create") && $this->before_validation_on_create() == 'cancel'){
			return false;
		} elseif(isset($this->before_validation_on_create)){
			$method = $this->before_validation_on_create;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if($ex && method_exists($this, "before_validation_on_update") && $this->before_validation_on_update() == 'cancel'){
			return false;
		} elseif(isset($this->before_validation_on_update)){
			$method = $this->before_validation_on_update;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if(is_array($this->not_null)){
			$e = false;
			for($i=0; $i<=count($this->not_null)-1; $i++){
				$f = $this->not_null[$i];
				if(is_null($this->$f) || $this->$f === ''){
					if(!$ex && $f == 'id'){
						continue;
					}
					if(!$ex && in_array($f, $this->_at)){
						continue;
					}
					if($ex && in_array($f, $this->_in)){
						continue;
					}
					Flash::error("Error: El campo {$f} no puede ser nulo en {$this->source}");
					$e = true;
				}
			}
			if($e){
				return false;
			}
		}
		if(is_array($this->validates_length)){
			$e = false;
			foreach($this->validates_length as $f => $opt){
				if($opt['in']){
					$in = explode(":", $opt['in']);
					if(is_numeric($in[0]) && is_numeric($in[1])){
						$opt['minimum'] = $in[0];
						$opt['maximum'] = $in[1];
					}
				}
				if(is_numeric($opt['minimum'])){
					$n = $opt['minimum'];
					if(strlen($this->$f) < $n){
						if(!$opt['too_short']){
							Flash::error("Error: El campo {$f} debe tener como m&iacute;nimo {$n} caracteres");
							$e = true;
						} else {
							Flash::error($opt['too_short']);
							$e = true;
						}
					}
				}
				if(is_numeric($opt['maximum'])){
					$n = $opt['maximum'];
					if(strlen($this->$f) > $n){
						if(!$opt['too_long']){
							Flash::error("Error: El campo {$f} debe tener como m&aacute;ximo {$n} caracteres");
							$e = true;
						} else {
							Flash::error($opt['too_long']);
							$e = true;
						}
					}
				}
			}
			if($e){
				return false;
			}
			unset($f);
			unset($n);
			unset($in);
		}

		# Validates Inclusion
		if(count($this->validates_inclusion)){
			$e = false;
			if(is_array($this->validates_inclusion)){
				foreach($this->validates_inclusion as $finc => $list){
					if(!is_array($list)){
						Flash::error(ucwords($finc)." debe tener un valor entre ($list)");
						$e = true;
					} elseif(!in_array($this->$finc, $list)){
						Flash::error(ucwords($finc)." debe tener un valor entre (".join(",", $list).")");
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates Exclusion
		if(count($this->validates_exclusion)){
			$e = false;
			if(is_array($this->validates_exclusion)){
				foreach($this->validates_exclusion as $finc => $list){
					if(!is_array($list)){
						Flash::error(ucwords($finc)." no debe tener un valor entre ($list)");
						$e = true;
					} elseif(in_array($this->$finc, $list)){
						Flash::error(ucwords($finc)." no debe tener un valor entre (".join(",", $list).")");
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates Numericality
		if(count($this->validates_numericality)){
			$e = false;
			if(is_array($this->validates_numericality)){
				foreach($this->validates_numericality as $fnum){
					if(!is_numeric($this->$fnum)){
						Flash::error(ucwords($fnum)." debe tener un valor num&eacute;rico");
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates format
		if(count($this->validates_format)){
			$e = false;
			if(is_array($this->validates_format)){
				foreach($this->validates_format as $fkey => $format){
					if($this->$fkey !== '' && $this->$fkey !== null && !ereg($format, $this->$fkey)){
						Flash::error("Formato erronero para ".ucwords($fkey));
						$e = true;
					} else {
						Flash::error("Formato erronero para ".ucwords($fkey));
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates date
		if(count($this->validates_date)){
			$e = false;
			if(is_array($this->validates_date)){
				foreach($this->validates_date as $fkey){
					if(!ereg("^\d{4}-(0[1-9]|1[12])-(0[1-9]|[12][0-9]|3[01])$", $this->$fkey, $regs)){
						Flash::error("Formato de fecha erronero para ".ucwords($fkey));
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates e-mail
		if(count($this->validates_email)){
			$e = false;
			if(is_array($this->validates_email)){
				foreach($this->validates_email as $fkey){
					if(!ereg("^[a-zA-Z0-9_\.\+]+@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$", $this->$fkey, $regs)){
						Flash::error("Formato de e-mail erronero en el campo ".ucwords($fkey));
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
		}

		# Validates Uniqueness
		if(count($this->validates_uniqueness)){
			$e = false;
			if(is_array($this->validates_uniqueness)){
				foreach($this->validates_uniqueness as $fkey){
					ActiveRecord::sql_item_sanizite($fkey);
					$number = $this->db->fetch_one("SELECT COUNT(*) FROM {$this->source} WHERE $fkey = '{$this->$fkey}' {$this->limit(1)}");
					if($number[0]){
						Flash::error("El valor '{$this->$fkey}' ya existe para el campo ".ucwords($fkey));
						$e = true;
					}
				}
			}
			if($e){
				return false;
			}
			unset($number);
		}

		#Run Validation Callbacks After
		if(!$ex && method_exists($this, "after_validation_on_create") && $this->after_validation_on_create() == 'cancel'){
			return false;
		} elseif(isset($this->after_validation_on_create)){
			$method = $this->after_validation_on_create;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if($ex && method_exists($this, "after_validation_on_update") && $this->after_validation_on_update()=='cancel'){
			return false;
		} elseif(isset($this->after_validation_on_update)){
			$method = $this->after_validation_on_update;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if(method_exists($this, 'after_validation') && $this->after_validation() == 'cancel'){
			return false;
		} elseif(isset($this->after_validation)){
			$method = $this->after_validation;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		# Run Before Callbacks
		if(method_exists($this, "before_save") && $this->before_save() == 'cancel'){
			return false;
		} elseif(isset($this->before_save)){
			$method = $this->before_save;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if($ex && method_exists($this, "before_update") && $this->before_update()=='cancel'){
			return false;
		} elseif(isset($this->before_update)){
			$method = $this->before_update;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		if(!$ex && method_exists($this, "before_create") && $this->before_create() == 'cancel'){
			return false;
		} elseif(isset($this->before_create)){
			$method = $this->before_create;
			if($this->$method() == 'cancel'){
				return false;
			}
		}
		$config = Config::read();
		if($ex){
			$set = "";
			foreach($this->non_primary as $np){
				$np = ActiveRecord::sql_item_sanizite($np);
				if(in_array($np, $this->_in)){
					if($config->database->type == 'oracle'){
						$this->$np = date("Y-m-d");
					} else {
						$this->$np = date("Y-m-d G:i:s");
					}
				}
				if(substr($this->$np, 0, 1) == "%"){
					$set.= "$np = ".str_replace("%", "", $this->$np).",";
				} else if(!$this->is_a_numeric_type($np)){
					/**
					 * Se debe especificar el formato de fecha en Oracle
					 */
					if($this->data_type[$np] == 'date' && $config->database->type == 'oracle'){
						$set.= "$np = TO_DATE('".addslashes($this->$np)."', 'YYYY-MM-DD'),";
					} else if( $this->$np !== null){
						$set.= "$np = '".addslashes($this->$np)."',";
					} else if( $this->$np === null ){
						$set.= $np . " = NULL,";
					}
				} else {
					if($this->$np !== ''&&$this->$np !== null){
						$set.= "$np = '".addslashes($this->$np)."'";
					} else {
						$set.= "$np = NULL";
					}
					$set.= ',';
				}
			}
			$set = substr($set, 0, strlen($set)-1);
			$query = "UPDATE {$this->source} SET {$set} WHERE {$this->where_pk}";
		} else {
			$insert = "INSERT INTO {$this->source} (";
			$values = ") VALUES (";
			foreach($this->fields as $field){
				if($field != 'id' && !$this->id){
					if(in_array($field, $this->_at)){
						if($config->database->type == 'oracle'){
							$this->$field = date("Y-m-d");
						} else {
							$this->$field = date("Y-m-d G:i:s");
						}
					}
					if(in_array($field, $this->_in)){
						$this->$field = "NULL";
					}
					$insert.= ActiveRecord::sql_sanizite($field).",";
					if(substr($this->$field, 0, 1) == "%"){
						$values.= str_replace("%", "", $this->$field).",";
					} else {
						if($this->is_a_numeric_type($field) || $this->$field == "NULL"){
							$values.= addslashes($this->$field !== ''&&$this->$field !== null ? $this->$field : "NULL").",";
						} else {
							if($this->data_type[$field]=='date' && $config->database->type == 'oracle'){
								/**
								 * Se debe especificar el formato de fecha en Oracle
								 */
								$values.= "TO_DATE('".addslashes($this->$field)."', 'YYYY-MM-DD'),";
							} else {
								if($this->$field !== '' && $this->$field!==null){
									$values.= "'".addslashes($this->$field)."'";
								} else {
									$values.= "NULL";
								}
								$values.= ',';
							}
						}
					}
				  // Los Campos Autonumericos en Oracle deben utilizar una sequencia auxiliar
				} elseif($config->database->type == 'oracle' && !$this->id){ 					
					$insert.= ActiveRecord::sql_sanizite($field).",";
					$values.= $this->source."_sequence.NextVal,";
				}
			}
			$insert = substr($insert, 0, strlen($insert)-1);
			$values = substr($values, 0, strlen($values)-1);
			$query = "$insert $values)";
		}

		$val = $this->db->query($query);
		if($config->database->type == 'oracle'){
			$this->commit();
		}
		if(!$ex){
			$m = $this->maximum('id');
			$this->find_first($m);
		}
        
		if($val){
			if($ex && method_exists($this, "after_update") && $this->after_update() == 'cancel'){
				return false;
			} elseif(isset($this->after_update)){
				$method = $this->after_update;
				if($this->$method() == 'cancel'){
					return false;
				}
			}
			if(!$ex && method_exists($this, "after_create") && $this->after_create() == 'cancel'){
				return false;
			} elseif(isset($this->after_create)){
				$method = $this->after_create;
				if($this->$method() == 'cancel'){
					return false;
				}
			}
			if(method_exists($this, "after_save") && $this->after_save() == 'cancel'){
				return false;
			} elseif(isset($this->after_save)){
				$method = $this->after_save;
				if($this->$method() == 'cancel'){
					return false;
				}
			}
			return $val;
		} else{
			return false;
		}
	}

	/**
	 * Find All data in the Relational Table
	 *
	 * @param string $field
	 * @param string $value
	 * @return ActiveRecod Cursor
	 */
	function find_all_by($field, $value){
		ActiveRecord::sql_item_sanizite($field);
		return $this->find("conditions: {$field} = '{$value}'");
	}

	/**
	 * Updates Data in the Relational Table
	 *
	 * @param mixed $values
	 * @return boolean sucess
	 */
	function update($values=''){
		if(!$this->dumped) {
			$this->dump();
		}
		if(func_num_args() > 1){
			$values = get_params(func_get_args());
		}
		if(is_array($values)){
			foreach($values as $k => $r){
				if(!is_numeric($k)){
					if(isset($this->$k)){
						$this->$k = $r;
					} else {
						throw new ActiveRecordException("No existe el Atributo '{$k}' en la entidad '{$this->source}' al ejecutar la inserci&oacute;n");
						return false;
					}
				}
			}
			if($this->exists()){
				return $this->save();
			} else {
				Flash::error('No se puede actualizar porque el registro no existe');
				return false;
			}
		} elseif($this->exists()){
			return $this->save();
		} else {
			Flash::error('No se puede actualizar porque el registro no existe');
			return false;
		}
	}

	/**
	 * Deletes data from Relational Map Table
	 *
	 * @param mixed $what
	 */
	function delete($what=''){
		if(!$this->dumped){
			$this->dump();
		}
		if(func_num_args() > 1){
			$what = get_params(func_get_args());
		}
		if(is_array($what)){
			$delete = "DELETE FROM {$this->source}";
			if($what["conditions"]){
				$delete.= " WHERE {$what["conditions"]}";
			}
		} elseif(is_numeric($what)){
			ActiveRecord::sql_sanizite($this->primary_key[0]);
			$delete = "DELETE FROM {$this->source} WHERE {$this->primary_key[0]}='{$what}'";
		} elseif($what){
			$delete = "DELETE FROM {$this->source} WHERE {$what}";
		} else {
			ActiveRecord::sql_sanizite($this->primary_key[0]);
			$delete = "DELETE FROM {$this->source} WHERE {$this->primary_key[0]} = '{$this->{$this->primary_key[0]}}'";
		}
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(method_exists($this, "before_delete")){
			if($this->id){
				$this->find($this->id);
			}
			if($this->before_delete() == 'cancel') {
				return false;
			}
		} elseif(isset($this->before_delete)){
			if($this->id){
				$this->find($this->id);
			}
			$method = $this->before_delete;
			if($this->$method() == 'cancel') {
				return false;
			}
		}
		$val = $this->db->query($delete);
		if($val){
			if(method_exists($this, "after_delete") && $this->after_delete() == 'cancel'){
				return false;
			} elseif(isset($this->after_delete)){
				$method = $this->after_delete;
				if($this->$method() == 'cancel') {
					return false;
				}
			}
		}
		return $val;
	}

	/**
	 * Actualiza todos los atributos de la entidad
	 * $Clientes->update_all("estado='A', fecha='2005-02-02'", "id>100");
	 * $Clientes->update_all("estado='A', fecha='2005-02-02'", "id>100", "limit: 10");
	 *
	 * @param string $values
	 */
	function update_all($values){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped) {
			$this->dump();
		}
		if(func_num_args() > 1){
			$params = get_params(func_get_args());
		}
		$query = "UPDATE {$this->source} SET {$values}";
		if($params['conditions']){
			$query.= " WHERE {$params['conditions']}";
		} elseif($params[1]){
			$query.= " WHERE {$params[1]}";
		}
		if($params['limit']){
			$query.= $this->limit($params['limit']);
		}
		return $this->db->query($query);
	}

	/**
	 * Delete All data from Relational Map Table
	 *
	 */
	function delete_all(){
		if(!$this->db){
			$this->db = db::raw_connect();
		}
		$this->db->debug = $this->debug;
		$this->db->logger = $this->logger;
		if(!$this->dumped) {
			$this->dump();
		}
		$this->db->query("DELETE FROM {$this->source}");
	}

	/**
	 * *********************************************************************************
	 * Metodos de Debug
	 * *********************************************************************************
	 */

	/**
	 * Imprime una version humana de los valores de los campos
	 * del modelo en una sola linea
	 *
	 */
	public function inspect(){
		if(!$this->dumped){
			$this->dump();
		}
		$inspect[] = array();
		foreach($this->fields as $field){
			$inspect[] = "$field: {$this->$field}";
		}
		return join(", ", $inspect);
	}

	/**
	 * Validate that Attributes cannot have a NULL value
	 */
	function validates_presence_of(){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->not_null)){
			$this->not_null = array();
		}
		if(func_num_args()){
			$params = func_get_args();
		} else{
			return true;
		}
		if(is_array($params[0])) {
			$params = $params[0];
		}
		foreach($params as $p){
			if(!in_array($p, $this->fields)){
				throw new ActiveRecordException('No se puede validar presencia de "'.$p.'"
					en el modelo '.$this->source.' porque no existe el atributo</u><br>
					Verifique que el atributo este bien escrito y/o exista en la relaci?n ');
				return false;
			}
			if(!in_array($p, $this->not_null)){
				$this->not_null[] = $p;
			}
		}
		return true;
	}

	/**
	 * *********************************************************************************
	 * Metodos de Validacion
	 * *********************************************************************************
	 */

	/**
	 * Valida el tamanio de ciertos campos antes de insertar
	 * o actualizar
	 *
	 * $this->validates_length_of("nombre", "minumum: 15")
	 * $this->validates_length_of("nombre", "minumum: 15", "too_short: El Nombre es muy corto")
	 * $this->validates_length_of("nombre", "maximum: 40", "too_long: El nombre es muy largo")
	 * $this->validates_length_of("nombre", "in: 15:40",
	 *      "too_short: El Nombre es muy corto",
	 *      "too_long: El nombre es muy largo (40 min)"
	 * )
	 *
	 * @return boolean
	 */
	public function validates_length_of(){
		if(!$this->dumped){
			$this->dump();
		}
		if(func_num_args()){
			$params = get_params(func_get_args());
		} else return true;
		if(!is_array($this->validates_length)){
			$this->validates_length = array();
		}
		if(is_array($params)){
			$this->validates_length[$params[0]] = array(
			"minimum" => $params['minimum'],
			"maximum" => $params['maximum'],
			"in" => $params["in"],
			"too_short" => $params["too_short"],
			"too_long" => $params["too_long"]
			);
		}
		return true;
	}

	/**
	 * Valida que el campo se encuentre entre los valores de una lista
	 * antes de insertar o actualizar
	 *
	 * $this->validates_inclusion_in("estado", array("A", "I"))
	 *
	 * @param string $campo
	 * @param array $list
	 * @return boolean
	 */
	public function validates_inclusion_in($campo, $list){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->validates_inclusion)){
			$this->validates_inclusion = array();
		}
		$this->validates_inclusion[$campo] = $list;
		return true;
	}

	/**
	 * Valida que el campo no se encuentre entre los valores de una lista
	 * antes de insertar o actualizar
	 *
	 * $this->validates_exclusion_of("edad", range(1, 13))
	 *
	 * @param string $campo
	 * @param array $list
	 * @return boolean
	 */
	public function validates_exclusion_of($campo, $list){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->validates_exclusion)){
			$this->validates_exclusion = array();
		}
		$this->validates_exclusion[$campo] = $list;
		return true;
	}

	/**
	 * Valida que el campo tenga determinado formato segun una expresion regular
	 * antes de insertar o actualizar
	 *
	 * $this->validates_format_of("email", "^(+)@((?:[?a?z0?9]+\.)+[a?z]{2,})$")
	 *
	 * @param string
	 * @param array $list
	 * @return boolean
	 */
	public function validates_format_of($campo, $pattern){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->validates_format)){
			$this->validates_format = array();
		}
		$this->validates_format[$campo] = $pattern;
		return true;
	}

	/**
	 * Valida que ciertos atributos tengan un valor numerico
	 * antes de insertar o actualizar
	 *
	 * $this->validates_numericality_of("precio")
	 */
	function validates_numericality_of(){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->not_null)){
			$this->not_null = array();
		}
		if(func_num_args()){
			$params = func_get_args();
		} else return true;
		if(is_array($params[0])) {
			$params = $params[0];
		}
		foreach($params as $p){
			if(!in_array($p, $this->fields)&&!isset($this->$p)){
				throw new ActiveRecordException('No se puede validar presencia de "'.$p.'"
					en el modelo '.$this->source.' porque no existe el atributo</u><br>
					Verifique que el atributo este bien escrito y/o exista en la relaci?n ');
				return false;
			}
			if(!in_array($p, $this->validates_numericality)){
				$this->validates_numericality[] = $p;
			}
		}
		return true;
	}

	/**
	 * Valida que ciertos atributos tengan un formato de e-mail correcto
	 * antes de insertar o actualizar
	 *
	 * $this->validates_email_in("correo")
	 */
	function validates_email_in(){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->not_null)){
			$this->not_null = array();
		}
		if(func_num_args()){
			$params = func_get_args();
		} else{
			return true;
		}
		if(is_array($params[0])) {
			$params = $params[0];
		}
		foreach($params as $p){
			if(!in_array($p, $this->fields)&&!isset($this->$p)){
				throw new ActiveRecordException('No se puede validar presencia de "'.$p.'"
					en el modelo '.$this->source.' porque no existe el atributo</u><br>
					Verifique que el atributo este bien escrito y/o exista en la relaci?n ');
				return false;
			}
			if(!in_array($p, $this->validates_email)){
				$this->validates_email[] = $p;
			}
		}
		return true;
	}

	/**
	 * Valida que ciertos atributos tengan un valor unico antes
	 * de insertar o actualizar
	 *
	 * $this->validates_uniqueness_of("cedula")
	 */
	function validates_uniqueness_of(){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->not_null)){
			$this->not_null = array();
		}
		if(func_num_args()){
			$params = func_get_args();
		} else{
			return true;
		}
		if(is_array($params[0])) {
			$params = $params[0];
		}
		foreach($params as $p){
			if(!in_array($p, $this->fields) && !isset($this->$p)){
				throw new ActiveRecordException('No se puede validar presencia de "'.$p.'"
					en el modelo '.$this->source.' porque no existe el atributo</u><br>
					Verifique que el atributo este bien escrito y/o exista en la relaci?n ');
				return false;
			}
			if(!in_array($p, $this->validates_uniqueness)){
				$this->validates_uniqueness[] = $p;
			}
		}
		return true;
	}

	/**
	 * Valida que ciertos atributos tengan un formato de fecha acorde al indicado en
	 * config/config.ini antes de insertar o actualizar
	 *
	 * $this->validates_date_in("fecha_registro")
	 */
	function validates_date_in(){
		if(!$this->dumped){
			$this->dump();
		}
		if(!is_array($this->not_null)){
			$this->not_null = array();
		}
		if(func_num_args()){
			$params = func_get_args();
		} else{
			return true;
		}
		if(is_array($params[0])) {
			$params = $params[0];
		}
		foreach($params as $p){
			if(!in_array($p, $this->fields)&&!isset($this->$p)){
				throw new ActiveRecordException('No se puede validar presencia de "'.$p.'"
					en el modelo '.$this->source.' porque no existe el atributo</u><br>
					Verifique que el atributo este bien escrito y/o exista en la relaci?n ');
				return false;
			}
			if(!in_array($p, $this->validates_date)){
				$this->validates_date[] = $p;
			}
		}
		return true;
	}


	/**
	 * Verifica si un campo es de tipo de dato numerico o no
	 *
	 * @param string $field
	 * @return boolean
	 */
	public function is_a_numeric_type($field){
		if(
		strpos(" ".$this->data_type[$field], "int")||
		strpos(" ".$this->data_type[$field], "decimal")||
		strpos(" ".$this->data_type[$field], "number")
		){
			return true;
		} else return false;
	}

	/**
	 * Obtiene los datos de los metadatos generados por Primera vez en la Sesi?n
	 *
	 * @param string $table
	 * @return array
	 */
	static function get_meta_data($table){
		if(isset(self::$models[$table])){
			return self::$models[$table];
		} else {
			if(isset($_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']][$table])){
				self::set_meta_data($table, $_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']][$table]);
				return self::$models[$table];
			}
			return array();
		}
	}

	/**
	 * Crea un registro de meta datos para la tabla especificada
	 *
	 * @param string $table
	 * @param array $meta_data
	 */
	static function set_meta_data($table, $meta_data){
		if(!isset($_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']][$table])){
			$_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']][$table] = $meta_data;
		}
		self::$models[$table] = $meta_data;
		return true;
	}


	/*******************************************************************************************
	* Metodos para generacion de relaciones
	*******************************************************************************************/

	/**
	 * Crea una relacion 1-1 entre dos modelos
	 *
	 * @param string $relation
	 */
	public function has_one($relation){
		if(!in_array($relation, $this->_has_one)){
			$this->_has_one[] = $relation;
		}
	}

	/**
	 * Crea una relacion 1-1 inversa entre dos modelos
	 *
	 * @param string $relation
	 */
	public function belongs_to($relation){
		if(!in_array($relation, $this->_belongs_to)){
			$this->_belongs_to[] = $relation;
		}
	}

	/**
	 * Crea una relacion 1-n entre dos modelos
	 *
	 * @param string $relation
	 */
	public function has_many($relation){
		if(!in_array($relation, $this->_has_many)){
			$this->_has_many[] = $relation;
		}
	}

	/**
	 * Elimina caracteres que podrian ayudar a ejecutar
	 * un ataque de Inyeccion SQL
	 *
	 * @param string $sql_item
	 */
	public static function sql_item_sanizite($sql_item){
		$sql_item = trim($sql_item);
		if($sql_item!==''&&$sql_item!==null){
			$sql_item = ereg_replace("[ ]+", "", $sql_item);
			if(!ereg("^[a-zA-Z0-9_]+$", $sql_item)){
				throw new ActiveRecordException("Se esta tratando de ejecutar una operacion maliciosa!");
			}
		}
		return $sql_item;
	}

	/**
	 * Elimina caracteres que podrian ayudar a ejecutar
	 * un ataque de Inyeccion SQL
	 *
	 * @param string $sql_item
	 */
	public static function sql_sanizite($sql_item){
		$sql_item = trim($sql_item);
		if($sql_item!==''&&$sql_item!==null){
			$sql_item = ereg_replace("[ ]+", "", $sql_item);
			if(!ereg("^[a-zA-Z_0-9\,\(\)\.]+$", $sql_item)){
				throw new ActiveRecordException("Se esta tratando de ejecutar una operacion maliciosa!");
			}
		}
		return $sql_item;
	}

}


/**
 * Clase para manejar errores ocurridos en operaciones de
 * ActiveRecord
 *
 */
class ActiveRecordException extends Exception {

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

	/**
	 * Muestra el mensaje de error de la excepci?n
	 *
	 */
	public function show_message(){
		Flash::error("
		<span style='font-size:24px;color:black'>KumbiaException: ".
		htmlentities($this->message)."</span><br/>
		<div>".htmlentities($this->extended_message)."<br>
		<span style='font-size:12px;color:black'>En el archivo <i>{$this->getFile()}</i> en la l?nea: <i>{$this->getLine()}</i></span></div>", true);
		print "<pre style='border:1px solid #969696;background:#FFFFE8;color:black'>";
		print $this->getTraceAsString()."\n";
		print "</pre>";
	}

	static function display_warning($title, $message, $source){
		Flash::warning("
		<span style='font-size:24px;color:black'>KumbiaWarning: $title</span><br/>
		<div>$message<br>
		<span style='font-size:12px;color:black'>En el modelo <i>{$source}</i> al ejecutar <i>{$_REQUEST['controller']}/{$_REQUEST['action']}/{$_REQUEST['id']}</i></span></div>", true);
		print "<pre style='border:1px solid #969696;background:#FFFFE8;color:black'>";
		print debug_print_backtrace()."\n";
		print "</pre>";
	}

}

?>
