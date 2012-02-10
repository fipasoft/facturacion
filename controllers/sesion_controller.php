<?php
/** SP5
 * Creado el 03/07/2008
 * Copyright (C) 2008 FiPa Software (contacto at fipasoft.com.mx)
 */

Kumbia :: import('app.componentes.*');
Kumbia :: import('lib.phpgacl.main');

class SesionController extends ApplicationController {

	public function index(){
		$this->redirect('sesion/autenticar', 0);
	}

	public function abrir() {
		$Usuarios = new Usuario();
		$c =  "pass = '" . sha1($this->post('pass')) . "'" .
			  "AND login = '" . $this->post('login') . "' " ;

		if($Usuarios->count($c) == 1){
			$Usuario = $Usuarios->find_first('conditions: ' . $c);

			$ejercicio = new Ejercicio();
			$ejercicio = $ejercicio->find_first("order: annio DESC");

			$m = new Menu();
			$gacl = new gacl_extra();
			$sys = Config :: read('sys.ini');

			$acl_global = $gacl->acl_check_multiple(
              array(
                  'sistema' => array(
                        'index'
                  )
              ),
              $Usuario->login
            );

			Session :: unset_data(
				'app.busqueda',
				'app.paginador'
			);

			Session :: set_data( 'acl.global',        $acl_global );
			Session :: set_data( 'sys.dependencia',   $sys->dependencia->nombre );
			Session :: set_data( 'sys.departamento',  $sys->departamento->nombre );
			Session :: set_data( 'sys.ciudad',        $sys->dependencia->ciudad );
			Session :: set_data( 'sys.estado',        $sys->dependencia->estado );
			Session :: set_data( 'eje.id',            $ejercicio->id );
			Session :: set_data( 'impuesto.iva',      $sys->impuesto->iva );
			Session :: set_data( 'usr.id',            $Usuario->id );
			Session :: set_data( 'usr.login',         $Usuario->login );
			Session :: set_data( 'usr.nombre',        $Usuario->nombre );
			Session :: set_data( 'usr.menu',          $m->menuSimple() );
			Session :: set_data( 'usr.acceso',        $gacl->get_user_acos($Usuario->login) );
			Session :: set_data( 'usr.grupos',        $gacl->get_user_groups($Usuario->login) );


			if( in_array('root', Session :: get_data('usr.grupos')) ){
				Session :: set_data( 'usr.grupos', $gacl->get_all_groups() );
			}

			$this->redirect('inicio', 0);

		}else{
			$this->redirect('sesion/autenticar/' . sha1('err_login'), 0);
		}
	}

	public function autenticar() {
		if(Session :: isset_data('usr.id')    &&
		   Session :: isset_data('usr.login') &&
		   Session :: isset_data('usr.nombre') )
		{
			$login = Session :: get_data('usr.login');
			if( $login != '' &&
				$login != 'anonimo')
			{
				$this->redirect('inicio');
			}
		}
	}

	public function cerrar() {
		Session::unset_data(
		    'acl.global',
			'app.busqueda',
			'app.paginador',
			'eje.id',
			'usr.id',
			'usr.login',
			'usr.nombre',
			'usr.menu',
			'usr.acceso',
			'usr.grupos',
			'impuesto.iva',
			'sys.departamento',
			'sys.dependencia',
			'sys.ciudad',
			'sys.estado'
		);
		$this->redirect('sesion/autenticar');
	}

	public function restringir() {

	}
}
?>