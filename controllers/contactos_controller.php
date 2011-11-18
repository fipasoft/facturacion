<?php
// publicidad, Creado el 30/06/2009
/**
 * Contactos
 *
 * @package    Controladores
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */

class ContactosController extends ApplicationController{
 	public $template = "system";

 	public function agregar(){
		if( $this->post('nombre') == '' ){

			$ejercicio_id = Session :: get_data( 'eje.id' );

			$dependencias = new Dependencia();
			$dependencias = $dependencias->find(
				"conditions: ejercicio_id = '" . $ejercicio_id . "' ",
				"order: clave"
			);

			$proveedores = new Proveedor();
			$proveedores = $proveedores->find(
				"conditions: ejercicio_id = '" . $ejercicio_id . "' ",
				"order: nombre"
			);

			$this->dependencias    =    $dependencias;
			$this->ejercicio_id    =    $ejercicio_id;
			$this->option          =    'captura';
			$this->proveedores     =    $proveedores;

		}else{
			$this->option = '';
			$this->error = '';

			$contacto = new Contacto();
			$contacto->titulo            =  $this->post( 'titulo' );
			$contacto->cargo             =  $this->post( 'cargo' );
			$contacto->nombre            =  $this->post( 'nombre' );
			$contacto->ap                =  $this->post( 'ap' );
			$contacto->am                =  ($this->post( 'am' ) == ''? ' ' : $this->post( 'am' ));
			$contacto->tel               =  $this->post( 'tel' );
			$contacto->cel               =  $this->post( 'cel' );
			$contacto->domicilio         =  $this->post( 'domicilio' );
			$contacto->trunk             =  $this->post( 'trunk' );
			$contacto->mail              =  ($this->post( 'mail' ) == ''? ' ' : $this->post( 'mail' ));
			$contacto->sexo              =  $this->post( 'sexo' );
			$contacto->observaciones     =  $this->post( 'observaciones' );

			if( $contacto->save() ){

				if( $this->post( 'institucion' ) == 'd' ){
					$depCnt                    =    new Depcontacto();
					$depCnt->ejercicio_id      =    $this->post( 'ejercicio_id' );
					$depCnt->contacto_id       =    $contacto->id;
					$depCnt->dependencia_id    =    $this->post( 'dependencia_id' );
					$depCnt->save();

				}else{
					$provCnt                    =    new Provcontacto();
					$provCnt->ejercicio_id      =    $this->post( 'ejercicio_id' );
					$provCnt->contacto_id       =    $contacto->id;
					$provCnt->proveedor_id      =   $this->post( 'proveedor_id' );
					$provCnt->save();

				}

				// historial
				$historial = new Historial();
				$historial->ejercicio_id    =   $contacto->ejercicio_id;
				$historial->usuario         =   Session :: get_data( 'usr.login' );
				$historial->descripcion     =   utf8_encode(
													'Agreg� ' .
													utf8_decode( $contacto->nombre ) . ' ' .
													utf8_decode( $contacto->ap ) .  ' ' .
													utf8_decode( $contacto->am ) .  ' (' .
													utf8_decode( $contacto->institucionNombre() ) .
													') ' .
													'[cnt' . $contacto->id . '] '
												);
				$historial->controlador     =   $this->controlador;
				$historial->accion          =   $this->accion;
				$historial->save();

				$this->option = 'exito';
			}else{
				$this->option = 'error';
				$this->error .= ' Error al guardar en la BD.'. $contacto->show_message();
			}

		}

 	}

 	public function campo(){
 		$this->set_response("view");
 		try{
 			$contacto_id	=	$this->post('contacto_id');
 			$campo			=	$this->post('campo');
 			
 			if($campo != 'domicilio'){
 				throw new Exception("El campo no es valido.");
 			}
 			
 			$contacto = new Contacto();
 			$contacto = $contacto->find($contacto_id);
 			
 			if($contacto->id == ''){
 				throw new Exception("El contacto no existe.");	
 			}
 			
 			$this->option = "exito";
 			$this->campo = $contacto->$campo;
 			
 		}catch(Exception $e){
 			$this->error($e->getMessage());
 		}
 	}
 	
