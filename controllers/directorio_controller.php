<?php
// publicidad, Creado el 30/06/2009
/** 
 * Directorio
 * 
 * @package    Controladores  
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
 
class DirectorioController extends ApplicationController{
 	
 	public function index( $pag = '' ){
 		$this->route_to( "controller: contactos", "action: index", "pag: " . $pag );
 	}
 	
}
?>
