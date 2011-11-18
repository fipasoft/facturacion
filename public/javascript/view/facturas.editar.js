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
			document.location.href = './';
		}
	}
	}
	
	rfc = $('dependencia_id');
	if(rfc){
    rfc.onchange = function(){
        new Ajax.Updater('fiscales', $('KUMBIA_PATH').value + 'externas/fiscales/', { 
           method: 'post',
           onLoading: function(){ $('check').hide(); $('spinner').show(); },
           onComplete: function() { $('spinner').hide(); $('check').show()},
           parameters: {dependencia_id: $('dependencia_id').value}
        });
    }
    }
	
	fdependencia = $('fdependencia_id');
	if(fdependencia){
	    rfc.value = fdependencia.value;
	    
	    new Ajax.Updater('fiscales', $('KUMBIA_PATH').value + 'externas/fiscales/', { 
           method: 'post',
           onLoading: function(){ $('check').hide(); $('spinner').show(); },
           onComplete: function() { $('spinner').hide(); $('check').show()},
           parameters: {dependencia_id: $('fdependencia_id').value}
        });
	}
}
addDOMLoadEvent(init);