var Balzak = Class.create({

	initialize: function( cnt ){
		this.contenedor = cnt ;
		this.almacenes();
		this.botones();
	},

	almacenes: function(){
		var cnt = this.contenedor.identify();
		var d = $$( '#' + cnt +' ._disponibles_' );
		var s = $$( '#' + cnt +' ._seleccion_' );
		this.disponible = d[ 0 ];
		this.seleccion = s[ 0 ];
	},

	botones: function(){
		this.boton( 'marcar' );
		this.boton( 'marcarTodo' );
		this.boton( 'desmarcar' );
		this.boton( 'desmarcarTodo' );
	},

	boton: function( tipo ){
		var cnt = this.contenedor.identify();
		$$( '#' + cnt + ' ._' + tipo + '_' ).each( function( a ){
			a.href = 'javascript:;';
			a.onclick = function(){
				switch( tipo ){
					case 'marcar':
							this.mover( this.disponible, this.seleccion );
						break;
					case 'desmarcar':
							this.mover( this.seleccion, this.disponible );
						break;
					case 'marcarTodo':
							this.mover( this.disponible, this.seleccion, 'todo' );
						break;
					case 'desmarcarTodo':
							this.mover( this.seleccion, this.disponible, 'todo' );
						break;
				}
			}.bind( this );
		}.bind( this ) );
	},

	mover: function( origen, destino, modo ){
		$( origen ).childElements().each( function( e ){
			if( e.selected || modo == 'todo'){
				$( destino ).insert( e, 'before' );
			}
		});
	},

	init: function(){
		$$( '._balzak_' ).each( function( c ){
			new Balzak( c );
		});
	}

});


document.observe("dom:loaded", Balzak.init );