<?php
class ExternasController extends ApplicationController{
	public $template = "system";

	public function agregar(){
		if( $this->post('nombre') == '' ){
			$this->option = 'captura';
			$this->ejercicio_id = Session :: get_data( 'eje.id' );

		}else{
			$this->option = '';
			$this->error = '';
			$existentes = new Dependencia();
			if($existentes->count(
					"clave = '" . $this->post('clave') . "' ".
					"AND ejercicio_id = '" . $this->post( 'ejercicio_id' ) . "' ".
					"AND nombre = '" . $this->post('nombre') . "' " 
					) == 0
					){

						$fiscal = new Fiscal();
						$fiscal->rfc = trim($this->post('rfc'));
						$fiscal->razon = trim($this->post('razon'));
						$fiscal->domicilio = trim($this->post('domicilio'));
						$fiscal->colonia = trim($this->post('colonia'));
						$fiscal->cp = trim($this->post('cp'));
						$fiscal->municipio_id = $this->post("municipio_id");
						if($fiscal->save()){
							$dependencia = new Dependencia();
							$dependencia->clave            =  $this->post('clave');
							$dependencia->nombre            =  $this->post('nombre');
							$dependencia->ejercicio_id      =  $this->post('ejercicio_id');
							$dependencia->fiscal_id = $fiscal->id;
							$dependencia->externo = 1;
							if( $dependencia->save() ){
								// historial
								$historial = new Historial();
								$historial->ejercicio_id    =   $dependencia->ejercicio_id;
								$historial->usuario         =   Session :: get_data( 'usr.login' );
								$historial->descripcion     =   utf8_encode(
														'Agreg� ' .
								utf8_decode( $dependencia->clave ) . ' - ' .
								utf8_decode( $dependencia->nombre ) . ' ' .
														'[dep' . $dependencia->id . '] '
														);
														$historial->controlador     =   $this->controlador;
														$historial->accion          =   $this->accion;
														$historial->save();
															
														$this->option = 'exito';
							}else{
								$this->option = 'error';
								$this->error .= ' Error al guardar en la BD.'. $dependencia->show_message();
							}
						}else{
							$this->option = 'error';
							$this->error .= ' Error al guardar en la BD.'. $dependencia->show_message();
						}
					}else{
						$this->option = 'error';
						$this->error .= 'La dependencia no se agreg&oacute; debido a que ya existe un registro con esos datos.';
					}
		}
	}

	public function editar( $id = '' ){
		$this->option = 'error';
		$this->error = '';

		if( $id != '' ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find($id);
			if( $dependencia->id != '' ){

				if($dependencia->externo==1){
					$this->option         =  'captura';
					$this->dependencia    =  $dependencia;
				}else{
					$this->error = 'La dependencia no es externa.';
				}


			}else{
				$this->error = 'La dependencia seleccionada no existe.';

			}

		}else if( $this->post('id') != '' ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find( $this->post('id') );

			if($dependencia->id != ''){
				if($dependencia->externo==1){
					$existentes = new Dependencia();

					if($existentes->count(
						"clave = '" . $this->post( 'clave' ) . "' ".
						"AND ejercicio_id = '" . $dependencia->ejercicio_id . "' ".
						"AND nombre = '" . $this->post('nombre' ) . "' "  .
						"AND id != '" . $this->post('id' ) . "' "
						) == 0)
						{

							$fiscal = $dependencia->fiscal();
							if($fiscal->id!=""){
								$fiscal->rfc = trim($this->post('rfc'));
								$fiscal->razon = trim($this->post('razon'));
								$fiscal->domicilio = trim($this->post('domicilio'));
								$fiscal->colonia = trim($this->post('colonia'));
								$fiscal->cp = trim($this->post('cp'));
								$fiscal->municipio_id = $this->post('municipio_id');
								if($fiscal->save()){
									$dependencia->clave             =   $this->post( 'clave' );
									$dependencia->nombre            =   $this->post( 'nombre' );

									if( $dependencia->save() ){
										// historial
										$historial = new Historial();
										$historial->ejercicio_id    =   $dependencia->ejercicio_id;
										$historial->usuario         =   Session :: get_data( 'usr.login' );
										$historial->descripcion     =   utf8_encode(
															'Edit� ' .
										utf8_decode( $dependencia->clave ) . ' - ' .
										utf8_decode( $dependencia->nombre ) . ' ' .
															'[dep' . $dependencia->id . '] '
															);
															$historial->controlador     =   $this->controlador;
															$historial->accion          =   $this->accion;
															$historial->save();

															$this->option = 'exito';
									}else{
										$this->option = 'error';
										$this->error .= ' Error al guardar en la BD.'. $dependencia->show_message();
									}
								}else{
									$this->option = 'error';
									$this->error .= ' Error al guardar en la BD.';
								}
							}else{
								$this->option = 'error';
								$this->error .= 'La informacion fiscal no existe.';
							}
						}else{
							$this->option = 'error';
							$this->error .= 'La dependencia no se edit&oacute; debido a que ya existe un registro con esos datos.';
						}
				}else{
					$this->error = 'La dependencia seleccionada no existe.';
				}
			}else{
				$this->error = 'La dependencia seleccionada no existe.';
			}

		}else{
			$this->error .= ' No se especific&oacute; la dependencia.';
		}
	}

