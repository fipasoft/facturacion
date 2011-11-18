<?php

class Factura extends ActiveRecord{

	public $pagado;

	public function conceptos(){
		$concepto = new Concepto();
		$conceptos = $concepto->find("factura_id='".$this->id."'");
		return $conceptos;
	}

	public function acomodo1(){
		$arreglo = array();
		foreach($impactos as $impacto){
			$solicitud = $impacto->solicitud();
			$campania = $solicitud->campania();
			$producto = $impacto->producto();
			$producto = $producto->extendido();
			$arreglo[$campania->nombre][$producto->proveedor_nombre][] = $impacto;
		}

		$arreglo2 = array();
		foreach ($arreglo as $c=>$r){
			foreach($r as $p => $i){
				$arreglo2[$c][$p]["periodos"] = $this->periodos($i);
			}
		}
		unset($arreglo);

		$conceptos = array();
		foreach($arreglo2 as $c=>$r){
			foreach($r as $p=>$pr){
				foreach ($pr as $periodo => $pp){
					foreach($pp as $fecha => $impactos){
						//var_dump($fecha);
						//var_dump(count($impactos));
						$productos = array();
						foreach($impactos as $impacto){
							$producto = $impacto->producto();
							$producto = $producto->extendido();

							if( $producto->medio_clave == 'RADIO' || $producto->medio_clave == 'TV' ){
								$productos[$producto->programa_id." [".$producto->precio."]"][] = $impacto;
							}else{
								$productos[$producto->seccion_id." [".$producto->precio."]"][] = $impacto;
							}
						}

						$conceptos[$c][$p][$fecha] = $productos;
					}
				}

			}

		}

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

	public function campanias(){
		$campanias = array();
		$conceptos = $this->conceptos();
		foreach($conceptos as $concepto){
			$impactos = new Impacto();
			$impactos = $impactos->find("concepto_id = '".$concepto->id."'");
			foreach ($impactos as $impacto){
				$solicitud = $impacto->solicitud();
				$campania = $solicitud->campania();
				$campanias[$campania->id] = $campania->nombre;
			}
		}

		$campanias = array_unique($campanias);
		return $campanias;

	}


	public function creaConceptos($impactos = array()){
		$conceptos = array();
		foreach($impactos as $impacto){
			$solicitud		=		$impacto->solicitud();
			$envio			=		$impacto->envio();
			$campania		=		$solicitud->campania();
			$producto		=		$impacto->producto();
			$producto		=		$producto->extendido();

			if( $producto->medio_clave == 'RADIO' || $producto->medio_clave == 'TV' ){
				$conceptos[$campania->nombre][$envio->clave()][$producto->proveedor_ncorto][$producto->pseudo_id][] = $impacto;
			}else{
				$conceptos[$campania->nombre][$envio->clave()][$producto->proveedor_ncorto][$producto->pseudo_id][] = $impacto;

			}
		}
		return $conceptos;
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

	public function impactos(){
		$impactos = new Impacto();
		$impactos = $impactos->find_all_by_sql(
			"SELECT " .
			     "impacto.*, " .
                 "preciofacturado.id AS preciofacturado_id, " .
                 "tprecio.clave AS tprecio_clave " .
			"FROM " .
			     "factura " .
			     "INNER JOIN concepto ON factura.id = concepto.factura_id " .
			     "INNER JOIN impacto ON concepto.id = impacto.concepto_id " .
		         "Left Join preciofacturado ON preciofacturado.impacto_id = impacto.id " .
                 "Left Join precioventa ON preciofacturado.precioventa_id = precioventa.id " .
                 "Left Join tprecio ON precioventa.tprecio_id = tprecio.id " .
			"WHERE " .
			     "factura.id = '".$this->id."' "
		);

		return $impactos;
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


	public function montoPagado(){
		$factura = new Factura();
		$factura = $factura->find_all_by_sql(
				"SELECT
					sum(pagos.detalle) as pagado
				FROM
				pagos
				WHERE pagos.factura_id = '".$this->id."'"
		);
		$factura = $factura[0];
		return $factura->pagado;
	}

	/*
	 * porPagar($id = '')
	 *
	 * Esta función regresará la lista de facturas que no han sido totalmente pagadas,
	 * se podrá obtener el id, folio, total, y lo que se ha depositado de cada una de
	 * ellas. Ejemplo:
	 *
	 * |-------------------------------------------------------------------|
	 * | factura_id  |  factura_folio  | factura_total  |  pago_suma_monto |
	 * |-------------------------------------------------------------------|
	 * | 19          |  5009           | 320.00         |  40.00           |
	 * | 22          |  5302           | 150.00         |  NULL            |
	 * |-------------------------------------------------------------------|
	 *
	 * Si se especifica un id, solo se obtendrán los datos para la factura especificada.
	 */
	public function porPagar($id = '', $editMode = ''){

		$totalRc = 0;
		$facturas = new Factura();

		$queryFactura = "SELECT ".
			"factura.id as factura_id, ".
			"factura.folio as factura_folio, ".
			"factura.total as factura_total, ".
			"sum(pagos.detalle) as pago_suma_monto ".
			"FROM ".
			"factura ".
			"Inner Join festados ON (factura.festados_id = festados.id)".
			"Left Join pagos ON (factura.id = pagos.factura_id) ".
			"WHERE (festados.clave <> 'CAN') ".
		(($editMode == '') ? "	AND (festados.clave <> 'PAG') " : "").
		(($id != '') ? "AND factura.id = '$id' " : "").
			"GROUP BY factura.id";

		if ($id != '') {
			$factura = $facturas->find_by_sql($queryFactura);
		} else {
			$factura = $facturas->find_all_by_sql($queryFactura);
		}

		return $factura;
	}


	public function impactosporperiodos($impactos){
		$p = '';
		$fechas = array();
		$ifechas = array();
		foreach($impactos as $impacto){
			$f = new Datetime($impacto->fecha);
			$fechas[$f->format('Ymd')] = $f;
			$ifechas[$f->format('Ymd')][] = $impacto;
		}

		$periodos = array();
		if(count($fechas)==1){
			$f = current($fechas);
			$p = Utils::fecha_espanol_sin_anio($f->format('Y-m-d'));
			$periodos[$p]["i"] = $f;
			$periodos[$p]["f"] = $f;

		}else if( count( $fechas ) > 0 ){

			$sig = null;
			$pini = null;
			$pfin = null;
			$cuenta = count($fechas);
			$i=1;

			foreach($fechas as $f){
				if($sig==null){ //inicio del periodo
					$sig = new Datetime($f->format('Y-m-d'));
					$sig->modify("+1 day");
					$pini=$f;
					//var_dump(" Sig: ".$sig->format("Y-m-d"));
				}else{
					if($sig->format("Ymd") != $f->format("Ymd")){
						//fin del perioodo
						if($pfin==null){
							//periodo de 1 dia
							$periodo = Utils::fecha_espanol_sin_anio($pini->format('Y-m-d'));
							$periodos[$periodo]["i"] = $pini;
							$periodos[$periodo]["f"] = $pini;

						}else{

							$periodo = Utils::fecha_espanol_periodo($pini,$pfin);
							$periodos[$periodo]["i"] = $pini;
							$periodos[$periodo]["f"] = $pfin;

						}
						$pini = $f;
						$pfin = null;


					}else{
						$pfin=$f;
					}
					$sig = new Datetime($f->format('Y-m-d'));
					$sig->modify("+1 day");

				}
				$i++;

			}
			if( $sig->format("Ymd") != $f->format("Ymd") ){
				//fin del perioodo
				if($pfin==null){
					//periodo de 1 dia
					$periodo = Utils::fecha_espanol_sin_anio($pini->format('Y-m-d'));
					$periodos[$periodo]["i"] = $pini;
					$periodos[$periodo]["f"] = $pini;
				}else{

					$periodo = Utils::fecha_espanol_periodo($pini,$pfin);
					$periodos[$periodo]["i"] = $pini;
					$periodos[$periodo]["f"] = $pfin;
				}
				$pini = $f;
				if($i < $cuenta){
					//$sig = null;
					$pfin = null;
				}

			}else{
				$pfin = $f;
			}
		}

		$imps = array();
		foreach($periodos as $llave => $p){
			$ini = $p["i"];
			$fin = $p["f"];

			if($ini->format("Ymd") == $fin->format("Ymd")){
				$imps[$llave] = $ifechas[$ini->format("Ymd")];
			}else{
				while($ini->format("U") <=  $fin->format("U")){
					if($imps[$llave] == null){
						$imps[$llave] = $ifechas[$ini->format("Ymd")];
					}else{
						$imps[$llave] = array_merge($imps[$llave],$ifechas[$ini->format("Ymd")]);
					}
					$ini->modify('+1 day');
				}
			}

		}
		return $imps;
	}


	public function periodos($impactos){
		$fechas = array();
		foreach($impactos as $impacto){
			$f = new Datetime($impacto->fecha);
			$fechas[$f->format('Ymd')] = $f;
		}
		ksort( $fechas );

		if(count($fechas)==1){
			$f = current($fechas);
			$p = substr(Utils::mes_espanol($f->format('m')),0,3) . "." . $f->format("d");
		}else if( count( $fechas ) > 0 ){
			$periodos = array();
			$sig = null;
			$pini = null;
			$pfin = null;
			$cuenta = count($fechas);
			$i=1;

			foreach($fechas as $f){
				if($sig==null){ //inicio del periodo
					$sig = new Datetime($f->format('Y-m-d'));
					$sig->modify("+1 day");
					$pini=$f;
					//var_dump(" Sig: ".$sig->format("Y-m-d"));
				}else{
					if($sig->format("Ymd") != $f->format("Ymd")){
						//fin del perioodo
						if($pfin==null){
							//periodo de 1 dia
							$periodo = substr(Utils::mes_espanol($pini->format('m')),0,3) . "." . $pini->format("d");;
							$periodos[] = $periodo;
						}else{

							$periodo = Utils::fecha_espanol_periodo_acortada($pini,$pfin);
							$periodos[] = $periodo;
						}
						$pini = $f;
						$pfin = null;


					}else{
						$pfin=$f;
					}
					$sig = new Datetime($f->format('Y-m-d'));
					$sig->modify("+1 day");

				}
				$i++;

			}
			if( $sig->format("Ymd") != $f->format("Ymd") ){
				//fin del perioodo
				if($pfin==null){
					//periodo de 1 dia
					$periodo = substr(Utils::mes_espanol($pini->format('m')),0,3) . "." . $pini->format("d");
					$periodos[] = $periodo;
				}else{

					$periodo = Utils::fecha_espanol_periodo_acortada($pini,$pfin);
					$periodos[] = $periodo;
				}
				$pini = $f;
				if($i < $cuenta){
					//$sig = null;
					$pfin = null;
				}

			}else{
				$pfin = $f;
			}
			//var_dump($periodos);exit;

			foreach($periodos as $pe){
				$p.= $pe.", ";
			}
			$p = substr($p,0,strlen($p)-2);

		}
		return $p;
	}

}

?>