<?php

 class Fiscal extends ActiveRecord{
 	
 	public function UDG(){
 		$fiscal = new Fiscal();
 		$fiscal = $fiscal->find_first(" rfc='UGU250907MH5' ");
 		return $fiscal;
 	}
 	
 	public function municipio(){
 		$municipio = new Municipio();
 		$municipio = $municipio->find($this->municipio_id);
 		return $municipio;
 	}
 	
 }
?>