 	public function editar( $id = '' ){
		$this->option = 'error';
		$this->error = '';

		if( $id != '' ){
			$contacto = new Contacto();
			$contacto = $contacto->find( $id );
			if( $contacto->id != '' ){

				$institucion  = $contacto->institucion();
				$ejercicio_id = $institucion->ejercicio_id;

				$dependencias = new Dependencia();
				$dependencias = $dependencias->find(
					"conditions: ejercicio_id = '" . $ejercicio_id . "' ",
					"order: clave"
				);

				$proveedores = new Proveedor();
				$proveedores = $proveedores->find(
					"conditions: ejercicio_id = '" . $ejercicio_id . "' ",
					"order: nombre"
				);

				$this->contacto        =    $contacto;
				$this->dependencias    =    $dependencias;
				$this->ejercicio_id    =    $ejercicio_id;
				$this->institucion     =    $institucion;
				$this->option          =    'captura';
				$this->proveedores     =    $proveedores;

			}else{
				$this->error = 'El contacto seleccionado no existe.';

			}

		}else if( $this->post( 'id' ) != '' ){
			$contacto = new Contacto();
			$contacto = $contacto->find( $this->post( 'id' ) );

			$this->option = '';
			$this->error  = '';

			if( $contacto->id != '' ){

					if( $contacto->eliminarLigas() ){

						$contacto->titulo            =  $this->post( 'titulo' );
						$contacto->cargo             =  $this->post( 'cargo' );
						$contacto->nombre            =  $this->post( 'nombre' );
						$contacto->ap                =  $this->post( 'ap' );
						$contacto->am                =  $this->post( 'am' );
						$contacto->tel               =  $this->post( 'tel' );
						$contacto->cel               =  $this->post( 'cel' );
						$contacto->domicilio         =  $this->post( 'domicilio' );
						$contacto->trunk             =  $this->post( 'trunk' );
						$contacto->mail              =  $this->post( 'mail' );
						$contacto->sexo              =  $this->post( 'sexo' );
						$contacto->observaciones     =  $this->post( 'observaciones' );

						if( $contacto->save() ){

							if( $this->post( 'institucion' ) == 'd' ){
								$depCnt                    =    new Depcontacto();
								$depCnt->ejercicio_id      =    $this->post( 'ejercicio_id' );
								$depCnt->contacto_id       =    $contacto->id;
								$depCnt->dependencia_id    =    $this->post( 'dependencia_id' );
								$depCnt->save();

							}else{
								$provCnt                    =    new Provcontacto();
								$provCnt->ejercicio_id      =    $this->post( 'ejercicio_id' );
								$provCnt->contacto_id       =    $contacto->id;
								$provCnt->proveedor_id      =    $this->post( 'proveedor_id' );
								$provCnt->save();

							}


							// historial
							$historial = new Historial();

							$historial->ejercicio_id    =   $contacto->ejercicio_id;
							$historial->usuario         =   Session :: get_data( 'usr.login' );
							$historial->descripcion     =   utf8_encode(
																'Edit� ' .
																utf8_decode( $contacto->nombre ) . ' ' .
																utf8_decode( $contacto->ap ) .  ' ' .
																utf8_decode( $contacto->am ) .  ' (' .
																utf8_decode( $contacto->institucionNombre() ) .
																') ' .
																'[cnt' . $contacto->id . '] '
															);
							$historial->controlador     =   $this->controlador;
							$historial->accion          =   $this->accion;
							$historial->save();

							$this->option = 'exito';
						}else{
							$this->option = 'error';
							$this->error .= ' Error al guardar en la BD.'. $contacto->show_message();
						}

					}else{
							$this->error = 'No se pudieron eliminar los v&iacute;nculos del contacto';
					}

			}else{
				$this->error = 'El contacto seleccionado no existe.';
			}

		}else{
			$this->error .= ' No se especific&oacute; el contacto.';
		}
 	}

