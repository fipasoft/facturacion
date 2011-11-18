<?php
// FiPa, Creado el 16/09/2008
/** 
 * Paginador
 * 
 * @package    Componentes
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
 class Paginador{
 	private $controlador;
 	private $vista;
 	private $parametros;
 	private $path;
 	private $pagina;
 	private $registros;
 	private $rpp;
 	private $np; // numero de paginas
 	private $tipo;
 	private $etiqueta;
 	private $botones;
 	
 	public function Paginador($controlador, $vista, $parametros = ''){
 		$sesion = Session :: get ('app.paginador');
 		$pagina = $sesion[$controlador][$vista];
 		$pagina = intval($pagina, 10);
 		$this->controlador = $controlador;
 		$this->vista = ($vista == '' ? 'index' : $vista);
 		$this->parametros = $parametros;
 		$this->path = $controlador . '/' . $vista;
 		$this->pagina = ($pagina-1 < 0 ? 0 : $pagina-1);
 		$this->registros = 0;
 		$this->rpp = 10;
		$this->np = 10;
		$this->botones = array();
 		$this->tipo = 'elemento';
 		$this->etiqueta = 'Mostrando ' . $this->registros . ' ' . $this->tipo . ($this->registros == 1 ? '' : 's');
 		$this->etiqueta = 'Mostrando del ' . ($pagina * $this->rpp) . ' al ' . '' ;
 	}
 	
 	public function generar(){
 		// solo muestra el paginador si hay registros
 		if($this->registros > 0){
 			$rpp  =  $this->rpp;
 			$reg  =  $this->registros;
 			$np   =  $this->np;
 			$path =  $this->path;
 			if($this->parametros != ''){
 				$path = str_replace('//', '/', $path . '/' . $this->parametros );
 			}
 			$nav = false;
 			$this->pagina = $pag  =  ($this->pagina < $reg / $rpp  ? $this->pagina : 0);
 			
 			// establecer intervalo de paginas
 			if($pag <= $np / 2 || $reg / $rpp <= $np){
 				$ini = 0;
 				if($np <= ceil($reg / $rpp)){
	 				$fin = $np;
 				}else{
 					$fin = ceil($reg / $rpp);
 				}
 			}else{
 				$nav = true;
 				$ini = $pag - intval($np / 2, 10);
 				if($pag + intval($np / 2, 10) <= ceil($reg / $rpp)){
 					$fin = $pag + intval($np / 2, 10);
 				}else{
 					$fin = ceil($reg / $rpp);
 				}
 			}
 			
 			// generar botones
 			if($nav){
 				$this->botones[] = new Boton($path . '/1',
 											 '<<',
 											 'boton',
 											 'inicial'
 									   );
 			}
 			for($p = $ini; $p < $fin; $p++){
 				$this->botones[] = new Boton($path . '/' . ($p + 1),
 											 $p + 1,
 											 ($p == $pag ? 'activo' : 'boton'),
 											 $p + 1
 									   );
 			}
 			if($reg / $rpp > $np && $pag + intval($np / 2, 10) < ceil($reg / $rpp)){
 				$this->botones[] = new Boton($path . '/' . ceil($reg / $rpp),
 											 '>>',
 											 'boton',
 											 'final'
 									   );
 			}
 		}
 	}
 	
 	public function guardarPagina($pag){
 		$pag = intval($pag, 10);
 		$controlador = $this->controlador;
 		$vista = $this->vista;
 		
 		$sesion = Session :: get ('app.paginador');
 		$sesion[$controlador][$vista] = $pag;
 		$this->pagina = ($pag-1 < 0 ? 0 : $pag-1);
 		
 		// guarda la sesion
		Session :: set ('app.paginador', $sesion);
 	}
 	
 	public function estableceRegistros($registros){
 		$this->registros = $registros;
 	}
 	
 	// acceso a variables privadas
 	public function botones(){
 		return $this->botones;
 	}
 	public function pagina(){
 		return $this->pagina;
 	}
 	public function rpp(){
 		return $this->rpp;
 	}
 }

/** 
 * Botones del paginador
 * 
 * @package    Componentes
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */ 
 class Boton{
 	private $url;
 	private $etiqueta;
 	private $titulo;
 	private $estilo;
 	
 	public function Boton($url, $etiqueta, $estilo, $titulo){
 		$this->url 		=  $url;
 		$this->etiqueta =  $etiqueta;
 		$this->estilo	=  $estilo;
 		$this->titulo = $titulo;
 	}
 	
 	// acceso a atributos privados
 	public function url(){
 		return $this->url;
 	}	
 	public function etiqueta(){
 		return $this->etiqueta;
 	}
  	public function estilo(){
 		return $this->estilo;
 	}
 	public function titulo(){
 		return $this->titulo;
 	}

}
?>
