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
*
*************************************************************************/

/**
 * MasterDetailForm: es la clase principal para la generación de formularios
 * maestro-detalle en Kumbia, esta clase hereda de StandarForm asi que sus
 * metodos tambien estan disponibles para esta
 *
 */
abstract class MasterDetailForm extends StandardForm {

	/**
	 * Es el metodo principal que es llamado implicitamente siempre
	 * para mostrar el formulario
	 */
	function index(){

		// Cuando tiene Scaffold intenta asumir la mayor parte de los parametros
		if($this->scaffold){
			if(isset($this->source)) $this->form["source"] = $this->source;
			if(isset($this->force)) $this->form['force'] = $this->force;
			if($this->detail){
				$this->form['type'] = "master-detail";
				$this->form['detail']['source'] = $this->detail;
				if(!$this->form['detail']['scrmax'])
				$this->form['detail']['scrmax'] = 10;
			}
			Generator::build_form($this->form, true);
		} else {
			if(count($this->form)){
				if($this->source){
					$this->form["source"] = $this->source;
				}
				Generator::build_form($this->form);
			} else {
				throw new kumbiaException("No se pudo generar el formulario", 
					"Debe especificar las propiedades del formulario 
					  a crear en \$this->form o coloque var \$scaffold = true 
					  para generar dinámicamente el formulario");				
			}
		}
	}

	/**
	 * Especifica cuantas filas tendrá la grilla en la parte inferior del
	 * formulario Maestro detalle
	 *
	 * @param integer $n
	 */
	function set_grid_size($n){
		$this->form['detail']['scrmax'] = $n;
	}

}
?>