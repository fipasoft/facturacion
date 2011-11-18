function init(){
	a = $('aSearch');
	if(a){
	a.href = 'javascript:;';
	a.onclick = function(){
				div_sw('search');
				}
		}
	reset = $('reset');
	if(reset){
		reset.onclick = function(){
		Effect.DropOut($('search'));
		frm_reset('frm_search');
	}
	}

	c = $('cicloBtn');
	if(c){
		c.onclick = function(){
			if($('cicloActual').style.display == 'none'){
				$('cicloSel').style.display = 'none';
				Effect.Appear('cicloActual');
			}else{
				$('cicloActual').style.display = 'none';
				Effect.Appear('cicloSel');
			}
		}
	}

	cS = $('cicloSelect');
	if(cS){
		cS.onchange = function (){$('frm_ciclo').submit();}
	}

	if($('saved_at')){
	Calendar.setup({
				button: 'saved_at',
				electric : false,
				inputField : 'saved_at',
				ifFormat : '%d/%m/%Y'
			});
			}
}
addDOMLoadEvent(init);