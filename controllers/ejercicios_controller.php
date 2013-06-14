<?php
// publicidad, Creado el 02/06/2009
/**
 * Ejercicios
 *
 * @package    Controladores
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */

class EjerciciosController extends ApplicationController {
    public $template = "system";

    public function agregar(){
        if($this->post('annio') == ''){
            $this->option = 'captura';

            $y = date('Y') - 3;
            $annios = array();
            for($i = 0; $i < 6; $i++){
                $annios[] = $y + $i;
            }

            $this->annios = $annios;
        }else{
            $this->option = '';
            $this->error = '';
            $existentes = new Ejercicio();
            if($existentes->count(
                "annio = '" . $this->post('annio') . "' "
            ) == 0)
            {
                $ejercicio = new Ejercicio();
                $ejercicio->annio           =   $this->post('annio');

                if( $ejercicio->save() ){

                    if( $this->option == '' ){

                        // historial
                        $historial = new Historial();
                        $historial->ejercicio_id    =   null;
                        $historial->usuario         =   Session :: get_data( 'usr.login' );
                        $historial->descripcion     =   utf8_encode(
                            'Agregó ' .
                            utf8_decode( $ejercicio->ver() ) . ' ' .
                            '[eje' . $ejercicio->id . '] '
                        );
                        $historial->controlador     =   $this->controlador;
                        $historial->accion          =   $this->accion;
                        $historial->save();

                        // copia de forma automatica la informacion de ejercicios anteriores
                        // ...

                        // si no hay ejercicio seleccionado se utiliza el nuevo
                        $this->option = 'exito';
                        $ej = new Ejercicio();
                        $eje_seleccionado = $ej->find( Session :: get_data ('eje.id') );
                        if($eje_seleccionado->id == ''){
                            Session :: set_data('eje.id', $ejercicio->id);
                        }

                    }
                }else{
                    $this->option = 'error';
                    $this->error .= ' Error al guardar en la BD.'. $ejercicio->show_message();
                }
            }else{
                $this->option = 'error';
                $this->error .= 'El ejercicio no se agreg&oacute; debido a que ya existe un registro con esos datos.';
            }
        }
    }

    public function editar( $id = '' ){
        $this->option = 'error';
        $this->error = '';

        if($id != ''){
            $ejercicio = new Ejercicio();
            $ejercicio = $ejercicio->find($id);

            if($ejercicio->id != ''){
                $this->option = 'captura';

                $y = $ejercicio->annio - 3;
                $annios = array();
                for($i = 0; $i < 6; $i++){
                    $annios[] = $y + $i;
                }

                $this->ejercicio = $ejercicio;
                $this->annios = $annios;

            }else{
                $this->error = 'El ejercicio seleccionado no existe.';
            }
        }else if( $this->post('id') != '' ){
            $this->option = '';
            $this->error = '';
            $ejercicio = new Ejercicio();
            $ejercicio = $ejercicio->find( $this->post('id') );

            if( $ejercicio->id != '' ){
                $existentes = new Ejercicio();
                if($existentes->count(
                    "annio = '" . $this->post('annio') . "' " .
                    "AND id != '" . $ejercicio->id . "' "
                ) == 0)
                {
                    $ejercicio->annio           =   $this->post('annio');

                    if( $ejercicio->save() ){
                        $this->option = 'exito';

                        // historial
                        $historial = new Historial();
                        $historial->ejercicio_id    =   null;
                        $historial->usuario         =   Session :: get_data( 'usr.login' );
                        $historial->descripcion     =   utf8_encode(
                            'Editó ' .
                            utf8_decode( $ejercicio->ver() ) . ' ' .
                            '[eje' . $ejercicio->id . '] '
                        );
                        $historial->controlador     =   $this->controlador;
                        $historial->accion          =   $this->accion;
                        $historial->save();

                    }else{
                        mysql_query("ROLLBACK") or die("EJE_EDI_7");
                        $this->option = 'error';
                        $this->error .= ' Error al actualizar la BD.'. $ejercicio->show_message();
                    }
                }else{
                    $this->option = 'error';
                    $this->error .= 'El ejercicio no se edit&oacute; debido a que ya existe un registro con esos datos.';
                }
            }else{
                $this->error = 'El ejercicio seleccionado no existe.';
            }
        }else{
            $this->error .= ' No se especific&oacute; el ejercicio.';
        }
    }

    public function eliminar( $id = '' ){
        if( $id != '' ){
            $this->option = 'captura';
            $ejercicio = new Ejercicio();
            $ejercicio = $ejercicio->find( $id );

            if( $ejercicio->id != '' ){
                $this->ejercicio = $ejercicio;
            }else{
                $this->option = 'error';
                $this->error = 'El ejercicio no existe.';
            }
        }else if( $this->post('id') != '' ){
            $this->option = '';
            $this->error = '';
            $ejercicio = new Ejercicio();
            $ejercicio = $ejercicio->find( $this->post('id') );
            if($ejercicio->id != ''){
                // informacion para el historial
                $_ejercicio = new stdClass();
                $_ejercicio->info = $ejercicio->ver();
                $_ejercicio->id   = $ejercicio->id;

                // eliminando el ejercicio
                try{
                    $ejercicio->delete( $ejercicio->id );
                    $this->option = 'exito';

                    // historial
                    $historial = new Historial();
                    $historial->ejercicio_id    =   null;
                    $historial->usuario         =   Session :: get_data( 'usr.login' );
                    $historial->descripcion     =   utf8_encode(
                        'Eliminó ' .
                        utf8_decode( $_ejercicio->info ) . ' ' .
                        '[eje' . $_ejercicio->id . '] '
                    );
                    $historial->controlador     =   $this->controlador;
                    $historial->accion          =   $this->accion;
                    $historial->save();


                    if($this->post( 'id' ) == Session :: get_data('eje.id') ){
                        $seleccionar = new Ejercicio();
                        $seleccionar = $seleccionar->find_first("order: annio DESC");
                        Session :: set_data( 'eje.id', $seleccionar->id );
                    }
                }catch(dbException $e){
                    $this->option = 'error';
                    $this->error .= 'Error al intentar eliminar de la BD. ' .
                        'Posiblemente existan datos vinculados al ejercicio.';
                }
            }else{
                $this->option = 'error';
                $this->error = 'El ejercicio no existe.';
            }
        }else{
            $this->option = 'error';
            $this->error = ' No se especific&oacute; el ejercicio a eliminar.';
        }
    }


    public function index( $pag = '' ){
        // vars
        $controlador = $this->controlador;
        $accion = $this->accion;
        $path = $this->path = KUMBIA_PATH;

        // cuenta todos los registros
        $ejercicios = new Ejercicio();
        $this->registros = $ejercicios->count();

        // paginacion
        $paginador = new Paginador($controlador, $accion);
        if($pag != ''){
            $paginador->guardarPagina($pag);
        }
        $paginador->estableceRegistros($this->registros);
        $paginador->generar();
        $this->paginador = $paginador;

        // ejecuta la consulta
        $this->ejercicios = $ejercicios->find(
            'limit: ' . ($paginador->pagina() * $paginador->rpp()) . ', '
            . $paginador->rpp(),
                'order: annio DESC'
            );

    }

    public function seleccionar() {
        Session :: set_data('eje.id', $this->post('ejercicio_id'));
        $this->redirect($this->post('controlador') . '/' . $this->post('accion'),0);
    }

}

?>
