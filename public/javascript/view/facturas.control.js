function init(){
	aceptar = $('aceptar');
	if(aceptar){
	aceptar.onclick = function(){
		f = $('frm_agregar');
		if(	frm_validar('frm_agregar') ){
			f.submit();
		}
	}
	}
	
	cancelar = $('cancelar');
	if(cancelar){
	cancelar.onclick = function(){
		if(confirm('Al cancelar se perderan los cambios hechos en este formulario, desea continuar?')){
			document.location.href = $('KUMBIA_PATH').value + 'facturas';
		}
	}
	}
	
	
}
addDOMLoadEvent(init);