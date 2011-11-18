<?php

class Pais extends ActiveRecord{
	
	public function estados(){
		$edos = new Edo();
		$edos = $edos->find("pais_id = '".$this->id."' ORDER BY nombre");
		return $edos;
	}
}

?>