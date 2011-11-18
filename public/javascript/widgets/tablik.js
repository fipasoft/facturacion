Tablik = Class.create();

Tablik.prototype = {
	id: null,
	selectors:{
		addRow:     '.addRow',
		delRow:     '.delRow',
		nCopy:      '.nCopy',
		ico:        '.icon',
		init:       '.tablik',
		unique:     '.unique',
		sw:         '.switch',
		swAllRows:  '.swAllRows'
	},
	callbacks: {},
	options: {},
	tables: {},

	initialize: function( t ){
		this.id = $( t ).identify();
		this.register();
	},

	addRow: function( callbacks ){
		var before = ( callbacks && callbacks.before ? callbacks.before : this.callbacks.addRow_Before );
		var after = ( callbacks && callbacks.after ? callbacks.after : this.callbacks.addRow_After );
		
		var tr;
		
		if( before ){
			before.apply( this );
		}
		
		$$( '#' + this.id + ' table' ).each( function( t ){
			var rows = $( t ).rows;
			var n = $( t ).rows.length;
			if( n > 1 ){
				var i = 1;
				var nTr = null;
				
				// para excluir las filas que no se repiten
				while( ( n - i >= 1 ) ){
					if( !$(rows[ n - i ]).hasClassName( this.selectors.unique.sub( '.', '' ) ) ){
						nTr = rows[ n - i ];
						break;
					}
					
					i++;
				}
				
				// si hay algo que duplicar
				if( nTr ){
					// inserta
					var k = n + 1 - i;
					tr = $( t ).insertRow( k );
					// copia
					tr.innerHTML = nTr.innerHTML; // TODO: change replace for regexp
					if( tr.id.startsWith( 'anonymous_element_' ) ){
						tr.removeAttribute( 'id' );
					}else{
						var regexp = new RegExp( '_' + ( k - 1 ) + '$' );
						tr.id = tr.id.replace( regexp, "_" + k )
					}
					// inicializa
					this.initRow( tr.identify() );
				}
				
			}
		}.bind( this ) );
		
		if( after ){
			after.apply( tr, [ tr.identify() ] );
		}
	},

	delRow: function(){
		$$( '#' + this.id + ' table' ).each( function( t ){
			var rows = $( t ).rows
			var n = $( t ).rows.length;
			if( n > 2 ){
				$( t ).deleteRow( n - 1 );
			}
		});
	},
	
	initRow: function( id, callbacks ){
		var before = ( callbacks && callbacks.before ? callbacks.before : this.callbacks.initRow_Before );
		var after = ( callbacks && callbacks.after ? callbacks.after : this.callbacks.initRow_After );
		var tr = $( id );
		
		if( before ){
			before.apply( tr, [ id ] );
		}
		
		initCampos( null, id );
		this.registerSws( id );
		this.actRow( null, id );
		this.resetRow( id );
		
		if( after ){
			after.apply( tr, [ id ] );
		}
		
	},

	register: function(){
		this.registerButtons();
		this.registerSws();
	},

	registerButtons: function(){
		$$( '#' + this.id + ' ' + this.selectors.addRow ).each( function( b ){
			if( b.tagName == 'A' ){
				b.href = 'javascript:;';
			}
			b.observe( 'click', function( event ){
				this.addRow();
			}.bind( this ));
		}.bind( this ));

		$$( '#' + this.id + ' ' + this.selectors.delRow ).each( function( b ){
			if( b.tagName == 'A' ){
				b.href = 'javascript:;';
			}
			b.observe( 'click', function( event ){
				this.delRow();
			}.bind( this ));
		}.bind( this ));

		$$( '#' + this.id + ' ' + this.selectors.swAllRows ).each( function( b ){
			if( b.tagName == 'A' ){
				b.href = 'javascript:;';
			}
			b.observe( 'click', function( event ){
				this.swAllRows( event );
			}.bind( this ));
		}.bind( this ));
	},

	registerSws: function( id ){
		var id =  id  ?  id  :  this.id;

		$$( '#' + id + ' ' + this.selectors.sw ).each( function( sw ){
			if( sw.tagName == 'A' ){
				sw.href = 'javascript:;';
			}
			if( sw.id.startsWith( 'anonymous_element_' ) ){
				sw.removeAttribute( 'id' );
			}
			sw.observe( 'click', function( event ){
				this.swRow( event );
			}.bind( this ) );
		}.bind( this ) );
	},

	// ROWS METHODS
	actAllRows: function( id ){
		$$('#' + id + ' input.' + this.selectors.sw ).each(function( sw ){
			if( sw.up( 'table' ).identify() == id ){
				sw.checked = true;
				sw.click();
			}
		}.bind( this ));
	},

	actRow: function( id, trId, callbacks ){
		var before = ( callbacks && callbacks.before ? callbacks.before : this.callbacks.actRow_Before );
		var after = ( callbacks && callbacks.after ? callbacks.after : this.callbacks.actRow_After );
		var tr = trId ? $( trId ) : $( id ).up( 'tr' );
		
		if( before ){
			before.apply( tr, [ tr.identify() ] );
		}
		
		tr.addClassName( 'selected' );
		$$('#' + tr.identify() + ' input, #' + tr.identify() + ' select').each(function( e ){
			e.enable();
			if( e.type == 'checkbox' && e.hasClassName( this.selectors.sw.sub( '.', '' ) ) ){
				e.checked = true;
			}
		}.bind( this ));
		
		if( after ){
			after.apply( tr, [ tr.identify() ] );
		}
		
		
	},

	dactAllRows: function( id ){
		$$('#' + id + ' input.' + this.selectors.sw ).each(function( sw ){
			if( sw.up( 'table' ).identify() == id ){
				sw.checked = false;
				sw.click();
			}
		}.bind( this ));
	},

	dactRow: function( id, trId, callbacks ){
		var tr = trId ? $( trId ) : $( id ).up( 'tr' );
		var before = ( callbacks && callbacks.before ? callbacks.before : this.callbacks.dactRow_Before );
		var after = ( callbacks && callbacks.after ? callbacks.after : this.callbacks.dactRow_After );
		
		if( before ){
			before.apply( tr, [ tr.identify() ] );
		}

		tr.removeClassName( 'selected' );
		
		$$('#' + tr.identify() + ' input, #' + tr.identify() + ' select').each( function( e ){
			if( !e.hasClassName('switch') && e.up( 'tr' ).identify() == tr.id  ){
				e.disable();
			}
			e.removeAttribute('_counted');
		});
		
		if( after ){
			after.apply( tr, [ tr.identify() ] );
		}
	},

	resetRow: function( id, callbacks ){
		var before = ( callbacks && callbacks.before ? callbacks.before : this.callbacks.resetRow_Before );
		var after = ( callbacks && callbacks.after ? callbacks.after : this.callbacks.resetRow_After );
		var tr = $( id );
		
		if( before ){
			before.apply( tr, [ id ] );
		}
		
		$$( '#' + id + ' *' ).each( function( e ){ 
			
			// reset id
			if( e.id.startsWith( 'anonymous_element_' ) ){
				e.removeAttribute( 'id' );
			}
			
			// form elements
			if( e.tagName == 'INPUT' || e.tagName == 'SELECT' ){
				if(e.type == 'text' || e.type == 'hidden'){
					e.clear();
				}else if(e.type == 'checkbox' && !e.hasClassName( this.selectors.sw.sub( '.', '' ) )){
					e.checked = false;
				}else if(e.type.startsWith('select') ){
					e.selectedIndex = 0;
					e.title = '';
				}
				e.removeAttribute('_counted');
			}
			
			// switches
			if( e.hasClassName( this.selectors.sw.sub( '.', '' ) )){
				e.show();
				e.enable();
			}
			
			// no copy
			if( e.hasClassName( this.selectors.nCopy.sub( '.', '' ) ) ){
				e.remove();
			}
			
			// ico
			if( e.hasClassName( this.selectors.ico.sub( '.', '' ) ) ){
				e.hide();
			}
		}.bind( this ) );
		
		if( after ){
			after.apply( tr, [ id ] );
		}

	},

	swAllRows: function( event ){
		e = event.element();

		var s = false;
		switch( e.tagName ){
			case 'A':
			case 'IMG':
					s = e.rel;
					e.rel = !e.rel;
				break;
			case 'INPUT':
					s = e.checked;
				break;
		}
		
		var id = e.up( 'table' ).identify();
		if( s ){
			this.actAllRows( id );
		}else{
			this.dactAllRows( id );
		}
	},

	swRow: function( event ){
		e = event.element();

		var s = false;
		switch( e.tagName ){
			case 'A':
					s = e.rel;
				break;
			case 'INPUT':
					s = e.checked;
				break;
		}

		var id = e.identify();
		if( s ){
			this.actRow( id );
		}else{
			this.dactRow( id );
		}

	}


};


Object.extend( Tablik, {

	load: function(){
		$$( '.tablik' ).each( function( t ){
			new Tablik( t );
		});
	}

});


document.observe("dom:loaded", Tablik.load );