 	public function eliminar( $id = '' ){
 		if($id != ''){
 			$contacto = new Contacto();
			$this->contacto  =  $contacto->find( $id );
			$this->option    =  'captura';

			if( $this->contacto->id == '' ){
				$this->option = 'error';
				$this->error = 'El contacto no existe.';
			}

		}else if($this->post('id') != ''){
			$this->option   =  '';
			$this->error    =  '';

			$contacto = new Contacto();
			$contacto = $contacto->find( $this->post( 'id' ) );

			$_contacto = new stdClass();
			$_institucion             =  $contacto->institucion();
			$_contacto->id            =  $contacto->id;
			$_contacto->ejercicio_id  =  $institucion->ejercicio_id;
			$_contacto->nombre        =  $contacto->nombre . ' ' . $contacto->ap . ' ' . $contacto->am;

			if( $contacto->id != '' ){
				// eliminando la contacto
				try{
					$contacto->delete( $contacto->id );
					$this->option = 'exito';

					// historial
					$historial = new Historial();
					$historial->ejercicio_id    =   $_contacto->ejercicio_id;
					$historial->usuario         =   Session :: get_data( 'usr.login' );
					$historial->descripcion     =   utf8_encode(
														'Elimin� ' .
														utf8_decode( $_contacto->nombre ) . ' ' .
														'(' . utf8_decode( $_institucion->nombre ) . ')' .
														'[cnt' . $_contacto->id . '] '
													);
					$historial->controlador     =   $this->controlador;
					$historial->accion          =   $this->accion;
					$historial->save();

				}catch(dbException $e){
					$this->option = 'error';
					$this->error .= 'Error al intentar eliminar de la BD. ' .
									'Posiblemente existan datos vinculados al contacto.';
				}
			}else{
				$this->option = 'error';
				$this->error = 'El contacto no existe.';
			}
		}else{
			$this->option = 'error';
			$this->error = ' No se especific&oacute; el contacto a eliminar.';
		}

 	}

 	public function index( $pag = '' ){
 		// vars
 		$controlador = $this->controlador;
		$accion = $this->accion;
		$path = $this->path = KUMBIA_PATH;
		$ejercicio_id = Session :: get_data( 'eje.id' );

		// busqueda
		$b = new Busqueda($controlador, $accion);
		$b->campos();

		// genera las condiciones
		$b->establecerCondicion(
			'proveedor',
			"(institucion_tipo = 'P' AND institucion_id = '" . $b->campo( 'proveedor' ) . "' ) "
		);
		$b->establecerCondicion(
			'dependencia',
			"(institucion_tipo = 'D' AND institucion_id = '" . $b->campo( 'dependencia' ) . "' ) "
		);
		$c = $b->condicion();

		// cuenta todos los registros
		$contactos = new Contacto();
		$registros = $contactos->count_by_sql(
			"SELECT " .
				"COUNT(*) " .
			"FROM " .
				"( " .
					$contactos->unionDeProveedoresYDependencias() .
				")AS contacto " .
			"WHERE " .
				"ejercicio_id = '" . $ejercicio_id . "' " .
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
		$contactos = $contactos->find_all_by_sql(
			"SELECT " .
				"* " .
			"FROM " .
				"( " .
					$contactos->unionDeProveedoresYDependencias() .
				")AS contacto " .
			"WHERE " .
				"ejercicio_id = '" . $ejercicio_id . "' " .
				( $c == "" ? "" : "AND " . $c ) .
			"ORDER BY " .
				"ap, am, nombre " .
			"LIMIT " .
				( $paginador->pagina() * $paginador->rpp() ) . ', ' .
				$paginador->rpp()
	  	);

	  	// catalogos
		$proveedores = new Proveedor();
		$proveedores = $proveedores->find(
			"conditions: ejercicio_id = '" . $ejercicio_id . "'",
			"order: nombre "
		);

		$dependencias = new Dependencia();
		$dependencias = $dependencias->find(
			"conditions: ejercicio_id = '" . $ejercicio_id . "'",
			"order: nombre "
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
		$this->busqueda      =  $b;
		$this->contactos     =  $contactos;
		$this->dependencias  =  $dependencias;
		$this->paginador     =  $paginador;
		$this->proveedores   =  $proveedores;
		$this->registros     =  $registros;
 	}

}
?>
