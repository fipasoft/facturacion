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
	    try{
	    $transaccion = false;
        if($this->post("ejercicio_id") !=""){
            mysql_query("BEGIN") or die("Error al iniciar la transaccion");
            $transaccion = true;
            
            $this->option = "exito";
            $dependencia_id = $this->post("dependencia_id");
            
            $cantidades = $this->post("cantidad");
            $unitarios = $this->post("unitario");
            $conceptos = $this->post("concepto");
            $cantidades = $this->post("cantidad");
            $costos = $this->post("costo");
            
            $subtotal = $this->post("subtotal");
            $iva = $this->post("iva");
            $total = $this->post("total");
            
            
            if($dependencia_id == "" ){
                throw new Exception("Error no se especifico el cliente.");
            }
            
            if(!is_array($cantidades) || count($cantidades) == 0){
                throw new Exception("Error las cantidades no son validas.");
            }
            
            if(!is_array($unitarios) || count($unitarios) == 0){
                throw new Exception("Error los unitarios no son validos.");
            }
            
            if(!is_array($cantidades) || count($cantidades) == 0){
                throw new Exception("Error las cantidades no son validas.");
            }
            
            if(!is_array($costos) || count($costos) == 0){
                throw new Exception("Error los costos no son validos.");
            }
            
            if($subtotal == "" ){
                throw new Exception("Error no se especifico el subtotal.");
            }
            
            if($iva == "" ){
                throw new Exception("Error no se especifico el iva.");
            }
            
            $activa = new Festados();
            $activa = $activa->find_first("clave='act'");
            
            if($activa->id == ''){
                throw new Exception("Error no se ha dado de alta el estado 'activo' de la factura.");
            }
            
            $dependencia = new Dependencia();
            $dependencia = $dependencia->find($dependencia_id);
            $fiscal = $dependencia->fiscal();
            
            $now = new DateTime();
            
            $factura = new Factura();
            $factura->ejercicio_id      =       $this->post("ejercicio_id");
            $factura->dependencia_id    =       $dependencia->id;
            $factura->festados_id       =       $activa->id;
            $factura->folio             =       $factura->obtenFolio();
            $factura->fecha             =       $now->format("Y-m-d");
            $factura->razon             =       $fiscal->razon;
            $factura->rfc               =       $fiscal->rfc;
            $factura->domicilio         =       $fiscal->domicilio;
            $factura->colonia           =       $fiscal->colonia;
            $factura->cpostal           =       $fiscal->cp;
            
            $factura->subtotal          =       $subtotal;
            $factura->iva               =       $iva;
            $factura->total             =       $total;
            $factura->observaciones     =       $observaciones;
            $factura->enviada           =       '0000-00-00';
            $factura->recibida          =       '0000-00-00';
            
            if(!$factura->save()){
                throw new Exception("Error al guardar la factura.");
            }
            
            foreach ($cantidades as $k => $cantidad) {
                    
                $concepto = new Concepto();
                $concepto -> factura_id        =   $factura->id;
                $concepto -> cantidad           =   $cantidad;
                $concepto -> descripcion        =   $conceptos[$k];
                $concepto -> unitario           =   $unitarios[$k];
                $concepto -> monto              =   $costos[$k];
                
                if(!$concepto->save()){
                    throw new Exception("Error al guardar el concepto.");
                }
                
            }
            
            $festado = new Festado();
            $festado->factura_id = $factura->id;
            $festado->festados_id = $activa->id; 
            if(!$festado->save()){
                throw new Exception("Error al guardar el festado.");
            }
            
            mysql_query("COMMIT") or die("Error al finalizar la transaccion");;
        }else{    
            $this->option = "captura";
            $ejercicio_id = Session :: get_data( 'eje.id' );
            
            $dependencias = new Dependencia();
            $dependencias = $dependencias->find();
            $this->dependencias = $dependencias;
            $this->ejercicio_id = $ejercicio_id;
            
        }
        }catch(Exception $e){
            if($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");
            
            $this->error( $e->getMessage(), $errvar, $e );
        }
	}

	public function editar( $id = '' ){

	}

	public function eliminar( $id = '' ){

	}
    
    public function control( $id = '' ){
        try{
            $transaccion = false;
            
            if($this->post('factura_id') == ''){
                $this->option = "captura";
                
                $factura = new Factura();
                $factura = $factura->find($id);
                
                if($factura->id == ''){
                    throw new Exception("Error la factura no es valida.");
                }
                
                $festados = new Festados();
                $festados = $festados->find();
                
                $this->factura = $factura;
                $this->festados = $festados;
            }else{
                $this->option = 'exito';
                mysql_query("BEGIN") or die("Error al iniciar la transaccion");
                $transaccion = true;
            
                
                $factura = new Factura();
                $factura = $factura->find($this->post("factura_id"));
                
                if($factura->id == ''){
                    throw new Exception("Error la factura no es valida.");
                }
                
                $edo = new Festados();
                $edo = $edo->find($this->post("festados_id"));
                
                if($factura->id == ''){
                    throw new Exception("Error el estado no es valida.");
                }
                
                $hoy = new DateTime();
                
                $factura->festados_id = $edo->id;
                $factura->enviada = ($factura->enviada == '0000-00-00'? $hoy->format("Y-m-d") : $factura->enviada);
                $factura->recibida = ($factura->recibida == '0000-00-00'? $hoy->format("Y-m-d") : $factura->recibida);
                
                if(!$factura->save()){
                    throw new Exception("Error al guardar la factura.");
                }
                
                
                $festado = new Festado();
                $festado->factura_id = $factura->id;
                $festado->festados_id = $edo->id; 
                if(!$festado->save()){
                    throw new Exception("Error al guardar el festado.");
                }
                
                mysql_query("COMMIT") or die("Error al finalizar la transaccion");;
                
            }
        }catch(Exception $e){
            if($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");
            
            $this->error( $e->getMessage(), $errvar, $e );
        }
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