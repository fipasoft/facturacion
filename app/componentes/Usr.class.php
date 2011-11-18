<?php
// FiPa, Creado el 30/08/2008
/** 
 * Usr
 * Esta clase permite almacenar la informacion basica de un usuario del 
 * sistema.
 * 
 * @package    Usr
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License
 * 
 */
 
class Usr {
	/**
	 * Almacena el id del usuario tal como se almacena en la base de datos 
	 * 
	 * @var int 
	 * @access public
	 */
	public $id;
	/**
	 * Guarda el login del usuario, este login es tambien una identificacion 
	 * unica y es util para la asignacion de permisos ACL.
	 * 
	 * @var string
	 * @access public
	 */
	public $login;
	/**
	 * Almacena el nombre del usuario, es util para la presentacion de 
	 * informacion en pantalla
	 * 
	 * @var string
	 * @access public
	 */
	public $nombre;	
	/**
	 * Constructor, en caso de no recibir parametros se crea un objeto con
	 * privilegios de usuario 'anonimo'...
	 * 
	 * @param int $id  (opcional) Id del usuario
	 * @param string $login (opcional) Login del usuario
	 * @param string $nombre (opcional) Nombre del usuario
	 * @access public
	 * 
	 */
    function Usr($id = NULL, $login = 'anonimo', $nombre = NULL) {
    	$this->id = $id;
    	$this->login = $login;
    	$this->nombre = $nombre;
    }
}
?>