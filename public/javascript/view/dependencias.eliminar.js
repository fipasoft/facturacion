function init(){
	aceptar = document.getElementById('aceptar');
	if(aceptar){
	aceptar.onclick = function(){
		f = document.getElementById('frm_eliminar');
		f.submit();
	}
	}
	
	cancelar = document.getElementById('cancelar');
	if(cancelar){
	cancelar.onclick = function(){
		document.location.href = '../';
	}
	}	
}
addDOMLoadEvent(init);