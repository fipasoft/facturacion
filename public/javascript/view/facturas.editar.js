document.observe("dom:loaded",  function(){
	
	factura = new Factura();
	
	$$( '.switch' ).each( function( e ){ 
		
		Evt.fire( e, 'click' );
		
	});
	
});