<?php

class AppModel extends Model {
	var $actsAs = array('Containable','BasicValidation');
	// var $cacheQueries = true;
	
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		if(isset($this->valid)){
			$this->setValidate($this->valid);
		}
	}
	
	function beforeValidate(){
		return true;
	}
	
	function debug(){
		$args = func_get_args();
		return call_user_func_array('__debug',$args);
	}
	
	function find($type,$params = array()){
		switch($type){
			case 'matches':
				$type = 'all';
				$habtm   = $this->hasAndBelongsToMany;
				$hasMany = $this->hasMany;
				$hasOne  = $this->hasOne;
				$belongsTo = $this->belongsTo;
				
				$joins = isset($params['joins'])?$params['joins']:array();
				$models = isset($params['models'])?$params['models']:true;
				
				if(!empty($habtm)){
					foreach($habtm as $Model => $assoc){
						if($models !== true && !in_array($Model,$models)){
							continue;
						}
						$bind = "{$assoc['with']}.{$assoc['foreignKey']} = {$this->alias}.{$this->primaryKey}";

						$joins[] = array(
							'table' => $assoc['joinTable'],
							'alias' => $assoc['with'],
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions'=> array($bind)
						);
						$bind = $Model . '.' . $this->$Model->primaryKey . ' = ';
						$bind .= "{$assoc['with']}.{$assoc['associationForeignKey']}";

						$joins[] = array(
							'table' => $this->$Model->table,
							'alias' => $Model,
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions'=> array($bind),
						);
						$this->unbindModel(array('hasAndBelongsToMany' => array($Model)));
					}
				}
				if(!empty($hasMany)){
					foreach($hasMany as $Model => $assoc){
						if($models !== true && !in_array($Model,$models)){
							continue;
						}
						$bind = "{$this->alias}.{$this->primaryKey} = {$Model}.{$assoc['foreignKey']}";
						$joins[] = array(
							'table' => $this->$Model->table,
							'alias' => $Model,
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions'=> array($bind)
						);
						$this->unbindModel(array('hasMany' => array($Model)));
					}
				}
				if(!empty($hasOne)){
					foreach($hasOne as $Model => $assoc){
						if($models !== true && !in_array($Model,$models)){
							continue;
						}
						$bind = "{$this->alias}.{$this->primaryKey} = {$Model}.{$assoc['foreignKey']}";
						$joins[] = array(
							'table' => $this->$Model->table,
							'alias' => $Model,
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions'=> array($bind)
						);
						$this->unbindModel(array('hasOne' => array($Model)));
					}
				}
				if(!empty($belongsTo)){
					foreach($belongsTo as $Model => $assoc){
						if($models !== true && !in_array($Model,$models)){
							continue;
						}
						$bind = "{$Model}.{$this->$Model->primaryKey} = {$this->alias}.{$assoc['foreignKey']}";
						$joins[] = array(
							'table' => $this->$Model->table,
							'alias' => $Model,
							'type' => 'LEFT',
							'foreignKey' => false,
							'conditions'=> array($bind)
						);
						$this->unbindModel(array('belongsTo' => array($Model)));
					}
				}
				$params['joins'] = $joins;
		}
		return parent::find($type, $params);
	}
	
	function readField($field,$alias = null,$id = null){
		if($alias === null){
			$alias = $this->alias;
		}
		if(isset($this->data[$alias][$field])){
			return $this->data[$alias][$field];
		}
		
		if($id === null){
			$id = $this->id;
			if (is_array($this->id)) {
				$id = $this->id[0];
			}
		}

		if ($id === null || $id === false) {
			return null;
		}

		
		$contain = $alias === $this->alias?false:array($alias);
		$fields = "$alias.$field";
		$conditions = array($this->alias . '.' . $this->primaryKey => $id);
		
		$result = $this->find('first',compact('contain','fields','conditions'));
		return $result[$alias][$field];
	}
	
	
	// override
	// change: delete all old links -> if exists same link,dont delete it

	function __saveMulti($joined, $id, &$db) {
		foreach ($joined as $assoc => $data) {

			if (isset($this->hasAndBelongsToMany[$assoc])) {
				list($join) = $this->joinModel($this->hasAndBelongsToMany[$assoc]['with']);

				$isUUID = !empty($this->{$join}->primaryKey) && (
						$this->{$join}->_schema[$this->{$join}->primaryKey]['length'] == 36 && (
						$this->{$join}->_schema[$this->{$join}->primaryKey]['type'] === 'string' ||
						$this->{$join}->_schema[$this->{$join}->primaryKey]['type'] === 'binary'
					)
				);

				$newData = $newValues = array();
				$primaryAdded = false;

				$fields =  array(
					$db->name($this->hasAndBelongsToMany[$assoc]['foreignKey']),
					$db->name($this->hasAndBelongsToMany[$assoc]['associationForeignKey'])
				);

				$idField = $db->name($this->{$join}->primaryKey);
				if ($isUUID && !in_array($idField, $fields)) {
					$fields[] = $idField;
					$primaryAdded = true;
				}


				$oldLinks = array();
				$toDeleteLinks = array();

				if ($this->hasAndBelongsToMany[$assoc]['unique']) {
					$conditions = array_merge(
						array($join . '.' . $this->hasAndBelongsToMany[$assoc]['foreignKey'] => $id),
						(array)$this->hasAndBelongsToMany[$assoc]['conditions']
					);
					$links = $this->{$join}->find('all', array(
						'conditions' => $conditions,
						'recursive' => empty($this->hasAndBelongsToMany[$assoc]['conditions']) ? -1 : 0,
						'fields' => $this->hasAndBelongsToMany[$assoc]['associationForeignKey']
					));

					$associationForeignKey = "{$join}." . $this->hasAndBelongsToMany[$assoc]['associationForeignKey'];
					$oldLinks = Set::extract($links, "{n}.{$associationForeignKey}");
					
					$toDeleteLinks = array();
					
					$newLinks = array();
					foreach((array)$data as $row){
						$newLink = null;
						
						if ((is_string($row) && (strlen($row) == 36 || strlen($row) == 16)) || is_numeric($row)) {
							$newLink = $row;
						} elseif (isset($row[$this->hasAndBelongsToMany[$assoc]['associationForeignKey']])) {
							if(isset($row[$this->{$join}->primaryKey])){
								continue;
							}
							$newLink = $row[$this->hasAndBelongsToMany[$assoc]['associationForeignKey']];
						} elseif (isset($row[$join]) && isset($row[$join][$this->hasAndBelongsToMany[$assoc]['associationForeignKey']])) {
							if(isset($row[$join][$this->{$join}->primaryKey])){
								continue;
							}
							$newLink = $row[$join][$this->hasAndBelongsToMany[$assoc]['associationForeignKey']];
						}
						
						if(!$newLink){
							continue;
						}
						
						$newLinks[] = $newLink;
					}
					$newLinks = array_unique($newLinks);
					
					$toDeleteLinks = array_diff($oldLinks,$newLinks);
					
					if (!empty($toDeleteLinks)) {
 						$conditions[$associationForeignKey] = $toDeleteLinks;
						$db->delete($this->{$join}, $conditions);
					}
				}

				foreach ((array)$data as $row) {
					if ((is_string($row) && (strlen($row) == 36 || strlen($row) == 16)) || is_numeric($row)) {
						if(in_array($row,$oldLinks)){
							continue;
						}
						$values = array(
							$db->value($id, $this->getColumnType($this->primaryKey)),
							$db->value($row)
						);
						if ($isUUID && $primaryAdded) {
							$values[] = $db->value(String::uuid());
						}
						$values = implode(',', $values);
						$newValues[] = "({$values})";
						unset($values);
					} elseif (isset($row[$this->hasAndBelongsToMany[$assoc]['associationForeignKey']])) {
						if(in_array($row[$this->hasAndBelongsToMany[$assoc]['associationForeignKey']],$oldLinks)
							&& !isset($row[$this->{$join}->primaryKey]) ){
							continue;
						}
						$newData[] = $row;
					} elseif (isset($row[$join]) && isset($row[$join][$this->hasAndBelongsToMany[$assoc]['associationForeignKey']])) {
						if(in_array($row[$join][$this->hasAndBelongsToMany[$assoc]['associationForeignKey']],$oldLinks)
							 && !isset($row[$join][$this->{$join}->primaryKey]) ){
							continue;
						}
						$newData[] = $row[$join];
					}
				}
				

				if (!empty($newData)) {
					foreach ($newData as $data) {
						$data[$this->hasAndBelongsToMany[$assoc]['foreignKey']] = $id;
						$this->{$join}->create($data);
						$this->{$join}->save();
					}
				}

				if (!empty($newValues)) {
					$fields =  implode(',', $fields);
					$db->insertMulti($this->{$join}, $fields, $newValues);
				}
				
			}
		}
	}
}

?>