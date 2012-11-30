<?php
// prefactura, Creado el 21/08/2012
/**
 * XLSPredactura
 *
 * @package    Reportes
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2012 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 *
 */

 Kumbia :: import('lib.excel.main');

 class XLSPrefactura extends Plantilla{

    protected $path;
 	protected $simbolos;

 	public function XLSPrefactura( $facturas ){

 	    $this->inicializarVariables();

 	    if( count( $facturas ) == 1 ){

 	        $f = $facturas[ 0 ];
     		$this->Plantilla( 'Factura preliminar - ' . $f->verFolio() . '.xls' );

 	    }else{

 	        $this->Plantilla( 'Factura preliminar.xls' );

 	    }

 		foreach( $facturas as $factura ){

     		$this->hojaFactura( $factura );

 		}

 	}

 	private function hojaFactura( $factura ){
		$rows = $this->rows();
		$cols = $this->cols( $this->encabezados() );

		$h = $this->agregar_hoja( $factura->verFolio(), $rows, $cols );
		$h->cc_max = count( $cols ) - 1;

		$this->emisor( $h, $factura );
		$this->receptor( $h, $factura );
		$this->conceptos( $h, $factura );
		$this->totales( $h, $factura );

		$h->rr_max = $h->rr;
		$this->propiedades( $h );
	}

	private function conceptos( &$h, $factura ){

	   $ejercicio = $this->ejercicio;
        $st = $this->getEstilos();

        $h->xls->setRow( $h->rr, 20 );

        foreach( $this->encabezados() as $clave => $campo ){
            $this->escribirCampo( $h, $campo );
        }
        $h->nueva_linea();

        $i = 0;
        foreach( $factura->conceptos() as $concepto ){

            foreach( $this->campos( 'contenido' ) as $clave => $campo ){
                $this->escribirCampo( $h, $campo );
            }
            $h->nueva_linea();
            $i ++;

            foreach( $this->campos( 'contenido' ) as $clave => $campo ){

                if( $clave == 'descripcion' ){

                    $h->write_merge(
                          utf8_decode( $concepto->$clave ),
                          $h->rr, $h->cc,
                          $h->rr + 1, $h->cc + 3,
                          $st[ 'TD.Borderside' ]
                    );
                    $h->cc += 4;

                }else{

                    $clave = $campo->id;
                    $this->escribirCampo( $h, $campo, null, null, $concepto->$clave );

                }

            }
            $h->nueva_linea();
            $i ++;

        }

        for( $i; $i < 28; $i++ ){

            foreach( $this->campos( 'contenido' ) as $clave => $campo ){

                    $this->escribirCampo( $h, $campo );

            }

            $h->nueva_linea();
        }

        for( $i = 0; $i < 8; $i++ ){
            $h->xls->writeBlank(
                  $h->rr, $h->cc,
                  $st[ 'TD.Bordertop' ]
            );
            $h->cc++;
        }

        $h->nueva_linea();


	}

	private function receptor( &$h, $factura ){
		$ejercicio = $this->ejercicio;
		$st = $this->getEstilos();

		$receptor = $factura->receptor(
		    array(
		      'razon' => 0,
		      'rfc' => 0,
		      'domicilio' => 0,
		      'colonia' => 0,
		      'cp' => 0,
		      'ciudad' => 0
		    )
		);

		$h->nueva_linea();

		$fiscal = array(
		  'CLIENTE' => 'razon',
		  'RFC' => 'rfc',
		  'DIRECCION' => 'domicilio',
		  'COLONIA' => 'colonia',
		  'C.P.' => 'cp',
		  'CIUDAD' => 'ciudad'
		);

		$i = 0;
		foreach( $fiscal as $clave => $valor ){

		    $h->xls->write(
		          $h->rr, $h->cc,
                  utf8_decode( $clave ) . ':',
                  $st['A10b']
            );
            $h->cc++;

    		$h->write_merge(
    		      utf8_decode( $receptor->$valor ),
    		      $h->rr, $h->cc,
    		      $h->rr, $h->cc + 2,
    		      $st[ ( $i == 0 ? 'A8.5' : 'A9.5' ) ]
    		);
    		$h->cc += 3;

    		$i++;

    		if( $i % 2 == 0 ){

                $h->nueva_linea();
                $h->nueva_linea();

    		}

		}

	}

    private function emisor( &$h, $factura ){
        $ejercicio = $this->ejercicio;
        $st = $this->getEstilos();

        $h->xls->insertBitmap($h->rr, 0, getcwd() . '/public/img/system/logo.bmp', 0, 0, 0.6, 0.55);

        $h->cc = 7;
        $h->xls->write( $h->rr, $h->cc, 'PRELIMINAR', $st['A11c'] );
        $h->xls->write( $h->rr + 1, $h->cc, $factura->verFolio(), $st['A11cg'] );

        $h->xls->write( $h->rr + 3, $h->cc, 'FECHA', $st['A11cg'] );
        $h->xls->write( $h->rr + 4, $h->cc, $factura->verFechaEspanol(), $st['A8.5'] );

        foreach( Session :: get_data( 'fsc.emisor' ) as $i => $linea ){

            $h->cc = 2;
            $h->write_merge(
                  utf8_decode( $linea ),
                  $h->rr, $h->cc,
                  $h->rr, $h->cc + 3,
                  $st['A11']
            );
            $h->nueva_linea();

        }

    }

	private function inicializarVariables(){

	 	// paths
	 	$path = new stdClass();
	 	$path->xml = getcwd() . '/app/reportes/prefactura.xml';

 		// periodo visible

 		// encabezado
		$subtitulo = strtoupper(
			'1 de Enero al ' . Utils :: fecha_espanol(date( 'Y-m-d'), '%d de %m' )
		);

 		// obtener info XML
 		$xml = simplexml_load_file( $path->xml );

 		// carga simbolos
		$simbolos = array();

		// vars
 		$this->path           =   $path;
 		$this->simbolos       =   $simbolos;
 		$this->subtitulo      =   $subtitulo;

 		$this->campos( 'encabezados', $xml->encabezados->campo );
 		$this->campos( 'contenido', $xml->contenido->campo );
	}

	private function propiedades( &$h ){
		$h->xls->centerHorizontally();
		$h->xls->centerVertically();
		$h->xls->hideGridlines();
		$h->xls->printArea(0, 0, $h->rr_max, $h->cc_max);
		$h->xls->setPortrait();
		$h->xls->setMarginLeft(0.15);
		$h->xls->setMarginRight(0.15);
		$h->xls->setMarginTop(0.1);
		$h->xls->setMarginBottom(0.30);
		$h->xls->setHeader('', 0);
		$h->xls->setFooter('', 0);
		$h->xls->setPaper(1);
		$h->xls->setPrintScale(100);
		$h->xls->setZoom(75);
	}

	private function totales( &$h, $factura ){

        $ejercicio = $this->ejercicio;
        $st = $this->getEstilos();

        $h->write_merge(
            'CANTIDAD CON LETRA',
            $h->rr, $h->cc,
            $h->rr, $h->cc + 1,
            $st[ 'A9cgNb' ]
        ); $h->cc++;$h->cc++;
        $h->write_merge(
            $factura->montoConLetra(),
            $h->rr, $h->cc,
            $h->rr + 1, $h->cc + 3,
            $st[ 'A9T' ]
        );
        $h->nueva_linea();
        $h->nueva_linea();
        
        $h->write_merge(
        		'METODO DE PAGO',
        		$h->rr, $h->cc,
        		$h->rr, $h->cc + 1,
        		$st[ 'A9cgNb' ]
        ); $h->cc++;$h->cc++;
        $h->write_merge(
        		$factura->mostrarMetodoDePago(),
        		$h->rr, $h->cc,
        		$h->rr + 1, $h->cc + 3,
        		$st[ 'A9T' ]
        );
        
        $h->rr -= 2;

        $cc = 6;
        $h->xls->write( $h->rr, $cc, 'SUBTOTAL', $st['A9rgNb'] );
        $h->xls->write( $h->rr, $cc + 1, $factura->subtotal, $st['A10Num'] );
        $h->nueva_linea();
        $h->nueva_linea();
        $h->xls->write( $h->rr, $cc, 'IVA', $st['A9rgNb'] );
        $h->xls->write( $h->rr, $cc + 1, $factura->iva, $st['A10Num'] );
        $h->nueva_linea();
        $h->nueva_linea();

        $h->xls->write( $h->rr, $cc, 'TOTAL', $st['A9rgNb'] );
        $h->xls->write( $h->rr, $cc + 1, $factura->total, $st['A10Num'] );
        $h->nueva_linea();
        $h->nueva_linea();

    }


 }
?>
