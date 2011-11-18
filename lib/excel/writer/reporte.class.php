<?php
class Reporte extends Spreadsheet_Excel_Writer{
	private $hojas;
	private $nombre;
	private $estilos;

	public function Reporte($nombre = 'Reporte.xls'){ // Metodo constructor
		$this->Spreadsheet_Excel_Writer();
		$this->nombre = $nombre;
		$this->estilos = $this->cargar_estilos();
		$this->hojas = array();
	}

	public function agregar_hoja($nombre, $arr_fil = array(), $arr_col = array()){ // Agrega una hoja al libro de Excel
		$this->hojas[$nombre] =& new Hoja($this->addWorksheet($nombre));
		$this->hojas[$nombre]->verificar_creacion();
		$this->hojas[$nombre]->ajustar_columnas($arr_col);
		$this->hojas[$nombre]->ajustar_filas($arr_fil);
		return $this->hojas[$nombre];
	}
	
	public function cargar_estilos(){
		$xlsST = new XLSEstilo();
		foreach($xlsST->catalogo as $nombre=>$propiedades){
			$estilos[$nombre] =& $this->addFormat();
			foreach($propiedades as $funcion=>$valor){
				$estilos[$nombre]->$funcion($valor);
			}		
		}
		return $estilos;
	}
	
	public function generar(){
		$this->send($this->nombre);
		$this->close();
	}
	
	// acceso a los atributos
	public function getHoja($n){
		return $this->hojas[$n];
	}
	public function getHojas(){
		return $this->hojas;
	}
	public function getEstilos(){
		return $this->estilos;
	}
	public function getNombre(){
		return $this->nombre;
	}
	public function setNombre($n = ''){
		$this->nombre = $n;
	}	
	
}// fin de clase Reporte
?>