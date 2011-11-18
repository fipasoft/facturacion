<?php
// FiPa, Creado el 19/09/2008
/** 
 * Busqueda
 * 
 * @package    Componentes
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
 
 class Busqueda{
 	private $accion;
 	private $actualizar;
 	private $campos;
 	private $condicion;
 	private $condiciones;
 	private $controlador;
 	private $comodin;
 	private $comodines;
 	private $excluir;
 	private $fechas;
 	private $operador;
 	private $operadores;
 	private $operadorC;
 	private $operadoresC;
 	
 	public function Busqueda($controlador, $accion){
 		$this->accion = $accion;
 		$this->campos = array();
 		$this->comodin = '%';
 		$this->comodines = array();
 		$this->condicion = '';
 		$this->condiciones = array();
 		$this->controlador = $controlador;
 		$this->operador = 'AND';
 		$this->operadorC = 'LIKE';
 		$this->excluir = array();
 		$this->fechas = array();
 		$this->operadores = array();
 		$this->operadoresC = array();
 		$this->actualizar = true;
 		$this->actualizar();
 	}
 	
 	public function actualizar(){
 		$controlador = $this->controlador;
 		$accion = $this->accion;
 		$sesion = Session :: get ('app.busqueda');
 		
 		// actualiza las variables en el arreglo temporal
		foreach($_POST as $var => $value){
			if($var != 'action' && $var != 'controller'){
				$sesion[$controlador][$accion][$var] = $value;
			}
		}
		if(!is_array($sesion[$controlador][$accion])){
			$sesion[$controlador][$accion] = array();
		}
		$this->actualizar = true;
		Session :: set ('app.busqueda', $sesion);
		$this->campos();
 	}
 	
 	
 	// cargar la consulta desde $_SESSION
 	public function cargar(){
 		$controlador = $this->controlador;
 		$accion = $this->accion;
 		$sesion = Session :: get ('app.busqueda');
		return $this->condicion = $sesion[$controlador][$accion]['consulta'];
 	}
 	
 	// obtiene el valor de un campo determinado
 	public function campo($nombre){
 		return $this->campos[$nombre];
 	}
 	
	// carga en un arreglo los campos que integran la consulta 	
 	public function campos(){
 		if($this->actualizar){
	 		$controlador = $this->controlador;
	 		$accion = $this->accion;
	 		$sesion = Session :: get ('app.busqueda');
	 		
	 		foreach($sesion[$controlador][$accion] as $campo => $valor){
				$campos[$campo] = $valor;	
			}
 			$this->campos = $campos;
 		}
 		return $this->campos;
 	}
 	 	
 	// genera las condiciones para usarlas en un script de MySQL
 	public function condicion(){
 		if($this->actualizar){
	 		$controlador = $this->controlador;
	 		$accion = $this->accion;
	 		$sesion = Session :: get ('app.busqueda');
	 		$condiciones = $this->condiciones;
	 		$excluir = $this->excluir;
	 		$fechas = $this->fechas;
	 		$c = '';
	 		$ops = $this->operadores;
	 		$opsC = $this->operadoresC;
	 		$coms = $this->comodines;
	 		foreach($sesion[$controlador][$accion] as $var => $value){
				$op  = ($ops[$var]  != ''  ?  $ops[$var]   :  $this->operador);
		 		$opC = ($opsC[$var] != ''  ?  $opsC[$var]  :  $this->operadorC);
		 		$com = ($coms[$var] != ''  ?  $coms[$var]  :  $this->comodin);
	 			if($value != '' && !isset( $excluir[$var] )){
		 			$c .= ($c == '' ? '' : $op . ' ');
		 			if(strlen($condiciones[$var]) > 0){
		 				$c .= "(" . $condiciones[$var] . ") ";
		 			}else if( isset($fechas[$var]) ){
		 				$c .= str_replace('_', '.', $var) . " = '" . $fechas[$var] ."' " ;
		 			}else{
			 			$c .= str_replace('_', '.', $var) . " " . $opC . " '" . $com .  $value .  $com . "' ";
		 			}
	 			}
			}
			$this->condicion = $c;;
 		}
 		return $this->condicion;
 	}
 	
 	// establece condiciones especificas para un campo
 	public function establecerCondicion($campo, $condicion){
 		$this->actualizar = true;
 		$this->condiciones[$campo] = $condicion;
 	}
 	
 	// establece el operador = y elimina los caracteres % de la comparacion
 	public function esEstricta($campo = ''){
		$this->actualizar = true;
 		if($campo != ''){
 			$this->operadoresC[$campo] = '=';
 			$this->comodines[$campo] = '';
 		}else{
	 		$this->operadorC = '=';
	 		$this->comodin = ''; 			
 		}
 	}
 	
 	// establece si el valor es fecha en formato latino DD/MM/YYYY, al consultar elimina los comodines (%) y convierte a formato YYYY-MM-DD
 	public function esFecha($campo){
		$this->actualizar = true;
 		if($campo != ''){
 			$this->fechas[$campo] = Utils :: fecha_convertir( $this->campo( $campo ) );
 		}
 	}
 	
 	// establece el operador LIKE y los caracteres especiales % al principio y fin de la comparacion
 	public function esFlexible($campo = ''){
		$this->actualizar = true;
 		if($campo != ''){
 			$this->operadoresC[$campo] = 'LIKE';
	 		$this->comodines[$campo] = '%';
 		}else{
	 		$this->operadorC = 'LIKE';
	 		$this->comodin = '%'; 			
 		}
 	}
 	
 	// establece los campos incluyen al generar la consulta
 	public function excluir($campo){
		$this->actualizar = true;
 		if($campo != ''){
 			$this->excluir[$campo] = true;
 		}
 	}
 	
 	// salvar la consulta en $_SESSION
 	public function guardar(){
 		$controlador = $this->controlador;
 		$accion = $this->accion;
 		$sesion = Session :: get ('app.busqueda');
		$sesion[$controlador][$accion]['consulta'] = $this->condicion;
		Session :: set ('app.busqueda', $sesion);
 	}
 	
 	// limpia los datos de la consulta en $_SESSION
 	public function reset(){
 		$controlador = $this->controlador;
 		$accion = $this->accion;
 		$sesion = Session :: get ('app.busqueda');
		unset( $sesion[$controlador][$accion] );
		Session :: set ('app.busqueda', $sesion);
 	}
 	
 	// cambia al operador AND
 	public function usaAnd($campo = ''){
		$this->actualizar = true;
 		if($campo != ''){	
	 		$this->operadores[$campo] = 'AND';
 		}else{
	 		$this->operador = 'AND';
 		}
 	}
 	
 	// cambia al operador OR
 	public function usaOr($campo = ''){
		$this->actualizar = true;
 		if($campo != ''){	
	 		$this->operadores[$campo] = 'OR'; 			
 		}else{
	 		$this->operador = 'OR';  			
 		}
 	}
 }
?>