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

/**
 * Listas ACL (Access Control List)
 * 
 * La Lista de Control de Acceso o ACLs (del inglés, Access Control List) 
 * es un concepto de seguridad informática usado para fomentar la separación 
 * de privilegios. Es una forma de determinar los permisos de acceso apropiados
 * a un determinado objeto, dependiendo de ciertos aspectos del proceso
 * que hace el pedido.
 * 
 * Cada lista ACL contiene una lista de Roles, unos resources y unas acciones de
 * acceso;
 * 
 * $roles = Lista de Objetos Acl_Role de Roles de la Lista
 * $resources = Lista de Objetos Acl_Resource que van a ser controlados
 * $access = Contiene la lista de acceso 
 * $role_inherits = Contiene la lista de roles que son heradados por otros
 * $resource_names = Nombres de Resources
 * $roles_names = Nombres de Resources
 * 
 * @access Public
 */
class Acl {
	
	private $roles_names = array();
	private $roles = array();
	private $resources = array();
	public $access = array();
	private $role_inherits = array();
	private $resources_names = array('*');
	private $access_list = array('*' => array('*'));

	/**
	 * Agrega un Rol a la Lista ACL
	 * 
	 * $roleObject = Objeto de la clase AclRole para agregar a la lista
	 * $access_inherits = Nombre del Role del cual hereda permisos ó array del grupo
	 * de perfiles del cual hereda permisos
	 * 
	 * Ej:
	 * $acl->add_role(new Acl_Role('administrador'), 'consultor');
	 *
	 * @param string $roleObject
	 * @return boolean
	 */
	public function add_role(Acl_Role $roleObject, $access_inherits=''){		
		if(in_array($roleObject->name, $this->roles_names)){
			Flash::warning("El Rol '{$roleObject->name}' ya existe en la lista");	
			return false;
		}
		$this->roles[] = $roleObject;
		$this->roles_names[] = $roleObject->name;
		$this->access[$roleObject->name]['*']['*'] = 'A';
		if($access_inherits){
			$this->add_inherit($roleObject->name, $access_inherits);
		}
	}
	
	/**
	 * Hace que un rol herede los accesos de otro rol
	 *
	 * @param string $role
	 * @param string $role_to_inherit
	 */
	public function add_inherit($role, $role_to_inherit){
		if(!in_array($role, $this->roles_names)){
			Flash::warning("El Rol '{$role}' no existe en la lista");	
			return false;
		}
		if($role_to_inherit!=''){
			if(is_array($role_to_inherit)){
				foreach($role_to_inherit as $rol_in){
					if($rol_in==$role){
						Flash::warning("El Rol '{$rol_in}' no se puede heredar asi mismo");	
						return false;	
					}
					if(!in_array($rol_in, $this->roles_names)){
						Flash::warning("El Rol '{$rol_in}' no existe en la lista");	
						return false;
					}
					$this->role_inherits[$role][] = $role_in;
				}
				$this->rebuild_access_list();
			} else {
				if($role_to_inherit==$role){
					Flash::warning("El Rol '{$role}' no se puede heredar asi mismo");	
					return false;	
				}
				if(!in_array($role_to_inherit, $this->roles_names)){
					Flash::warning("El Rol '{$role_to_inherit}' no existe en la lista");	
					return false;
				}
				$this->role_inherits[$role][] = $role_to_inherit;
				$this->rebuild_access_list();
			}
		} else {
			Flash::warning("Debe especificar un rol a heredar en Acl::add_inherit");	
			return false;
		}
	}
	
	/**
	 *
	 * Verifica si un rol existe en la lista o no
	 *
	 * @param string $role_name
	 * @return boolean
	 */
	public function is_role($role_name){
		return in_array($role_name, $this->roles_names);
	}
	
	/**
	 *
	 * Verifica si un resource existe en la lista o no
	 *
	 * @param string $resource_name
	 * @return boolean
	 */
	public function is_resource($resource_name){
		return in_array($resource_name, $this->resources_names);
	}

	/**
	 * Agrega un  a la Lista ACL
	 * 
	 * Resource_name puede ser el nombre de un objeto concreo, por ejemplo
	 * consulta, buscar, insertar, valida etc ó una lista de ellos
	 * 
	 * Ej:
	 * 
	 * Agregar un resource a la lista:
	 * $acl->add_resource(new AclResource('clientes'), 'consulta');
	 * 
	 * Agregar Varios resources a la lista:
	 * $acl->add_resource(new AclResource('clientes'), 'consulta', 'buscar', 'insertar');
	 * 
	 * @param AclResource $resource
	 * @return boolean
	 */
	public function add_resource(Acl_Resource $resource){
		if(!in_array($resource->name, $this->resources)){
			$this->resources[] = $resource;
			$this->access_list[$resource->name] = array();
			$this->resources_names[] = $resource->name;
		}
		if(func_num_args()>1){
			$access_list = func_get_args();
			unset($access_list[0]);
			$this->add_resource_access($resource->name, $access_list);	
		}		
	}
	
