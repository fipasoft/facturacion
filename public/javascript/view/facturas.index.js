document.observe("dom:loaded",  function(){
	
	$$( '.sw-op' ).each( function( a ){
		a.href = 'javascript:;';
		a.observe( 'click', function( e ){ 
			$$( '#' + e.element().up( 'td' ).identify() + ' .div-op' ).invoke('toggle');
		});
	});
	
});