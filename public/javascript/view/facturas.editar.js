document.observe("dom:loaded",  function(){
	
	factura = new Factura();
	
	$$( '.switch' ).each( function( e ){ 
		
		Evt.fire( e, 'click' );
		
	});
	
	$( 'metodopago_id' ).observe( 'change', function( ev ){
		if( $F( ev.element() ) > 2 ){
			$( 'ctapago_div' ).show();
			$( 'ctapago' ).enable();
			
		}else{
			$( 'ctapago_div' ).hide();
			$( 'ctapago' ).clear();
			$( 'ctapago' ).disable();
		}
	});
	
});