	/**
	 * Agrega accesos a un Resource 
	 *
	 * @param $resource
	 * @param $access_list
	 */
	public function add_resource_access($resource, $access_list){
		
		if(is_array($access_list)){
			foreach($access_list as $access_name) {				
				if(!in_array($access_name, $this->access_list[$resource])){
					$this->access_list[$resource][] = $access_name;
				}
			}
		} else {
			if(!in_array($access_list, $this->access_list[$resource])){
				$this->access_list[$resource][] = $access_list;
			}
		}
		
	}
	
	/**
	 * Elimina un acceso del resorce
	 *
	 * @param string $resource
	 * @param mixed $access_list
	 */
	public function drop_resource_access($resource, $access_list){
		
		if(is_array($access_list)){
			foreach($access_list as $access_name) {				
				if(in_array($access_name, $this->access_list[$resource])){					
					foreach($this->access_list[$resource] as $i => $access){
						if($access==$access_name){
							unset($this->access_list[$resource][$i]);
						}
					}
				}
			}
		} else {
			if(in_array($access_list, $this->access_list[$resource])){
				foreach($this->access_list[$resource] as $i => $access){
					if($access==$access_list){
						unset($this->access_list[$resource][$i]);
					}
				}
			}
		}
		$this->rebuild_access_list();
		
	}
	
	/**
	 * Agrega un acceso de la lista de resources a un rol 
	 *
	 * Utilizar '*' como comodín
	 *  
	 * Ej:
	 * 
	 * Acceso para invitados a consultar en clientes
	 * $acl->allow('invitados', 'clientes', 'consulta');
	 * 
	 * Acceso para invitados a consultar e insertar en clientes
	 * $acl->allow('invitados', 'clientes', array('consulta', 'insertar'));
	 *
	 * Acceso para cualquiera a visualizar en productos
	 * $acl->allow('*', 'productos', 'visualiza');
	 * 
	 * Acceso para cualquiera a visualizar en cualquier resource
	 * $acl->allow('*', '*', 'visualiza');
	 *  
	 * @param string $role
	 * @param string $resource
	 * @param mixed $access
	 */	
	public function allow($role, $resource, $access){
		
		if(!in_array($role, $this->roles_names)){
			Flash::error("No existe el rol '$role' en la lista");
			return;
		}
		
		if(!in_array($resource, $this->resources_names)){
			Flash::error("No existe el resource '$resource' en la lista");
			return;		
		}

		if(is_array($access)){
			foreach($access as $acc){
				if(!in_array($acc, $this->access_list[$resource])){
					Flash::error("No existe el acceso '$acc' en el resource '$resource' de la lista");
					return false;	
				}
			}
			foreach($access as $acc){
				$this->access[$role][$resource][$acc] = 'A';				
			}						
		} else {		
			if(!in_array($access, $this->access_list[$resource])){
				Flash::error("No existe el acceso '$acc' en el resource '$resource' de la lista");
				return false;	
			}
			$this->access[$role][$resource][$access] = 'A';		
			$this->rebuild_access_list();	
		}
	}
	
	/**
	 * Denegar un acceso de la lista de resources a un rol 
	 *
	 * Utilizar '*' como comodín
	 *  
	 * Ej:
	 * 
	 * Denega acceso para invitados a consultar en clientes
	 * $acl->deny('invitados', 'clientes', 'consulta');
	 * 
	 * Denega acceso para invitados a consultar e insertar en clientes
	 * $acl->deny('invitados', 'clientes', array('consulta', 'insertar'));
	 *
	 * Denega acceso para cualquiera a visualizar en productos
	 * $acl->deny('*', 'productos', 'visualiza');
	 * 
	 * Denega acceso para cualquiera a visualizar en cualquier resource
	 * $acl->deny('*', '*', 'visualiza');
	 *  
	 * @param string $role
	 * @param string $resource
	 * @param mixed $access
	 */	
	public function deny($role, $resource, $access){

		if(!in_array($role, $this->roles_names)){
			Flash::error("No existe el rol '$role' en la lista");
			return;
		}
		
		if(!in_array($resource, $this->resources_names)){
			Flash::error("No existe el resource '$resource' en la lista");
			return;		
		}
				
		if(is_array($access)){
			foreach($access as $acc){
				if(!in_array($acc, $this->access_list[$resource])){
					Flash::error("No existe el acceso '$access' en el resource '$resource' de la lista");
					return false;	
				}
			}
			foreach($access as $acc){
				$this->access[$role][$resource][$acc] = 'D';				
			}			
		} else {		
			if(!in_array($access, $this->access_list[$resource])){
				Flash::error("No existe el acceso '$access' en el resource '$resource' de la lista");
				return false;	
			}
			$this->access[$role][$resource][$access] = 'D';		
			$this->rebuild_access_list();
		}		
	}
	
