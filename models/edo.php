<?php

class Edo extends ActiveRecord{
	
	public function municipios(){
		$municipios = new Municipio();
		$municipios = $municipios->find("edo_id = '".$this->id."' ORDER BY nombre");
		return $municipios;
	}
	
	public function pais(){
		$pais = new Pais();
		$pais = $pais->find($this->pais_id);
		return $pais;
	}
}

?>