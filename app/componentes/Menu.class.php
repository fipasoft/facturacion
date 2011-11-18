<?php


// FiPa, Creado el 30/08/2008
/** 
 * Menu
 * Permite almacenar generar menus de la aplicacion basandose en los privilegios
 * acl del modulo phpgacl
 * 
 * @package    Componentes
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License
 * 
 */
Kumbia :: import('lib.phpgacl.main');

class Menu {
	/**
	 * @var array Define los controladores que se excluiran al generar el menu
	 * @access private
	 */
	private $excluirControladores;
	/**@var array Define los grupos que se excluiran al generar el menu
	 * @access private
	 */
	private $excluirGrupos;
	/**
	 * @var array Define los nombres de las vistas que se excluiran al generar el menu
	 * @access private
	 */
	private $excluirVistas;

	/**
	 * Metodo constructor de la clase Menu. Carga el archivo de configuracion
	 * menu.ini
	 */
	public function Menu() {
		$cfg = Config :: read('menu.ini');
		$tmp = explode(',', $cfg->excluir->controladores);
		$tmp = array_map( 'trim', $tmp );
		$this->excluirControladores = (is_array($tmp) ? $tmp : array (
			$tmp
		));
		$tmp = explode(',', $cfg->excluir->grupos);
		$tmp = array_map( 'trim', $tmp );
		$this->excluirGrupos = (is_array($tmp) ? $tmp : array (
			$tmp
		));
		$tmp = explode(',', $cfg->excluir->vistas);
		$tmp = array_map( 'trim', $tmp );
		$this->excluirVistas = (is_array($tmp) ? $tmp : array (
			$tmp
		));
	}

	/**
	 * Este metodo genera la estructura principal del menu usando todas 
	 * las vistas y controladores a los que tiene acceso el usuario
	 * @return array
	 */
	public function menuPrincipal($login = '') {
		if($login == ''){
			$login = Session :: get_data('usr.login');
		}
		$gacl_x = new gacl_extra();
		$estructura = $gacl_x->get_user_acos($login, $this->excluirGrupos);
		foreach ($estructura as $controlador => $vistas) {
			if (array_search($controlador, $this->excluirControladores) !== false) {
					unset ($estructura[$controlador]);
			}else{
				foreach ($vistas as $vista => $valor) {
					if (array_search($vista, $this->excluirVistas) !== false) {
						unset ($estructura[$controlador][$vista]);
					}
				}
			}
		}
		return $estructura;
	}

	/**
	 * Este metodo genera la estructura principal del menu usando solo los controladores
	 * a los que tiene acceso el usuario
	 * @return array
	 */
	public function menuSimple() {
		$login = Session :: get_data('usr.login');
		$gacl_x = new gacl_extra();
		$estructura = $gacl_x->get_user_sections($login, $this->excluirGrupos);
		if (is_array($estructura)) {
			foreach ($estructura as $controlador) {
				if (array_search($controlador, $this->excluirControladores) !== false) {
					unset ($estructura[$controlador]);
				}
			}
		}		
		return $estructura;
	}

	/**
	 * Este metodo genera la estructura principal del menu usando
	 * solo las vistas del controlador seleccionado
	 * @param string $controlador Recibe el nombre del controlador
	 * @return array
	 */
	public function menuSeccion($controlador) {
		$controlador = strtolower($controlador);
		$login = Session :: get_data('usr.login');
		$gacl_x = new gacl_extra();
		$estructura = $gacl_x->get_user_acos($login, $this->excluirGrupos);
		$estructura = $estructura[$controlador];
		if (is_array($estructura)) {
			foreach ($estructura as $vista => $valor) {
				if (array_search($vista, $this->excluirVistas) !== false) {
					unset ($estructura[$vista]);
				}
			}
		}
		return $estructura;
	}
}
?>