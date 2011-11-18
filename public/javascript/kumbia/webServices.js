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
* TIPO DE GARANTIA; dejando atrás su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para más detalles.
* 
* Debes recibir una copia de la Licencia Pública General GNU junto con este
* framework, si no es asi, escribe a Fundación del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*
*****************************************************************************/

var req;
var globalObj;
var errMessage;
var useHelper;

function loadXMLDoc(url, code) {
        // branch for native XMLHttpRequest object
        //	alert(url)
        if (window.XMLHttpRequest) {
                req = new XMLHttpRequest();
                switch(code){
                        case 1:
                        req.onreadystatechange = processReqChangeDetailValues;
                        break;
                        case 2:
                        req.onreadystatechange = processReqCheckValueIn;
                        break;
                }
                req.open("GET", url, true);
                req.send(null);
                // branch for IE/Windows ActiveX version
        } else if (window.ActiveXObject) {
                isIE = true;
                req = new ActiveXObject("Microsoft.XMLHTTP");
                if (req) {
                        switch(code){
                                case 1:
                                req.onreadystatechange = processReqChangeDetailValues;
                                break;
                                case 2:
                                req.onreadystatechange = processReqCheckValueIn;
                                break;
                        }
                        req.open("GET", url, true);
                        req.send();
                }
        }
}

function processReqChangeDetailValues() {
        // only if req shows "loaded"
        if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {
                        clearSelectList();
                        buildSelectList();
                } else {
                        alert("There was a problem retrieving the XML data:\n" + req.statusText);
                }
        }
}

function processReqCheckValueIn() {
        // only if req shows "loaded"
        if (req.readyState == 4) {
                // only if "OK"
                if (req.status == 200) {
                        checkNumberOfInstances();
                } else {
                        alert("There was a problem retrieving the XML data:\n" + req.statusText);
                }
        }
}

function checkNumberOfInstances(){
        var items = req.responseXML.getElementsByTagName("row");
        if(items.length>=1){
                if(parseInt(items[0].getAttribute("num"))==0){
                        if(useHelper){
	                        if(confirm(errMessage+"\nDesea crearlo?")){
							  openHelper(useHelper, globalObj)
							}
						} else {
						  	document.getElementById(globalObj+"_det").value  = errMessage
						  	window.status = errMessage
						}                        
                } else {
				 	document.getElementById(globalObj+"_det").value = items[0].getAttribute("detail")
					window.status = ""  
				}
        } else {          		
                document.getElementById(globalObj+"_det").value = "ERROR"                                
        }
}

function getDetailValues(name, foreignTable, detailField, condition, op){
        globalObj = "flid_" + name
        try {
          	if(document.getElementById("rsa32_key")){
                loadXMLDoc("webServices/getDetailValues.php?name="+name+"&ftable="+foreignTable+"&dfield="+detailField+"&condition="+condition+"&op="+op+"&k="+document.getElementById("rsa32_key").value, 1);
            } else {
			  	    loadXMLDoc("webServices/getDetailValues.php?name="+name+"&ftable="+foreignTable+"&dfield="+detailField+"&condition="+condition+"&op="+op, 1);
			}
            
        }
        catch(e){
                alert('There was a problem retrieving the WebSevice Request: '+e.message)
        }
}

function clearSelectList(){
        while(document.getElementById(globalObj).options.length){
                document.getElementById(globalObj).remove(0);
        }
}

function buildSelectList(){
        var elemObj;
        var items = req.responseXML.getElementsByTagName("row");
        elemObj = document.createElement("OPTION");
        elemObj.value = "@"
        if(document.all) elemObj.innerText = "Seleccione ..."
        else elemObj.text = "Seleccione ..."
        document.getElementById(globalObj).appendChild(elemObj)
        for (var i = 0; i < items.length; i++) {
                elemObj = document.createElement("OPTION");
                elemObj.value = items[i].getAttribute("value")
                if(document.all) elemObj.innerText = items[i].getAttribute("text")
                else elemObj.text = items[i].getAttribute("text")
                document.getElementById(globalObj).appendChild(elemObj)
        }
}


function checkValueIn(name, foreignTable, err, dfield, helper, crelation){
	globalObj = "flid_" + name
	errMessage = err
	useHelper = helper
	if(document.getElementById(globalObj).value!=""){
		try {
			loadXMLDoc("webServices/checkValueIn.php?name="+name+"&ftable="+foreignTable+"&value="+document.getElementById("flid_"+name).value+"&dfield="+dfield+"&crelation="+crelation+"&rand="+parseInt(Math.random()*10000), 2);
		}
		catch(e){
			alert('There was a problem retrieving the WebSevice Request: '+e.message)
		}
	}
}

function clearSelectList(){
        while(document.getElementById(globalObj).options.length){
                document.getElementById(globalObj).remove(0);
        }
}

/***************************************************************************
Ajax Functions
****************************************************************************/

var response = null;
var ajaxAction
var responseQuenue = 0
var ajaxRequestQuenue = new Array()
var xmlRequestNumber = 0

function ajaxXMLRequest(url, parameters, action, secure) {        		
	url = "webServices/"+url+".php?" + parameters
	if(getObj("rsa32_key")){
		url = url + "&k=" + getObj("rsa32_key").value
	}	 
	//	window.status = url
        ajaxAction = action                
        // branch for native XMLHttpRequest object                
        if (window.XMLHttpRequest) {
                response = new XMLHttpRequest();
                response.onreadystatechange = processRequest;
                response.open("GET", url, true);
                response.send(null);
                // branch for IE/Windows ActiveX version
        } else if (window.ActiveXObject) {
                isIE = true;
                response = new ActiveXObject("Microsoft.XMLHTTP");
                if (response) {                        
                        response.onreadystatechange = processRequest;
                        response.open("GET", url, true);
                        response.send();                        
                }                
        }
}

