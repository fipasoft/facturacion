<?php
class DependenciasController extends ApplicationController{
	public $template = "system";

	public function index( $pag = '' ){

        $this->route_to('controller: externas', 'action: index');

	}
}
?>
