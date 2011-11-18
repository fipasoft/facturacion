function init(){
	a = $('aSearch');
	a.href = 'javascript:;';
	a.onclick = function(){
				div_sw('search');
				}
	reset = $('reset');
	reset.onclick = function(){
		Effect.DropOut($('search'));
		frm_reset('frm_search');
	}
}
addDOMLoadEvent(init);