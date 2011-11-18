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
			'insertos' => array(
				'precios',
				'preciosAdmin',
				'index'
				),	
			'spots' => array(
				'precios',
				'preciosAdmin',
				'index'
				),	
			'publicaciones' => array(
				'agregar',
				'editar',
				'eliminar',
				'index'
				),	
			'envios' => array(
				'agregar',
				'campania',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'imprimir',
				'eliminar',
				'index',
				'aprobar',
				'pauta',
				'enviar'
				),
			'facturas' => array(
				'agregar',
				'eliminar',
				'index',
				'imprimir',
				'cancelar',
				'fecha',
				'conceptos',
				'vistaprevia',
				'verificaoficio',
				'ver',
				'control',
				'oficio',
				'facturas',
				'editaroficio',
				'eliminaroficio',
				'imprimiroficio',
				'entregar',
				'epagada',
				'entregaroficio',
				'impactos',
				'campania',
				'avanzado'
				),	
			'pagos' => array(
				'agregardeposito',
				'agregartransferencia',
				'editardeposito',
				'editartransferencia',
				'eliminar',
				'eliminardeposito',
				'exportar',
				'index',
				'status',
				'ver',
				'validafolio'
				),
			'monitor' => array(
				'index',
				'concentrado'
			),
			'categorias' => array(
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'ejercicios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'convenios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'directorio' => array (
				'index'
				),
			'catalogos' => array(
				'index'
				),
			'reportes' => array(
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

				// no importa su orden en el menu
			'folios' => array(
				'eliminar',
				'establecer',
				'factura',
				'index',
				'impreso',
				'obten',
				'valida'
				),	
			'campanias' => array(
				'agregar',
				'editar',
				'eliminar',
				'index',
				'json',
				'ver'
				),
			'canales' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
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
			'externas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'estaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'generos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'grupos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'impresos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'macrogeneros' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'productos' => array (
				'json'
				),
			'programas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'proveedores' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'clonar'
				),
			'sitios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				),
			'secciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'apariciones'
				),
			'solicitudes' => array (
				'agregar',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'editar',
				'eliminar',
				'revisaclave',
				'index',
				'pauta',
				'revisaOficio',
				'aprobar'
				),
			'localizacion' => array (
				'inicia',
				'editar',
				'estados',
				'municipios'
				),
			'oficios' => array (
				'index',
				'editar',
				'eliminar',
				'ver',
				'editar',
				'facturacion'
				)
				
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
					'_'
					),
				'autenticados' => array(
					'_'
					),
				'publicacion' => array(
					'edominguez',
					'enavarrete',
					'publicador',
					'test',
					'tpubli'
					),
				'consulta' => array(
					'asanchez'
					),
				'facturacion' => array(
					'test',
					'marisela',
					'pagos',
					'tfacturacion',
					'rmarquez'
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
			'administradores' => 'autenticados',
			'publicacion' => 'autenticados',
			'facturacion' => 'autenticados',
			'consulta' => 'autenticados'
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
				// grupo administradores
				$this->privilegios['administradores'][] = array (
			'campanias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'json',
				'ver'
				)
				);
				$this->privilegios['administradores'][] = array (
			'canales' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'catalogos' => array (
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'contactos' => array (
				'agregar',
				'editar',
				'campo',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'convenios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'dependencias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'contactos'
				)
				);
				$this->privilegios['administradores'][] = array (
			'externas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'directorio' => array (
				'index'
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
			'estaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'generos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'grupos' => array (
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
			'impresos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'macrogeneros' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'monitor' => array (
					'index',
					'concentrado'
				)
			);
				$this->privilegios['administradores'][] = array (
			'productos' => array (
				'json'
				)
				);
				$this->privilegios['administradores'][] = array (
			'programas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'categorias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'proveedores' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'clonar'
				)
				);
				$this->privilegios['administradores'][] = array (
				 'facturas' => array (
				 'agregar',
				'eliminar',
				'index',
				'imprimir',
				'cancelar',
				'fecha',
				'conceptos',
				'vistaprevia',
				'verificaoficio',
				'ver',
				'control',
				'oficio',
				'facturas',
				'editaroficio',
				'eliminaroficio',
				'imprimiroficio',
				'entregar',
				'epagada',
				'entregaroficio',
				'impactos',
				'campania',
				'avanzado'
				 )
				 );
				 
				$this->privilegios['administradores'][] = array (
				 'folios' => array (
				 'valida'
				 )
				 );
				$this->privilegios['administradores'][] = array (
			'publicaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'envios' => array (
				'agregar',
				'campania',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'imprimir',
				'eliminar',
				'index',
				'aprobar',
				'pauta',
				'enviar'
				)
				);
				$this->privilegios['administradores'][] = array (
			'reportes' => array (
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'secciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'apariciones'
				)
				);
				$this->privilegios['administradores'][] = array (
			'sitios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['administradores'][] = array (
			'solicitudes' => array (
				'agregar',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'editar',
				'eliminar',
				'revisaclave',
				'index',
				'pauta',
				'revisaOficio',
				'aprobar'
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
			'insertos' => array(
				'precios',
				'preciosAdmin',
				'index'
				)
				);
				
				$this->privilegios['administradores'][] = array (
			'spots' => array(
				'precios',
				'preciosAdmin',
				'index'
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
				'pagos' => array (
					'agregardeposito',
					'agregartransferencia',
					'editardeposito',
					'editartransferencia',
					'eliminar',
					'eliminardeposito',
					'exportar',
					'index',
					'status',
					'ver',
					'validafolio'
				));
				
				$this->privilegios['administradores'][] = array (
			'oficios' => array (
				'index',
				'editar',
				'eliminar',
				'ver',
				'editar',
				'facturacion'
				)
				);
				// grupo editores
				$this->privilegios['publicacion'][] = array (
			'canales' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'catalogos' => array (
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'contactos' => array (
				'agregar',
				'editar',
				'eliminar',
				'campo',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'dependencias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'contactos'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'externas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'directorio' => array (
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'estaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'generos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'grupos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'historial' => array(
				'buscar',
				'exportar',
				'index',
				'ver'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'impresos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'macrogeneros' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'productos' => array (
				'json'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'programas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'proveedores' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'clonar'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'publicaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'envios' => array (
				'agregar',
				'campania',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'imprimir',
				'eliminar',
				'index',
				'aprobar',
				'pauta',
				'enviar'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'secciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'apariciones'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'sitios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				
				$this->privilegios['publicacion'][] = array (
			'insertos' => array(
				'precios',
				'preciosAdmin',
				'index'
				)
				);
				
				$this->privilegios['publicacion'][] = array (
			'spots' => array(
				'precios',
				'preciosAdmin',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
			'convenios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['publicacion'][] = array (
				'localizacion' => array (
				'inicia',
				'editar',
				'estados',
				'municipios'
				));
				
				$this->privilegios['publicacion'][] = array (
			'categorias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				
				$this->privilegios['publicacion'][] = array (
			'campanias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'json',
				'ver'
				)
				);

				$this->privilegios['publicacion'][] = array (
			'solicitudes' => array (
				'agregar',
				'cancelar',
				'cancelarpauta',
				'controlar',
				'editar',
				'eliminar',
				'revisaclave',
				'index',
				'pauta',
				'revisaOficio',
				'aprobar'
				)
				);
				
				
				$this->privilegios['publicacion'][] = array (
			'reportes' => array (
				'index'
				)
				);
				
				$this->privilegios['publicacion'][] = array (
			'monitor' => array (
					'index',
					'concentrado'
				));
				
				
				$this->privilegios['publicacion'][] = array (
			'oficios' => array (
				'index',
				'editar',
				'eliminar',
				'ver',
				'editar'
				)
				);
				
				//grupo facturacion
				
				$this->privilegios['facturacion'][] = array (
			'historial' => array(
				'buscar',
				'exportar',
				'index',
				'ver'
				)
				);
				
				//Modulo facturacion
			$this->privilegios['facturacion'][] = array (
			'facturas' => array(
				'agregar',
				'eliminar',
				'index',
				'imprimir',
				'cancelar',
				'fecha',
				'conceptos',
				'vistaprevia',
				'verificaoficio',
				'ver',
				'control',
				'oficio',
				'facturas',
				'editaroficio',
				'eliminaroficio',
				'imprimiroficio',
				'entregar',
				'epagada',
				'entregaroficio',
				'impactos',
				'campania',
				'avanzado'
				));
				
			$this->privilegios['facturacion'][] = array (
			'folios' => array(
				'valida'
				));	
				
			//Catalogos
				$this->privilegios['facturacion'][] = array (
			'canales' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'catalogos' => array (
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'contactos' => array (
				'agregar',
				'editar',
				'eliminar',
				'campo',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'dependencias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'contactos'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'externas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'directorio' => array (
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'estaciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'generos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'grupos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				
				$this->privilegios['facturacion'][] = array (
			'impresos' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'macrogeneros' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'productos' => array (
				'json'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'programas' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'proveedores' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'clonar'
				)
				);
			
				$this->privilegios['facturacion'][] = array (
			'secciones' => array (
				'agregar',
				'editar',
				'eliminar',
				'index',
				'apariciones'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'sitios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				
				$this->privilegios['facturacion'][] = array (
			'insertos' => array(
				'precios',
				'index'
				)
				);
				
				$this->privilegios['facturacion'][] = array (
			'spots' => array(
				'precios',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'convenios' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
				'localizacion' => array (
				'inicia',
				'editar',
				'estados',
				'municipios'
				));
				
				$this->privilegios['facturacion'][] = array (
			'categorias' => array (
				'agregar',
				'editar',
				'eliminar',
				'index'
				)
				);
				
				$this->privilegios['facturacion'][] = array (
			'monitor' => array (
					'index',
					'concentrado'
				));
				
				
				$this->privilegios['facturacion'][] = array (
				'pagos' => array (
					'agregardeposito',
					'agregartransferencia',
					'editardeposito',
					'editartransferencia',
					'eliminar',
					'eliminardeposito',
					'exportar',
					'index',
					'status',
					'ver',
					'validafolio'
				));
				
				
				$this->privilegios['facturacion'][] = array (
				'solicitudes' => array (
					'revisaOficio'
					)
				);
				
				
				$this->privilegios['facturacion'][] = array (
			'reportes' => array (
				'index'
				)
				);
				$this->privilegios['facturacion'][] = array (
			'oficios' => array (
				'index',
				'editar',
				'eliminar',
				'ver',
				'editar',
				'facturacion'
				)
				);
				// grupo consulta
				$this->privilegios['consulta'][] = array (
			'campanias' => array (
				'index',
				'json',
				'ver'
				)
				);
				$this->privilegios['consulta'][] = array (
			'canales' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'catalogos' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'contactos' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'convenios' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'dependencias' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'externas' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'directorio' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'ejercicios' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'estaciones' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'generos' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'grupos' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'historial' => array(
				'buscar',
				'exportar',
				'index',
				'ver'
				)
				);
				$this->privilegios['consulta'][] = array (
			'impresos' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'macrogeneros' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'productos' => array (
				'json'
				)
				);
				$this->privilegios['consulta'][] = array (
			'programas' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'proveedores' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'publicaciones' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'reportes' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'secciones' => array (
				'index',
				'apariciones'
				)
				);
				$this->privilegios['consulta'][] = array (
			'sitios' => array (
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
			'solicitudes' => array (
				'index'
				)
				);
				
				$this->privilegios['consulta'][] = array (
			'insertos' => array(
				'index'
				)
				);
				
				$this->privilegios['consulta'][] = array (
			'spots' => array(
				'index'
				)
				);
				$this->privilegios['consulta'][] = array (
				'localizacion' => array (
				'inicia',
				'editar',
				'estados',
				'municipios'
				));
				
				$this->privilegios['consulta'][] = array (
			'categorias' => array (
				'index'
				)
				);
				
				
				$this->privilegios['consulta'][] = array (
			'reportes' => array (
				'index'
				)
				);
				
				$this->privilegios['consulta'][] = array (
			'monitor' => array (
					'index',
					'concentrado'
				));
				

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