
/** KumbiaForms - PHP Rapid Development Framework *****************************
*	
* Copyright (C) 2005-2007 Andrés Felipe Gutiérrez (andresfelipe at vagoogle.net)
* 	
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
* 
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
* 
* You should have received a copy of the GNU Lesser General Public
* License along with this framework; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
* 
* Este framework es software libre; puedes redistribuirlo y/o modificarlo
* bajo los terminos de la licencia pública general GNU tal y como fue publicada
* por la Fundación del Software Libre; desde la versión 2.1 o cualquier
* versión superior.
* 
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN 
* TIPO DE GARANTIA; sin dejar atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
* 
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*****************************************************************************/

function loadValues(ini, fin){
	var i, k;		
   	document.body.onkeypress = goLastWithKey 
	if(fin>Values.length) {
		while((Values.length-1)!=parseInt(document.getElementById("scrmax").value)){
			fin = Values.length
			Values[fin] = fEmptyArr()
		}
		fin = Values.length
	}
	if(ini<0||(fin>Values.length&&fin>document.getElementById("scrmax").value)) return;
	if((fin-ini)<=document.getElementById("scrmax").value) num = fin - ini;
	else num = document.getElementById("scrmax").value;
	if(fin==Values.length){
		Values[fin] = fEmptyArr()
	}
	k = ini;
	reg = 0;
	for(j=0;j<=num-1;j++){
		for(i=0;i<=Fields.length-1;i++){
			val = ""
			if (!Values[k][i]){				
		       		if(Values[k][i]!=null){
		       				if(Values[k][i]!="")
		       					if(isNaN(Values[k][i]))
				   					Values[k][i] = ""
					} else val = Values[k][i]
			}  else val = Values[k][i]
		    		  			    
			val = (Values[k][i] != null) ? Values[k][i] : ""			
			if(document.getElementById("flid_"+Fields[i]+j).tagName!="SELECT"){
				if(document.getElementById("flid_"+Fields[i]+j).type=="hidden")
				Values[k][i] = document.getElementById("flid_"+Fields[i]+j).value
				else {
					if(document.getElementById("flid_"+Fields[i]+j).type!="button"){
						if(document.getElementById("flid_"+Fields[i]+j).title=='Color'){
							if(val.length){
								document.getElementById("flid_"+Fields[i]+j).style.color = val
								document.getElementById("flid_"+Fields[i]+j).style.background = val
							} else {
								document.getElementById("flid_"+Fields[i]+j).style.color = '#FFFFFF'
								document.getElementById("flid_"+Fields[i]+j).style.background = '#FFFFFF'
							}
						} else document.getElementById("flid_"+Fields[i]+j).value = val;
					}
				}
			} else document.getElementById("flid_"+Fields[i]+j).selectedIndex = getSelected(document.getElementById("flid_"+Fields[i]+j), val);
			document.getElementById("flid_"+Fields[i]+j).lang = reg;
			Registers[reg++] = new Array(k, i);
		}
		k++;
	}
	document.getElementById("actmin").value = ini;
	document.getElementById("actmax").value = fin;
}

function goNext(){     
	loadValues(parseInt(document.getElementById("actmin").value)+1, parseInt(document.getElementById("actmax").value)+1)  
}

function goPrev(){
	loadValues(parseInt(document.getElementById("actmin").value)-1, parseInt(document.getElementById("actmax").value)-1)    
}

function goLast(){
  /* loadValues(Values.length-parseInt(document.getElementById("scrmax").value), Values.length)*/
	if(Values.length>25)
		loadValues(parseInt(document.getElementById("actmin").value)+5, parseInt(document.getElementById("actmax").value)+5)
}

function goFirst(){
  	if(Values.length>25)
		loadValues(parseInt(document.getElementById("actmin").value)-5, parseInt(document.getElementById("actmax").value)-5)
    /*  loadValues(0, parseInt(document.getElementById("scrmax").value)) */
}

function newRow(){
	loadValues(Values.length-parseInt(document.getElementById("scrmax").value), Values.length)	  	
	goNext()
	document.getElementById('modifica').click()
}

function goLastWithKey(){
    //window.status = event.keyCode
    if(document.getElementById('modifica')){
     if(document.getElementById('modifica').value=='Modificar'){      
  		if(event.keyCode==100){
    		loadValues(Values.length-parseInt(document.getElementById("scrmax").value), Values.length)	
		}
		if(event.keyCode==117){
			loadValues(0, parseInt(document.getElementById("scrmax").value))
		}
		if(event.keyCode==110){
		  	newRow()
		}
	  }
	}
}

function enableForEdit(obj){
	obj.value = "Guardar";
	//alert(obj.value)
	for(j=0;j<=document.getElementById("scrmax").value-1;j++){
		for(i=0;i<=Fields.length-1;i++){
			document.getElementById("flid_"+Fields[i]+j).disabled = false;
		}
	}
}

function enableForReport(){
    window.location = $Kumbia.path+$Kumbia.controller+"/report"
}

function saveValue(obj){
  	var num;
	num = parseInt(obj.lang);
	if(isNaN(num)) return;
	if(obj.tagName=="SELECT")
		Values[Registers[num][0]][Registers[num][1]] = obj.options[obj.selectedIndex].value;
	else Values[Registers[num][0]][Registers[num][1]] = obj.value;  	
}

