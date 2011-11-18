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


function oNumero(numero){
//Propiedades
this.valor = numero || 0
this.dec = -1;

//Métodos
this.formato = numFormat;
this.ponValor = ponValor;

//Definición de los métodos
function ponValor(cad)
{
if (cad =='-' || cad=='+') return
if (cad.length ==0) return
if (cad.indexOf('.') >=0)
this.valor = parseFloat(cad);
else
this.valor = parseInt(cad);
}

function numFormat(dec, miles)
{
var num = this.valor, signo=3, expr;
var cad = ""+this.valor;
var ceros = "", pos, pdec, i;
for (i=0; i < dec; i++)
ceros += '0';
pos = cad.indexOf('.')
if (pos < 0)
cad = cad+"."+ceros;
else
{
pdec = cad.length - pos -1;
if (pdec <= dec)
{
for (i=0; i< (dec-pdec); i++)
cad += '0';
}
else
{
num = num*Math.pow(10, dec);
num = Math.round(num);
num = num/Math.pow(10, dec);
cad = new String(num);
}
}
pos = cad.indexOf('.')
if (pos < 0) pos = cad.lentgh
if (cad.substr(0,1)=='-' || cad.substr(0,1) == '+')
signo = 4;
if (miles && pos > signo)
do{
expr = /([+-]?\d)(\d{3}[\.\,]\d*)/
cad.match(expr)
cad=cad.replace(expr, RegExp.$1+','+RegExp.$2)
}
while (cad.indexOf(',') > signo)
if (dec<0) cad = cad.replace(/\./,'')
return cad;
}
}
//Fin del objeto oNumero:


var Serie = {
	sumar: function ( serie, prcs, includeAll ){
		var precision = ( prcs ? prcs : 2 ) ;
		return $$( serie ).inject( 0.00, function(acc, n) {
			acc = parseFloat( acc );
			var n = parseFloat( n.value || n.innerHTML.sub( ',', '') );
			return parseFloat( acc + ( isNaN( n ) || ( includeAll && n.disabled ) ? 0 : n ) ).toFixed( precision ); 
		});
	}
};