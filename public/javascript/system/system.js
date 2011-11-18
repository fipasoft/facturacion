function sys(){
// selector de ejercicio
$('ejercicioBoton').onclick = function(){
if($('ejercicioActual').style.display == 'none'){
	$('ejercicioActual').style.width = this.dWidth;
	$('ejercicioSelector').style.display = 'none';
	Effect.Appear('ejercicioActual');
}else{
	this.dWidth = $('ejercicioActual').getWidth();
	$('ejercicioActual').style.display = 'none';
	Effect.Appear('ejercicioSelector');
}
};

$('ejercicioSelect').onchange = function (){$('frm_ejercicio').submit();};

// botones
a = $('aSearch');
if( a ){
a.href = 'javascript:;';
a.onclick = function(){
div_sw('search');
};
}

aceptar = $('aceptar');
_frm_id = 'frm_' +  $F('VIEW');
if( aceptar && $( _frm_id ) ){
aceptar.onclick = function(){
f = $( _frm_id );
if( f.verificar() ){
f.submit();
}
};
}

cancelar = document.getElementById('cancelar');
if(cancelar){
cancelar.onclick = function(){
document.location.href = 'http://' + document.domain + $F('KUMBIA_PATH') + $F('CONTROLLER') + '/' ;
};
}

reset = $('reset');
if(reset){
reset.onclick = function(){
$('frm_search').limpiar();
$('frm_search').submit();
Effect.DropOut($('search'));
};
}

$$('.sub').each(function(a){
	a.href = 'javascript:;';
	a.onclick = function(){
		div_sw(this.id.replace("bsub", "dsub"));
	};
});

}
addDOMLoadEvent(sys);