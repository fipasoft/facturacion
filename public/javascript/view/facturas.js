Factura = Class.create({

	initialize: function( id ){
		
		this.id = id;
		
		// tabla dinamica
		var tbl = $( 'tblConceptos' );
		
		if( tbl ){
			t = new Tablik( tbl );
			t.callbacks.dactRow_Before = this.desactivarFila;
			t.callbacks.initRow_After = this.inicializarTablero;
			t.callbacks.addRow_After = this.inicializarFila;
			t.callbacks.delRow_Before = this.eliminarFila;
			this.inicializarForma();
			this.inicializarTablero( 'tblConceptos' );
		}

	},
	
	activarFila: function(){
		
		$$( '#' + id + ' .cantidad' ).each( function( e ){ 
			Evt.fire( e, 'blur' );
		});
		
	},
	
	desactivarFila: function( id ){
		
		$$( '#' + id + ' .cantidad' ).each( function( e ){ 
			e.value = '';
			Evt.fire( e, 'blur' );
		});
		
	},
	
	eliminarFila: function( id ){
		
		$$( '#' + id + ' .cantidad' ).each( function( e ){ 
			Evt.fire( e, 'blur' );
		});
		
	},
	
	inicializarForma: function(){
		
		rfc = $( 'dependencia_id' );
		if( rfc ){
			rfc.observe( 'change', function( ev ){
				var e = ev.element();
		        if( e.value != '' ){
		            new Ajax.Updater('fiscales', $('KUMBIA_PATH').value + 'externas/fiscales/', { 
		               method: 'post',
		               onLoading: function(){ $('check').hide(); $('spinner').show(); },
		               onComplete: function() { $('spinner').hide(); $('check').show();},
		               parameters: {dependencia_id: $('dependencia_id').value}
		            });
	            }else{
	                $( 'fiscales' ).innerHTML = 'Elija una dependencia.';
	            }
		    });
		}
		
		fdependencia = $( 'fdependencia_id' );
		if(fdependencia){
		    $('dependencia_id').value = fdependencia.value;
		    new Ajax.Updater('fiscales', $('KUMBIA_PATH').value + 'externas/fiscales/', { 
                       method: 'post',
                       onLoading: function(){ $('check').hide(); $('spinner').show(); },
                       onComplete: function() { $('spinner').hide(); $('check').show();},
                       parameters: {dependencia_id: fdependencia.value}
                    });
		}
		
	},
	
	inicializarTablero: function( scope ){
		
		// evento para montos asignados
		$$( ( scope ? '#' + scope + ' ' : '' ) + '.unitario' ).invoke( 'observe', 'blur', function( e ){
			
			var e = e.element();
			var td = e.up( 'td' );
			var tr = td.up( 'tr' );
			var cantidad = $( tr.down( '.cantidad' ) );
			var table = tr.up( 'table' ).identify();
			
			// actualizando columna
			var subtotal = Serie.sumar( '#' + table + ' .monto' );
			var iva = parseFloat( parseFloat( subtotal ) * GlobalVars.iva ).toFixed( 2 );
			$( 'subtotal'  ).value = subtotal;
			$( 'iva'  ).value = iva;
			$( 'total' ).value =  ( parseFloat( subtotal ) + parseFloat( iva ) ).toFixed( 2 );
			
			Evt.fire( cantidad, 'blur' );
			
			e.removeAttribute( '_counted' );
			
		});
		
		
		$$( ( scope ? '#' + scope + ' ' : '' ) + '.cantidad' ).invoke( 'observe', 'blur', function( e ){
			
			var e = e.element();
			var td = e.up( 'td' );
			var tr = td.up( 'tr' );
			var cantidad = $F( e );
			var vCantidad = parseFloat( cantidad );
			var unitario = $( tr.down( '.unitario' ) );
			var vUnitario = $F( unitario );
			$( tr.down( '.costo' ) ).value = ( isNaN( parseFloat( vUnitario ) ) || isNaN( parseFloat( vCantidad ) ) ? '0.00' : ( vCantidad * parseFloat( vUnitario ) ).toFixed( 2 ) );
			Evt.fire( unitario, 'blur' );
			
		});
		
		
		initCampos( null, scope );
		
	}
	
});