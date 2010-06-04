<?php

class AppModel extends Model {
	var $actsAs = array('Containable', 'BasicValidation', 'JoinsGeneratable');
	// var $cacheQueries = true;
	var $paginateType = null;
	var $options = array();
	
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		if (isset($this->valid)) {
			$this->setValidate($this->valid);
		}
	}
	
	function debug() {
		$args = func_get_args();
		return call_user_func_array('__debug',$args);
	}
	
	function paginate($conditions, $fields, $order, $limit, $page, $recursive, $extra){
		$parameters = compact('conditions', 'fields', 'order', 'limit', 'page');
		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}
		// $parameters = Set::merge($parameters, $this->options($this->paginateType));
		$type = ($this->paginateType === null && isset($extra['type'])) ? $extra['type'] : $this->paginateType;
		$results = $this->find($type, array_merge($parameters, $extra));
		return $results;
	}
   
	function paginateCount($conditions, $recursive, $extra){
		$parameters = compact('conditions');
		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}
		// $parameters = Set::merge($parameters, $this->options($this->paginateType));
		$results = $this->find('count', array_merge($parameters, $extra));
		return $results;
	}
   
	function options($type){
		return isset($this->options[$type]) ? $this->options[$type] : array();
	}
   
	function find($type, $params = array()) {
		switch ($type) {
			default:
				break;
		}
		if (!array_key_exists($type, $this->_findMethods)) {
			$type = 'all';
		}
		return parent::find($type, $params);
	}
	
	function readField($field, $alias = null, $id = null) {
		if ($alias === null) {
			$alias = $this->alias;
		}
		if (isset($this->data[$alias][$field])) {
			return $this->data[$alias][$field];
		}
		
		if ($id === null) {
			$id = $this->id;
			if (is_array($this->id)) {
				$id = $this->id[0];
			}
		}

		if ($id === null || $id === false) {
			return null;
		}

		
		$contain = $alias === $this->alias ? false : array($alias);
		$fields = "$alias.$field";
		$conditions = array($this->alias . '.' . $this->primaryKey => $id);
		
		$result = $this->find('first', compact('contain', 'fields', 'conditions'));
		return $result[$alias][$field];
	}
}
