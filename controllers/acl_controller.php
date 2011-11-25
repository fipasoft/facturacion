<?php


/**
 * ACL
 * Creado el 03/07/2008
 * Copyright (C) 2008 FiPa Software (contacto at fipasoft.com.mx)
 */

Kumbia :: import('lib.phpgacl.main');

class AclController extends ApplicationController {
	public $template = "sesion";

	public function crear(){
		$acl = new gacl_api();

		$this->privilegios = array ();
		$this->acl_section = array ();
		$this->aco = array ();
		$this->aco_section = array ();
		$this->aro = array ();
		$this->aro_section = array ();
		$this->grupos = array ();
		$this->privilegios = array ();
		$this->lista_acl = array ();
		$this->grupos_asignados = array ();

		/* BD reset
		 * Limpia la base de datos ACL, los registros de la tabla ACLSection no se eliminan...
		 */
		$acl->clear_database();

		$val = $this->acl_section['value'] = 'sistema';
		$this->acl_section['id'] = $acl->add_object_section($val, $val, 0, 0, 'ACL');

		/* ACOs, Creacion de los Access Control Objects
		 * El arreglo posee la siguiente forma
		 * array( ACO_Section1 =<> array(ACO_1, ..., ACO_N), ... ACO_SectionN => array(...))
		 */
		// El menu
		$acos = array (
		// comunes
			'ALL' => array (
				'ALL'
				),
			'sesion' => array (
				'abrir',
				'autenticar',
				'cerrar',
				'index',
				'restringir'
				),
				// System
			'inicio' => array (
				'index'
				),
			'facturas' => array(
				'agregar',
				'eliminar',
				'index',
				'imprimir',
				'cancelar',
				'editar',
				'control',
				'ver'
				),
            'externas' => array (
                'agregar',
                'editar',
                'eliminar',
                'index',
                'fiscales'
                ),
			'ejercicios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'directorio' => array (
				'index'
				),
			'sistema' => array(
				'ayuda',
				'autocompletar',
				'configuracion',
				'password'
				),
			'usuarios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'password',
				'validarLogin',
				'verAcceso',
				'ver',
				),
			'historial' => array(
				'buscar',
				'exportar',
				'index',
				'ver'
				),

			'contactos' => array (
				'agregar',
				'editar',
				'campo',
				'eliminar',
				'index'
				),
			'dependencias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'contactos'
				),
                
            'localizacion' => array (
                'inicia',
                'editar',
                'estados',
                'municipios'
                ),
				);
				$i = 0;
				foreach ($acos as $section => $objects) {
					$this->aco_section[$section] = $acl->add_object_section($section, $section, $i, 0, 'ACO');
					$j = 0;
					foreach ($objects as $sect => $obj) {
						$this->aco[$section][$obj] = $acl->add_object($section, $obj, $obj, $j, 0, 'ACO');
					}
					$i++;
				}

				/* AROs creacion de los Access Request Objects
				 * El arreglo tiene la siguiente forma:
				 * array(ARO_Section1 => array(GROUP => ARO, ..., GROUP_N => ARO_N), ..., ARO_SectionN => array(..))
				 */

		  $aros = $lista = array (
			'usuarios' => array (
				'usuarios' => array(
					'anonimo'
					),
				'root' => array(
					'root'
					),
				'administradores' => array(
					'admin'
					),
				'autenticados' => array(
					'_'
					)
					)
					);

					$i = 0;
					foreach ($lista as $section => $objects) {
						$this->aro_section[$section] = $acl->add_object_section($section, $section, $i, 0, 'ARO');
						$j = 0;
						foreach ($objects as $objs) {
							foreach($objs as $obj){
								$this->aro[$obj] = $acl->add_object($section, $obj, $obj, $j, 0, 'ARO');
							}
						}
						$i++;
					}

					// Grupos
					/*
					 * Usuarios
					 *  |-Root
					 *  |-Administradores
					 *  |-Editores
					 *  '-Consulta
					 */
					$lista = array (
			'usuarios' => 0,
			'autenticados' => 'usuarios',
			'root' => 'autenticados',
			'administradores' => 'autenticados'
			);
			$this->grupos[0] = 0; // trick para generar la raiz
			foreach ($lista as $group => $parent) {
				$this->grupos[$group] = $acl->add_group($group, $group, $this->grupos[$parent], 'aro');
			}

			/* Privilegios
			 *
			 * Se establece un arreglo con los privilegios por grupo
			 * $this->privilegios[GRUPO][] = array(ACO_SECCION, array(ACOS))
			 */
	 	// grupo usuarios
			$this->privilegios['usuarios'][] = array (
			'sesion' => array(
				'abrir',
				'autenticar',
				'cerrar',
				'index',
				'restringir'
				)
				);
				// grupo root
				$this->privilegios['root'][] = array (
			'ALL' => $acos['ALL']
				);
				// grupo autenticados
				$this->privilegios['autenticados'][] = array (
			'inicio' => array (
				'index'
				)
				);
				$this->privilegios['autenticados'][] = array (
			'sistema' => array (
				'ayuda',
				'autocompletar',
				'configuracion',
				'password'
				)
				);
                
                
                $this->privilegios['administradores'][] = array (
                'localizacion' => array (
                'inicia',
                'editar',
                'estados',
                'municipios'
                ));
                
				$this->privilegios['administradores'][] = array (
			'dependencias' => array (
				'agregar',
                'editar',
                'eliminar',
                'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'externas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
                'fiscales'
				)
				);
				$this->privilegios['administradores'][] = array (
			'ejercicios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'historial' => array(
				'buscar',
				'exportar',
				'index',
				'ver'
				)
				);
				
				 
				$this->privilegios['administradores'][] = array (
				 'usuarios' => array (
				 'agregar',
				 'editar',
				 'eliminar',
				 'index',
				 'password',
				 'validarLogin',
				 'verAcceso',
				 'ver'
				 )
				 );
                 
                 $this->privilegios['administradores'][] = array (
                 'facturas' => array (
                 'agregar',
                 'eliminar',
                 'index',
                 'imprimir',
                 'cancelar',
                 'editar',
                 'control',
                 'ver'
                 )
                 );
				
				

				// carga los permisos en la lista acl
				$i = 0;
				foreach ($this->privilegios as $grupo => $lst) {
					foreach ($lst as $_acos) {
						$id = $acl->add_acl($_acos, NULL, array (
						$this->grupos[$grupo]
						));
						if ($id !== FALSE) {
							$this->lista_acl[$id] = $grupo . ' ACL ' . $id;
						} else {
							$this->lista_acl[$i] = 'ERROR!';
							$i++;
						}
					}
				}

				// asigna usuarios a los grupos
				foreach ($aros as $sec => $lista) {
					foreach ($lista as $grp => $aros) {
						foreach($aros as $aro){
							$this->grupos_asignados[$grp . ' ' . $sec . '-' . $aro] = $acl->add_group_object($this->grupos[$grp], $sec, $aro);
						}
					}
				}

	}

	public function actualizar(){

	}
}
?>