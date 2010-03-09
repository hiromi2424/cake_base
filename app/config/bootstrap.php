<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
 
 
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