<?php
// bancos2, Creado el 10/06/2009
/** 
 * Plantilla
 * 
 * Permite utilizar un archivo intermedio en XML para establecer las propiedades de estilo
 * al generar un reporte de Excel.
 * 
 * @package    SpreadsheetExcelWriter 
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
class Plantilla extends Reporte{

	public function Plantilla( $nombre = '' ){ // Metodo constructor
		$this->Reporte( $nombre );
	}
	
	protected function cols( $encabezados = array() ){
		$cols = array();
		
		foreach( $encabezados as $campo ){
			if( !isset( $campo->nHijos ) ){
				if( isset($campo->ancho) ){
					$cols[] = $campo->ancho;
				}else{
					$cols[] = 12;
				}
			}else{
				$cols = array_merge( $cols, $this->cols( $campo->hijos ) );
			}
		}
		return $cols;
	}
	
	protected function campos( $grupo = 'base', $xmlCampos = array() ){
		if( !isset( $this->campos[ $grupo ] ) ){
			$campos = new stdClass();
			
		    foreach( $xmlCampos as $campo ){
			    	$id = $campo->id;
			    	
			    	// copia atributos
			    	$c = new stdClass();

		    		foreach( $campo as $clave => $attr ){
			    		$c->$clave = (string) $attr;	
			    	}

			    	if( !isset( $campo->mapear ) ){
			    		// para campos simples
			    		
			    		if( $this->verificarExistencia( $c ) ){
			    			
				    		$c->valor = $c->nombre = $this->reemplazarVariables( $c->nombre );
				    		
				    		if( !isset( $campo->padre ) ){
						    	$campos->$id = $c;
				    		}else{
						    	$campos->{$campo->padre}->hijos->$id = $c;
						    	$campos->{$campo->padre}->nHijos++;
				    		}
				    		
			    		}
			    		
			    	} else {
				    	
				    	// para campos mapeados
				    	
						$variable = $c->mapear;
						if( count( $this->$variable ) > 0 ){
							
							$items = $this->$variable;
							
							foreach( $items as $item ){
								$id = $c->id . '_' . $item;
								$obj = clone $c;
								if( $this->verificarExistencia( $obj ) ){
									$obj->id = $id;
									$obj->valor = $obj->nombre = $this->reemplazarVariables( 
										$obj->nombre, array( $variable => $item ) 
									);
									if( !isset( $obj->padre ) ){
										$campos->$id = $obj;
									}else{
										$campos->{ $obj->padre . '_' . $item }->hijos->$id = $obj;
										$campos->{ $obj->padre . '_' . $item }->nHijos++;
									}
									
								}
								
							}
						}						
						
					}
			    	
		    }
			
			$this->campos[ $grupo ] = $campos;
		}
		return $this->campos[ $grupo ];
	}
	
	protected function camposContenido(){
		return $this->campos( 'contenido' );
	}
	
	protected function encabezados(){
		return $this->campos( 'encabezados' );
	}
	
	protected function escribirCampo( &$h, $campo, $_rr = null, $_cc = null, $_valor = null ){
		$st = $this->getEstilos();

		$rr = ( isset( $_rr ) ? $_rr : $h->rr );
		$cc = ( isset( $_cc ) ? $_cc : $h->cc );
		$valor = ( isset( $_valor ) ? $_valor : $campo->valor );
		
		$hijos = $campo->nHijos;
		
		if( isset( $campo->combinarFilas ) || isset( $campo->combinarColumnas ) || $hijos > 0){
			$ccOffset = 0;
			$rrOffset = 0;
			
			if( isset( $campo->combinarFilas ) ){
				$rrOffset = $campo->combinarFilas - 1;
			}
			
			if( isset( $campo->combinarColumnas ) ){
				$ccOffset = $campo->combinarColumnas - 1;
			}else if( $hijos > 0 ){
				$ccOffset = $hijos - 1;
			}
			
			if( $hijos == 0 || !isset( $campo->oculto )  ){
				$h->write_merge( 
					$valor, 
					$rr, $cc, 
					$rr + $rrOffset, $cc + $ccOffset, 
					$st[ $campo->clase ]
				);
			}
			
		}else{
			$h->xls->write($rr, $cc, $valor, $st[ $campo->clase ]);
			
		}
		
		if( $hijos > 0 ){
			foreach( $campo->hijos as $cSub => $sub ){
				
				$v = ( isset( $_valor->$cSub ) ? null : $_valor->$cSub );
				$this->escribirCampo( $h, $sub, $rr + ( isset( $campo->oculto ) ? 0 : 1 ), null, $v );
				
			}
			
		}else{
			$h->cc++;
			
		}
		
	}
	
	protected function verificarExistencia( $c ){
		$ok = true;
		if( isset( $c->existe ) ) {
			$var = $c->existe;
			if( !isset( $this->$var ) ){
				$ok = false;
			}
		}
		return $ok;
	}

	protected function reemplazarVariables( $valor, $valores = array() ){
		$simbolos = array_merge( $this->simbolos, $valores );

		foreach( $simbolos as $sym => $val ){
			$valor = str_replace( '@@' . $sym, $val, $valor );
		}
		
		return $valor;
	}
	
	protected function rows(){
		$r = array();
		return $r;
	}
	
	protected function validarNumero( $n ){
		return ( $n == '' ? 0 : $n );
	}

	
}// fin de clase Plantill
?>