	/**
	 * Devuelve true si un $role, tiene acceso en un resource
	 * 
	 * //Andres tiene acceso a insertar en el resource productos
	 * $acl->is_allowed('andres', 'productos', 'insertar');
	 * 
	 * //Invitado tiene acceso a editar en cualquier resource?
	 * $acl->is_allowed('invitado', '*', 'editar');
	 * 
	 * //Invitado tiene acceso a editar en cualquier resource?
	 * $acl->is_allowed('invitado', '*', 'editar');
	 *
	 * @param string $role
	 * @param string $resource
	 * @param mixed $access
	 * @return boolean
	 */
	public function is_allowed($role, $resource, $access_list){
		
		if(!in_array($role, $this->roles_names)){
			Flash::error("El rol '$role' no existe en la lista en acl::is_allowed");
			return false;
		}
		if(!in_array($resource, $this->resources_names)){
			Flash::error("El resource '$resource' no existe en la lista en acl::is_allowed");
			return false;
		}		
		if(is_array($access_list)){
			foreach ($access_list as $access) {
				if(!in_array($access, $this->access_list[$resource])){
					Flash::error("No existe en acceso '$access' en el resource '$resource' en acl::is_allowed");
					return false;
				}	
			}
		} else {
			if(!in_array($access_list, $this->access_list[$resource])){
				Flash::error("No existe en acceso '$access_list' en el resource '$resource' en acl::is_allowed");
				return false;
			}
		}
				
		/*foreach($this->access[$role] as ){
			
		}*/
							
	}
	
	/**
	 * Reconstruye la lista de accesos a partir de las herencias
	 * y accesos permitidos y denegados
	 *
	 * @access private
	 */
	private function rebuild_access_list(){
				
		for($i=0;$i<=ceil(count($this->roles)*count($this->roles)/2);$i++){
			foreach($this->roles_names as $role){				
				if(isset($this->role_inherits[$role])){
					foreach($this->role_inherits[$role] as $role_inherit){
						if(isset($this->access[$role_inherit])){
							foreach($this->access[$role_inherit] as $resource_name => $access){
								foreach ($access as $access_name => $value){
									if(!in_array($access_name, $this->access_list[$resource_name])){
										unset($this->access[$role_inherit][$resource_name][$access_name]);
									} else {
										if(!isset($this->access[$role][$resource_name][$access_name])){									
											$this->access[$role][$resource_name][$access_name] = $value;
										}
									}
								}								
							}	
						}	
					}
				}
			}
		}
	}
	
}

/**
 * Esta clase define los roles y parametros
 * de cada uno
 * 
 * @access public
 * 
 */
class Acl_Role{
	
	public $name;
	
	/**
	 * Constructor de la clase Rol
	 *
	 * @param string $name
	 * @return Acl_Role
	 */
	function Acl_Role($name){		
		if($name=='*'){
			Flash::error('Nombre invalido "*" para nombre de Rol en Acl_Role::__constuct');
		}
		$this->name = $name;	
	}
	
	/**
	 * Impide que le cambien el nombre al Rol en el Objeto
	 *
	 * @param string $name
	 * @param string $value	 
	 */
	function __set($name, $value){
		if($name!='name'){
			$this->$name = $value;
		}
	}
	
}

/**
 * Clase para la creación de Resources ACL
 *
 */
class Acl_Resource{
	
	public $name;
	
	/**
	 * Constructor de la clase Rol
	 *
	 * @param string $name
	 * @return Acl_Resource
	 */
	function Acl_Resource($name){
		if($name=='*'){
			Flash::error('Nombre invalido "*" para nombre de Resource en Acl_Resoruce::__constuct');
		}
		$this->name = $name;	
	}
	
	/**
	 * Impide que le cambien el nombre al Rol en el Objeto
	 *
	 * @param string $name
	 * @param string $value	 
	 */
	function __set($name, $value){
		if($name!='name'){
			$this->$name = $value;
		}
	}
	
}

?>