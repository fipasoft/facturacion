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
				'ejercicios',
				'usuarios',
				'facturas',
				'externas'
			);
		}else if( in_array('administradores',  $usr_grupos)){
			$controladores = array(
				'ejercicios',
                'usuarios',
                'facturas',
                'externas'
			);
		}
		return $controladores;

 	}

 }
?>
