<?php
// Hekademos, Creado el 11/10/2008
// Publicidad, Integrado el 02/06/2009
/**
 * Historial
 *
 * @package	   Modelos
 * @author     J Jonathan Lopez <jlopez@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */
 class Historial extends ActiveRecord{

 	public function obtenControladores(){
		$usr_grupos = Session :: get_data('usr.grupos');

		if( in_array('root',  $usr_grupos) ){
			$controladores = array(
				'banners',
				'campanias',
				'canales',
				'contactos',
				'convenios',
				'dependencias',
				'ejercicios',
				'estaciones',
				'generos',
				'grupos',
				'impresos',
				'insertos',
				'macrogeneros',
				'programas',
				'proveedores',
				'publicaciones',
				'secciones',
				'sitios',
				'usuarios',
				'solicitudes',
				'facturas',
				'categorias',
				'envios',
				'facturacion',
				'pagos',
				'oficios'
			);
		}else if( in_array('administradores',  $usr_grupos) ||  
					in_array('facturacion',  $usr_grupos) ||  
					in_array('publicacion',  $usr_grupos)  ||  
					in_array('consulta',  $usr_grupos)){
			$controladores = array(
				'banners',
				'campanias',
				'canales',
				'contactos',
				'convenios',
				'dependencias',
				'ejercicios',
				'estaciones',
				'generos',
				'grupos',
				'impresos',
				'insertos',
				'macrogeneros',
				'proveedores',
				'programas',
				'publicaciones' .
				'secciones',
				'sitios',
				'usuarios',
				'solicitudes',
				'facturas',
				'categorias',
				'envios',
				'facturacion',
				'pagos',
				'oficios'
			);
		}
		return $controladores;

 	}

 }
?>
