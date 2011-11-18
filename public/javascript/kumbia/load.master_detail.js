
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

function saveMasterDataMD(action){
	var obj;
	if(document.getElementById("errStatus").value>0){
		if(!confirm("El Formulario tiene errores\nDesea Continuar?")) return;
	}
	for(i=0;i<=FieldsMaster.length-1;i++){
		obj = document.createElement("INPUT");
		obj.type = "hidden";
		obj.name = "fl_"+FieldsMaster[i]
		if(document.getElementById("flid_"+FieldsMaster[i]).type=='checkbox')
			sobj.value = document.getElementById("flid_"+FieldsMaster[i]).checked
		else obj.value = document.getElementById("flid_"+FieldsMaster[i]).value
		document.saveDataForm.appendChild(obj)
	}
	if(!document.getElementById("noExtendItems").value){
		for(j=0;j<=Values.length-1;j++){
			for(i=0;i<=Fields.length-1;i++){
				if(j<=document.getElementById("scrmax").value){
					obj = document.createElement("INPUT");
					obj.type = "hidden";
					obj.name = "fl_"+Fields[i]+"__n"+j
					obj.value = Values[j][i]
					document.saveDataForm.appendChild(obj)
				}
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
	//Action
	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "action"
	obj.value = document.fl.aaction.value;
	document.saveDataForm.appendChild(obj)
	//Value
	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "value"
	if(document.fl.vvalue) obj.value = document.fl.vvalue.value;
	document.saveDataForm.appendChild(obj)
	//Parameter
	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "param"
	if(document.fl.vvalue) obj.value = document.getElementById("param").value;
	document.saveDataForm.appendChild(obj)
	//Old Action
	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "oldsubaction"
	if(document.fl.vvalue) obj.value = document.getElementById("oldAction").value;
	document.saveDataForm.appendChild(obj)
	obj = document.createElement("INPUT");
	obj.type = "hidden";
	obj.name = "subaction"
	obj.value = action;
	document.saveDataForm.appendChild(obj)
	document.saveDataForm.submit();
}

function enableFormMD(){
	var i;
	for(i=0;i<=FieldsMaster.length-1;i++){
		document.getElementById("flid_"+FieldsMaster[i]).disabled = false
	}
	for(i=0;i<=DateFields.length-1;i++){
		document.getElementById("xfl_"+DateFields[i]+'_Month_ID').disabled = false
		document.getElementById("xfl_"+DateFields[i]+'_Day_ID').disabled = false
		document.getElementById("xfl_"+DateFields[i]+'_Year_ID').disabled = false
	}
	for(j=0;j<=document.getElementById("scrmax").value-1;j++)
	for(i=0;i<=Fields.length-1;i++)
	document.getElementById("flid_"+Fields[i]+j).disabled = false;
	document.getElementById('aceptar').disabled = false
	document.getElementById('cancelar').disabled = false
}

function disableFormMD(){
	var i;
	for(i=0;i<=FieldsMaster.length-1;i++){
		document.getElementById("flid_"+FieldsMaster[i]).disabled = true
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT"){
			document.getElementById("flid_"+FieldsMaster[i]).selectedIndex = 0
		}
	}
	for(j=0;j<=document.getElementById("scrmax").value-1;j++){
		for(i=0;i<=Fields.length-1;i++){
			document.getElementById("flid_"+Fields[i]+j).disabled = true;
		}
	}
	if(document.getElementById("aceptar")) document.getElementById("aceptar").disabled = true
	if(document.getElementById("cancelar")) document.getElementById("cancelar").disabled = true
	if(document.getElementById("adiciona")) document.getElementById("adiciona").disabled = false
	if(document.getElementById("consulta")) document.getElementById("consulta").disabled = false
	if(document.getElementById("visualiza")) document.getElementById("visualiza").disabled = false
	if(document.getElementById("modifica")) document.getElementById("modifica").disabled = true
	if(document.getElementById("borra")) document.getElementById("borra").disabled = true
	if(document.getElementById("reporte")) document.getElementById("reporte").disabled = false
	if(document.getElementById("anterior")) document.getElementById("anterior").disabled = false;
	if(document.getElementById("primero")) document.getElementById("primero").disabled = false;
	if(document.getElementById("siguiente")) document.getElementById("siguiente").disabled = false;
	if(document.getElementById("ultimo")) document.getElementById("ultimo").disabled = false;
	if(document.getElementById("actAction").value=='Modificar'||document.getElementById("actAction").value=='Borrar') {
		if(document.getElementById("modifica")) document.getElementById("modifica").disabled = false
		if(document.getElementById("borra")) document.getElementById("borra").disabled = false
	}
}

function enableInsertMD(obj, x){
	enableFormMD();
	obj.disabled = true;
	document.getElementById("actAction").value = obj.value
	if(x!=1){
		for(i=0;i<=AddFields.length-1;i++){
			if(document.getElementById("flid_"+AddFields[i]).tagName=="SELECT")
			document.getElementById("flid_"+AddFields[i]).selectedIndex = 0
			if(document.getElementById("flid_"+AddFields[i]).tagName=="TEXTAREA")
			document.getElementById("flid_"+AddFields[i]).innerText = ""
			if(document.getElementById("flid_"+AddFields[i]).tagName=="INPUT")
			if(document.getElementById("flid_"+AddFields[i]).type!="hidden")
			if(document.getElementById("flid_"+AddFields[i]).type=="checkbox")
			document.getElementById("flid_"+AddFields[i]).checked = false
			else
			document.getElementById("flid_"+AddFields[i]).value = ""
		}
	}

	for(i=0;i<=AutoValuesFields.length-1;i++)
	document.getElementById("flid_"+AutoValuesFields[i]).value = AutoValuesFFields[i];

	for(i=0;i<=AutoFields.length-1;i++)
	document.getElementById("flid_"+AutoFields[i]).readOnly = true

	for(i=0;i<=queryOnlyFields.length-1;i++){
		if(document.getElementById("flid_"+queryOnlyFields[i]).tagName!="SELECT")
		document.getElementById("flid_"+queryOnlyFields[i]).readOnly = true
		else
		document.getElementById("flid_"+queryOnlyFields[i]).disabled = true
	}

	getComboValuesAdd()

	for(i=0;i<=queryOnlyDateFields.length-1;i++){
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Month_ID').disabled = true
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Day_ID').disabled = true
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = true
	}

	if(document.getElementById("consulta")) document.getElementById("consulta").disabled = true
	if(document.getElementById("reporte")) document.getElementById("reporte").disabled = true
	if(document.getElementById("visualiza")) document.getElementById("visualiza").disabled = true
}

function enableUpdateMD(obj){

	if(document.getElementById("anterior")) document.getElementById("anterior").disabled = true;
	if(document.getElementById("primero")) document.getElementById("primero").disabled = true;
	if(document.getElementById("siguiente")) document.getElementById("siguiente").disabled = true;
	if(document.getElementById("ultimo")) document.getElementById("ultimo").disabled = true;

	obj.disabled = true;
	document.getElementById("actAction").value = obj.value

	var i;
	for(i=0;i<=UFields.length-1;i++){
		document.getElementById("flid_"+UFields[i]).disabled = false
	}

	for(i=0;i<=queryOnlyFields.length-1;i++){
		if(document.getElementById("flid_"+queryOnlyFields[i]).tagName!="SELECT")
		document.getElementById("flid_"+queryOnlyFields[i]).readOnly = true
		else
		document.getElementById("flid_"+queryOnlyFields[i]).disabled = true
	}
	
	for(i=0;i<=emailFields.length-1;i++){
		document.getElementById(emailFields[i]+'_email1').disabled = false
		document.getElementById(emailFields[i]+'_email2').disabled = false
	}
	
	for(i=0;i<=DateFields.length-1;i++){
		document.getElementById("xfl_"+DateFields[i]+'_Month_ID').disabled = false
		document.getElementById("xfl_"+DateFields[i]+'_Day_ID').disabled = false
		document.getElementById("xfl_"+DateFields[i]+'_Year_ID').disabled = false
	}

	for(i=0;i<=queryOnlyDateFields.length-1;i++){
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Month_ID').disabled = true
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Day_ID').disabled = true
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = true
	}

	if(document.getElementById("borra")) document.getElementById("borra").disabled = true


	for(j=0;j<=document.getElementById("scrmax").value-1;j++)
	for(i=0;i<=Fields.length-1;i++)
	document.getElementById("flid_"+Fields[i]+j).disabled = false;

	document.getElementById("aceptar").disabled = false
	document.getElementById("cancelar").disabled = false
}



function enableQueryMD(obj){
  	var i
	enableFormMD();
	obj.disabled = true;

	for(j=0;j<=document.getElementById("scrmax").value-1;j++)
	for(i=0;i<=Fields.length-1;i++)
	document.getElementById("flid_"+Fields[i]+j).disabled = true;

	for(i=0;i<=AutoFields.length-1;i++)
	document.getElementById("flid_"+AutoFields[i]).readOnly = false

	for(i=0;i<=queryOnlyFields.length-1;i++){
		document.getElementById("flid_"+queryOnlyFields[i]).readOnly = false
	}

	for(i=0;i<=queryOnlyDateFields.length-1;i++){
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Month_ID').disabled = false
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Day_ID').disabled = false
		document.getElementById("xfl_"+queryOnlyDateFields[i]+'_Year_ID').readOnly = false
	}

	getComboValuesQuery()

	document.getElementById("actAction").value = obj.value
	for(i=0;i<=FieldsMaster.length-1;i++){
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT")
		document.getElementById("flid_"+FieldsMaster[i]).selectedIndex = 0
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="TEXTAREA")
		document.getElementById("flid_"+FieldsMaster[i]).innerText = ""
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="INPUT")
		if(document.getElementById("flid_"+FieldsMaster[i]).type!="hidden")
		if(document.getElementById("flid_"+FieldsMaster[i]).type=="checkbox")
		document.getElementById("flid_"+FieldsMaster[i]).checked = false
		else
		document.getElementById("flid_"+FieldsMaster[i]).value = ""
	}
	if(document.getElementById("adiciona")) document.getElementById("adiciona").disabled = true
	if(document.getElementById("reporte")) document.getElementById("reporte").disabled = true
	if(document.getElementById("visualiza")) document.getElementById("visualiza").disabled = true
}

