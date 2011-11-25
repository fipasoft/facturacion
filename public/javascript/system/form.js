var Formulario = {
	inicializar: function( f ){
//		f.inicializarEnteros( f );
//		f.inicializarFechas( f );
		f.inicializarReales( f );
	},

	inicializarReales: function( f ){

	},

	limpiar: function( f ){
		$( f ).getElements().each( function( e ){
			e.value = '';
			e.selectedIndex = 0;
		});
	},

	verificar: function( f ){
		var pass = true;
		if( !f.verificarCampos( f ) ||
			!f.verificarGrupos( f ) ||
			!f.verificarBloques( f ) ||
			!f.verificarPeriodos( f )
		){
			pass = false;
		}

		return pass;
	},

	verificarCampos: function( f ){
		var pass = true;

		$( f ).getElements().each( function( e ){
			if( e.value == '' && !e.disabled && !$(e).hasClassName( '_opcional_' ) ){
				pass = false;
				try{
					$(e).focus();
					if( e.type == 'hidden' || !e.visible() ){
						alert('Hay campos requeridos-ocultos por llenar');	
					}else{
						Effect.Shake(e);
					}
				}catch(err){
					alert('Faltan campos por llenar');
				}
				throw $break;
			}
		});

		return pass;
	},
	
	
	verificarBloque: function( f, x, b ){
		var pass = false;
		var u;
		$$( '#' + $( b ).identify() + ' .ico-bloque' ).each( function( e ){
			if( e.src.include( 'disponible' ) ){
				pass = true;
				throw $break;
			}
		});
		return pass;
	},
	
	
	verificarBloques: function( f ){
		var pass = true;
		var u;
		$$( '#' + $( f ).identify() + ' ._bloque_' ).each( function( b ){
			if( b.visible() && !f.verificarBloque( f, b ) ){ 
				u = b; pass = false; 
			}
		});
		if(!pass){
			try{
				Effect.Shake( u );
			}catch(err){
				alert( 'Faltan campos por llenar' );
			}
		}
		return pass;
	},

	verificarGrupo: function( f, x, g ){
		var pass = false;
		var activo = false;
		var u;
		$$( '#' + $( g ).identify() + ' input[type="radio"], ' +
			'#' + $( g ).identify() + ' input[type="checkbox"]' ).each( function( e ){
			u = e;
			if( !$( e ).disabled ){
				activo = true;
			}
			if( $( e ).checked ){
				pass = true;
				throw $break;
			}
		});
		if( activo && !pass){
			try{
				$(u).focus();
				Effect.Shake(u);
			}catch(err){
				alert('Faltan campos por llenar');
			}
		}
		return pass;
	},

	verificarGrupos: function( f ){
		var pass = true;
		$$( '#' + $( f ).identify() + ' ._grupo_' ).each( function( g ){
			if( g.visible() && !f.verificarGrupo( f, g ) ){ pass = false; }
		});
		return pass;
	},

	valPeriodo: function( ini, fin ){
		var f1 = new Date( fechaMMDDAAAA( ini ) );
		var f2 = new Date( fechaMMDDAAAA( fin ) );
		if(f1 > f2){
			return false;
		}
		return true;
	},
	
	valRango: function( fecha, ini, fin ){
		if( 
			Formulario.valPeriodo( ini, fecha ) && 
			Formulario.valPeriodo( fecha, fin )
		){
			return true;
		}
		return false;
	},
	
	verificarPeriodo: function( f, x, ini, fin ){
		var f1 = new Date( fechaMMDDAAAA( ini ) );
		var f2 = new Date( fechaMMDDAAAA( fin ) );
		if(f1 > f2){
			alert( 'La fecha de inicio debe ser menor a la de fin.' );
			return false;
		}
		return true;
	},

	verificarPeriodos: function( f ){
		var pass = true;
		var ini;
		var fin;
		$$( '#' + $( f ).identify() + ' ._periodo_' ).each( function( p ){
			
			ini = '';
			fin = '';
			
			$$( '#' + $( p ).identify() + ' ._fecha_' ).each( function( c ){
				fin = $F( c );
				if( ini != '' ){
					if( !f.verificarPeriodo( f, ini, fin ) ){
						try{
							$( c ).focus();
						}catch(err){

						}
						pass = false; throw $break;
					}
				}
				ini = $F( c );
			});
			
		});
		return pass;
	}
};

Element.addMethods( [ 'FORM' ], Formulario );

function initCampos( e, eId ){
	// inicializar campos
	$$( ( eId ? '#' + eId + ' ' : '' ) + 'input.real, ' + ( eId ? '#' + eId + ' ' : '' ) + 'input._real_').each( function(campo){
		campo.onblur = function(){validar_cantidad(this);};
	});

	$$( ( eId ? '#' + eId + ' ' : '' ) + 'input.entero, ' + ( eId ? '#' + eId + ' '  : '' ) + 'input._entero_').each(function(campo){
		campo.onblur = function(){validar_entero(this);};
	});

	$$( 'input._fecha_').each( function(campo){
		campo.observe( 'blur', function( e ){
			var e = e.element();
			valFecha( e );
		});
//		campo.onblur = function(){ valFecha(campo) };
		var id = campo.identify();
		boton = $('btn_' + id );
		var ops = $H({electric : false, inputField : id, ifFormat : '%d/%m/%Y' });
		if( campo.hasClassName( '_ejercicio_' ) ){ ops.set( 'range', [ $F( 'EJE_ANNIO' ), $F( 'EJE_ANNIO' ) ]); }
		if( boton ){ boton.href = 'javascript:;'; ops.set( 'button', boton.id ); }
		Calendar.setup( ops.toObject() );
	});

	$$( ( eId ? '#' + eId + ' ': '') + '._lista_').each( function( lista ){
		var opcion = lista.options[ lista.selectedIndex ];
		lista.title = opcion.title;
		lista.onchange = function(){
			var opcion = this.options[ this.selectedIndex ];
			this.title = opcion.title;
		};
	});
}

document.observe("dom:loaded", initCampos );