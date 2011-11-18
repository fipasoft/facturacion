<?php
Kumbia :: import('app.componentes.*');
Kumbia :: import('lib.phpgacl.main');
	
/** SP5
 * Creado el 03/07/2008
 * Copyright (C) 2008 FiPa Software (contacto at fipasoft.com.mx)
 */

class UsuariosController extends ApplicationController {
	public $template = "system";
		
	public function agregar(){
		if($this->post('nombre') == ''){
			$this->option = 'captura';
			$gacl_x = new gacl_extra();
			$this->grupos = $gacl_x->get_all_groups();
		}else{
			$this->option = '';
			$this->error = '';
			$usuario = new Usuario();
			$usuario->nombre = $this->post('nombre');
			$usuario->ap = $this->post('ap');
			$usuario->am = $this->post('am');
			$usuario->mail = $this->post('mail');
			$usuario->login = $this->post('login');
			$usuario->pass = sha1($this->post('pass'));
			$usuario->validates_uniqueness_of('login');
			if($usuario->save()){
				$this->option = 'exito';
				$gacl = new gacl_api();
				foreach($this->post('grupo') as $grupo){
					if($gacl->add_object('usuarios', $usuario->login, $usuario->login, 0, 0, 'ARO')){
						if(!$gacl->add_group_object($grupo, 'usuarios', $usuario->login)){
							$this->option = 'error';
							$this->error .= ' No se pudo agregar el usuario al grupo seleccionado.';
						}
					}else{
						$this->option = 'error';
						$this->error .= ' No se pudo crear el ARO en la lista ACL.';
					}
					
				}
			}else{
				$this->option = 'error';
				$this->error .= ' Error al guardar en la BD.';
			}
		}
	}

	public function editar($id = ''){
		if($id != ''){
			$this->option = 'captura';
			$id = intval($id, 10);
			$Usuarios = new Usuario();
			$this->usuario = $Usuarios->find($id);
			$gacl_x = new gacl_extra();
			$usr_grupos = $gacl_x->get_user_groups($this->usuario->login);
			$this->usr_grupo = $usr_grupos[0];
			$this->grupos = $gacl_x->get_all_groups();
			if($this->usuario->id == ''){
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}
		}else if($this->post('nombre') != ''){
			$this->option = '';
			$this->error = '';
			$usuario = new Usuario();
			$usuario = $usuario->find($this->post('id'));
			$usuario->nombre = $this->post('nombre');
			$usuario->ap = $this->post('ap');
			$usuario->am = $this->post('am');
			$usuario->mail = $this->post('mail');
			if($usuario->id != ''){
				if($usuario->save()){
					$this->option = 'exito';
					$gacl = new gacl_api();
					$aro = $gacl->get_object_id('usuarios', $usuario->login, 'ARO');
					$gacl->del_object($aro, 'ARO', TRUE);
					foreach($this->post('grupo') as $grupo){
						if($gacl->add_object('usuarios', $usuario->login, $usuario->login, 0, 0, 'ARO')){
							if(!$gacl->add_group_object($grupo, 'usuarios', $usuario->login)){
								$this->option = 'error';
								$this->error .= ' No se pudo agregar el usuario al grupo seleccionado.';
							}
						}else{
							$this->option = 'error';
							$this->error .= ' No se pudo crear el ARO en la lista ACL.';
						}
						
					}
				}else{
					$this->option = 'error';
					$this->error .= ' Error al guardar en la BD.';
				}				
			}else{
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}
		}else{
			$this->option = 'error';
			$this->error = ' El usuario no existe.';			
		}
	}	

	public function eliminar($id = ''){
		if($id != ''){
			$this->option = 'captura';
			$id = intval($id, 10);
			$Usuarios = new Usuario();
			$this->usuario = $Usuarios->find($id);
			if($this->usuario->id == ''){
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}
		}else if($this->post('id') != ''){			
			$this->option = '';
			$this->error = '';
			$Usuarios = new Usuario();
			$usuario = $Usuarios->find($this->post('id'));
			if($usuario->id != ''){
				// eliminado el usuario
				$login = $usuario->login;
				if($Usuarios->delete($this->post('id'))){
					$this->option = 'exito';
				}else{
					$this->option = 'error';
					$this->error .= ' Error al intentar eliminar de la BD.';					
				}

				// eliminandolo de sus grupos en ACL
				$gacl = new gacl_api();
				$aro = $gacl->get_object_id('usuarios', $login, 'ARO');
				// eliminadolo de la lista ACL
				if(!$gacl->del_object($aro, 'ARO', TRUE)){
					$this->option = 'error';
					$this->error .= ' No se pudo eliminar de la lista ACL.';
				}		
			}else{
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}
		}else{
			$this->option = 'error';
			$this->error = ' No se especific&oacute; el usuario a eliminar.';
		}
	}

