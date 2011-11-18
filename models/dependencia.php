<?php
// publicidad, Creado el 22/06/2009
/**
 * Dependencia
 *
 * @package    Modelos
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */

class Dependencia extends ActiveRecord{
	protected $tipo;
	protected $facturado;
	protected $publicado;

	private $_cached;
	
	public function contactos(){
		$contacto = new Contacto();
		return	
			$contacto->find_all_by_sql(
				"SELECT
					contacto.*
				FROM 
					contacto
				INNER JOIN depcontacto ON contacto.id = depcontacto.contacto_id
				WHERE 
				depcontacto.ejercicio_id = '".Session :: get_data( 'eje.id')."' AND
				depcontacto.dependencia_id = '".$this->id."'
				"	
			);
	}

	public function conCampania( $ejercicio_id = '' ){
		
		$dependencias = new Dependencia();
		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );
		
		return 
			$dependencias->find_all_by_sql(
				"SELECT " .
					"dependencia.* " . 
				"FROM " .
					"dependencia " .
					"Inner Join campania ON campania.dependencia_id = dependencia.id " .
				"WHERE " .
					( $ejercicio_id = 'ALL' ? '1 ' : "dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
				"GROUP BY " .
				 	"dependencia.id " .
				"ORDER BY " . 
					"dependencia.nombre "
			);
		
	}
	
	public function dependenciasPorEstadoFactura($clave){
		$dependencias = new Dependencia();
		return
		$dependencias->find_all_by_sql(
					"SELECT
						dependencia.*
					FROM 
						dependencia
					INNER JOIN factura ON dependencia.id = factura.dependencia_id
					INNER JOIN festados ON factura.festados_id = festados.id
					WHERE festados.clave = '".$clave."'
					GROUP BY dependencia.id
					ORDER BY dependencia.nombre
					"
					);
	}

	public function esUDG(){
		$fiscal = $this->fiscal();

		return($fiscal->rfc == "UGU250907MH5");

	}

	public function fiscal(){
		$fiscal = new Fiscal();
		$fiscal = $fiscal->find($this->fiscal_id);
		return $fiscal;
	}

	public function facturadas( $ejercicio_id = '' ){

		$dependencias = new Dependencia();
		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		return
		$dependencias->find_all_by_sql(
				"SELECT " .
					"dependencia.id, " . 
					"dependencia.fiscal_id, " . 
					"dependencia.ejercicio_id, " . 
					"dependencia.clave, " . 
					"dependencia.nombre, " . 
					"dependencia.saved_at, " . 
					"dependencia.modified_in, " . 
					"dependencia.externo " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
				"WHERE " .
					( $ejercicio_id == 'ALL' ? '1 ' : "factura.ejercicio_id = '" . $ejercicio_id . "' "  ) .
				"GROUP BY " .
				 	"dependencia.id " .
				"ORDER BY " . 
					"dependencia.nombre "
		);

	}


	public function facturado( $ejercicio_id = '' ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( impacto.preciof ) AS facturado " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
					"Inner Join concepto ON concepto.factura_id = factura.id " .
					"Inner Join impacto ON impacto.concepto_id = concepto.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
					"AND ( " . 
						"estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->facturado;
						
		}

