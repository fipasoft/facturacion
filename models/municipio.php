<?php

class Municipio extends ActiveRecord{
	
	public function edo(){
		$edo = new Edo();
		$edo = $edo->find($this->edo_id);
		return $edo;
	}
}

?>