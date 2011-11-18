<?php
// publicidad, Creado el 25/11/2009
/** 
 * Estado
 * 
 * @package    Modelos
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
 class Festados extends ActiveRecord{
 	
 	public function porclave($clave){
 		$estado = new Festados();
 		$estado = $estado->find_first("clave='".$clave."'");
 		return $estado;
 	}
 	
 }
?>