	public function index($pag = ''){
		$Usuarios = new Usuario();
		$controlador = $this->controlador;
		$accion = $this->accion;
		$path = $this->path = KUMBIA_PATH;
		$this->gacl_x = new gacl_extra();		
		
		// busqueda
		$b = new Busqueda($controlador, $accion);
		$b->campos();
		$b->establecerCondicion(
					'nombre',
					"CONCAT(nombre, ' ', ap, ' ', am) LIKE '%" . $b->campo('nombre') . "%' "
		);
		// genera las condiciones
		$c = $b->condicion();
		$this->busqueda = $b;
		
		// cuenta todos los registros
		$this->registros = $Usuarios->count(($c == '' ? '' : $c));
		
		// paginacion
		$paginador = new Paginador($controlador, $accion);
		if($pag != ''){
			$paginador->guardarPagina($pag);
		}
		$paginador->estableceRegistros($this->registros);
		$paginador->generar();
		$this->paginador = $paginador;

		// ejecuta la consulta
		$this->usuarios = $Usuarios->find(
							'conditions: ' . ($c == "" ? "1" : $c),
							'order: ap, am, nombre',
							'limit: ' . ($paginador->pagina() * $paginador->rpp()) . ', ' 
									  . $paginador->rpp()
						  );
	}

	public function password($id = ''){
		if($id != ''){
			$this->option = 'captura';
			$id = intval($id, 10);
			$Usuarios = new Usuario();
			$this->usuario = $Usuarios->find($id);
			if($this->usuario->id == ''){
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}
		}else if($this->post('pass') != ''){			
			$this->option = '';
			$this->error = '';
			$usuario = new Usuario();
			$usuario = $usuario->find($this->post('id'));
			if($usuario->id != ''){
				if($this->post('pass') == $this->post('pass2')){
					if(strlen($this->post('pass')) >= 6){
						$usuario->pass = sha1($this->post('pass'));		
						if($usuario->save()){
							$this->option = 'exito';
						}else{
							$this->option = 'error';
							$this->error .= ' Error al guardar en la BD.';
						}
					}else{
						$this->option = 'error';
						$this->error .= ' La longitud m&iacute;nima del password es de 6 caracteres.';
					}
				}else{
					$this->option = 'error';
					$this->error .= ' No coincide la confirmaci&oacute;n del password.';
				}				
			}else{
				$this->option = 'error';
				$this->error = ' El usuario no existe.';	
			}
		}else{
			$this->option = 'error';
			$this->error = ' No se especific&oacute; el usuario.';	
		}
	}

	public function validarLogin(){
		$this->set_response('view');
		$this->login = $login = $this->post('login');
		if($login != ''){
			$usuarios = new Usuario();
			if($usuarios->count("login = '" . $login . "'") == 0){
				$this->disponible = true;
			}else{
				$this->disponible = false;
			}
		}else{
			$this->disponible = false;
		}
	}
	
	public function ver($id = ''){
		$id = intval($id, 10);
		$Usuarios = new Usuario();
		$this->usuario = $Usuarios->find($id);
		$m = new Menu();
		$this->acceso = $m->menuPrincipal($this->usuario->login);
		$gacl_x = new gacl_extra();
		$this->grupos = $gacl_x->get_user_groups($this->usuario->login);
	}
	
	public function verAcceso(){
		$this->set_response('view');
		$this->grupo = $grupo = strtolower($this->post('grupo'));
		if($grupo != ''){
			$gacl_x = new gacl_extra();	
			$this->acceso = $gacl_x->get_group_acos($grupo);
		}else{
			$this->acceso = array();
		}
	}
}
?>