function saveData(){
	var obj, i, j, t;
	if(document.getElementById("errStatus").value>0){
		if(!confirm("El Formulario tiene errores\nDesea Continuar?")) {
			return;			
		}
	}
	
	if(!document.getElementById("noExtendItems").value){
		for(j=0;j<=Values.length-1;j++){
			for(i=0;i<=Fields.length-1;i++){
				obj = document.createElement("INPUT");
				obj.type = "hidden";
				obj.name = "fl_"+Fields[i]+"__n"+j
				obj.value = Values[j][i]
				document.saveDataForm.appendChild(obj)
			}
		}
	} else {
		for(j=0;j<=Values.length-1;j++){
			for(i=0;i<=Fields.length-1;i++){
				obj = document.createElement("INPUT");
				obj.type = "hidden";
				obj.name = "fl_"+Fields[i]+"__n"+j
				obj.value = Values[j][i]
				document.saveDataForm.appendChild(obj)
			}
		}
	}
	
	document.saveDataForm.action = $Kumbia.path+$Kumbia.controller+"/update"
		
	document.saveDataForm.appendChild(obj)

	document.saveDataForm.submit();

}

function getSelected(obj, Valor){
    var i;
	if(Valor=="") return 0
	for(i=0;i<=obj.options.length-1;i++)
	if(obj.options[i].value==Valor){
		return i
	}
	return 0
}


function deleteRow(num){
    var i;
    if(document.getElementById("modifica").value=="Guardar"){
		Values[num] = null
		for(i=num;i<=Values.length-1;i++){
			Values[i] = Values[i+1]
		}
		Values[i-1] = fEmptyArr()
		loadValues(parseInt(document.getElementById("actmin").value), parseInt(document.getElementById("actmax").value))  	
	}
	
}

function fillLines(obj){
	obj.innerText = "";
	for(i=0;i<=Values.length*3;i++)
	obj.innerText+="_\r\n";
}

function scrollDown(obj){
	por = (window.event.y)/(parseInt(obj.style.height)+50)*100;
	row = 0
	if(por>90){
		loadValues(parseInt(document.getElementById("actmin").value)+1, parseInt(document.getElementById("actmax").value)+1)
		fillLines(obj);
	}
	if(por<10)
	loadValues(parseInt(document.getElementById("actmin").value)-1, parseInt(document.getElementById("actmax").value)-1)
}
function scrollOn(obj){
	por = (window.event.y)/(parseInt(obj.style.height)+50)*100;
	scrmax = parseInt(document.getElementById("scrmax").value);
	if((por>=19)&&por<=101){
		row = parseInt(scrmax*(por/100))
		if(Values.length>scrmax)
		while(parseInt(row/2)>=parseInt(scrmax/2))
		row--;
		while(((row*2)-parseInt(scrmax/2))<1)
		row++;
		if(((row-parseInt(scrmax/2))+scrmax)<=Values.length){
			//loadValues(row-parseInt(scrmax/2),row-parseInt(scrmax/2)+scrmax)
		}

	}
}

function getColor(obj){
    if(document.getElementById("modifica").value=='Modificar'){
	  return;
	}
	col = document.createElement("SPAN");
	col.id = "colorSelect"
	col.innerHTML = "<table>" +
	"<tr><td bgcolor='#FF8000' style='cursor:hand' width=80 onclick=\"changeColor('#FF8000', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#008000' onclick=\"changeColor('#008000', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#0080C0' onclick=\"changeColor('#0080C0', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#800080' onclick=\"changeColor('#800080', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#FEF101' onclick=\"changeColor('#FEF101', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#DD0000' onclick=\"changeColor('#DD0000', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#69C2F8' onclick=\"changeColor('#69C2F8', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"<tr><td style='cursor:hand' bgcolor='#FFCC00' onclick=\"changeColor('#FFCC00', '"+obj.id+"')\">&nbsp;</td></tr>" +
	"</table>\r\n"
	col.style.position = "absolute"
	col.style.top = "200px"
	col.style.left = "450px"
	col.style.border = "1px solid black"
	col.style.background = "#FFFFFF"
	document.getElementById('iBody').appendChild(col)

}

function changeColor(col, obj){
	document.getElementById(obj).style.color = col
	document.getElementById(obj).style.background = col
	document.getElementById(obj).value = col
	document.getElementById('iBody').removeChild(document.getElementById('iBody').lastChild)
	saveValue(document.getElementById(obj))
}

function totalizeColumn(nam, num){
	var i, tot, decimal, tota;
	tot = 0
	decimal = 0
	num = parseInt(num)
	for(i=0;i<=Values.length-1;i++){
		if(Values[i][num])
		tot+=parseFloat(Values[i][num])
	}
	tota = tot.toString()
	if(tota.indexOf(".")!=-1){
		decimal = tota.substring(tota.indexOf(".")+1, 2)
		tota = tota.substring(0, tota.indexOf("."))
	}
	document.getElementById("tot_"+nam).innerHTML=tota+"."+decimal
}
