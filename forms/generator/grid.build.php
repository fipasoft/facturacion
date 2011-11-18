<?php

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

class GridForm_Generator {

	static function build_form_grid($form){

		/* if(!kumbia::is_model($this->source)){
		Flash::error('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de inserci&oacute;n');
		$this->_create_model();
		return $this->route_to('action: index');
		} */

		//print_r($form);
		Generator::forms_print("<div id='{$form["source"]}'>\n");
		Generator::forms_print("<table cellspacing='0' align='center'>\n");
		Generator::forms_print("<tr>\n");
		foreach($form["components"] as $name => $component){
			Generator::forms_print("\t<th>&nbsp;{$component["caption"]}&nbsp;</th>\n");
		}
		Generator::forms_print("</tr>\n");

		$modelName = kumbia::get_model_name($form['source']);

		$modelObj = kumbia::$models[$modelName];

		if(!$modelObj->is_dumped()){
			$modelObj->dump();
		}
		$i = 1;
		foreach($modelObj->find() as $row){
			if(($i%2)==0){
				Generator::forms_print("<tr class='tr_color_1'>\n");
			} else {
				Generator::forms_print("<tr class='tr_color_2'>\n");
			}
			foreach($modelObj->fields as $field){
				Generator::forms_print("\t<td>&nbsp;{$row->$field}&nbsp;</td>\n");
			}
			Generator::forms_print("</tr>\n");
			$i++;
		}
		Generator::forms_print("<tr>\n");
		foreach($form["components"] as $name => $com){
			$com['not_label'] = true;
			Generator::forms_print("<td>");
			switch($com['type']){
				case 'text':
					Component::build_text_component($com, $name, $form);
					break;

				case 'combo':
					Component::build_standard_combo($com, $name);
					break;

				case 'helpText':
					Component::build_help_context($com, $name, $form);
					break;

				case 'userDefined':
					Component::build_userdefined_component($com, $name, $form);
					break;

				case 'time':
					Component::build_time_component($com, $name, $form);
					break;

				case 'password':
					Component::build_standard_password($com, $name);
					break;

				case 'textarea':
					Component::build_text_area($com, $name);
					break;

				case 'image':
					Component::build_standard_image($com, $name);
					break;

					//Este es el Check Chulito
				case 'check':
					if($com['first']) Generator::forms_print("<b>".$com['groupcaption']."</b></td><td><table cellpadding=0>");
					Generator::forms_print("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
					if($_REQUEST['fl_'.$name]==$com['checkedValue'])
					Generator::forms_print(" checked ");
					if($com["attributes"])
					foreach($com["attributes"] as $nitem => $item) Generator::forms_print(" $nitem='$item' ");
					Generator::forms_print(">\r\n</td><td>".$com['caption']."</td></tr>");
					if($com["last"]) Generator::forms_print("</table>");
					break;

					//Cajita Grande
				case 'textarea':
					Generator::forms_print("<b>".$com['caption']." :</br></td><td><textarea disabled name='fl_$name' id='flid_$name' ");
					foreach($com['attributes'] as $natt => $vatt)
					Generator::forms_print("$natt='$vatt' ");
					Generator::forms_print(">".$_REQUEST['fl_'.$name]."</textarea>");
					break;

					//Oculto
				case 'hidden':
					Generator::forms_print("<input type='hidden' name='fl_$name' id='flid_$name' value='".($com['value'] ? $com['value'] : $_REQUEST['fl_'.$name])."'/>\r\n");
					break;

			}
			Generator::forms_print("</td>");
		}
		Generator::forms_print("</div>");

	}
}
?>