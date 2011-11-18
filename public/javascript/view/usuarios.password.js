function init(){
	aceptar = document.getElementById('aceptar');
	if(aceptar){
	aceptar.onclick = function(){
		f = document.getElementById('frm_pass');
		if(	frm_validar('frm_pass')   	&&
			pss_longitud('pass')	     &&
			pss_comparar('pass', 'pass2')
		){
			f.submit();
		}
	}
	}
	
	cancelar = document.getElementById('cancelar');
	if(cancelar){
	cancelar.onclick = function(){
		if(confirm('Al cancelar se perderan los cambios hechos en este formulario, desea continuar?')){
			document.location.href = '../';
		}
	}
	}	
}
addDOMLoadEvent(init);