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

            $this->factura = $factura;

        }catch( Exception $e ){

            $this->error( $e->getMessage(), $errvar, $e );

        }

    }

	public function index( $pag = '' ){

	   try{


        }catch( Exception $e ){

            $this->error( $e->getMessage(), $errvar, $e );

        }

	}

}
?>