//MasterDetail
function FormAcceptMD(){
	var iErr = 0
	var i
	if(document.getElementById("actAction").value=='Adicionar'){
		for(i=0;i<=NotNullFields.length-1;i++){
			if(document.getElementById("flid_"+NotNullFields[i]).tagName=="INPUT")
			if(document.getElementById("flid_"+NotNullFields[i]).type!="hidden")
			if(!document.getElementById("flid_"+NotNullFields[i]).value) {
				alert('Este campo es Obligatorio');
				document.getElementById("flid_"+NotNullFields[i]).select()
				document.getElementById("flid_"+NotNullFields[i]).focus()
				return;
			} else {
				if(document.getElementById("flid_"+NotNullFields[i]).value=='00/00/0000'){
					alert('Este campo es Obligatorio');
					document.getElementById("flid_"+NotNullFields[i]).select()
					document.getElementById("flid_"+NotNullFields[i]).focus()
					return;
				}
			}
		}
		for(i=0;i<=FieldsMaster.length-1;i++){
			if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT"){
				if(document.getElementById("flid_"+FieldsMaster[i]).selectedIndex==0){
					if(!document.getElementById("flid_"+FieldsMaster[i]).disabled){
						document.getElementById("flid_"+FieldsMaster[i]).className = "iError"
						iErr = 1
					}
				} else document.getElementById("flid_"+FieldsMaster[i]).className = "iNormal"
			}
		}
		document.getElementById("errStatus").value = iErr
		if(iErr) return;
		for(i=0;i<=FieldsMaster.length-1;i++)
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT")
		document.getElementById("flid_"+FieldsMaster[i]).disabled = true
		saveMasterDataMD('insert')
	}
	if(document.getElementById("actAction").value=='Consultar'){
		saveMasterDataMD('query')
	}

	if(document.getElementById("actAction").value=='Reporte'){
		saveMasterDataMD('report')
	}

	if(document.getElementById("actAction").value=='Modificar'){
		for(i=0;i<=NotNullFields.length-1;i++){
			if(document.getElementById("flid_"+NotNullFields[i]).tagName=="INPUT")
			if(document.getElementById("flid_"+NotNullFields[i]).type!="hidden")
			if(!document.getElementById("flid_"+NotNullFields[i]).value) {
				alert('Este campo es Obligatorio');
				document.getElementById("flid_"+NotNullFields[i]).select()
				document.getElementById("flid_"+NotNullFields[i]).focus()
				return;
			} else {
				if(document.getElementById("flid_"+NotNullFields[i]).value=='00/00/0000'){
					alert('Este campo es Obligatorio');
					document.getElementById("flid_"+NotNullFields[i]).select()
					document.getElementById("flid_"+NotNullFields[i]).focus()
					return;
				}
			}
		}
		for(i=0;i<=FieldsMaster.length-1;i++){
			if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT"){
				if(document.getElementById("flid_"+FieldsMaster[i]).selectedIndex==0){
					if(!document.getElementById("flid_"+FieldsMaster[i]).disabled){
						document.getElementById("flid_"+FieldsMaster[i]).className = "iError"
						iErr = 1
					}
				} else document.getElementById("flid_"+FieldsMaster[i]).className = "iNormal"
			}
		}
		document.getElementById("errStatus").value = iErr
		if(iErr) return;
		saveMasterDataMD('update')
	}
	disableFormMD();
}

