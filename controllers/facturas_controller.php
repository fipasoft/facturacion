<?php
// facturacion, Creado el 17/11/2011
/**
 * Facturas
 *
 * @package    Controladores
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2011 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */
class FacturasController extends ApplicationController{

	public $template = "system";

	public function agregar(){

	}

	public function editar( $id = '' ){

	}

	public function eliminar( $id = '' ){

	}

    public function imprimir( $id = '' ){

        try{

            $factura = new Factura();
            $factura = $factura->find( $id );

            if( !$factura || $factura->id  == '' ){

                throw new Exception( 'Id no valido' );

            }

            $this->factura = $factura;
            $this->exito();
            $this->set_response( 'view' );

        }catch( Exception $e ){

            $this->error( $e->getMessage(), $errvar, $e );

        }

    }

	public function index( $pag = '' ){

	   try{
        // vars
        $controlador = $this->controlador;
        $accion = $this->accion;
        $path = $this->path = KUMBIA_PATH;

        $ejercicio_id = Session :: get_data( 'eje.id' );

        // busqueda
        $b = new Busqueda($controlador, $accion);
        $b->campos();

        // genera las condiciones
        $b->establecerCondicion(
            'festados_id',
            "festados_id = '" . $b->campo( 'festados_id' ) . "'"
            );

            $b->establecerCondicion(
            'dependencia_id',
            "dependencia_id = '" . $b->campo( 'dependencia_id' ) . "'"
            );

            $b->establecerCondicion(
            'fecha',
            "fecha = '" . Utils :: fecha_convertir( $this->post('fecha') ) . "'"
            );



            $c = "factura.ejercicio_id = '" . $ejercicio_id . "' ";
            $c .= ( $b->condicion() ? "AND " . $b->condicion() : '' );

            // cuenta todos los registros
            $facturas = new Factura();
            $registros = $facturas->count( ( $c == "" ? "" : $c ) );

            // paginacion
            $paginador = new Paginador( $controlador, $accion );
            if( $pag != '' ){
                $paginador->guardarPagina( $pag );
            }
            $paginador->estableceRegistros( $registros );
            $paginador->generar();

            // ejecuta la consulta
            $facturas = $facturas->find(
            "conditions: " . $c,
            'order: folio DESC ',
            'limit: ' . ( $paginador->pagina() * $paginador->rpp() ) . ', '
            . $paginador->rpp()
            );

            $dependencias = new Dependencia();
            $dependencias = $dependencias->facturadas( $ejercicio_id );

            $festados = new Festados();
            $festados = $festados->find();

            // salida
            $this->busqueda = $b;
            $this->paginador = $paginador;
            $this->registros = $registros;
            $this->facturas = $facturas;
            $this->dependencias = $dependencias;
            $this->festados = $festados;
        }catch( Exception $e ){
            $this->error( $e->getMessage(), $errvar, $e );

        }

	}

}
?>