<?php
class Factura extends ActiveRecord{
    
    public $ultimo;

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
			"(" . strtoupper( Utils:: NumerosALetras( $pesos ) ) . "PESOS " . $centavos . "/100 M.N.)";

	}
    
    public static function obtenFolio(){
        $facturas = new Factura();
        if($facturas->count() > 0){
            
            $q = "SELECT MAX(CAST(folio AS DECIMAL(10))) AS ultimo FROM factura;";
            $factura = new Factura();
            $factura = $factura->find_by_sql($q);
            return $factura['ultimo'] + 1;
            
        }else{
            
            $sys = Config :: read('sys.ini');
            return $sys->facturacion->folio_ini;
            
        }
    }

}
?>