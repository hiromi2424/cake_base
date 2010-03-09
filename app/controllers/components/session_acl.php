<?php  
/** 
 * ACL Caching. 
 * 
 * Yet another take at Caching ACL queries, now using Session. 
 * Adapted from http://www.nabble.com/ACL-Auth-Speed-Issues-td21386047.html 
 * and bits and pieces taken from cached_acl.php 
 * 
 * It also extends ACL with some nifty functions for easier and simpler code. 
 * 
 * Cake's ACL doesn't cache anything. For better performance, we 
 * put results of check into session. Only ::check() is wrapped, 
 * other functions are simply piped to the parent Acl object, 
 * though it can be handy to wrap these too in future. 
 * 
 * @author macduy 
 */ 
App::import('Component', 'Acl'); 
App::import('component', 'Session'); 
class SessionAclComponent extends AclComponent 
{ 

	function initialize(&$controller) 
	{ 
		$this->master =& $controller; 
		$controller->Acl =& $this; 
		$this->Session = new SessionComponent(); 
	} 
	 
	function check($aro, $aco, $action = "*") 
	{ 
		$path = $this->__cachePath($aro, $aco, $action); 
		if ($this->Session->check($path)) 
		{ 
			return $this->Session->read($path); 
		} else 
		{ 
			$check = parent::check($aro, $aco, $action); 
			$this->Session->write($path, $check); 
			return $check; 
		} 
	} 

	/** 
	 * Allow 
	 */ 
	function allow($aro, $aco, $action = "*") 
	{ 
		parent::allow($aro, $aco, $action); 
		$this->__delete($aro, $aco, $action); 
	} 

	/** 
	 * Deny method. 
	 */ 
	function deny($aro, $aco, $action = "*") 
	{ 
		parent::deny($aro, $aco, $action); 
		$this->__delete($aro, $aco, $action); 
	} 

	/** 
	 * Inherit method. 
	 * 
	 * This method overrides and uses the original 
	 * method. It only adds cache to it. 
	 * 
	 * @param string $aro ARO 
	 * @param string $aco ACO 
	 * @param string $action Action (defaults to *) 
	 * @access public 
	 */ 
	function inherit($aro, $aco, $action = "*") 
	{ 
		parent::inherit($aro, $aco, $action); 
		$this->__delete($aro, $aco, $action); 
	} 

	/** 
	 * Grant method. 
	 * 
	 * This method overrides and uses the original 
	 * method. It only adds cache to it. 
	 * 
	 * @param string $aro ARO 
	 * @param string $aco ACO 
	 * @param string $action Action (defaults to *) 
	 * @access public 
	 */ 
	function grant($aro, $aco, $action = "*") 
	{ 
		parent::grant($aro, $aco, $action); 
		$this->__delete($aro, $aco, $action); 
	} 

	/** 
	 * Revoke method. 
	 * 
	 * This method overrides and uses the original 
	 * method. It only adds cache to it. 
	 * 
	 * @param string $aro ARO 
	 * @param string $aco ACO 
	 * @param string $action Action (defaults to *) 
	 * @access public 
	 */ 
	function revoke($aro, $aco, $action = "*") 
	{ 
		parent::revoke($aro, $aco, $action); 
		$this->__delete($aro, $aco, $action); 
	} 

	/** 
	 * Returns a unique, dot separated path to use as the cache key. Copied from CachedAcl.
	 * 
	 * @param string $aro ARO 
	 * @param string $aco ACO 
	 * @param boolean $acoPath Boolean to return only the path to the ACO or the full path to the permission.
	 * @access private 
	 */ 
	function __cachePath($aro, $aco, $action, $acoPath = false) 
	{ 
		if ($action != "*") 
		{ 
			$aco .= '/' . $action; 
		} 
		$path = Inflector::slug($aco); 

		if (!$acoPath) 
		{ 
			if (!is_array($aro)) 
			{ 
				$_aro = explode(':', $aro); 
			} elseif (Set::countDim($aro) > 1) 
			{ 
				$_aro = array(key($aro), current(current($aro))); 
			} else 
			{ 
				$_aro = array_values($aro); 
			} 
			$path .= '.' . Inflector::slug(implode('.', $_aro)); 
		} 

		return "Acl.".$path; 
	} 

	/** 
	 * Deletes the cache reference in Session, if found 
	 */ 
	 function __delete($aro, $aco, $action) { 
		 $key = $this->__cachePath($aro, $aco, $action, true); 
		 if ($this->Session->check($key)) 
		 { 
			 $this->Session->delete($key); 
		 } 
	 } 

	 /** 
	  * Deletes the whole cache from the Session variable 
	  */ 
	 function flushCache() { 
		 $this->Session->delete('Acl'); 
	 } 

	 /** 
	  * Checks that ALL of given pairs of aco-action are satisfied 
	  */ 
	 function all($aro, $pairs) { 
		 foreach ($pairs as $aco => $action) 
		 { 
			 if (!$this->check($aro,$aco,$action)) 
			 { 
				 return false; 
			 } 
		 } 
		 return true; 
	 } 


	 /** 
	  * Checks that AT LEAST ONE of given pairs of aco-action is satisfied 
	  */ 
	 function one($aro, $pairs) { 
		 foreach ($pairs as $aco => $action) 
		 { 
			 if ($this->check($aro,$aco,$action)) 
			 { 
				 return true; 
			 } 
		 } 
		 return false; 
	 } 
	  
	 /** 
	  * Returns an array of booleans for each $aco-$aro pair 
	  */ 
	 function can($aro, $pairs) { 
		 $can = array(); 
		 $i = 0; 
		 foreach ($pairs as $aco => $action) 
		 { 
			 $can[$i] = $this->check($aro,$aco,$action); 
			 $i++; 
		 } 
		 return $can; 
	 } 
} 
?>