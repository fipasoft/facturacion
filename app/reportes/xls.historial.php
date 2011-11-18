<?php
/** 
 * XLSHistorial
 * 
 * @package	   Reportes
 * @author     jonathan lopez <jlopez@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */

Kumbia :: import('lib.excel.main');

class XLSHistorial extends Reporte{
	private $condicion;
	private $ejercicio;

	public function XLSHistorial( $ejercicio_id ){
		$controlador = 'historial';
		$accion = 'index';
		$b = new Busqueda($controlador, $accion);
		$b->establecerCondicion(
			'saved_at', 
			"saved_at  LIKE '" . Utils :: fecha_convertir( trim( $b->campo('saved_at') ) ) . "%' "
		);
		// genera las condiciones
		$this->condicion = $c = $b->condicion();

		if($b->campo('controlador')==''){
			$historial = new Historial();
			$controladores = $historial->obtenControladores();
			$cons = '';
			foreach($controladores as $cr){
				$cons .= " controlador LIKE '%" . $cr . "%' OR ";

			}
			$cons = substr($cons, 0, strlen($cons)-3 );
			$c .= ($c == "" ? "" : "AND " . "") ." (" . $cons . ") ";
			$this->condicion = $c;
		}else{
			$b->establecerCondicion('controlador', "controlador LIKE '" . $b->campo('controlador') . "'");
		}

		$ejercicio = new Ejercicio();
		$this->ejercicio = $ejercicio = $ejercicio->find_first( $ejercicio_id );
		$this->Reporte( 'Historial ' . $ejercicio->ver() . ' ' . ($c != '' ? ' FILTRADO' : '' ) . '.xls');

		$fechas = $this->datos_historial();
		foreach( $fechas as $llave=>$f ){
			foreach( $f as $k=>$d ){
				$this->hoja( utf8_decode( strToUpper( Utils::mes_espanol($k) ) . ' DE ' . $llave ), $d );
			}
		}

		if( count( $this->getHojas() ) == 0 ){
			$this->hoja_vacia();
		}
		
	}

	public function hoja( $fecha, $datos ){
		$nombre = $fecha;
		$hojas = $this->getHojas();
		if( array_key_exists( $nombre, $hojas ) ){
			$h = $hojas[$nombre];
		}else{
			$cols = array( 22, 16, 16, 16, 58 );
			$h = $this->agregar_hoja($nombre, null, $cols);
			$h->cc_max = 4;
		}
		$this->contenido( $h, $datos, $fecha );
		$this->propiedades( $h );
	}

	public function hoja_vacia(){
		$nombre = 'HISTORIAL';
		$h = $this->agregar_hoja( $nombre );
		$h->xls->write(0, 0, "No hay registros que coincidan con esas condiciones");
	}

	public function contenido(&$h, $datos, $fecha){

		$st = $this->getEstilos();

		$this->encabezado($h, $fecha);
		$h->xls->write($h->rr, $h->cc, 'Fecha', $st['TH.BGPurpleCenter']); $h->cc++;
		$h->xls->write($h->rr, $h->cc, 'Usuario', $st['TH.BGBlueCenter']); $h->cc++;
		$h->xls->write($h->rr, $h->cc, utf8_decode('Módulo'), $st['TH.BGBlueCenter']); $h->cc++;
		$h->xls->write($h->rr, $h->cc, utf8_decode('Acción'), $st['TH.BGBlueCenter']); $h->cc++;
		$h->xls->write($h->rr, $h->cc, utf8_decode('Descripción'), $st['TH.BGLightyellowCenter']); $h->cc++;
		
		$h->nueva_linea();
		$n = 0;

		foreach($datos as $his){
			$n++;
			$td = ($n%2 == 0 ? 'Par' : '');
			$h->xls->write($h->rr, $h->cc, utf8_decode(Utils::fecha_espanol($his->saved_at).' '.substr($his->saved_at,10)), $st['TD' . $td . '.Normal']);$h->cc++;
			$h->xls->write($h->rr, $h->cc,utf8_decode($his->usuario), $st['TD' . $td . '.Normal']); $h->cc++;
			$h->xls->write($h->rr, $h->cc, utf8_decode($his->controlador), $st['TD' . $td . '.Normal']);$h->cc++;
			$h->xls->write($h->rr, $h->cc, utf8_decode($his->accion), $st['TD' . $td . '.Normal']);$h->cc++;
			$h->xls->write($h->rr, $h->cc, utf8_decode($his->descripcion), $st['TD' . $td . '.Normal']);$h->cc++;
			$h->nueva_linea();
		}

		$h->nueva_linea();
		$h->rr_max = $h->rr;
	}

	public function encabezado(&$h,$fecha){
		$st = $this->getEstilos();
		
		$h->xls->insertBitmap($h->rr, $h->cc, getcwd() . '/public/img/system/logo.bmp', 0, 0, 0.4, 0.9);
		$h->nueva_linea();
		$h->xls->write($h->rr, $h->cc, strtoupper( Session :: get_data('sys.dependencia') ), $st['H3']);
		$h->xls->mergeCells($h->rr, $h->cc, $h->rr, $h->cc + 4);
		$h->nueva_linea();
		$h->xls->write($h->rr, $h->cc, strtoupper( Session :: get_data('sys.departamento') ), $st['H4']);
		$h->xls->mergeCells($h->rr, $h->cc, $h->rr, $h->cc + 4);
		$h->nueva_linea();
		$h->xls->write($h->rr, $h->cc, 'HISTORIAL DEL MES DE '.$fecha, $st['H4']);
		$h->xls->mergeCells($h->rr, $h->cc, $h->rr, $h->cc + 4);
		$h->nueva_linea();
		$h->xls->write($h->rr, $h->cc,'', $st['H4']);
		$h->xls->mergeCells($h->rr, $h->cc, $h->rr, $h->cc + 4);
		$h->nueva_linea();
		$h->nueva_linea();
		$h->xls->repeatRows(0, 6);
		$h->xls->freezePanes( array(7, 0) );
	}

	public function propiedades(&$h){
		$h->xls->centerHorizontally();
		$h->xls->hideGridlines();
		$h->xls->printArea(0, 0, $h->rr_max, $h->cc_max);
		$h->xls->setFooter("BANCOS " . date("j/n/Y H:i"), 0);
		$h->xls->setMargins_LR(0.2);
		$h->xls->setMargins_TB(0.27);
		$h->xls->setPortrait();
		$h->xls->setPaper(3);
		$h->xls->setPrintScale(80);
		$h->xls->setZoom(80);
	}

	public function datos_historial(){
		$c = $this->condicion;
		$ejercicio = $this->ejercicio;
		$historial = new Historial();

		$historial = $historial->find_all_by_sql(
			"SELECT " .
				"* " .
			"FROM " .
				"historial " .
			"WHERE " .
				"( ejercicio_id = '" . $ejercicio->id . "' OR ejercicio_id IS NULL ) " .
		 		($c == "" ? "" : "AND " . $c) . "  ".
			"ORDER BY " .
				"saved_at DESC,controlador,accion,usuario "
		);
		$this->condicion = $c;

		$fecha = array();
		foreach( $historial as $h ){
			$fecha[substr($h->saved_at,0,4)][substr($h->saved_at,5,2)][] = $h;
		}

		return $fecha;
	}

}
?>