		return
		$_cached[ __FUNCTION__ ];


	}

	/**
	 * Obtiene para todas las dependencias del ejercicio los montos facturados 
	 * 
	 * @param int $ejercicio_id
	 */
	public function facturadoPorDependencia( $ejercicio_id = '', $dependencia_id = '' ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( impacto.preciof ) AS facturado " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
					"Inner Join concepto ON concepto.factura_id = factura.id " .
					"Inner Join impacto ON impacto.concepto_id = concepto.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
					"AND ( " . 
						"estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->facturado;
						
		}

		return
			$_cached[ __FUNCTION__ ];


	}
	
	
	public function facturadoPorMedio( $medio = '', $ejercicio_id = '' ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( impacto.preciof ) AS facturado " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
					"Inner Join concepto ON concepto.factura_id = factura.id " .
					"Inner Join impacto ON impacto.concepto_id = concepto.id " .
					"Inner Join producto ON impacto.producto_id = producto.id " .
					"Inner Join proveedor ON producto.proveedor_id = proveedor.id " .
					"Inner Join medio ON proveedor.medio_id = medio.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
					"AND medio.clave = '" . $medio . "' " .
					"AND ( " . 
						"estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->facturado;
						
		}

		return
		$_cached[ __FUNCTION__ ];

	}

	public function facturadoPorMes( $mes = 1, $ejercicio_id = ''  ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( impacto.preciof ) AS facturado " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
					"Inner Join concepto ON concepto.factura_id = factura.id " .
					"Inner Join impacto ON impacto.concepto_id = concepto.id " .
					"Inner Join producto ON impacto.producto_id = producto.id " .
					"Inner Join proveedor ON producto.proveedor_id = proveedor.id " .
					"Inner Join medio ON proveedor.medio_id = medio.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ) .
					"AND MONTH( factura.fecha ) = '" . $mes . "' " .
					"AND ( " . 
						"estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->facturado;
						
		}

		return
		$_cached[ __FUNCTION__ ];

	}
	
	public function impactosAgrupadosPorFacturar(){
		
		$estado = new Estado();
		$estado = $estado->porclave("FCT-INI");

		$cargo = new Cargo();
		$cargo = $cargo->find( '1' ); // ID: 1 = Con cargo a la dependencia

		$impactos = new Impacto();
		$impactos = $impactos->find_all_by_sql(
			"SELECT
				*
			FROM
			(
				(	
					SELECT
				 		CONCAT( '_', seccion.id, '_', tamanio.id, '_', impresion.id, '_', producto.precio ) AS pseudo_id,
						GROUP_CONCAT( impacto.id ORDER BY impacto.id DESC SEPARATOR ','  ) AS ids,
						campania.id AS campania_id,
						campania.nombre AS campania_nombre,
						solicitud.id AS solicitud_id,
						CONCAT( SUBSTRING( medio.clave, 1, 3 ) , '-', LPAD( solicitud.clave, 3, '0' ) ) AS solicitud_clave,
						medio.clave AS medio_clave,
						proveedor.ncorto AS proveedor_ncorto,
						CONCAT( publicacion.nombre, ' ', seccion.nombre ) AS canal_info,
						CONCAT( bloque.nombre, ' (', impresion.clave, ')' ) AS producto_info,
						COUNT( impacto.id ) AS cantidad,
						producto.precio AS producto_precio
					FROM
						impacto
						Inner Join estado ON impacto.estado_id = estado.id
						Inner Join solicitud ON impacto.solicitud_id = solicitud.id
						Inner Join campania ON solicitud.campania_id = campania.id
						Inner Join producto ON impacto.producto_id = producto.id
						Inner Join inserto ON inserto.producto_id = producto.id
						Inner Join impresion ON inserto.impresion_id = impresion.id
						Inner Join aparicion ON inserto.aparicion_id = aparicion.id
						Inner Join distribucion ON aparicion.distribucion_id = distribucion.id
						Inner Join dia ON distribucion.dia_id = dia.id
						Inner Join seccion ON aparicion.seccion_id = seccion.id
						Inner Join publicacion ON seccion.publicacion_id = publicacion.id
						Inner Join proveedor ON publicacion.proveedor_id = proveedor.id
						Inner Join medio ON proveedor.medio_id = medio.id
						Inner Join tamanio ON inserto.tamanio_id = tamanio.id
						Inner Join bloque ON tamanio.bloque_id = bloque.id
					WHERE
						estado.clave = 'FCT-INI'
						AND impacto.cargo_id = '1'
						AND campania.dependencia_id = '"  . $this->id . "'
					GROUP BY
						solicitud.id, seccion.id, tamanio.id, impresion.id, producto.precio
					ORDER BY
						campania.nombre, solicitud.clave
				)
				UNION ALL
				(
				
				SELECT 
				 		CONCAT( '_', spot.duracion, '_', programa.id, '_', producto.precio ) AS pseudo_id, 
						GROUP_CONCAT( impacto.id ORDER BY impacto.id DESC SEPARATOR ','  ) AS ids,
						campania.id AS campania_id,
						campania.nombre AS campania_nombre,
						solicitud.id AS solicitud_id,
						CONCAT( SUBSTRING( medio.clave, 1, 3 ) , '-', LPAD( solicitud.clave, 3, '0' ) ) AS solicitud_clave,
						medio.clave AS medio_clave, 
						proveedor.nombre AS proveedor_nombre, 
						CONCAT( programa.nombre, ' (', canal.nombre, ')' ) AS canal_info,
						CONCAT( 'SPOT ', spot.duracion, '\"' ) AS producto_info,
						COUNT( impacto.id ) AS cantidad,
						producto.precio AS producto_precio
					FROM 
						impacto
						Inner Join estado ON impacto.estado_id = estado.id
						Inner Join solicitud ON impacto.solicitud_id = solicitud.id
						Inner Join campania ON solicitud.campania_id = campania.id
						Inner Join producto ON impacto.producto_id = producto.id
						Inner Join spot ON spot.producto_id = producto.id 
						Inner Join proveedor ON producto.proveedor_id = proveedor.id  
						Inner Join medio ON medio.id = proveedor.medio_id 
						Inner Join horario ON spot.horario_id = horario.id 
						Inner Join dia ON horario.dia_id = dia.id 
						Inner Join programa ON horario.programa_id = programa.id 
						Inner Join audiencia ON programa.audiencia_id = audiencia.id 
						Inner Join canal ON programa.canal_id = canal.id 
					WHERE 
						estado.clave = 'FCT-INI'
						AND impacto.cargo_id = '1'
						AND campania.dependencia_id = '"  . $this->id . "'
					GROUP BY
						solicitud.id, spot.duracion, programa.id, producto.precio
					ORDER BY
						campania.nombre, solicitud.clave
				) 
			) AS impacto
			ORDER BY
				campania_nombre, solicitud_clave" 
		);
		
		$datos = array();
		foreach($impactos as $impacto){
			
			$datos[ $impacto->campania_id ][ $impacto->solicitud_id ][ $impacto->pseudo_id] = $impacto;
			
		}

		return 
			$datos;
		
	}

	public function publicado( $ejercicio_id = '' ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( producto.precio ) AS publicado " .
				"FROM " .
					"dependencia " .
					"Inner Join campania ON campania.dependencia_id = dependencia.id " .
					"Inner Join solicitud ON solicitud.campania_id = campania.id " .
					"Inner Join impacto ON impacto.solicitud_id = solicitud.id " .
					"Inner Join producto ON impacto.producto_id = producto.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
					"AND ( " . 
						"estado.clave = 'PUB-OK' " . 
						"OR estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->publicado;
						
		}

		return
		$_cached[ __FUNCTION__ ];


	}
	
	
	public function publicadoPorMedio( $medio = '', $ejercicio_id = '' ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( producto.precio ) AS publicado " .
				"FROM " .
					"dependencia " .
					"Inner Join campania ON campania.dependencia_id = dependencia.id " .
					"Inner Join solicitud ON solicitud.campania_id = campania.id " .
					"Inner Join impacto ON impacto.solicitud_id = solicitud.id " .
					"Inner Join producto ON impacto.producto_id = producto.id " .
					"Inner Join proveedor ON producto.proveedor_id = proveedor.id " .
					"Inner Join medio ON proveedor.medio_id = medio.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
					"AND medio.clave = '" . $medio . "' " .
					"AND ( " .
						"estado.clave = 'PUB-OK' " . 
						"OR estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->publicado;
						
		}

		return
		$_cached[ __FUNCTION__ ];

	}
	
	
	public function porfacturar( $ejercicio_id = ''  ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		$estado = new Estado();
		$estado = $estado->porclave("FCT-INI");

		$cargo = new Cargo();
		$cargo = $cargo->find('1');

		$dependencia = new Dependencia();
		$dependencias = $dependencia->find_all_by_sql(
			"SELECT " . 
				"dependencia.* " .
			"FROM " . 
				"impacto " .
				"INNER JOIN solicitud ON impacto.solicitud_id = solicitud.id " .
				"INNER JOIN campania ON solicitud.campania_id = campania.id " .
				"INNER JOIN dependencia ON campania.dependencia_id = dependencia.id " .
			"WHERE " . 
				"impacto.estado_id = '".$estado->id."' AND impacto.cargo_id = '".$cargo->id."' " .
				( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ) .
			"GROUP BY " . 
				"dependencia.id " .
			"ORDER BY " .
				"dependencia.nombre "
		);

		return
			$dependencias;
					
	}

	public function facturasPorEstadoFactura($clave){
		$dependencias = new Dependencia();
		return
		$dependencias->find_all_by_sql(
					"SELECT
						factura.*
					FROM 
						dependencia
					INNER JOIN factura ON dependencia.id = factura.dependencia_id
					INNER JOIN festados ON factura.festados_id = festados.id
					WHERE festados.clave = '".$clave."' AND dependencia.id = '".$this->id."'
					GROUP BY factura.id
					ORDER BY factura.folio DESC
					"
					);
	}

	// TODO: Mostrar
	public function razon(){

		$fiscal = new Fiscal();
		$fiscal = $fiscal->find( $this->fiscal_id );

		return
		$fiscal->razon;

	}

	public function recuperadoPorMes( $mes = 1, $ejercicio_id = ''  ){

		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		if( !isset( $_cached[ __FUNCTION__ ] ) ){
			$dependencia = new Dependencia();
			$dependencia = $dependencia->find_all_by_sql(
				"SELECT " .
					"SUM( impacto.preciof ) AS facturado " .
				"FROM " .
					"dependencia " .
					"Inner Join factura ON factura.dependencia_id = dependencia.id " .
					"Inner Join concepto ON concepto.factura_id = factura.id " .
					"Inner Join impacto ON impacto.concepto_id = concepto.id " .
					"Inner Join producto ON impacto.producto_id = producto.id " .
					"Inner Join proveedor ON producto.proveedor_id = proveedor.id " .
					"Inner Join medio ON proveedor.medio_id = medio.id " .
					"Inner Join estado ON impacto.estado_id = estado.id " .
				"WHERE " .
					"dependencia.id = '" . $this->id . "' " .
					( $ejercicio_id == 'ALL' ? '' : "AND dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ) .
					"AND MONTH( factura.fecha ) = '" . $mes . "' " .
					"AND ( " . 
						"estado.clave = 'FCT-INI' " .
						"OR estado.clave = 'FCT-LST' " .
						"OR estado.clave = 'FCT-ENV' " .
						"OR estado.clave = 'FCT-PAG' " .
					") "
					);
						
					$dependencia = $dependencia[ 0 ];

					$_cached[ __FUNCTION__ ] = $dependencia->facturado;
						
		}

		/*return
			rand( 10000, 1000000 );*/

		return
		$_cached[ __FUNCTION__ ];

	}
	
	public function todas( $ejercicio_id = '' ){

		$dependencias = new Dependencia();
		$ejercicio_id = ( $ejercicio_id ? $ejercicio_id : Session :: get_data( 'eje.id' ) );

		return
			$dependencias->find_all_by_sql(
				"SELECT " .
					"dependencia.id, " . 
					"dependencia.fiscal_id, " . 
					"dependencia.ejercicio_id, " . 
					"dependencia.clave, " . 
					"dependencia.nombre, " . 
					"dependencia.saved_at, " . 
					"dependencia.modified_in, " . 
					"dependencia.externo " .
				"FROM " .
					"dependencia " .
				"WHERE " .
					( $ejercicio_id = 'ALL' ? '1 ' : "dependencia.ejercicio_id = '" . $ejercicio_id . "' "  ).
				"GROUP BY " .
				 	"dependencia.id " .
				"ORDER BY " . 
					"dependencia.nombre "
			);

	}

}
?>