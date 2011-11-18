<?php
class Hoja{
	public $hBreaks;
	public $rr;
	public $rr_max;
	public $cc_max;
	public $estilos;
	
	public $xls;
	public function Hoja(&$h){
		$this->xls = &$h;
		$this->rr = 0;
	}
	
	public function ajustar_columnas($arr_col){
		for($ii=0; $ii<count($arr_col); $ii++){
			$this->xls->setColumn($ii, $ii, $arr_col[$ii]);
		}
	}
	
	public function ajustar_filas($arr_fil){
		for($ii=0; $ii<count($arr_fil); $ii++){
			$this->xls->setRow($ii, $arr_fil[$ii]);
		}
	}
	
	public function nueva_linea(){
		$this->rr++;
		$this->cc=0;
	}
	
	public function frml_sumar_columna($cc, $rr_ini, $rr_fin){
		return "=SUM(".Spreadsheet_Excel_Writer::rowcolToCell($rr_ini, $cc).":".Spreadsheet_Excel_Writer::rowcolToCell($rr_fin, $cc).")";
	}
	
	public function frml_sumar_renglon($rr, $cc_ini, $cc_fin){
		return "=SUM(".Spreadsheet_Excel_Writer::rowcolToCell($rr,$cc_ini).":".Spreadsheet_Excel_Writer::rowcolToCell($rr,$cc_fin).")";
	}
	
	public function frml_sumar_totales_columna($arr, $col){
		$E='';
		if( count($arr) > 0 ){
			for($i=0; $i<count($arr); $i++)
				$E .= ($E==''?'':'+').Spreadsheet_Excel_Writer::rowcolToCell($arr[$i],$col);
			$frml = "=SUM(".$E.")";
		}else{
			$frml = '=0';
		}
		return $frml;
	}
	
	public function frml_sumar_totales_fila($arr, $fil){
		$E='';
		if( count($arr) > 0 ){
			for($i=0; $i<count($arr); $i++)
				$E .= ($E==''?'':'+').Spreadsheet_Excel_Writer::rowcolToCell($fil, $arr[$i]);
			$frml = "=SUM(".$E.")";
		}else{
			$frml = '=0';
		}
		return $frml;
	}
	
	public function write_merge($txt, $rr_ini, $cc_ini, $rr_fin, $cc_fin, $estilo){
		for( $i = $rr_ini; $i <= $rr_fin; $i++ ){
			for( $j = $cc_ini; $j <= $cc_fin; $j++ ){
				if( $i == $rr_ini && $j == $cc_ini ){
					$this->xls->write( $i, $j, $txt, $estilo);	
				}else{
					$this->xls->writeBlank( $i, $j, $estilo);
				}
			}
		}
		$this->xls->mergeCells($rr_ini, $cc_ini, $rr_fin, $cc_fin);
	}
	
	public function verificar_creacion(){ // Verifica que las hojas se hayan generado correctamente
		if (PEAR::isError($this->xls)){
			die($this->xls->getMessage());
		}
	}

}// fin de clase Hoja
?>