	public function eliminar( $id = '' ){
		if($id != ''){
			$this->option = 'captura';
			$dependencia = new Dependencia();
			$this->dependencia = $dependencia->find($id);
			if($this->dependencia->id == ''){

				if($dependencia->externo==1){
					$this->option = 'error';
					$this->error = 'La dependencia no existe.';
				}else{
					$this->option = 'error';
					$this->error = 'La dependencia no existe.';
				}
			}

		}else if($this->post('id') != ''){
			$this->option = '';
			$this->error = '';
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find( $this->post('id') );
				
			$_dependencia = new stdClass();
			$_dependencia->id            =  $dependencia->id;
			$_dependencia->ejercicio_id  =  $dependencia->ejercicio_id;
			$_dependencia->clave         =  $dependencia->clave;
			$_dependencia->nombre        =  $dependencia->nombre;

			if( $dependencia->id != '' ){
				// eliminando la dependencia

				if($dependencia->externo==1){
					try{
						$fiscal = $dependencia->fiscal();
						$dependencia->delete( $dependencia->id );
						$fiscal->delete($fiscal->id);
						$this->option = 'exito';

						// historial
						$historial = new Historial();
						$historial->ejercicio_id    =   $_dependencia->ejercicio_id;
						$historial->usuario         =   Session :: get_data( 'usr.login' );
						$historial->descripcion     =   utf8_encode(
														'Elimin� ' .
						utf8_decode( $_dependencia->clave ) . ' - ' .
						utf8_decode( $_dependencia->nombre ) . ' ' .
														'[dep' . $_dependencia->id . '] '
														);
														$historial->controlador     =   $this->controlador;
														$historial->accion          =   $this->accion;
														$historial->save();
															
					}catch(dbException $e){
						$this->option = 'error';
						$this->error .= 'Error al intentar eliminar de la BD. ' .
									'Posiblemente existan datos vinculados a la dependencia.';
					}
				}else{
					$this->option = 'error';
					$this->error = 'La dependencia no existe.';
				}
			}else{
				$this->option = 'error';
				$this->error = 'La dependencia no existe.';
			}
		}else{
			$this->option = 'error';
			$this->error = ' No se especific&oacute; la dependencia a eliminar.';
		}

	}

	public function index( $pag = '' ){
		// vars
		$controlador = $this->controlador;
		$accion = $this->accion;
		$path = $this->path = KUMBIA_PATH;

		// busqueda
		$b = new Busqueda($controlador, $accion);
		$b->campos();

		// genera las condiciones
		$c = $b->condicion();

		// cuenta todos los registros
		$dependencias = new Dependencia();
		$registros = $dependencias->count_by_sql("SELECT count(dependencia.id)
		FROM dependencia
		INNER JOIN fiscal ON dependencia.fiscal_id = fiscal.id 
		WHERE
		ejercicio_id = '" . Session :: get_data( 'eje.id' ) . "' AND externo='1' " . 
		( $c == "" ? "" : "AND " . $c )
		
		);

		// paginacion
		$paginador = new Paginador( $controlador, $accion );
		if( $pag != '' ){
			$paginador->guardarPagina( $pag );
		}
		$paginador->estableceRegistros( $registros );
		$paginador->generar();

		// ejecuta la consulta
		$dependencias = $dependencias->find_all_by_sql(
			"SELECT dependencia.*
		FROM dependencia
		INNER JOIN fiscal ON dependencia.fiscal_id = fiscal.id 
		WHERE
		ejercicio_id = '" . Session :: get_data( 'eje.id' ) . "' AND externo='1' " . 
		( $c == "" ? "" : "AND " . $c ).
			' ORDER BY clave, nombre '.
			' limit ' . ( $paginador->pagina() * $paginador->rpp() ) . ', ' 
			. $paginador->rpp()
			);

			
	 // verificar privilegios disponibles
          $acl = new gacl_extra();
          $this->acl = $acl->acl_check_multiple(
              array(
                  $this->controlador => array(
            			'agregar',
                  		'editar',
                  		'eliminar'
                  )
              ),
              Session :: get_data( 'usr.login' )
          );
		
			// salida
			$this->busqueda = $b;
			$this->dependencias = $dependencias;
			$this->paginador = $paginador;
			$this->registros = $registros;



	}
}
?>