function ajaxViewRequest(url, parameters, action) {        		
	  	url = "webServices/"+url+".php?" + parameters
	  	if(getObj("rsa32_key")){
	    	url = url + "&k=" + getObj("rsa32_key").value
	 	}
        ajaxAction = action                
        // branch for native XMLHttpRequest object                
        if (window.XMLHttpRequest) {
                response = new XMLHttpRequest();
                response.onreadystatechange = processView;
                response.open("GET", url, true);
                response.send(null);
                // branch for IE/Windows ActiveX version
        } else if (window.ActiveXObject) {
                isIE = true;
                response = new ActiveXObject("Microsoft.XMLHTTP");
                if (response) {                        
                        response.onreadystatechange = processView;
                        response.open("GET", url, true);
                        response.send();                        
                }                
        }
}

function processView(){
	// only if req shows "loaded"
        showObj('spinner')
        if (response.readyState == 4) {
                if (response.status == 200) {
				  	if(getObj(ajaxAction))
                        getObj(ajaxAction).innerHTML = response.responseText
                    else
                    	alert("KumbiaError: Container Ajax Object Not Found")
                } else {
                        alert("AuroraError: There was a problem retrieving the XML data: " + response.statusText);                                               
                }        
                //response = null
                hideObj('spinner')
        }
}


function processRequest() {
        // only if req shows "loaded"
        showObj('spinner')
        if (response.readyState == 4) {
                if (response.status == 200) {                  		
                        if(window.execScript){
                          execScript(ajaxAction);
                        } else {
	                      eval(ajaxAction)  
	                    }                        
                } else {
                        alert("AuroraError: There was a problem retrieving the XML data: " + response.statusText);                                               
                }        
                //response = null
                hideObj('spinner')
        }
}

function dummy(){
  
}

function getComboValuesQuery(){
	var i = 0; 
	for(i=0;i<=comboQueryFields.length-1;i++){
		ajaxXMLRequest("getComboValues", "field="+comboQueryFields[i]+"&file="+getObj("aaction").value+"&xaction=query", "showValues('"+comboQueryFields[i]+"')")
	}
}

function getComboValuesAdd(){
	var i = 0; 
	for(i=0;i<=comboAddFields.length-1;i++){
		ajaxXMLRequest("getComboValues", "field="+comboAddFields[i]+"&file="+getObj("aaction").value+"&xaction=add", "showValues('"+comboAddFields[i]+"')")
	}
}

function showValues(x){
	var i = 0;
	//alert("x")
	var items = response.responseXML.getElementsByTagName("row");
	while(getObj("flid_"+x).lastChild){
		getObj("flid_"+x).removeChild(getObj("flid_"+x).lastChild)
	}
	a = document.createElement("option")
	a.value = "@"
	a.innerText = "Seleccione ..."
	getObj("flid_"+x).appendChild(a)
	for(i=0;i<=items.length-1;i++){
		a = document.createElement("option")
		a.value = items[i].getAttribute('value')
		a.innerText = items[i].getAttribute('text') 
		getObj("flid_"+x).appendChild(a)
	}
}

function executeAjaxCallback(oname){
  	var i
  	if(!response.responseXML){
	    alert("KumbiaError: Application Callback don't return a valid XML Response")
	    return
	}  		
	var items = response.responseXML.getElementsByTagName("row");
	for(i=0;i<=items.length-1;i++){	  
	  	if(items[i].getAttribute("type")=="data"){	  	  	
		 	if(document.getElementById("flid_"+items[i].getAttribute("name"))){
		 	  	obj = document.getElementById("flid_"+items[i].getAttribute("name"))
		 	  	objName = items[i].getAttribute("name")
				if(obj.tagName=='INPUT'&&obj.type=='text'){
					if(obj.style.visibility=='hidden'){
						if(document.getElementById("x"+obj.name+'_Month_ID')){
						    obj.value = items[i].getAttribute("value") 						    						    
						    setDateValue(objName, obj.value)
						} else obj.value = items[i].getAttribute("value")    
					} else {
						obj.value = items[i].getAttribute("value")
					}
				}
				if(obj.tagName=='SELECT'){
				  	for(j=0;j<=obj.options.length-1;j++){
					    if(obj.options[j].value==items[i].getAttribute("value")){
						  obj.selectedIndex = j
						}
					}
				}
				if(obj.tagName=='INPUT'&&obj.type=='hidden'){
				  	obj.value = items[i].getAttribute("value")				 						 						
				  	if(obj.value.indexOf('@')!=-1){
				  	  	document.getElementById(objName+'_email1').value = obj.value.substr(0, obj.value.indexOf('@'))
				  	  	document.getElementById(objName+'_email2').value = obj.value.substr(obj.value.indexOf('@')+1)
					}				 	
				}
			}
		} 
		if(items[i].getAttribute("type")=="error"){		  	
			if(document.getElementById('det_'+oname)){
				document.getElementById('det_'+oname).innerHTML = items[i].getAttribute("value")
				if(Effect){
				  	new Effect.Highlight('det_'+oname)
				}
				window.setTimeout('cleanDetail("'+oname+'")', 7000)
			} else alert(items[i].getAttribute("value"))
		}
	}
}

function cleanDetail(oname){
 	document.getElementById('det_'+oname).innerHTML = "" 	
}
