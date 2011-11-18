function init(){
	agregar = $('agregar');
	if(agregar){
	agregar.onclick = function(){
		f = $('frm_agregar');
		if(	frm_validar('frm_agregar')   &&
			pss_longitud('pass')	     &&
			pss_comparar('pass', 'pass2')
		){
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
	
	login = $('login');
	if(login){
	login.onkeyup = function(){
		new Ajax.Updater('check', './validarLogin/', { 
		   method: 'post',
		   onLoading: function(){ $('check').hide(); $('spinner').show(); },
		   onComplete: function() { $('spinner').hide(); $('check').show()},
		   parameters: {login: login.value}
		});
	}
	}
	
	grupo = document.getElementById('grupo');
	if(grupo){
	grupo.onchange = function(){
		new Ajax.Updater('acceso', './verAcceso/', { 
		   method: 'post',
		   onLoading: function(){ $('acceso').hide(); $('spinner1').show();},
		   onComplete: function() { $('spinner1').hide(); $('acceso').show()},
		   parameters: {grupo: grupo.value}
		});
	}
	}
	
}
addDOMLoadEvent(init);