<?php
// FiPa, Creado el 01/12/2008
/**
 * Asistencias Controller
 *
 * @package    Controladores
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */
class SistemaController extends ApplicationController {
	 public $template = "system";

	 function autocompletar(){
	 	$this->set_response('view');

	 	$tabla   =  $this->post('tabla');
	 	$campo   =  $this->post('campo');
	 	$valor   =  $this->post('valor');
	 	$modo    =  $this->post('modo');
	 	$limit   =  ($this->post('limit') != '' ? $this->post('limit') : '10');
	 	if( $tabla != '' && $campo != ''){
		 	$$tabla = new $tabla();
		 	switch( $modo ){
		 		case 'LEFT':
				 		$c = $campo . " LIKE '%" . $valor . "' ";
		 			break;

				case 'RIGHT':
						$c = $campo . " LIKE '" . $valor . "%' ";
		 			break;

				case 'STRICT':
						$c = $campo . " = '" . $valor . "' ";
		 			break;

	 			case 'BOTH':
	 			default:
	 					$c = $campo . " LIKE '%" . $valor . "%' ";
		 			break;
		 	}
		 	$resultados = $$tabla->find(
		 		"columns: " . $campo,
		 		"conditions: " . $c ,
		 		"group: " . $campo,
		 		"order: " . $campo,
		 		"limit: " . $limit
		 	);

	 	}else{
	 		$resultados = array();
	 		$campo = null;
	 	}

	 	$this->campo       =  $campo;
	 	$this->resultados  =  $resultados;
	 }

	 function ayuda(){
	 	$grupos = Session :: get_data('usr.grupos');
	 	ksort($grupos);
	 	if( !is_array($grupos) ){
	 		$grupos = array();
	 	}
	 	$this->grupos = $grupos;
	 }

 	public function index(){
 		$categorias = array();

 		$categorias[ '' ] = array(
 		    'ejercicios' => 'Ejercicios',
			'usuarios' => 'Usuarios'
 		);

		$this->categorias   =  $categorias;
		$this->path         =  KUMBIA_PATH;
 	}


	 function configuracion(){

	 }

	 function password(){
	 	$this->option = '';
	 	$this->error = '';
	 	$this->exito = '';
	 	if(!$this->post('pass')){
	 		$this->option = 'captura';
	 	}else{
	 		$usr_id = Session :: get_data('usr.id');
			$usuario = new Usuario();
			$usuario = $usuario->find( $usr_id );
			if($usuario->id != ''){
				if( $usuario->pass == sha1($this->post('pass')) ){
					if( $this->post('pass2') == $this->post('pass3') ){
						if(strlen($this->post('pass')) >= 6){
							$usuario->pass = sha1($this->post('pass2'));
							if($usuario->save()){
								$this->option = 'exito';
							}else{
								$this->option = 'error';
								$this->error .= ' Error al guardar en la BD.';
							}
						}else{
							$this->option = 'error';
							$this->error .= ' La longitud m&iacute;nima del password es de 6 caracteres.';
						}
					}else{
						$this->option = 'error';
						$this->error .= ' No coincide la confirmaci&oacute;n del password.';
					}
				}else{
					$this->option = 'error';
					$this->error .= ' La contrase&ntilde;a anterior es incorrecta.';
				}
			}else{
				$this->option = 'error';
				$this->error = ' El usuario no existe.';
			}

	 	}
	 }
}
?>