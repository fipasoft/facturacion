<?php

class InteractiveBuilder {

    /*
	 * Pregunta si se quiere crear un modelo al usuario
	 *
	 * @param string $model
	 * @param string $controller
	 * @param string $action
	 */
	static function create_model($model, $controller, $action){
		Flash::interactive("Es necesario crear un modelo para que este formulario pueda 'hablar' con la tabla '$model'.<br> ¿Desea que Kumbia lo codifique por usted?
		".button_to_action("Si", "builder/create_model/$model/$controller/$action")."<input type='button' value='No' onclick='$(this.parentNode).hide()'/>");
	}

	/*
	 * Pregunta si se quiere crear un controlador al usuario
	 *
	 * @param string $model
	 * @param string $controller
	 * @param string $action
	 */
	static function create_controller($controller, $action){

		$interactive_message = "Est&aacute; tratando de acceder a un controlador que no existe.
		Kumbia puede codificarlo por usted:
		<form action='".KUMBIA_PATH."builder/create_controller/$controller/$action' method='post'>
		 <table>
		  ";
			$db = db::raw_connect();
			if($db->table_exists($controller)){
				$interactive_message.="
				<tr>
				<td><input type='radio' checked  name='kind' value='standardform'></td>
				<td>Deseo crear un controlador StandardForm de la tabla '$controller'</td>
				</tr>";
				$interactive_message.="
				<tr>
				<td><input type='radio' name='kind' value='applicationcontroller'></td>
				<td>Deseo crear un controlador ApplicationController</td>
				</tr>";
			} else {
				$interactive_message.="
				<tr>
				<td><input type='radio' name='kind' value='applicationcontroller'></td>
				<td>Deseo crear un controlador ApplicationController</td>
				</tr>";
				$interactive_message.="
				<tr>
				<td><input type='radio' name='kind' value='standardform'/></td>
				<td>Deseo crear un controlador StandardForm de la tabla '$controller'</td>
				</tr>";
			}

		$interactive_message.="</table>
		".submit_tag("Aceptar")."
		<a href='#' onclick='this.parentNode.style.display=\"\"; return false'>Cancelar</a>
		</form>";
		Flash::interactive($interactive_message);
	}

}

?>