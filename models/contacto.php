<?php
// publicidad, Creado el 30/06/2009
/** 
 * Contacto
 * 
 * @package    Modelos    
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2008 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
 class Contacto extends ActiveRecord{
 	private   $_institucion;
 	
 	protected $institucion_id;
 	protected $institucion_nombre;
 	protected $institucion_tipo;
 	protected $ejercicio_id;
 	protected $tipo; 	
 	
 	public function institucion(){
 		$c = new Dependencia();
 		
 		$c = (object) $c->find_by_sql(
 			"SELECT " .
	 			"* " .
 			"FROM " .
 			"( " .
	 			"( " .
		 			"SELECT " .
			 			"dependencia.id, " .
		 				"'dependencia' AS tipo, " .
		 				"dependencia.ejercicio_id, " .
			 			"dependencia.nombre " .
		 			"FROM " .
			 			"contacto " .
			 			"Inner Join depcontacto ON depcontacto.contacto_id = contacto.id " .
			 			"Inner Join dependencia ON dependencia.id = depcontacto.dependencia_id " .
			 		"WHERE " .
			 			"contacto.id = " . $this->id . " " .
	 			") " .
	 			"UNION ALL " .
	 			"( " .
		 			"SELECT " .
			 			"proveedor.id, " .
			 			"'proveedor' AS tipo, " .
			 			"proveedor.ejercicio_id, " .
			 			"proveedor.nombre " .
		 			"FROM " .
			 			"contacto " .
			 			"Inner Join provcontacto ON provcontacto.contacto_id = contacto.id " .
			 			"Inner Join proveedor ON proveedor.id = provcontacto.proveedor_id " .
			 		"WHERE " .
			 			"contacto.id = " . $this->id . " " .
	 			") " .
 			") AS instituciones " .
 			"LIMIT 1 " 
 		);
 		
 		return $c;
 	}
 	
 	public function institucionNombre(){
 		if( !isset( $this->_institucion ) ){
	 		$this->_institucion = $this->institucion();
 		}
 		
 		return $this->_institucion->nombre;
 	}
 	 	
 	public function eliminarLigas(){
 		return 
 			$this->eliminarLigasConDependencias() &&
	 		$this->eliminarLigasConProveedores();
 		
 	}
 	
 	public function eliminarLigasConDependencias(){
 		$dependencias = new Depcontacto();
 		$dependencias->delete(
 			"contacto_id = '" . $this->id . "' "
 		);
 		
 		return true;
 	}
 	
 	public function eliminarLigasConProveedores(){
 		$proveedores = new Provcontacto();
 		$proveedores->delete(
 			"contacto_id = '" . $this->id . "' "
 		);
 		
 		return true;
 	}
 	
 	public function entidad(){
 		$depcontacto = new Depcontacto();
 		$depcontacto = $depcontacto->find_first("contacto_id = '".$this->id."' AND 
 												ejercicio_id = '".Session :: get_data( 'eje.id')."'");
 		
 		$dependencia = new Dependencia();
 		$dependencia = $dependencia->find($depcontacto->dependencia_id);
 		
 		if($dependencia->esUDG()){
 			$fiscal = $dependencia->fiscal();
 			return $fiscal->razon;
 		}else{
 			return "";
 		}
 		
 	}
 	
 	public function unionDeProveedoresYDependencias(){
 		$t = 
			"( " .
				"SELECT " .
					"'D' as institucion_tipo, " .
					"dependencia.id AS institucion_id, " .
					"dependencia.nombre AS institucion_nombre, " .
					"dependencia.ejercicio_id, " .
					"contacto.id, " .
					"contacto.cargo, " .
					"contacto.nombre, " .
					"contacto.ap, " .
					"contacto.am, " .
					"contacto.titulo, " .
					"contacto.tel, " .
					"contacto.cel, " .
					"contacto.domicilio, " .
					"contacto.trunk, " .
					"contacto.mail, " .
					"contacto.sexo, " .
					"contacto.observaciones " .
				"FROM " .
					"contacto " .
					"Inner Join depcontacto ON contacto.id = depcontacto.contacto_id " .
					"Inner Join dependencia ON dependencia.id = depcontacto.dependencia_id " .
			") " .
			"UNION ALL " .
			"( " .
				"SELECT " .
					"'P' as institucion_tipo, " .
					"proveedor.id AS institucion_id, " .
					"proveedor.nombre AS institucion_nombre, " .
					"proveedor.ejercicio_id, " .
					"contacto.id, " .
					"contacto.cargo, " .
					"contacto.nombre, " .
					"contacto.ap, " .
					"contacto.am, " .
					"contacto.titulo, " .
					"contacto.tel, " .
					"contacto.cel, " .
					"contacto.domicilio, " .
					"contacto.trunk, " .
					"contacto.mail, " .
					"contacto.sexo, " .
					"contacto.observaciones " .
				"FROM " .
					"contacto " .
					"Inner Join provcontacto ON contacto.id = provcontacto.contacto_id " .
					"Inner Join proveedor ON proveedor.id = provcontacto.proveedor_id " .
			") ";
		return $t;
 	}
 }
?>
