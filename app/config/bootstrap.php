<?php
if(!function_exists('__debug')){
	function __debug(){
		$argc = func_num_args();
		$args = func_get_args();
		if($argc == 0) return false;
		
		global $__session;
		if(Configure::read() == 0){
			return false;
		}
		$class = 'CakeSession';
		if($__session === null){
			if(ClassRegistry::isKeySet($class)){
				$__session =& ClassRegistry::getObject($class);
			}else{
				if(!class_exists($class)){
					App::import('Core',$class);
				}
				$__session =& new $class;
				ClassRegistry::addObject($class,$__session);
			}
		}
		
		$session =& $__session;
		
		if(!$session->started()){
			$session->start();
		}
		
		$debugs = array();
		if($session->check('debug.printable')){
			$debugs = $session->read('debug.printable');
		}
		
		$debugger =& Debugger::getInstance();
		
		$trace = $debugger->trace(aa('start',2,'format','array'));
		while($trace[0]['function'] === __FUNCTION__
			 || false !== strpos($trace[0]['function'],'call_user_func') ){
			array_shift($trace);
		}
		
		$debug = compact('args','trace');
		
		$debugs = array_merge($debugs,array($debug));
		// debug(compact('prints','args'));
		$session->write('debug.printable',$debugs);
		
		return true;
	}
}

if(!function_exists('action')){
	function action($action){
		$args = func_get_args();
		$argc = func_num_args();
		switch($argc){
			case  0: return null; break;
			case  1: return array('action'     => $args[0]); break;
			case  2: return array('controller' => $args[0] , 'action' => $args[1]); break;
			case  3: return array('controller' => $args[0] , 'action' => $args[1] , $args[2]); break;
			default:
				$controller = array_shift($args);
				$action     = array_shift($args);
				return array_merge($args,compact('controller','action'));
				break;
		}
		return compact('action');
	}
}

?>