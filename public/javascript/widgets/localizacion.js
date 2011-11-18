Localizacion = Class.create();

Localizacion.prototype = {
	recipiente: null,
	tipo: null,
	callbacks: {},
	options: {},
	tables: {},

	initialize: function( d, ti ){
		this.recipiente = $( d ).identify();
		this.tipo = ti;
		this.register();
	},

	register: function(){
		if($(this.recipiente)){
			if(this.tipo == 'editar'){
				new Ajax.Request(
						$('KUMBIA_PATH').value + 'localizacion/editar',
						{
							method : 'post',
							parameters : {
								id : $("municipio_id").value
							},
							onLoading : function() {
								$(this.recipiente).innerHTML = '<img src="' + $('KUMBIA_PATH').value + 'img/system/spin.gif" />';
							}.bind(this),
							onSuccess : function(transport) {
								$(this.recipiente).innerHTML = transport.responseText;
								if($('edo_id') && $("loc_municipios")){
									$('edo_id').onchange = function (){
										this.municipios();		
									}.bind(this);
								}
							}.bind(this)
						}).bind(this);
			}else{
			new Ajax.Request(
					$('KUMBIA_PATH').value + 'localizacion/inicia',
					{
						method : 'post',
						parameters : {
							id : this.tipo
						},
						onLoading : function() {
							$(this.recipiente).innerHTML = '<img src="' + $('KUMBIA_PATH').value + 'img/system/spin.gif" />';
						}.bind(this),
						onSuccess : function(transport) {
							$(this.recipiente).innerHTML = transport.responseText;
							if($('edo_id') && $("loc_municipios")){
								$('edo_id').onchange = function (){
									this.municipios();		
								}.bind(this);
							}
						}.bind(this)
					}).bind(this);
			}
		}
	},
	
	municipios: function(){
		new Ajax.Request(
				$('KUMBIA_PATH').value + 'localizacion/municipios',
				{
					method : 'post',
					parameters : {
						id : $("edo_id").value
					},
					onLoading : function() {
						$("loc_municipios").innerHTML = '<img src="' + $('KUMBIA_PATH').value + 'img/system/spin.gif" />';
					}.bind(this),
					onSuccess : function(transport) {
						$("loc_municipios").innerHTML = transport.responseText;
					}.bind(this)
				}).bind(this);
	}

};


Object.extend( Localizacion, {

	load: function(){
		$$( 'div.localizacion_estado' ).each( function( d ){
			new Localizacion( d , 'estado' );
		});

		$$( 'div.localizacion_editar' ).each( function( d ){
			new Localizacion( d , 'editar' );
		});
	}

});


document.observe("dom:loaded", Localizacion.load );