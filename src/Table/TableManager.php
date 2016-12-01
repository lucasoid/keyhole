<?php

namespace Keyhole\Table;

class TableManager {
	
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;
	
	/**
	 * 
	 * @var integer
	 */
	private $id;
	
	/**
	 * 
	 * @var \Keyhole\Registry\Registry
	 */
	private $registry;
	
	/**
	 * 
	 * @var array
	 */
	private $columns;
	
	/**
	 * 
	 * @var \Keyhole\Migration\MigrationManager
	 */
	private $migrationManager;
	
	/**
	 * 
	 * @var \Keyhole\Registry\RegisteredTable
	 */
	private $registeredTable;
	
	/**
	 * 
	 * @var array	array of \Keyhole\Registry\RegisteredField objects
	 */
	private $registeredFields;
	
	/**
	 *
	 * @var array	array of \Keyhole\Registry\RegisteredAccess objects
	 */
	private $registeredAccess;
	/**
	 * 
	 * @param unknown $id
	 * @param unknown $registry
	 */
	public function __construct($conn, $id, $registry) {
		
		$this->conn = $conn;
		$this->id = $id;
		$this->registry = $registry;
				
		$this->registeredTable = $this->registry->getTable($id);
		
		$conditions = array('where'=>array(array('field'=>$this->registry->getFieldMapper()->mapPropertyToColumn('tableId'), 'operator'=>'=', 'value'=>'?')), 'params'=>array($id));
		$this->registeredFields = $this->registry->listFields($conditions);
		
		$conditions = array('where'=>array(array('field'=>$this->registry->getAccessMapper()->mapPropertyToColumn('tableId'), 'operator'=>'=', 'value'=>'?')), 'params'=>array($id));
		$this->registeredAccess = $this->registry->listAccess($conditions);
		
		$this->setColumns();
		
		if(null != $this->registeredTable && !empty($this->columns)) {
			$this->migrationManager = new \Keyhole\Migration\MigrationManager($conn, $this->getTableName(), $this->columns);
		}
		
		if($this->migrationsNeeded()) {
			$this->doMigrations();
		}
		
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getTableName() {
		if(isset($this->registry) && isset($this->registeredTable)) {
			$prefix = $this->registry->getPrefix();	
			return $prefix . $this->registeredTable->getName();
		}
		return '';
	}
	
	/**
	 *
	 * @return array
	 */
	public function getFields() {
		return $this->registeredFields;
	}
	
	/**
	 *
	 * @return array
	 */
	public function getAccess() {
		return $this->registeredAccess;
	}
	/**
	 * 
	 * @return boolean
	 */
	public function migrationsNeeded() {
		if(null != ($this->migrationManager)) {
			return $this->migrationManager->migrationsNeeded();
		}
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function doMigrations() {
		if(null != ($this->migrationManager)) {
			return $this->migrationManager->doMigrations();
		}
	}
	
	public function findById($id) {
		$conditions = array();
		$conditions['where'][] = array('field'=>'id', 'operator'=>'=', 'value'=>'?');
		$conditions['maxResults'] = 1;
		$conditions['params'] = array($id);
		$rows = $this->select($conditions);
		if(count($rows) > 0) {
			return $rows[0];
		}
		return null;
	}
	/**
	 * 
	 * @param array $conditions
	 * @return array 	provides an array of populated \Keyhole\Table\Row objects
	 */
	public function select(array $conditions = array()) {
		
		$qb = $this->conn->createQueryBuilder();
				
		$select = !empty($conditions['select']) ? $conditions['select'] : $this->getColumnNames();
	
		foreach($select as $k=>$v) {
			$select[$k] = $this->getTableName() . '.' . $v;
		}
		
		$qb->select($select)->from($this->getTableName());
		
		if(!empty($conditions['where']) && is_array($conditions['where'])) {
			foreach($conditions['where'] as $where) {
				if(is_array($where)) {
					$qb->andWhere($where['field'] . ' ' . $where['operator'] . ' ' .$where['value']);
				}
			}
		}
		
		if(!empty($conditions['params'])) {
			foreach($conditions['params'] as $k=>$v) {
				$qb->setParameter($k, $v);
			}
		}
	
		if(!empty($conditions['join'])) {
			foreach($conditions['join'] as $join) {
				$qb->join($this->getTableName(), $join['table'], $join['table'], $join['on']);
			}
		}
		
		if(!empty($conditions['firstResult'])) {
			$qb->setFirstResult($conditions['firstResult']);
		}
		
		if(!empty($conditions['maxResults'])) {
			$qb->setMaxResults($conditions['maxResults']);
		}
		
		$rows = array();
		
		try {
			$stmt = $qb->execute();
			
			while($result = $stmt->fetch()) {
				$row = new Row();
				$row->setId($result['id']);
				$row->setData($result);
				$rows[] = $row;
			}
		}
		catch(\Exception $e) {
			
		}
		
		return $rows;
	}
	
	public function getNewRow() {
		return new Row();
	}
	/**
	 * 
	 * @param \Keyhole\Table\Row $row
	 * @return \Keyhole\Table\Row|null
	 */
	public function save(Row $row) {
		$qb = $this->conn->createQueryBuilder();
		
		$data = $row->getData();
		$columns = $this->getColumnNames();
		
		$params = array();
		
		foreach($columns as $column) {
			if($column != 'id') {
				$params[] = isset($data[$column]) ? $data[$column] : '';
			}
		}
			
		if(null != $row->getId()) {
			$params[] = $row->getId();
			$qb->update($this->getTableName());
			foreach($columns as $column) {
				if($column != 'id') {
					$qb->set($column, '?');
					$qb->where('id = ?');
				}
			}
		}
		else {
			$qb->insert($this->getTableName());
			foreach($columns as $column) {
				if($column != 'id') {
					$qb->setValue($column, '?');
				}
			}
		}
		foreach($params as $index=>$param) {
			$qb->setParameter($index, $param);
		}
		try {
			if($stmt = $qb->execute()) {
				if(null == $row->getId() && $id = $this->conn->lastInsertId()) {
					$row->setId($id);
				}
				return $row;
			}
		}
		catch(\Exception $e) {
			
		}
		return null;
		
	}
	
	/**
	 * 
	 * @param \Keyhole\Table\Row $row
	 * @return boolean
	 */
	public function delete(Row $row) {
		$qb = $this->conn->createQueryBuilder();
		
		if(null != $row->getId()) {
			$qb
				->delete($this->getTableName())
				->where('id = ?')
				->setParameter(0, $row->getId())
			;
			try {
				if($stmt = $qb->execute()) {
					return true;
				}
			}
			catch(\Exception $e) {
				
			}
			return false;
		}
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getColumnNames() {
		
		$names = array();
		
		$this->setColumns();
		foreach($this->columns as $column) {
			if(is_array($column) && !empty($column['name'])) {
				$names[] = $column['name'];
			}
		}
		
		return $names;
		
	}
	
	private function setColumns() {
		
		if(!isset($this->columns)) {
			$this->columns = array();
			
			$this->columns[] = array('name'=>'id', 'type'=>'integer', 'options'=>array('autoincrement'=>true), 'primarykey'=>true);
			
			foreach($this->registeredFields as $field) {
				$type = $field->getFieldtype();
				if(TypeMap::typeIsSupported($type)) {
					$options = json_decode($field->getOptions(), true);
					$this->columns[] = array('name'=>$field->getName(), 'type'=>$field->getFieldtype(), 'options'=>$options);
				}
			}
		}
	}
	
	
	
	
	
	
}