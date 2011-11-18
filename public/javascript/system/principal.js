function div_sw(id){
if($(id).style.display=='none'){
Effect.BlindDown(id); return false;
}else{
Effect.BlindUp(id); return false;
}
}
function enEspera(){
var arrayPageSize = getPageSize();
var arrayPageScroll = getPageScroll();
var lightboxTop = arrayPageScroll[1] + (arrayPageSize[3] / 10);
var lightboxLeft = arrayPageScroll[0];

Element.setTop('lightbox', lightboxTop);
Element.setLeft('lightbox', lightboxLeft);
$('lightbox').onclick = null;

Element.show('lightbox');
Element.hide('lightboxImage');
Element.hide('hoverNav');
Element.hide('prevLink');
Element.hide('nextLink');
Element.hide('imageDataContainer');
Element.hide('numberDisplay');

var o = $('overlay');
var hOver = $('main').getWidth() + 250;
if(hOver < arrayPageSize[0]){
	hOver = arrayPageSize[0];
}
Element.setWidth(o, hOver);
Element.setHeight(o, arrayPageSize[1]);
new Effect.Appear(o, { duration: 0.2, from: 0.0, to: 0.9 });
o.onclick = null;
o.style.backgroundColor = '#fff';

var i = $('imageContainer');
i.style.backgroundColor = '#fff';
i.style.opacity = 0.9;
i.style.padding = 0;
i.onclick = null;

var c = $('outerImageContainer');
c.style.backgroundColor = '#fff';
c.style.opacity = 0.9;
c.style.padding = 0;
c.onclick = null;

var l = $('loadingLink');
l.href = 'javascript:;';
l.onclick = function(){ alert('Por favor espere...')};
}
function esEntero(s) {
for (var i = 0; i < s.length; i++) {
var c = s.charAt(i);
if (!((c >= "0") && (c <= "9"))) {
return false;
}
}
return true;
}
function frm_reset(forma){
forma = $(forma);
for(var i=0; i<forma.elements.length-2; i++){
forma.elements[i].value='';
forma.elements[i].selectedIndex=0;
}
forma.submit();
}
function frm_validar(forma){
forma = $(forma);
for(var i=0; i<forma.elements.length; i++){
if(forma.elements[i].value=='' && !forma.elements[i].disabled){
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
function frm_validar_campos(lista){
var valido = true;
lista.each(function(campo) {
if($F(campo) == ''){
try{
$(campo).focus();
Effect.Shake(campo);
}catch(e){
alert('Faltan campos por llenar');
}
valido = false;
throw $break;
}
});
return valido;
}
function frm_validar_radiogroup(lista){
var valido = false;
var campo;
lista.each(function(radio) {
campo = radio;
if($(radio).checked){
valido = true;
throw $break;
}
});
if(!valido){
try{
$(campo).focus();
Effect.Shake(campo);
}catch(e){
alert('Faltan campos por llenar');
}
}
return valido;
}
function pss_longitud(a){
a = $(a);
if(a.value.length > 0 && a.value.length < 6){
alert('La longitud minima del password es de 6 caracteres.');
return false;
}
return true;
}
function pss_comparar(a, b){
a = $(a);
b = $(b);
if(a.value != b.value){
alert('No coincide la confirmacion del password.');
return false;
}
return true;
}
//Convertir fecha
function fechaDDMMAAAA(f) {
return f.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/, "$2/$1/$3");
}
function fechaMMDDAAAA(f) {
return f.replace(/^(\d{2})\/(\d{2})\/(\d{4})$/, "$2/$1/$3");
}
// Validar rangos de fechas
function valPeriodo(fecha, fecha2){
f1=new Date( fechaMMDDAAAA(fecha) );
f2=new Date( fechaMMDDAAAA(fecha2) );
if(f1 <= f2){
return true;
}
alert('El rango de fechas no es valido.');
return false;
}
//Validar fechas...
function esDigito(sChr){
var sCod = sChr.charCodeAt(0);
return ((sCod > 47) && (sCod < 58));
}
function valSep(oTxt){
var bOk = false;
bOk = bOk || ((oTxt.value.charAt(2) == "-") && (oTxt.value.charAt(5) == "-"));
bOk = bOk || ((oTxt.value.charAt(2) == "/") && (oTxt.value.charAt(5) == "/"));
return bOk;
}
function finMes(oTxt){
var nMes = parseInt(oTxt.value.substr(3, 2), 10);
var nRes = 0;
switch (nMes){
case 1: nRes = 31; break;
case 2: nRes = 29; break;
case 3: nRes = 31; break;
case 4: nRes = 30; break;
case 5: nRes = 31; break;
case 6: nRes = 30; break;
case 7: nRes = 31; break;
case 8: nRes = 31; break;
case 9: nRes = 30; break;
case 10: nRes = 31; break;
case 11: nRes = 30; break;
case 12: nRes = 31; break;
}
return nRes;
}
function valDia(oTxt){
var bOk = false;
var nDia = parseInt(oTxt.value.substr(0, 2), 10);
bOk = bOk || ((nDia >= 1) && (nDia <= finMes(oTxt)));
return bOk;
}
function valMes(oTxt){
var bOk = false;
var nMes = parseInt(oTxt.value.substr(3, 2), 10);
bOk = bOk || ((nMes >= 1) && (nMes <= 12));
return bOk;
}
function valAno(oTxt){
var bOk = true;
var nAno = oTxt.value.substr(6);
bOk = bOk && ((nAno.length == 2) || (nAno.length == 4));
if (bOk){
for (var i = 0; i < nAno.length; i++){
bOk = bOk && esDigito(nAno.charAt(i));
}
}
return bOk;
}
function valEjercicio(oTxt, year){
if((oTxt.substr(6)!=year)&&(oTxt.substr(6)!=year.substr(2))){
alert("La fecha seleccionada no corresponde al "+year);
return false;
}
return true;
}
function valFecha(oTxt){
var bOk = true;
if (oTxt.value != ""){
bOk = bOk && (valAno(oTxt));
bOk = bOk && (valMes(oTxt));
bOk = bOk && (valDia(oTxt));
bOk = bOk && (valSep(oTxt));
if (!bOk){
oTxt.value = "";
oTxt.select();
oTxt.focus();
alert("Fecha invalida.\nFormato para fechas: 01/01/2007 o 01/01/07...");
}
}
}
// Validar cantidad
function validar_cantidad(campo) {
	decimales = 2;  // cantidad de decimales
	pass = true;
	if(campo.value != ''){
		if (isNaN(campo.value)){
			alert("El formato valido para cantidades solo acepta numeros sin comas y dos decimales delimitados por un punto\n123456.78");
			campo.select();
			campo.focus();
			campo.value='';
			pass = false;
		}else{
			if (campo.value.indexOf('.') == -1) campo.value += ".";
				dectext = campo.value.substring(campo.value.indexOf('.')+1, campo.value.length);
			if (dectext.length > decimales){
				alert ("La cantidad debe tener un maximo de 2 decimales.");
				campo.select();
				campo.value='';
				campo.focus();
				pass = false;
			}else{
				var j = 0;
				for(j=dectext.length; j<2; j++)
					campo.value += "0";
				campo.value = campo.value;
			}
		}
	}
	return pass;
}

function validar_entero(campo){
	if( !esEntero(campo.value) ){
		alert('Este campo solo acepta valores enteros.');
		Effect.Shake(campo);
		campo.value = '';
		campo.focus();
	}
}

function validar_entero2(campo){
	if( !esEntero(campo.value) ){
		campo.focus();
		return false;
	}
	return true;
}