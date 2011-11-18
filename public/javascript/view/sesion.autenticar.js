function frm_validar(forma){
forma = $(forma);
for(var i=0; i<forma.elements.length; i++){
if(forma.elements[i].value==''){
try{
forma.elements[i].focus();
Effect.Shake(forma.elements[i]);
}catch(e){
alert('Faltan campos por llenar');
}
return false;
}
}
return true;
}
function init(){
	$('frm_auth').onsubmit = function(){
		if(frm_validar('frm_auth')){
			$('frm_auth').submit();
		}else{
			return false;
		}
	}
}
addDOMLoadEvent(init);