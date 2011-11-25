var Evt = {
	fire: function (element,event){
	    if (document.createEventObject){
	        // dispatch for IE
	        var evt = document.createEventObject();
	        return element.fireEvent('on'+event,evt);
	    }
	    else{
	        // dispatch for firefox + others
	        var evt = document.createEvent("HTMLEvents");
	        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
	        return !element.dispatchEvent(evt);
	    }
	}
};

var Format = {
		
	int: function( n ){
		return Format.real(n, 0);
	},
	
	real: function( n, dec ){
		var d = ( dec >= 0 ? dec : 2 );
		var n = parseFloat( n.sub( ',', '' ) ).toFixed( d );
		if( isNaN( n ) ) return 'NaN';
		var tmp = n.split( '.' );
		var i = 1;
		var ints = tmp[ 0 ].split('').reverse();
		var decs = tmp[ 1 ];
		
		for( i = ints.length; i > 0 ; i-- ){
			if( i % 3 == 0 && i < ints.length ){
				ints.splice( i, 0, ',' );
			}
			
		}

		var m = ints.reverse().join('') + ( d > 0 ? '.' + decs.toString() : '' );
		return ( parseFloat( m ) == 0 ? '-' : m );
	}

};

var Serie = {
	sumar: function ( serie, prcs, includeAll ){
		var precision = ( prcs ? prcs : 2 ) ;
		return $$( serie ).inject( 0.00, function(acc, n) {
			acc = parseFloat( acc );
			var n = parseFloat( n.value || n.innerHTML.gsub( ',', '') );
			return parseFloat( acc + ( isNaN( n ) || ( includeAll && n.disabled ) ? 0 : n ) ).toFixed( precision ); 
		});
	}
};

var Impuestos = {
	verificar: function( tr ){
		var sub = tr.down( 'input.sub' );
		var iva = tr.down( 'input.iva' );
		var isr = tr.down( 'input.isr' );
		
		// verificacion de iva
			
		if( $F( sub ) != '' && $F( iva ) != '' && $F( iva ) != parseFloat( parseFloat( $F( sub ) ) * GlobalVars.iva ).toFixed( 2 ) ){
			iva.up('td').addClassName( 'alert' );
		}else{
			iva.up('td').removeClassName( 'alert' );
		}
		
	
		// verificacion de isr
		var isrVal = 0;
		if( isr ){
			isrVal = $F( isr );
			if( $F( sub ) != '' && $F( isr ) != '' && $F( isr ) != parseFloat( parseFloat( $F( sub ) ) * GlobalVars.isr ).toFixed( 2 ) ){
				isr.up('td').addClassName( 'alert' );
			}else{
				isr.up('td').removeClassName( 'alert' );
			}
		}
		
		// verificacion de monto
		var tot = tr.down( 'input.monto' );
		if( $F( tot ) != '' && $F( sub ) != '' && $F( iva ) != '' && $F( tot ) != parseFloat( parseFloat( $F( sub ) ) + parseFloat( $F( iva ) ) - parseFloat( isrVal ) ).toFixed( 2 ) ){
			tot.up('td').addClassName( 'alert' );
		}else{
			tot.up('td').removeClassName( 'alert' );
		}
	}
};