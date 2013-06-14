<?php
/**
 * Facturas
 *
 * @package    Controladores
 * @copyright  FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU GPL
 *
 */
class FacturasController extends ApplicationController
{
    public $template = "system";

    public function agregar()
    {
        try{
            $transaccion = false;
            if ($this->post("ejercicio_id") !="") {
                mysql_query("BEGIN") or die("Error al iniciar la transaccion");
                $transaccion = true;

                $this->option = "exito";
                $dependencia_id = $this->post("dependencia_id");

                $cantidades = $this->post("cantidad");
                $unitarios = $this->post("unitario");
                $conceptos = $this->post("concepto");
                $cantidades = $this->post("cantidad");
                $costos = $this->post("costo");
                $claves = $this->post("clave");

                $subtotal = $this->post("subtotal");
                $iva = $this->post("iva");
                $total = $this->post("total");


                if ($dependencia_id == "") {
                    throw new Exception("Error no se especifico el cliente.");
                }

                if (!is_array($cantidades) || count($cantidades) == 0) {
                    throw new Exception("Error las cantidades no son validas.");
                }

                if (!is_array($unitarios) || count($unitarios) == 0) {
                    throw new Exception("Error los unitarios no son validos.");
                }

                if (!is_array($cantidades) || count($cantidades) == 0) {
                    throw new Exception("Error las cantidades no son validas.");
                }

                if (!is_array($costos) || count($costos) == 0) {
                    throw new Exception("Error los costos no son validos.");
                }

                if (!is_array($claves) || count($claves) == 0) {
                    throw new Exception("Error las claves no son validos.");
                }

                if ($subtotal == "") {
                    throw new Exception("Error no se especifico el subtotal.");
                }

                if ($iva == "") {
                    throw new Exception("Error no se especifico el iva.");
                }

                $activa = new Festados();
                $activa = $activa->find_first("clave='act'");

                if ($activa->id == '') {
                    throw new Exception("Error no se ha dado de alta el estado 'activo' de la factura.");
                }

                $dependencia = new Dependencia();
                $dependencia = $dependencia->find($dependencia_id);
                $fiscal = $dependencia->fiscal();


                $factura = new Factura();
                $factura->ejercicio_id      =       $this->post("ejercicio_id");
                $factura->dependencia_id    =       $dependencia->id;
                $factura->festados_id       =       $activa->id;
                $factura->metodopago_id     =       $this->post('metodopago_id');
                $factura->folio             =       $factura->obtenFolio();
                $factura->fecha             =       Utils::convierteFechaMySql($this->post('fecha'));
                $factura->razon             =       $fiscal->razon;
                $factura->rfc               =       $fiscal->rfc;
                $factura->domicilio         =       $fiscal->domicilio;
                $factura->colonia           =       $fiscal->colonia;
                $factura->cpostal           =       $fiscal->cp;

                $factura->subtotal          =       $subtotal;
                $factura->iva               =       $iva;
                $factura->total             =       $total;
                $factura->ctapago           =       ($this->post('ctapago') ?
                    $this->post('ctapago') :
                    null
               );
                $factura->observaciones     =       trim($this->post("observaciones"));
                $factura->enviada           =       '0000-00-00';
                $factura->recibida          =       '0000-00-00';

                if (!$factura->save()) {
                    throw new Exception("Error al guardar la factura.");
                }

                foreach ($cantidades as $k => $cantidad) {

                    $concepto = new Concepto();

                    if (trim($cantidad) == '' ||
                        trim($conceptos[$k]) == '' ||
                        trim($unitarios[$k]) == '' ||
                        trim($costos[$k]) == '') {
                            throw new Exception("Error los datos de los conceptos no son consistentes.");
                        }

                    $concepto -> factura_id        =   $factura->id;
                    $concepto -> cantidad           =   trim($cantidad);
                    $concepto -> descripcion        =   trim($conceptos[$k]);
                    $concepto -> unitario           =   trim($unitarios[$k]);
                    $concepto -> monto              =   trim($costos[$k]);
                    $concepto -> clave              =   trim($claves[$k]);

                    if (!$concepto->save()) {
                        throw new Exception("Error al guardar el concepto.");
                    }

                }

                $festado = new Festado();
                $festado->factura_id = $factura->id;
                $festado->festados_id = $activa->id;
                if (!$festado->save()) {
                    throw new Exception("Error al guardar el festado.");
                }


                $historial = new Historial();
                $historial->ejercicio_id    =   $dependencia->ejercicio_id;
                $historial->usuario         =   Session :: get_data('usr.login');
                $historial->descripcion     =   utf8_encode(
                    'Agrego la factura ' .
                    utf8_decode($factura->folio) . ' - ' .
                    utf8_decode($factura->rfc) . ' ' .
                    '[fac' . $factura->id . '] '
               );
                $historial->controlador     =   $this->controlador;
                $historial->accion          =   $this->accion;
                $historial->save();

                mysql_query("COMMIT") or die("Error al finalizar la transaccion");;
            }else{
                $this->option = "captura";
                $ejercicio_id = Session :: get_data('eje.id');

                $dependencias = new Dependencia();
                $dependencias = $dependencias->find(
                    "conditions: ejercicio_id = '" . Session :: get_data('eje.id') . "'",
                    "order: nombre"
               );

                $metodospago = new Metodopago();
                $metodospago = $metodospago->find();

                $this->dependencias = $dependencias;
                $this->metodospago  = $metodospago;
                $this->ejercicio_id = $ejercicio_id;

            }
        }catch(Exception $e) {

            if ($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");

            $this->error($e->getMessage(), $errvar, $e);
        }
    }

    public function editar($id = '')
    {
        try{
            $transaccion = false;
            if ($this->post('factura_id') == '') {
                $this->option = "captura";

                $factura = new Factura();
                $factura = $factura->find($id);

                if ($factura->id == '') {
                    throw new Exception("Error la factura no es valida.");
                }

                if (!$factura->editable()) {
                    throw new Exception("Error la factura no esta en estado activada.");

                }

                $dependencias = new Dependencia();
                $dependencias = $dependencias->find(
                    "conditions: ejercicio_id = '" . $factura->ejercicio_id . "'",
                    "order: nombre"
               );

                $this->dependencias = $dependencias;
                $this->factura = $factura;
            }else{
                mysql_query("BEGIN") or die("Error al iniciar la transaccion");
                $transaccion = true;

                $this->option = "exito";
                $dependencia_id = $this->post("dependencia_id");

                $cantidades = $this->post("cantidad");
                $unitarios = $this->post("unitario");
                $conceptos = $this->post("concepto");
                $cantidades = $this->post("cantidad");
                $costos = $this->post("costo");
                $claves = $this->post("clave");

                $subtotal = $this->post("subtotal");
                $iva = $this->post("iva");
                $total = $this->post("total");


                if ($dependencia_id == "") {
                    throw new Exception("Error no se especifico el cliente.");
                }

                if (!is_array($cantidades) || count($cantidades) == 0) {
                    throw new Exception("Error las cantidades no son validas.");
                }

                if (!is_array($unitarios) || count($unitarios) == 0) {
                    throw new Exception("Error los unitarios no son validos.");
                }

                if (!is_array($cantidades) || count($cantidades) == 0) {
                    throw new Exception("Error las cantidades no son validas.");
                }

                if (!is_array($costos) || count($costos) == 0) {
                    throw new Exception("Error los costos no son validos.");
                }

                if (!is_array($claves) || count($claves) == 0) {
                    throw new Exception("Error las claves no son validos.");
                }

                if ($subtotal == "") {
                    throw new Exception("Error no se especifico el subtotal.");
                }

                if ($iva == "") {
                    throw new Exception("Error no se especifico el iva.");
                }

                $activa = new Festados();
                $activa = $activa->find_first("clave='act'");

                if ($activa->id == '') {
                    throw new Exception("Error no se ha dado de alta el estado 'activo' de la factura.");
                }

                $dependencia = new Dependencia();
                $dependencia = $dependencia->find($dependencia_id);
                $fiscal = $dependencia->fiscal();

                $factura = new Factura();
                $factura = $factura -> find($this->post('factura_id'));

                if ($factura->id == '') {
                    throw new Exception("Error la factura no es valida.");
                }

                if (!$factura->editable()) {
                    throw new Exception("Error la factura no esta en estado activada.");

                }

                $factura->fecha             =       Utils::convierteFechaMySql($this->post('fecha'));
                $factura->dependencia_id    =       $dependencia->id;
                $factura->festados_id       =       $activa->id;
                $factura->metodopago_id     =       $this->post('metodopago_id');
                $factura->razon             =       $fiscal->razon;
                $factura->rfc               =       $fiscal->rfc;
                $factura->domicilio         =       $fiscal->domicilio;
                $factura->colonia           =       $fiscal->colonia;
                $factura->cpostal           =       $fiscal->cp;

                $factura->subtotal          =       $subtotal;
                $factura->iva               =       $iva;
                $factura->total             =       $total;
                $factura->ctapago           =       ($this->post('ctapago') ?
                    $this->post('ctapago') :
                    null
               );
                $factura->observaciones     =       $this->post("observaciones");

                if (!$factura->save()) {
                    throw new Exception("Error al guardar la factura.");
                }

                $elimina = new Concepto();
                if (!$elimina->delete("factura_id = '".$factura->id."'")) {
                    throw new Exception("Error al eliminar los conceptos.");
                }

                foreach ($cantidades as $k => $cantidad) {

                    $concepto = new Concepto();

                    if (trim($cantidad) == '' ||
                        trim($conceptos[$k]) == '' ||
                        trim($unitarios[$k]) == '' ||
                        trim($costos[$k]) == '') {
                            throw new Exception("Error los datos de los conceptos no son consistentes.");
                        }

                    $concepto -> factura_id        =   $factura->id;
                    $concepto -> cantidad           =   trim($cantidad);
                    $concepto -> descripcion        =   trim($conceptos[$k]);
                    $concepto -> unitario           =   trim($unitarios[$k]);
                    $concepto -> monto              =   trim($costos[$k]);
                    $concepto -> clave              =   trim($claves[$k]);

                    if (!$concepto->save()) {
                        throw new Exception("Error al guardar el concepto.");
                    }

                }

                $historial = new Historial();
                $historial->ejercicio_id    =   $dependencia->ejercicio_id;
                $historial->usuario         =   Session :: get_data('usr.login');
                $historial->descripcion     =   utf8_encode(
                    'Edito la factura ' .
                    utf8_decode($factura->folio) . ' - ' .
                    utf8_decode($factura->rfc) . ' ' .
                    '[fac' . $factura->id . '] '
               );
                $historial->controlador     =   $this->controlador;
                $historial->accion          =   $this->accion;
                $historial->save();



                mysql_query("COMMIT") or die("Error al finalizar la transaccion");

                $this->factura = $factura;


            }
        }catch(Exception $e) {

            if ($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");

            $this->error($e->getMessage(), $errvar, $e);
        }
    }

    public function control($id = '')
    {
        try{
            $transaccion = false;

            if ($this->post('factura_id') == '') {
                $this->option = "captura";

                $factura = new Factura();
                $factura = $factura->find($id);

                if ($factura->id == '') {
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

                if ($factura->id == '') {
                    throw new Exception("Error la factura no es valida.");
                }

                $edo = new Festados();
                $edo = $edo->find($this->post("festados_id"));

                if ($factura->id == '') {
                    throw new Exception("Error el estado no es valida.");
                }

                $hoy = new DateTime();

                $anterior = new Festados();
                $anterior = $anterior->find($factura->festados_id);

                $factura->festados_id = $edo->id;
                $factura->enviada = ($factura->enviada == '0000-00-00'? $hoy->format("Y-m-d") : $factura->enviada);
                $factura->recibida = ($factura->recibida == '0000-00-00'? $hoy->format("Y-m-d") : $factura->recibida);

                if (!$factura->save()) {
                    throw new Exception("Error al guardar la factura.");
                }


                $festado = new Festado();
                $festado->factura_id = $factura->id;
                $festado->festados_id = $edo->id;
                if (!$festado->save()) {
                    throw new Exception("Error al guardar el festado.");
                }

                $historial = new Historial();
                $historial->ejercicio_id    =   Session :: get_data('eje.id');
                $historial->usuario         =   Session :: get_data('usr.login');
                $historial->descripcion     =   utf8_encode(
                    'Cambio estado de la factura ' .
                    utf8_decode($factura->folio) . ' - ' .
                    utf8_decode($factura->rfc) . ' .' .
                    'De '. $anterior->singular . ' a '. $edo->singular.' '.
                    '[fac' . $factura->id . '] '
               );
                $historial->controlador     =   $this->controlador;
                $historial->accion          =   $this->accion;
                $historial->save();

                mysql_query("COMMIT") or die("Error al finalizar la transaccion");;

            }
        }catch(Exception $e) {
            if ($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");

            $this->error($e->getMessage(), $errvar, $e);
        }
    }

    public function eliminar($id = '')
    {
    }

    public function prefactura($id = '')
    {

        try{

            $factura = new Factura();
            $factura = $factura->find($id);

            if (!$factura || $factura->id  == '') {

                throw new Exception('Id no valido');

            }

            $this->factura = $factura;
            $this->exito();
            $this->set_response('view');

        }catch(Exception $e) {

            $this->error($e->getMessage(), $errvar, $e);

        }

    }

    public function imprimir($id = '')
    {

        try{

            $factura = new Factura();
            $factura = $factura->find($id);

            if (!$factura || $factura->id  == '') {

                throw new Exception('Id no valido');

            }

            $this->factura = $factura;
            $this->exito();
            $this->set_response('view');

        }catch(Exception $e) {

            $this->error($e->getMessage(), $errvar, $e);

        }

    }

    public function index($pag = '')
    {

        try{
            // vars
            $controlador = $this->controlador;
            $accion = $this->accion;
            $path = $this->path = KUMBIA_PATH;

            $ejercicio_id = Session :: get_data('eje.id');

            // busqueda
            $b = new Busqueda($controlador, $accion);
            $b->campos();

            // genera las condiciones
            $b->establecerCondicion(
                'festados_id',
                "festados_id = '" . $b->campo('festados_id') . "'"
           );

            $b->establecerCondicion(
                'dependencia_id',
                "dependencia_id = '" . $b->campo('dependencia_id') . "'"
           );

            $b->establecerCondicion(
                'metodopago_id',
                "metodopago_id = '" . $b->campo('metodopago_id') . "'"
           );

            $b->establecerCondicion(
                'fecha',
                "fecha = '" . Utils :: fecha_convertir($this->post('fecha')) . "'"
           );

            $c = "factura.ejercicio_id = '" . $ejercicio_id . "' ";
            $c .= ($b->condicion() ? "AND " . $b->condicion() : '');

            // cuenta todos los registros
            $facturas = new Factura();
            $registros = $facturas->count(($c == "" ? "" : $c));

            // paginacion
            $paginador = new Paginador($controlador, $accion);
            if ($pag != '') {
                $paginador->guardarPagina($pag);
            }
            $paginador->estableceRegistros($registros);
            $paginador->generar();

            // ejecuta la consulta
            $facturas = $facturas->find(
                "conditions: " . $c,
                'order: folio DESC ',
                'limit: ' . ($paginador->pagina() * $paginador->rpp()) . ', '
                . $paginador->rpp()
           );

            // catalogos
            $dependencias = new Dependencia();
            $dependencias = $dependencias->facturadas($ejercicio_id);

            $festados = new Festados();
            $festados = $festados->find();

            $metodospago = new Metodopago();
            $metodospago = $metodospago->find();


            $acl = new gacl_extra();

            // salida
            $this->acl = $acl->acl_check_multiple(
                array(
                    $this->controlador => array(
                        'agregar',
                        'control',
                        'editar',
                        'eliminar'
                   )
               ),
                Session :: get_data('usr.login')
           );

            $this->busqueda      =  $b;
            $this->dependencias  =  $dependencias;
            $this->facturas      =  $facturas;
            $this->festados      =  $festados;
            $this->paginador     =  $paginador;
            $this->registros     =  $registros;
            $this->metodospago   =  $metodospago;

        }catch(Exception $e) {
            $this->error($e->getMessage(), $errvar, $e);

        }

    }

    public function ver($id = '')
    {
        try{
            $this->option = "captura";

            $factura = new Factura();
            $factura = $factura->find($id);

            if ($factura->id == '') {
                throw new Exception("Error la factura no es valida.");
            }

            $this->factura = $factura;

        }catch(Exception $e) {

            if ($transaccion)
                mysql_query("ROLLBACK") or die("Error al cancelar la transaccion");

            $this->error($e->getMessage(), $errvar, $e);
        }
    }
}
