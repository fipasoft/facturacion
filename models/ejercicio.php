<?php
// publicidad, Creado el 02/06/2009
/** 
 * Ejercicio
 * 
 * @package    Modelos   
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */

 class Ejercicio extends ActiveRecord{
 	
 	public function ver(){
 		return $this->annio;
 	}
 	
 }
?>
