<?php
class Factura extends ActiveRecord{

	public function conceptos(){
		$concepto = new Concepto();
		$conceptos = $concepto->find("factura_id='".$this->id."'");
		return $conceptos;
	}


	public function cancelable(){

		$fipr = new Festados();
		$fipr = $fipr->porclave('IPR');

		$fcap = new Festados();
		$fcap = $fcap->porclave('CAP');

		return
				($this->festados_id == $fcap->id ||
				$this->festados_id == $fipr->id);
	}

	public function dependencia(){
		$d = new Dependencia();
		$d = $d->find_first($this->dependencia_id);
		return $d;
	}

	public function eliminable(){

		$fcap = new Festados();
		$fcap = $fcap->porclave('CAN');

		return
				( $this->festados_id == $fcap->id );
	}

	public function folio(){
		$folio = new Folio();
		$folio = $folio->find_first("factura_id='".$this->id."'");
		return $folio;
	}

	public function estado( $r = '' ){

		$estado = new Festados();
		$estado = $estado->find(
		$this->festados_id
		);

		return
		( $r ? $estado->$r : $estado );

	}


	public function montoConLetra(){

		$pesos = intval( $this->total );
		$centavos = number_format( ( $this->total - $pesos ) * 100 , 0, '', '');
		if( strlen($centavos) == 1 ){
			$centavos = '0' . $centavos;
		}

		return
			strtoupper( Utils:: NumerosALetras( $pesos ) ) . "PESOS (" . $centavos . "/100)";

	}

	public function receptor(){

        $dependencia = $this->dependencia();
        $fiscal = $dependencia->fiscal();

        $receptor = new stdClass();

        foreach( $fiscal as $clave => $valor ){

            $receptor->$clave = $valor;

        }

        return
            $receptor;

	}

	public function verFecha(){

	    return
	       Utils :: fecha_convertir( $this->fecha );
	}

    public function verFechaEspanol(){

        return
           Utils :: fecha_espanol( $this->fecha );
    }

	public function verFolio(){

	       return
	           $this->folio;
	}


}
?>