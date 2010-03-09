<?php

class TransitionComponent extends Object{
	var $components = array('Session');
	var $action;
	var $messages = array();
	var $nextStep = null;
	var $automation = false;
	var $autoComplete = true;
	var $models = null;
	
	var $_controller;
	
	
	function initialize(&$controller){
		// set default
		$this->messages = array(
			'invalid' => __('Input Data was not able to pass varidation. Please, try again.', true),
			'prev'    => __('Session timed out.', true)
		);
		$this->_controller =& $controller;
		$this->action = $controller->params['action'];
	}
	
	function startup(&$controller){
		if($this->automation){
			if(is_string($this->automation)){
				$this->automation = array($this->automation);
			}
			
			$doAutomate = 
				$this->automation === true || (
					is_array($this->automation) &&
					in_array($this->action,$this->automation
				)
			);
				
			if($doAutomate){
				return $this->automate($this->nextStep);
			}
		}
		return true;
	}
	
	function automate($nextStep,$models = null,$prev = null,$messages = array()){
		$c =& $this->_controller;
		$messages = array_merge($this->messages,$messages);
		
		if($prev !== null){
			if(!$this->checkPrev($prev,$messages['prev'])){
				return false;
			}
		}
		if($models !== null){
			if(!$this->checkData($nextStep,$models,$messages['invalid'])){
				return false;
			}
		}
		return true;
	}
	
	function checkPrev($prev,$message = null,$prevAction = null){
		if(is_array($prev)){
			foreach($prev as $p){
				if(!$this->checkPrev($p,$message,$prevAction)){
					return false;
				}
			}
			return true;
		}
		if($prevAction === null){
			$prevAction = $prev;
		}
		if($message === null){
			$message = $this->messages['prev'];
		}
		if(!$this->Session->check($this->sessionKey($prev))){
			$this->Session->setFlash($message);
			$this->_controller->redirect(array('action'=>$prevAction));
			return false;
		}
		return true;
	}
	
	// @param String key action name
	// @return mixed session data
	function data($key){
		$key = $this->sessionKey($key);
		if($this->Session->check($key)){
			return $this->Session->read($key);
		}
		return null;
	}
	
	function allData(){
		return $this->data(null);
	}
	
	function setData($key,$data){
		return $this->Session->write($this->sessionKey($key),$data);
	}
	
	function sessionKey($key,$cname = null){
		$key   = $key   === null ? "":".$key";
		$cname = $cname === null ? ".".$this->_controller->name:".$cname";
		return 'Transition'.$cname.$key;
	}
	
	function delData($key){
		$key = $this->sessionKey($key);
		if($this->Session->check($key)){
			return $this->Session->delete($key);
		}
	}
	
	function clearData(){
		return $this->delData(null);
	}
	
	function checkData($nextStep = null,$models = null,$message = null,$sessionKey = null){
		$models = $this->_autoLoadModels($models);
		$c =& $this->_controller;
		if($models === null){
			return false;
		}
		if($sessionKey === null){
			$sessionKey = $this->sessionKey($this->action);
		}
		if($message === null){
			$message = $this->messages['invalid'];
		}
		if(!empty($c->data)){
			$this->Session->write($sessionKey,$c->data);
			
			$result = true;
			foreach($models as $model){
				if( !$this->validateModel($model) ){
					$result = false;
				}
			}
			if($result){
				if($nextStep !== null){
					$nextStep = !is_array($nextStep)?array('action'=>$nextStep):$nextStep;
					$c->redirect($nextStep);
				}
			}else{
				$this->Session->setFlash($message);
				return false;
			}
		}elseif($this->autoComplete && $this->Session->check($sessionKey)){
			$c->data = $this->Session->read($sessionKey);
		}
		
		return true;
	}
	
	function validateModel($model){
		if($this->models === null){
			$this->_autoLoadModels(null);
		}
		if($model === null){
			foreach($this->models as $model){
				$result = $this->validateModel($model);
				if(!$result){
					return false;
				}
			}
		}
		
		$c =& $this->_controller;
		
		if(!is_object($model)){
			$controllerModel = $c->modelClass;
			$modelName = Inflector::classify($model);
			
			$controllerHasModel = in_array($model,$this->models) && 
				(
					property_exists($c,$modelName) ||
					property_exists($c->{$controllerModel},$modelName)
				)
			;
			if( $controllerHasModel ){
				$model = property_exists($c->{$controllerModel},$modelName)?$c->{$controllerModel}->{$modelName}:$c->{$modelName};
				if(get_class($model) == 'AppModel'){
					if(!class_exists($modelName)){
						App::import('Model',$modelName);
					}
					if(!class_exists($modelName)){
						return false;
					}
					$model = new $modelName();
				}
			}else{
				if(!class_exists($modelName)){
					App::import('Model',$modelName);
				}
				if(!class_exists($model)){
					return false;
				}else{
					$model = new $model();
				}
			}
		}
			
		$data = $c->data;
		
		// $model->create();
		// $this->_controller->debug($c->data);
		$result = true;
		
		if(!empty($data)){
			$model->set($data);
			if(!$model->validates()){
				$result = false;
			}
		}
			//var_dump($model->beforeValidate());
			// exit;
		if(!$result){
			$c->debug($model->validationErrors);
		}
		
		return $result;
	}
	
	function _autoLoadModels($models){
		if($models === null){
			$c =& $this->_controller;
			if($c->uses !== false && is_array($c->uses) ){
				$models = $c->uses;
			}elseif($c->modelClass !== null){
				$models = $c->modelClass;
			}
		}
		
		if($models !== null && !is_array($models)){
			$models = array($models);
		}
		return $this->models = $models;
	}

}

?>