function cancelFormMD(){
    var i;
	if(document.getElementById('actAction').value!='Modificar'&&document.getElementById('actAction').value!='Borrar') {
		for(i=0;i<=FieldsMaster.length-1;i++){
			if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT")
			document.getElementById("flid_"+FieldsMaster[i]).selectedIndex = 0
			if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="INPUT"){
				if(document.getElementById("flid_"+FieldsMaster[i]).type!="hidden")
				document.getElementById("flid_"+FieldsMaster[i]).value = document.getElementById("flid_"+FieldsMaster[i]).defaultValue
			}
			document.getElementById("flid_"+FieldsMaster[i]).className = "iNormal";

		}
	}
	disableFormMD();
}

function enableDeleteMD(){
	if(confirm("Esta seguro que desea borrar el registro?")) saveMasterDataMD('delete')
}

function enableReportMD(obj){
	enableFormMD();
	obj.disabled = true;

	for(j=0;j<=document.getElementById("scrmax").value-1;j++)
	for(i=0;i<=Fields.length-1;i++)
	document.getElementById("flid_"+Fields[i]+j).disabled = true;

	for(i=0;i<=AutoFields.length-1;i++)
	document.getElementById("flid_"+AutoFields[i]).readOnly = false

	document.getElementById("actAction").value = obj.value
	for(i=0;i<=FieldsMaster.length-1;i++){
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="SELECT")
		document.getElementById("flid_"+FieldsMaster[i]).selectedIndex = 0
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="TEXTAREA")
		document.getElementById("flid_"+FieldsMaster[i]).innerText = ""
		if(document.getElementById("flid_"+FieldsMaster[i]).tagName=="INPUT")
		if(document.getElementById("flid_"+FieldsMaster[i]).type!="hidden")
		if(document.getElementById("flid_"+FieldsMaster[i]).type=="checkbox")
		document.getElementById("flid_"+FieldsMaster[i]).checked = false
		else
		document.getElementById("flid_"+FieldsMaster[i]).value = ""
	}
	if(document.getElementById("adiciona")) document.getElementById("adiciona").disabled = true
	if(document.getElementById("consulta")) document.getElementById("consulta").disabled = true
	if(document.getElementById("consulta")) document.getElementById("visualiza").disabled = true
}


