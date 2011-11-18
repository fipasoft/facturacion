<?php
class LocalizacionController extends ApplicationController{
	public $template = "system";

	public function inicia($id , $clave = 'MEX'){

		if($this->post('id')!= ''){
			$id =$this->post('id');
		}

		if($this->post('clave')!= ''){
			$clave =$this->post('clave');
		}

		$this->set_response("view");
		try{
			$pais = new Pais();
			$pais = $pais->find_first("clave = '".$clave."'");

			if($pais->id == ""){
				throw new Exception("El pais no existe.");
			}

			if($id == 'estado'){
				$estados = $pais->estados();
				$this->estados = $estados;
			}else{
				throw new Exception("La informaci&oacute;n no es valida.");
			}
			
			$this->option = $id;
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
	}
	
	public function editar($id){

		if($this->post('id')!= ''){
			$id =$this->post('id');
		}

		$this->set_response("view");
		try{

			$municipio = new Municipio();
			$municipio = $municipio->find($id);
			if($municipio->id == ""){
				throw new Exception("El municipio no existe.");
			}
			
			$edo = $municipio->edo();
			$pais = $edo->pais();
			
			$municipios = $edo->municipios();
			$estados = $pais->estados();
			
			$this->municipios = $municipios;
			$this->estados = $estados;
			
			$this->municipio = $municipio;
			$this->edo = $edo;
			
			$this->option = 'exito';
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
	}

	public function estados($id = 'MEX'){
		if($this->post('id')!= ''){
			$id =$this->post('id');
		}

		$this->set_response("view");
		try{
			if($id!=''){
				$pais = new Pais();
				$pais = $pais->find_first("clave = '".$id."'");

				if($pais->id == ""){
					throw new Exception("El pais no existe.");
				}
				
				$estados = $pais->estados();
				$this->estados = $estados;

				$this->option = 'exito';
			}else{
				throw new Exception("La informaci&oacute;n no es valida.");
			}
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
	}

	public function municipios($id){
		if($this->post('id')!= ''){
			$id =$this->post('id');
		}

		$this->set_response("view");
		try{
			if($id!=''){
				$estado = new Edo();
				$estado = $estado->find($id);

				if($estado->id == ""){
					throw new Exception("El estado no existe.");
				}
				$municipios = $estado->municipios();
				$this->municipios = $municipios;

				$this->option = 'exito';
			}else{
					
				throw new Exception("La informaci&oacute;n no es valida.");
			}
		}catch (Exception $e){
			$this->error($e->getMessage());
		}
	}
}