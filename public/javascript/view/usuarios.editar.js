function ajaxAcceso(grupo){
		new Ajax.Updater('acceso', '../verAcceso/', { 
		   method: 'post',
		   onLoading: function(){ $('acceso').hide(); $('spinner1').show();},
		   onComplete: function() { $('spinner1').hide(); $('acceso').show()},
		   parameters: {grupo: grupo.value}
		});
}

function init(){
	editar = $('editar');
	if(editar){
	editar.onclick = function(){
		f = $('frm_editar');
		if(	frm_validar('frm_editar')){
			f.submit();
		}
	}
	}
	
	cancelar = $('cancelar');
	if(cancelar){
	cancelar.onclick = function(){
		if(confirm('Al cancelar se perderan los cambios hechos en este formulario, desea continuar?')){
			document.location.href = '../';
		}
	}
	}
	
	grupo = $('grupo');
	if(grupo){
		ajaxAcceso(grupo);
		grupo.onchange = function(){
			new Ajax.Updater('acceso', '../verAcceso/', { 
			   method: 'post',
			   onLoading: function(){ $('acceso').hide(); $('spinner1').show();},
			   onComplete: function() { $('spinner1').hide(); $('acceso').show()},
			   parameters: {grupo: grupo.value}
			});
		}
	}
	
}
addDOMLoadEvent(init);