<?php
// publicidad, Creado el 22/06/2009
/**
 * Dependencias
 *
 * @package    Controladores
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */
class DependenciasController extends ApplicationController{
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
						$fiscal= $fiscal->UDG();
						if($fiscal->id!=""){
							$dependencia = new Dependencia();
							$dependencia->clave            =  $this->post('clave');
							$dependencia->nombre            =  $this->post('nombre');
							$dependencia->ejercicio_id      =  $this->post('ejercicio_id');
							$dependencia->externo = 0;
							$dependencia->fiscal_id = $fiscal->id;

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
							$this->error .= 'No se encontraron los datos fiscales de la UDG.';
						}
					}else{
						$this->option = 'error';
						$this->error .= 'La dependencia no se agreg&oacute; debido a que ya existe un registro con esos datos.';
					}
		}
	}
	
	public function contactos(){
		$this->set_response("view");
		try{

			$dependencia_id = $this->post("dependencia_id");
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find($dependencia_id);

			if($dependencia->id == ""){
				throw new Exception("La dependencia no existe.");
			}

			$contactos = $dependencia->contactos();
			
			if(count($contactos) == 0){
				throw new Exception("No se han capturado los contactos.");
			}

			$this->contactos	=		$contactos;
			$this->dependencia	=		$dependencia;
			$this->option		=		"exito";

		}catch (Exception $e){
			$this->error($e->getMessage());
		}
	}

	public function editar( $id = '' ){
		$this->option = 'error';
		$this->error = '';

		if( $id != '' ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find($id);
			if( $dependencia->id != '' ){
				if($dependencia->externo==0){
					$this->option         =  'captura';
					$this->dependencia    =  $dependencia;
				}else{
					$this->error = 'La dependencia seleccionada no existe.';
				}

			}else{
				$this->error = 'La dependencia seleccionada no existe.';

			}

		}else if( $this->post('id') != '' ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find( $this->post('id') );

			if($dependencia->id != ''){

				if($dependencia->externo==0){
					$existentes = new Dependencia();

					if($existentes->count(
						"clave = '" . $this->post( 'clave' ) . "' ".
						"AND ejercicio_id = '" . $dependencia->ejercicio_id . "' ".
						"AND nombre = '" . $this->post('nombre' ) . "' "  .
						"AND id != '" . $this->post('id' ) . "' "
						) == 0)
						{

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
				if($dependencia->externo==0){
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
				if($dependencia->externo==0){
					try{
						$dependencia->delete( $dependencia->id );
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
		$registros = $dependencias->count(
			"ejercicio_id = '" . Session :: get_data( 'eje.id' ) . "'  AND externo='0' " . 
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
		$dependencias = $dependencias->find(
			"conditions: ejercicio_id = '" . Session :: get_data( 'eje.id' ) . "'  AND externo='0' " . 
		( $c == "" ? "" : "AND " . $c ),
			'order: clave, nombre',
			'limit: ' . ( $paginador->pagina() * $paginador->rpp() ) . ', ' 
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
